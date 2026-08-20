<div class="flex flex-col gap-4 p-4">
    <div class="flex items-center justify-between">
        <h2 class="text-sm font-semibold text-gray-800 dark:text-white">Nurturing Information</h2>
    </div>

    @if($lead->status === 'Nurturing')
        <div class="flex flex-col gap-2 rounded-lg bg-blue-50 p-3 text-sm dark:bg-blue-900/20">
            <div class="flex justify-between">
                <span class="text-gray-500 dark:text-gray-400">Reason</span>
                <span class="font-medium text-gray-900 dark:text-gray-100">
                    {{ $lead->nurture_reason_id ? app('Webkul\Attribute\Repositories\AttributeOptionRepository')->find($lead->nurture_reason_id)?->name : 'Not Specified' }}
                </span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500 dark:text-gray-400">Started At</span>
                <span class="font-medium text-gray-900 dark:text-gray-100">
                    {{ $lead->nurtured_at ? core()->formatDate($lead->nurtured_at, 'M d, Y') : '--' }}
                </span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500 dark:text-gray-400">Re-engagement</span>
                <span class="font-medium text-gray-900 dark:text-gray-100">
                    {{ $lead->nurture_reengagement_date ? core()->formatDate($lead->nurture_reengagement_date, 'M d, Y') : '--' }}
                </span>
            </div>
            @if($lead->nurture_notes)
            <div class="mt-2 text-gray-700 dark:text-gray-300">
                <span class="font-medium text-gray-500 dark:text-gray-400">Notes:</span><br>
                {{ $lead->nurture_notes }}
            </div>
            @endif
        </div>
    @else
        <p class="text-sm text-gray-500 dark:text-gray-400">Lead is not currently nurturing.</p>
    @endif

    @if($lead->nurtureHistories->count() > 0)
        <div class="mt-2">
            <h3 class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400 mb-2">Past Nurturing</h3>
            <div class="space-y-3">
                @foreach($lead->nurtureHistories as $history)
                    <div class="text-sm">
                        <div class="flex justify-between font-medium">
                            <span>{{ $history->reason ? $history->reason->name : 'Unknown Reason' }}</span>
                            <span>{{ $history->end_reason ?: 'Ongoing' }}</span>
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            {{ core()->formatDate($history->started_at, 'M d, Y') }} - 
                            {{ $history->ended_at ? core()->formatDate($history->ended_at, 'M d, Y') : 'Present' }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
