<?php

namespace Pterodactyl\Http\Controllers\Admin\Servers;

use Illuminate\Container\EntryNotFoundException;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Pterodactyl\Models\Server;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Models\Filters\AdminServerFilter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ServerController extends Controller
{
    /**
     * Returns all the servers that exist on the system using a paginated result set. If
     * a query is passed along in the request it is also passed to the repository function.
     */
    public function index(Request $request): View
    {
        $servers = QueryBuilder::for(Server::query()->with('node', 'user', 'allocation'))
            ->allowedFilters([
                AllowedFilter::exact('owner_id'),
                AllowedFilter::custom('*', new AdminServerFilter()),
            ])
            ->paginate(config()->get('pterodactyl.paginate.admin.servers'));

        return view('admin.servers.index', ['servers' => $servers]);
    }

    /**
     * Export servers list as CSV
     *
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function export(Request $request): BinaryFileResponse
    {
        $path = $this->snapshot_servers();

        // Return file as download
        return response()->download($path, basename($path))->deleteFileAfterSend(true);
    }

    public function snapshot_servers(): string
    {
        $servers = QueryBuilder::for(Server::query()->with('user', 'node', 'egg'));

        // Prepare data for export
        $exportData = [];
        $servers->each(function (Server $server) use (&$exportData) {
            $exportData[] = [
                'ID' => $server->id,
                'Owner' => $server->user->username,
                'Name' => $server->name,
                'Node' => $server->node->name,
                'Egg' => $server->egg->name,
                'CPU' => $server->cpu,
                'Memory' => $server->memory,
                'Disk' => $server->disk,
                'Created' => $server->created_at->format('Y-m-d'),
                'Type' => 'Unset',
            ];
        });

        // Create CSV file
        $filename = 'servers-export-' . date('Y-m-d') . '.csv';

        $dir = storage_path('snapshots/');
        $path = $dir . $filename;

        // Ensure temp directory exists
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        // Open file handle
        $handle = fopen($path, 'w');

        if (!empty($exportData)) {

            // Add UTF-8 BOM for Excel compatibility
            fputs($handle, "\xEF\xBB\xBF");

            // Add headers
            fputcsv($handle, array_keys($exportData[0]));

            // Add data rows
            foreach ($exportData as $row) {
                fputcsv($handle, $row);
            }
        }

        fclose($handle);

        return $path;
    }
}
