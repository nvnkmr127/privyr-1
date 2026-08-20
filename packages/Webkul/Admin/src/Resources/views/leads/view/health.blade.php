<div class="p-4 sm:p-6 pb-2 border-b border-slate-100 dark:border-gray-800 flex justify-between items-center">
    <h3 class="text-base font-semibold text-gray-800 dark:text-white">Lead Health</h3>
    @php
        $health = $lead->health_state;
        $healthClass = 'bg-emerald-100 text-emerald-800';
        if ($health === 'Inactive') $healthClass = 'bg-gray-200 text-gray-700';
        if ($health === 'Needs Attention') $healthClass = 'bg-amber-100 text-amber-800';
        if ($health === 'Overdue') $healthClass = 'bg-rose-100 text-rose-800';
    @endphp
    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-bold {{ $healthClass }}">
        {{ $health }}
    </span>
</div>
<div class="p-4 sm:p-6 space-y-4">
    <div class="flex justify-between items-center">
        <span class="text-sm text-gray-500 dark:text-gray-400">Lead Age</span>
        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $lead->lead_age_days }} days</span>
    </div>
    <div class="flex justify-between items-center">
        <span class="text-sm text-gray-500 dark:text-gray-400">Stage Age</span>
        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $lead->stage_age_days }} days</span>
    </div>
    <div class="flex justify-between items-center">
        <span class="text-sm text-gray-500 dark:text-gray-400">Last Activity</span>
        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
            {{ $lead->last_activity_at ? \Carbon\Carbon::parse($lead->last_activity_at)->diffForHumans() : '--' }}
        </span>
    </div>
    <div class="flex justify-between items-center">
        <span class="text-sm text-gray-500 dark:text-gray-400">Last Contact</span>
        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
            {{ $lead->last_contacted_at ? \Carbon\Carbon::parse($lead->last_contacted_at)->diffForHumans() : '--' }}
        </span>
    </div>
    <div class="flex justify-between items-center">
        <span class="text-sm text-gray-500 dark:text-gray-400">Next Follow-up</span>
        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
            {{ $lead->next_follow_up_at ? core()->formatDate($lead->next_follow_up_at, 'M d, Y H:i') : '--' }}
        </span>
    </div>
</div>
