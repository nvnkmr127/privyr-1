<x-admin::layouts>
    <x-slot:title>
        Lead Productivity Workspace
    </x-slot>

    <!-- Head Details Section -->
    <div class="mb-5 flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="grid gap-1.5">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
                TODAY
            </h1>
            <p class="text-sm text-slate-500">Your daily lead productivity overview</p>
        </div>
    </div>

    <!-- Metric Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4 mb-8">
        @php
            $cards = [
                ['label' => 'New Leads', 'value' => $metrics['new_leads'], 'color' => 'emerald', 'preset' => 'new'],
                ['label' => 'Unassigned', 'value' => $metrics['unassigned'], 'color' => 'slate', 'preset' => 'unassigned'],
                ['label' => 'Needs Contact', 'value' => $metrics['needs_contact'], 'color' => 'rose', 'preset' => 'needs_contact'],
                ['label' => 'Due Today', 'value' => $metrics['due_today'], 'color' => 'blue', 'preset' => 'due_today'],
                ['label' => 'Overdue', 'value' => $metrics['overdue'], 'color' => 'rose', 'preset' => 'overdue_follow_ups'],
                ['label' => 'Hot Leads', 'value' => $metrics['hot'], 'color' => 'orange', 'preset' => 'hot'],
                ['label' => 'No Next Action', 'value' => $metrics['no_next_action'], 'color' => 'slate', 'preset' => 'no_next_action'],
                ['label' => 'Stale', 'value' => $metrics['stale'], 'color' => 'slate', 'preset' => 'stale'],
                ['label' => 'Qualified', 'value' => $metrics['qualified'], 'color' => 'indigo', 'preset' => 'qualified'],
                ['label' => 'Waiting Response', 'value' => $metrics['waiting_for_response'], 'color' => 'amber', 'preset' => 'waiting_for_response'],
                ['label' => 'Stage Changed', 'value' => $metrics['stage_changed'], 'color' => 'purple', 'preset' => 'stage_changed'],
            ];
        @endphp

        @foreach($cards as $card)
            <a href="{{ route('admin.leads.inbox', ['preset' => $card['preset']]) }}" class="flex flex-col p-4 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 transition dark:bg-gray-900 dark:border-gray-800 dark:hover:bg-gray-800">
                <span class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-2">{{ $card['label'] }}</span>
                <span class="text-3xl font-bold text-{{ $card['color'] }}-600 dark:text-{{ $card['color'] }}-400">{{ $card['value'] }}</span>
            </a>
        @endforeach
    </div>

    <!-- My Next Actions -->
    <div class="mb-5 flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="grid gap-1.5">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">
                MY NEXT ACTIONS
            </h2>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-xl border border-slate-200 dark:border-gray-800 overflow-hidden mb-8">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-50 dark:bg-gray-800/50 text-slate-500 border-b border-slate-200 dark:border-gray-800">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Lead</th>
                        <th class="px-4 py-3 font-semibold">Action</th>
                        <th class="px-4 py-3 font-semibold">Due Date</th>
                        <th class="px-4 py-3 font-semibold">Priority</th>
                        <th class="px-4 py-3 font-semibold text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-gray-800">
                    @forelse($nextActions as $actionLead)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-gray-800/20 transition">
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.leads.view', $actionLead->id) }}" class="font-semibold text-blue-600 hover:underline">
                                    {{ $actionLead->title }}
                                </a>
                            </td>
                            <td class="px-4 py-3 font-medium text-slate-700 dark:text-slate-300">
                                {{ $actionLead->next_action }}
                            </td>
                            <td class="px-4 py-3">
                                @if(\Carbon\Carbon::parse($actionLead->next_follow_up_at)->isPast() && !\Carbon\Carbon::parse($actionLead->next_follow_up_at)->isToday())
                                    <span class="text-rose-600 font-semibold">{{ \Carbon\Carbon::parse($actionLead->next_follow_up_at)->format('M d, Y h:i A') }} (Overdue)</span>
                                @else
                                    <span class="text-blue-600 font-semibold">{{ \Carbon\Carbon::parse($actionLead->next_follow_up_at)->format('M d, Y h:i A') }} (Today)</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($actionLead->priority === 'urgent' || $actionLead->priority === 'high')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-rose-50 text-rose-700">{{ ucfirst($actionLead->priority) }}</span>
                                @elseif($actionLead->priority === 'medium')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-amber-50 text-amber-700">{{ ucfirst($actionLead->priority) }}</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-slate-100 text-slate-700">{{ ucfirst($actionLead->priority ?? 'None') }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <form action="{{ route('admin.leads.follow_up.complete', $actionLead->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 hover:bg-emerald-100 transition">
                                        <i class="fa-solid fa-check"></i> Complete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-400">
                                No upcoming actions assigned to you!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin::layouts>
