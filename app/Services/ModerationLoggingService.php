<?php

namespace Pterodactyl\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Pterodactyl\Models\ModerationLog;
use Pterodactyl\Models\ModerationSettings;

class ModerationLoggingService
{
    public static function log($actionBy, $actionType, $targetType, $targetId, $description, $reason = null, $ipAddress = null, $metadata = [])
    {
        $log = ModerationLog::create([
            'user_id' => $targetId && $targetType === 'user' ? $targetId : null,
            'action_by' => $actionBy,
            'action_type' => $actionType,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'description' => $description,
            'reason' => $reason,
            'ip_address' => $ipAddress,
            'metadata' => $metadata,
        ]);

        $settings = ModerationSettings::getSettings();
        
        if ($settings->discord_webhook_enabled && $settings->discord_webhook_url) {
            try {
                self::sendDiscordWebhook($log->fresh(['actionBy', 'user']), $settings->discord_webhook_url);
            } catch (\Exception $e) {
                Log::error('Failed to send Discord webhook for moderation log: ' . $e->getMessage());
            }
        }

        return $log;
    }

    private static function sendDiscordWebhook($log, $webhookUrl)
    {
        if (!$log) {
            return;
        }

        $color = match($log->action_type) {
            'ban', 'ip_ban', 'block' => 15158332,
            'unban', 'unblock' => 3066993,
            'warn' => 15844367,
            'force_logout' => 16776960,
            'login_success' => 3066993,
            'login_failed', 'login_blocked', 'login_blocked_ban', 'login_blocked_block' => 15158332,
            'ban_deleted', 'warning_deleted', 'block_deleted' => 9807270,
            default => 9807270,
        };

        $actionBy = $log->actionBy;
        $targetUser = $log->user;
        
        $fields = [
            [
                'name' => 'Action Type',
                'value' => ucfirst(str_replace('_', ' ', $log->action_type)),
                'inline' => true,
            ],
        ];

        if ($actionBy) {
            $fields[] = [
                'name' => 'Action By',
                'value' => $actionBy->email . ' (' . $actionBy->username . ')',
                'inline' => true,
            ];
        } else {
            $fields[] = [
                'name' => 'Action By',
                'value' => 'System',
                'inline' => true,
            ];
        }

        if ($targetUser) {
            $fields[] = [
                'name' => 'Target User',
                'value' => $targetUser->email . ' (' . $targetUser->username . ')',
                'inline' => true,
            ];
        } elseif ($log->ip_address && ($log->action_type === 'ip_ban' || $log->target_type === 'ip')) {
            $fields[] = [
                'name' => 'Target IP',
                'value' => $log->ip_address,
                'inline' => true,
            ];
        }

        if ($log->reason) {
            $fields[] = [
                'name' => 'Reason',
                'value' => strlen($log->reason) > 1024 ? substr($log->reason, 0, 1021) . '...' : $log->reason,
                'inline' => false,
            ];
        }

        if ($log->ip_address && !in_array($log->action_type, ['ip_ban']) && $log->target_type !== 'ip') {
            $fields[] = [
                'name' => 'IP Address',
                'value' => $log->ip_address,
                'inline' => true,
            ];
        }

        $fields[] = [
            'name' => 'Timestamp',
            'value' => $log->created_at->format('Y-m-d H:i:s'),
            'inline' => true,
        ];

        $embed = [
            'title' => 'Moderation Action',
            'description' => strlen($log->description) > 4096 ? substr($log->description, 0, 4093) . '...' : $log->description,
            'color' => $color,
            'fields' => $fields,
            'timestamp' => $log->created_at->toIso8601String(),
            'footer' => [
                'text' => 'Log ID: ' . $log->id,
            ],
        ];

        $payload = [
            'embeds' => [$embed],
        ];

        try {
            $response = Http::timeout(5)
                ->post($webhookUrl, $payload);

            if (!$response->successful()) {
                Log::warning('Discord webhook returned non-200 status: ' . $response->status());
            }
        } catch (\Exception $e) {
            Log::error('Discord webhook error: ' . $e->getMessage());
            throw $e;
        }
    }
}

