<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LeadDistributionService
{
    /**
     * Check if current time is within active team working hours (09:00 AM - 07:00 PM).
     */
    public function isWithinWorkingHours(): bool
    {
        $now = Carbon::now('Asia/Kolkata');
        $start = Carbon::createFromTime(9, 0, 0, 'Asia/Kolkata');
        $end = Carbon::createFromTime(19, 0, 0, 'Asia/Kolkata');

        return $now->between($start, $end);
    }

    /**
     * Get assigned user ID based on shift schedule, working hours, rule evaluation, or round-robin.
     */
    public function resolveAssignedUserId(array $extractedData = []): int
    {
        $value = (float) ($extractedData['value'] ?? 0);
        $source = strtolower($extractedData['source'] ?? '');

        // Shift Schedule Check: Out-of-Office / After-Hours Auto-Reassignment
        if (! $this->isWithinWorkingHours()) {
            Log::info('Lead captured after working hours (Outside 09:00-19:00 IST) -> Assigned to Night Duty Lead #1');

            return 1;
        }

        // 0. Dynamic Database Rules Evaluation
        $dbRules = DB::table('lead_routing_rules')->where('status', 1)->orderBy('sort_order', 'asc')->get();
        foreach ($dbRules as $rule) {
            if ($rule->condition_type === 'value_gte' && $value >= (float) $rule->condition_value) {
                Log::info("DB Rule Matched [{$rule->name}]: Value >= {$rule->condition_value} -> User #{$rule->user_id}");

                return $rule->user_id ?? 1;
            }

            if ($rule->condition_type === 'source_is' && str_contains($source, strtolower($rule->condition_value))) {
                Log::info("DB Rule Matched [{$rule->name}]: Source contains '{$rule->condition_value}' -> User #{$rule->user_id}");

                return $rule->user_id ?? 1;
            }
        }

        // Rule 2: Source-Based Routing
        if (str_contains($source, 'indiamart') || str_contains($source, 'justdial')) {
            $teleAgent = DB::table('users')->where('status', 1)->where('name', 'like', '%tele%')->first();
            if ($teleAgent) {
                return $teleAgent->id;
            }
        }

        // Rule 3: Round-Robin Fallback among active team members
        return $this->getNextAssignedUserId();
    }

    /**
     * Get next user ID using round-robin distribution among active users.
     */
    public function getNextAssignedUserId(): int
    {
        $users = DB::table('users')
            ->where('status', 1)
            ->orderBy('id', 'asc')
            ->pluck('id')
            ->toArray();

        if (empty($users)) {
            return 1;
        }

        $lastIndex = Cache::get('lead_distribution_last_index', -1);
        $nextIndex = ($lastIndex + 1) % count($users);

        Cache::put('lead_distribution_last_index', $nextIndex, 86400 * 30);

        return $users[$nextIndex];
    }

    /**
     * Get multi-number phone routing details for a given user ID.
     */
    public function getAgentRoutingDetails(int $userId): array
    {
        $user = DB::table('users')->where('id', $userId)->first();

        return [
            'user_id' => $userId,
            'agent_name' => $user->name ?? 'Agent',
            'agent_email' => $user->email ?? '',
            'agent_phone' => $user->phone ?? null,
            'whatsapp_direct_url' => $user?->phone ? 'https://wa.me/'.preg_replace('/[^0-9]/', '', $user->phone) : null,
        ];
    }
}
