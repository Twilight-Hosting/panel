<?php

namespace Pterodactyl\Http\Controllers\Api\Remote\Servers;

use Exception;
use Illuminate\Http\Request;
use Pterodactyl\Models\Node;
use Webmozart\Assert\Assert;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Pterodactyl\Models\Allocation;
use Illuminate\Support\Facades\Log;
use Pterodactyl\Models\ServerTransfer;
use Illuminate\Database\ConnectionInterface;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Exceptions\Http\HttpForbiddenException;
use Pterodactyl\Repositories\Eloquent\ServerRepository;
use Pterodactyl\Repositories\Wings\DaemonServerRepository;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Pterodactyl\Exceptions\Http\Connection\DaemonConnectionException;

use function Laravel\Prompts\error;

class ServerTransferController extends Controller
{
    /**
     * ServerTransferController constructor.
     */
    public function __construct(
        private ConnectionInterface $connection,
        private ServerRepository $repository,
        private DaemonServerRepository $daemonServerRepository,
    ) {
    }

    /**
     * The daemon notifies us about a transfer failure.
     *
     * @throws \Throwable
     */
    public function failure(Request $request, string $uuid): JsonResponse
    {
        $server = $this->repository->getByUuid($uuid);
        $transfer = $server->transfer;
        if (is_null($transfer)) {
            throw new ConflictHttpException('Server is not being transferred.');
        }

        /* @var Node $node */
        Assert::isInstanceOf($node = $request->attributes->get('node'), Node::class);

        // Either node can tell the panel that the transfer has failed. Only the new node
        // can tell the panel that it was successful.
        if (! $node->is($transfer->newNode) && ! $node->is($transfer->oldNode)) {
            throw new HttpForbiddenException('Requesting node does not have permission to access this server.');
        }

        return $this->processFailedTransfer($transfer);
    }

    /**
     * The daemon notifies us about a transfer success.
     *
     * @throws \Throwable
     */
    public function success(Request $request, string $uuid): JsonResponse
    {
        $server = $this->repository->getByUuid($uuid);
        $transfer = $server->transfer;
        if (is_null($transfer)) {
            throw new ConflictHttpException('Server is not being transferred.');
        }

        /* @var Node $node */
        Assert::isInstanceOf($node = $request->attributes->get('node'), Node::class);

        // Only the new node communicates a successful state to the panel, so we should
        // not allow the old node to hit this endpoint.
        if (! $node->is($transfer->newNode)) {
            throw new HttpForbiddenException('Requesting node does not have permission to access this server.');
        }

        if ($server->egg_id == 16)
        {
            try {
                $oldIp = "";
                $newIp = "";
                $oldPort = 0;
                $newPort = 0;

                foreach ($server->allocations as $allocation) {
                    $oldIp = $allocation->ip;
                    $oldPort = $allocation->port;
                    break;
                }

                $newId = $transfer->new_allocation;
                $newAllocation = null;

                foreach ($node->allocations as $allocation) {
                    if ($allocation->id == $newId) {
                        $newAllocation = $allocation;
                        break;
                    }
                }

                if (!is_null($newAllocation)) {
                    $newIp = $newAllocation->ip;
                    $newPort = $newAllocation->port;
                }

                if ($oldIp == "" || $oldPort == 0) {
                    error("Failed to find allocation data for the source of a SL server transfer.");
                }
                else if ($newIp == "" || $newPort == 0) {
                    error("Failed to find allocation data for the target of a SL server transfer.");
                }
                else {
                    $this->sendRequest($oldIp, $oldPort, $newIp, $newPort);
                }
            } catch (Exception $ex) {
                error($ex->getMessage());
                error($ex->getTraceAsString());
            }
        }

        /** @var \Pterodactyl\Models\Server $server */
        $server = $this->connection->transaction(function () use ($server, $transfer) {
            $allocations = array_merge([$transfer->old_allocation], $transfer->old_additional_allocations);

            // Remove the old allocations for the server and re-assign the server to the new
            // primary allocation and node.
            Allocation::query()->whereIn('id', $allocations)->update(['server_id' => null]);
            $server->update([
                'allocation_id' => $transfer->new_allocation,
                'node_id' => $transfer->new_node,
            ]);

            $server = $server->fresh();
            $server->transfer->update(['successful' => true]);

            return $server;
        });

        // Delete the server from the old node making sure to point it to the old node so
        // that we do not delete it from the new node the server was transferred to.
        try {
            $this->daemonServerRepository
                ->setServer($server)
                ->setNode($transfer->oldNode)
                ->delete();
        } catch (DaemonConnectionException $exception) {
            Log::warning($exception, ['transfer_id' => $server->transfer->id]);
        }

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    /**
     * Release all the reserved allocations for this transfer and mark it as failed in
     * the database.
     *
     * @throws \Throwable
     */
    protected function processFailedTransfer(ServerTransfer $transfer): JsonResponse
    {
        $this->connection->transaction(function () use (&$transfer) {
            $transfer->forceFill(['successful' => false])->saveOrFail();

            $allocations = array_merge([$transfer->new_allocation], $transfer->new_additional_allocations);
            Allocation::query()->whereIn('id', $allocations)->update(['server_id' => null]);
        });

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    private function sendRequest(string $oldIp, int $oldPort, string $newIp, int $newPort): void
    {
        $url = 'https://api.scpslgame.com/provider/manageserver.php';
        $data = array(
            'user' => config('secretLaboratory.vhp_user_id'),
            'token' => config('secretLaboratory.vhp_key'),
            'ip' => $oldIp,
            'port' => $oldPort,
            'action' => 'reassign',
            'newip' => $newIp,
            'newport' => $newPort,
        );

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($curl);

        if ($result === false) {
            throw new Exception('Curl error: ' . curl_error($curl));
        }

        $file = fopen("/var/www/pterodactyl/storage/logs/transfer.log", "a");
        fwrite($file, date("Y-m-d h:i:s", time()) . "\n");
        fwrite($file, "$oldIp:$oldPort -> $newIp:$newPort\n");
        fwrite($file, $result . "\n");
        fclose($file);
    }
}
