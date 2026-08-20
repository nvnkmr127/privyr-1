<x-admin::layouts>
    <x-slot:title>
        Lead Action Center
    </x-slot>

    <!-- Header & Filters -->
    <div class="mb-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="grid gap-1.5">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
                Lead Action Center
            </h1>
            <p class="text-sm text-slate-500">Your daily lead productivity overview</p>
        </div>

        <form method="GET" action="{{ route('admin.dashboard.index') }}" class="flex items-center gap-2">
            <select name="date_range" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300" onchange="this.form.submit()">
                <option value="today" {{ request('date_range') == 'today' ? 'selected' : '' }}>Today</option>
                <option value="yesterday" {{ request('date_range') == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                <option value="last_7_days" {{ request('date_range') == 'last_7_days' ? 'selected' : '' }}>Last 7 Days</option>
                <option value="last_30_days" {{ request('date_range', 'last_30_days') == 'last_30_days' ? 'selected' : '' }}>Last 30 Days</option>
            </select>
            
            @if(bouncer()->hasPermission('leads.all'))
                <select name="user_id" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300" onchange="this.form.submit()">
                    <option value="">All Users (Team)</option>
                    <option value="{{ auth()->guard('user')->id() }}" {{ request('user_id') == auth()->guard('user')->id() ? 'selected' : '' }}>My Leads</option>
                </select>
            @endif
        </form>
    </div>

    <!-- Manager Summary (conditionally shown) -->
    @if(bouncer()->hasPermission('leads.all') && empty(request('user_id')))
        <div class="mb-6 p-4 rounded-xl border border-indigo-200 bg-indigo-50 dark:bg-indigo-900/30 dark:border-indigo-800">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-indigo-900 dark:text-indigo-300">Team Summary</h3>
                    <p class="text-xs text-indigo-700 dark:text-indigo-400">Overview of all team activities</p>
                </div>
                <div class="flex gap-4">
                    <div class="text-center">
                        <span class="block text-2xl font-bold text-indigo-700 dark:text-indigo-300">{{ $metrics['today']['new_leads'] }}</span>
                        <span class="text-xs text-indigo-600 dark:text-indigo-400 uppercase tracking-wide">New Leads</span>
                    </div>
                    <div class="text-center">
                        <span class="block text-2xl font-bold text-rose-600 dark:text-rose-400">{{ $metrics['today']['overdue'] }}</span>
                        <span class="text-xs text-indigo-600 dark:text-indigo-400 uppercase tracking-wide">Overdue Actions</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Alpine.js Tabs Component -->
    <div x-data="{ tab: 'today' }" class="mb-8">
        
        <!-- Tab Navigation -->
        <div class="border-b border-slate-200 dark:border-gray-800 mb-6">
            <nav class="-mb-px flex gap-6" aria-label="Tabs">
                <button @click="tab = 'today'"
                        :class="tab === 'today' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300'"
                        class="whitespace-nowrap border-b-2 py-3 px-1 text-sm font-medium transition-colors">
                    TODAY
                </button>
                <button @click="tab = 'pipeline'"
                        :class="tab === 'pipeline' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300'"
                        class="whitespace-nowrap border-b-2 py-3 px-1 text-sm font-medium transition-colors">
                    LEAD PIPELINE
                </button>
                <button @click="tab = 'health'"
                        :class="tab === 'health' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300'"
                        class="whitespace-nowrap border-b-2 py-3 px-1 text-sm font-medium transition-colors">
                    LEAD HEALTH
                </button>
            </nav>
        </div>

        <!-- Tab Contents -->
        
        <!-- TODAY TAB -->
        <div x-show="tab === 'today'" class="space-y-6">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                @php
                    $todayCards = [
                        ['label' => 'New Leads Today', 'value' => $metrics['today']['new_leads'], 'color' => 'emerald', 'preset' => 'new'],
                        ['label' => 'Due Today', 'value' => $metrics['today']['due_today'], 'color' => 'blue', 'preset' => 'due_today'],
                        ['label' => 'Overdue', 'value' => $metrics['today']['overdue'], 'color' => 'rose', 'preset' => 'overdue_follow_ups'],
                        ['label' => 'Unassigned', 'value' => $metrics['today']['unassigned'], 'color' => 'slate', 'preset' => 'unassigned'],
                        ['label' => 'High Priority', 'value' => $metrics['today']['high_priority'], 'color' => 'orange', 'preset' => 'hot'],
                    ];
                @endphp
                @foreach($todayCards as $card)
                    <a href="{{ route('admin.leads.inbox', ['preset' => $card['preset']]) }}" class="flex flex-col p-4 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 transition-all dark:bg-gray-900 dark:border-gray-800 dark:hover:bg-gray-800 shadow-sm hover:shadow-md">
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">{{ $card['label'] }}</span>
                        <span class="text-3xl font-bold text-{{ $card['color'] }}-600 dark:text-{{ $card['color'] }}-400">{{ $card['value'] }}</span>
                    </a>
                @endforeach
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- My Work Today -->
                <div class="lg:col-span-2">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-4">My Work Today</h2>
                    <div class="bg-white dark:bg-gray-900 rounded-xl border border-slate-200 dark:border-gray-800 overflow-hidden shadow-sm">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm whitespace-nowrap">
                                <thead class="bg-slate-50 dark:bg-gray-800/50 text-slate-500 border-b border-slate-200 dark:border-gray-800">
                                    <tr>
                                        <th class="px-4 py-3 font-semibold">Lead</th>
                                        <th class="px-4 py-3 font-semibold">Action</th>
                                        <th class="px-4 py-3 font-semibold">Due</th>
                                        <th class="px-4 py-3 font-semibold">Priority</th>
                                        <th class="px-4 py-3 font-semibold text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-gray-800">
                                    @forelse($nextActions as $action)
                                        <tr class="hover:bg-slate-50/50 dark:hover:bg-gray-800/20 transition">
                                            <td class="px-4 py-3">
                                                <a href="{{ route('admin.leads.view', $action->lead_id) }}" class="font-semibold text-blue-600 hover:underline">
                                                    {{ $action->lead->title ?? 'Unknown Lead' }}
                                                </a>
                                            </td>
                                            <td class="px-4 py-3 font-medium text-slate-700 dark:text-slate-300">
                                                {{ $action->title }} ({{ ucfirst($action->type) }})
                                            </td>
                                            <td class="px-4 py-3">
                                                @if(\Carbon\Carbon::parse($action->schedule_from)->isPast() && !\Carbon\Carbon::parse($action->schedule_from)->isToday())
                                                    <span class="text-rose-600 font-semibold">{{ \Carbon\Carbon::parse($action->schedule_from)->format('M d, Y h:i A') }} (Overdue)</span>
                                                @else
                                                    <span class="text-blue-600 font-semibold">{{ \Carbon\Carbon::parse($action->schedule_from)->format('h:i A') }} (Today)</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3">
                                                @if($action->priority === 'urgent' || $action->priority === 'high')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-rose-50 text-rose-700">{{ ucfirst($action->priority) }}</span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-slate-100 text-slate-700">{{ ucfirst($action->priority ?? 'None') }}</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <form action="{{ route('admin.follow_ups.complete', $action->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-2.5 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-100 transition">
                                                        <i class="fa-solid fa-check"></i> Complete
                                                    </button>
                                                </form>
                                                <!-- Action Modals would be triggered from here -->
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-4 py-8 text-center text-slate-400">
                                                <div class="flex flex-col items-center justify-center">
                                                    <i class="fa-solid fa-check-circle text-4xl mb-3 text-emerald-400"></i>
                                                    <p>All caught up! No follow-ups due today.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Secondary Lists (New / Unassigned) -->
                <div class="space-y-6">
                    <div>
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white">New Leads</h2>
                            <a href="{{ route('admin.leads.inbox', ['preset' => 'new']) }}" class="text-sm text-blue-600 hover:underline">View All</a>
                        </div>
                        <div class="bg-white dark:bg-gray-900 rounded-xl border border-slate-200 dark:border-gray-800 shadow-sm p-4 space-y-4">
                            @forelse($newLeads as $lead)
                                <div class="flex items-start justify-between">
                                    <div>
                                        <a href="{{ route('admin.leads.view', $lead->id) }}" class="font-semibold text-slate-800 dark:text-white hover:text-blue-600 block">
                                            {{ $lead->title }}
                                        </a>
                                        <span class="text-xs text-slate-500">{{ $lead->created_at->diffForHumans() }}</span>
                                    </div>
                                    @if(empty($lead->user_id))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600">Unassigned</span>
                                    @endif
                                </div>
                            @empty
                                <div class="text-center py-4 text-slate-400 text-sm">No new leads.</div>
                            @endforelse
                        </div>
                    </div>

                    @if(bouncer()->hasPermission('leads.all'))
                    <div>
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Unassigned</h2>
                            <a href="{{ route('admin.leads.inbox', ['preset' => 'unassigned']) }}" class="text-sm text-blue-600 hover:underline">View All</a>
                        </div>
                        <div class="bg-white dark:bg-gray-900 rounded-xl border border-slate-200 dark:border-gray-800 shadow-sm p-4 space-y-4">
                            @forelse($unassignedLeads as $lead)
                                <div class="flex items-center justify-between">
                                    <div>
                                        <a href="{{ route('admin.leads.view', $lead->id) }}" class="font-semibold text-slate-800 dark:text-white hover:text-blue-600 block">
                                            {{ $lead->title }}
                                        </a>
                                        <span class="text-xs text-slate-500">{{ $lead->source->name ?? 'Unknown Source' }}</span>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4 text-slate-400 text-sm">No unassigned leads.</div>
                            @endforelse
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- PIPELINE TAB -->
        <div x-show="tab === 'pipeline'" class="space-y-6" style="display: none;">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                @php
                    $pipelineCards = [
                        ['label' => 'Open Leads', 'value' => $metrics['pipeline']['open'], 'color' => 'blue', 'preset' => 'all'],
                        ['label' => 'Qualified', 'value' => $metrics['pipeline']['qualified'], 'color' => 'indigo', 'preset' => 'qualified'],
                        ['label' => 'Nurturing', 'value' => $metrics['pipeline']['nurturing'], 'color' => 'amber', 'preset' => 'nurturing'],
                        ['label' => 'Converted', 'value' => $metrics['pipeline']['converted'], 'color' => 'emerald', 'preset' => 'won'],
                        ['label' => 'Lost', 'value' => $metrics['pipeline']['lost'], 'color' => 'slate', 'preset' => 'lost'],
                    ];
                @endphp
                @foreach($pipelineCards as $card)
                    <a href="{{ route('admin.leads.inbox', ['preset' => $card['preset']]) }}" class="flex flex-col p-4 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 transition-all dark:bg-gray-900 dark:border-gray-800 dark:hover:bg-gray-800 shadow-sm hover:shadow-md">
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">{{ $card['label'] }}</span>
                        <span class="text-3xl font-bold text-{{ $card['color'] }}-600 dark:text-{{ $card['color'] }}-400">{{ $card['value'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- HEALTH TAB -->
        <div x-show="tab === 'health'" class="space-y-6" style="display: none;">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @php
                    $healthCards = [
                        ['label' => 'Needs Attention', 'value' => $metrics['health']['needs_attention'], 'color' => 'rose', 'preset' => 'needs_contact'],
                        ['label' => 'Inactive (>14d)', 'value' => $metrics['health']['inactive'], 'color' => 'slate', 'preset' => 'stale'],
                        ['label' => 'Overdue', 'value' => $metrics['health']['overdue'], 'color' => 'rose', 'preset' => 'overdue_follow_ups'],
                        ['label' => 'No Next Action', 'value' => $metrics['health']['no_follow_up'], 'color' => 'amber', 'preset' => 'no_next_action'],
                    ];
                @endphp
                @foreach($healthCards as $card)
                    <a href="{{ route('admin.leads.inbox', ['preset' => $card['preset']]) }}" class="flex flex-col p-4 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 transition-all dark:bg-gray-900 dark:border-gray-800 dark:hover:bg-gray-800 shadow-sm hover:shadow-md">
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">{{ $card['label'] }}</span>
                        <span class="text-3xl font-bold text-{{ $card['color'] }}-600 dark:text-{{ $card['color'] }}-400">{{ $card['value'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
        
    </div>

</x-admin::layouts>
