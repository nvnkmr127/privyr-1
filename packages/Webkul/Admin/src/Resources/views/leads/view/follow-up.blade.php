<div class="p-4">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-base font-semibold text-slate-800 dark:text-white">
            Next Action
        </h3>

        <!-- Action Dropdown for Follow up -->
        <x-admin::dropdown position="bottom-right">
            <x-slot:toggle>
                <button class="flex items-center justify-center p-2 rounded-md hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <span class="icon-more text-lg"></span>
                </button>
            </x-slot:toggle>
            
            <x-slot:menu class="!p-1">
                <x-admin::dropdown.menu.item @click="$emitter.emit('open-follow-up-modal', { lead_id: {{ $lead->id }} })">
                    <span class="icon-calendar mr-2"></span> @lang('admin::app.follow-ups.schedule')
                </x-admin::dropdown.menu.item>
            </x-slot:menu>
        </x-admin::dropdown>
    </div>

    @php
        $nextFollowUp = clone $lead; 
        $nextFollowUp->type = $lead->next_action;
        $nextFollowUp->schedule_from = $lead->next_follow_up_at;
        $nextFollowUp->title = $lead->next_action_note;
        $nextFollowUp->priority = $lead->next_action_priority;
    @endphp

    @if ($lead->next_action)
        <div class="flex flex-col gap-3 p-3 rounded-lg border border-slate-100 bg-slate-50 dark:border-slate-800 dark:bg-slate-900/50">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium {{ $nextFollowUp->schedule_from < now() ? 'text-red-600' : 'text-slate-700 dark:text-slate-300' }}">
                    {{ $nextFollowUp->schedule_from->format('D M d, Y h:i A') }}
                </span>
                <span class="px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-700">
                    {{ ucfirst($nextFollowUp->type) }}
                </span>
            </div>

            <p class="text-sm text-slate-600 dark:text-slate-400">
                {{ $nextFollowUp->title }}
            </p>

            <div class="flex items-center justify-between pt-2 mt-1 border-t border-slate-200 dark:border-slate-700/50">
                <div class="flex gap-2 text-xs text-slate-500">
                    <span>
                        <span class="icon-user text-sm align-middle mr-1"></span>
                        {{ $lead->followUpOwner->name ?? 'Unassigned' }}
                    </span>
                    @if($nextFollowUp->priority)
                        <span class="ml-2 px-1.5 rounded bg-slate-200 text-slate-700">{{ ucfirst($nextFollowUp->priority) }}</span>
                    @endif
                </div>
                
                <div class="flex gap-2">
                    <form method="POST" action="{{ route('admin.leads.follow_up.complete', $lead->id) }}">
                        @csrf
                        <button type="submit" class="text-xs font-medium text-green-600 hover:text-green-700 bg-green-50 px-2 py-1 rounded">
                            Complete
                        </button>
                    </form>
                    <button type="button" class="text-xs font-medium text-blue-600 hover:text-blue-700 bg-blue-50 px-2 py-1 rounded" @click="$emitter.emit('open-follow-up-snooze-modal', { lead_id: {{ $lead->id }} })">
                        Reschedule
                    </button>
                </div>
            </div>
        </div>
    @else
        <div class="flex flex-col items-center justify-center py-6 text-slate-500 border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-lg">
            <span class="icon-calendar text-3xl mb-2 text-slate-400"></span>
            <p class="text-sm">@lang('admin::app.follow-ups.no-next-action')</p>
            <button class="mt-3 text-sm text-blue-600 font-medium hover:underline" @click="$emitter.emit('open-follow-up-modal', { lead_id: {{ $lead->id }} })">
                @lang('admin::app.follow-ups.schedule-now')
            </button>
        </div>
    @endif
</div>
