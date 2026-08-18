<x-admin::layouts>
    <!-- Title -->
    <x-slot:title>
        Lead Analytics
    </x-slot>

    <div class="flex flex-col gap-4">
        <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
            <div class="flex flex-col gap-1">
                <h1 class="text-xl font-bold text-slate-900 dark:text-white">
                    Lead Analytics
                </h1>
                <p class="text-sm text-slate-500 dark:text-gray-400">
                    Measure historical velocity, acquisition flow, and pipeline health.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <!-- Date Filter -->
                <form method="GET" action="{{ route('admin.analytics.index') }}" class="flex items-center gap-2">
                    <input type="date" name="start_date" value="{{ substr($startDate, 0, 10) }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm dark:bg-gray-900 dark:border-gray-800 dark:text-white">
                    <span class="text-slate-400">to</span>
                    <input type="date" name="end_date" value="{{ substr($endDate, 0, 10) }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm dark:bg-gray-900 dark:border-gray-800 dark:text-white">
                    <button type="submit" class="rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-blue-700">Filter</button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Metric Cards -->
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <p class="text-sm font-medium text-slate-500 dark:text-gray-400">Total Leads Received</p>
                <div class="mt-2 flex items-baseline gap-2">
                    <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ $metrics['total_leads'] }}</p>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <p class="text-sm font-medium text-slate-500 dark:text-gray-400">Conversion Rate (Won)</p>
                <div class="mt-2 flex items-baseline gap-2">
                    <p class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ $metrics['conversion_rate'] }}%</p>
                </div>
                <a href="{{ route('admin.leads.index', ['stage' => 'won']) }}" class="text-xs text-blue-600 hover:underline mt-1 inline-block">View Won Leads &rarr;</a>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <p class="text-sm font-medium text-slate-500 dark:text-gray-400">Qualified Leads</p>
                <div class="mt-2 flex items-baseline gap-2">
                    <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ $metrics['qualified_leads'] }}</p>
                </div>
                <a href="{{ route('admin.leads.index', ['qualification_status' => 'qualified']) }}" class="text-xs text-blue-600 hover:underline mt-1 inline-block">View Qualified &rarr;</a>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <p class="text-sm font-medium text-slate-500 dark:text-gray-400">Stale Leads (>14 days uncontacted)</p>
                <div class="mt-2 flex items-baseline gap-2">
                    <p class="text-3xl font-bold text-rose-600 dark:text-rose-400">{{ $metrics['stale_leads'] }}</p>
                </div>
                <a href="{{ route('admin.leads.index', ['preset' => 'stale']) }}" class="text-xs text-blue-600 hover:underline mt-1 inline-block">View Stale Leads &rarr;</a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <!-- Sources Table -->
            <div class="rounded-xl border border-slate-200 bg-white shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-slate-200 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white">Leads by Source</h3>
                </div>
                <div class="p-0">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-gray-800">
                        <thead class="bg-slate-50 dark:bg-gray-800/50">
                            <tr>
                                <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-gray-400">Source</th>
                                <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-gray-400">Volume</th>
                                <th scope="col" class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-gray-400">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white dark:divide-gray-800 dark:bg-gray-900">
                            @forelse($metrics['leads_by_source'] as $source)
                            <tr>
                                <td class="whitespace-nowrap px-5 py-3 text-sm font-medium text-slate-900 dark:text-white">{{ $source['name'] }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-sm text-slate-500 dark:text-gray-400">
                                    <div class="flex items-center gap-2">
                                        <span>{{ $source['count'] }}</span>
                                        <div class="h-2 w-24 overflow-hidden rounded-full bg-slate-100 dark:bg-gray-800">
                                            <div class="h-full bg-blue-500" style="width: {{ $metrics['total_leads'] > 0 ? ($source['count'] / $metrics['total_leads']) * 100 : 0 }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3 text-right text-sm font-medium">
                                    <a href="{{ route('admin.leads.index', ['lead_source_id' => $source['id']]) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">View Leads</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-5 py-4 text-center text-sm text-slate-500 dark:text-gray-400">No data available</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Owners Table -->
            <div class="rounded-xl border border-slate-200 bg-white shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-slate-200 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white">Leads by Owner</h3>
                </div>
                <div class="p-0">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-gray-800">
                        <thead class="bg-slate-50 dark:bg-gray-800/50">
                            <tr>
                                <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-gray-400">Owner</th>
                                <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-gray-400">Volume</th>
                                <th scope="col" class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-gray-400">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white dark:divide-gray-800 dark:bg-gray-900">
                            @forelse($metrics['leads_by_owner'] as $owner)
                            <tr>
                                <td class="whitespace-nowrap px-5 py-3 text-sm font-medium text-slate-900 dark:text-white">{{ $owner['name'] }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-sm text-slate-500 dark:text-gray-400">
                                    <div class="flex items-center gap-2">
                                        <span>{{ $owner['count'] }}</span>
                                        <div class="h-2 w-24 overflow-hidden rounded-full bg-slate-100 dark:bg-gray-800">
                                            <div class="h-full bg-emerald-500" style="width: {{ $metrics['total_leads'] > 0 ? ($owner['count'] / $metrics['total_leads']) * 100 : 0 }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3 text-right text-sm font-medium">
                                    <a href="{{ route('admin.leads.index', ['user_id' => $owner['id']]) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">View Leads</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-5 py-4 text-center text-sm text-slate-500 dark:text-gray-400">No data available</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <p class="text-sm font-medium text-slate-500 dark:text-gray-400">Avg Lead Aging (Days)</p>
                <div class="mt-2 flex items-baseline gap-2">
                    <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ $metrics['aging_leads_avg_days'] }}</p>
                    <span class="text-sm text-slate-500">days since creation</span>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <p class="text-sm font-medium text-slate-500 dark:text-gray-400">Avg Response Time (Hours)</p>
                <div class="mt-2 flex items-baseline gap-2">
                    <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ $metrics['avg_response_time_hours'] }}</p>
                    <span class="text-sm text-slate-500">hours to first activity</span>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <p class="text-sm font-medium text-slate-500 dark:text-gray-400">Overdue Follow-ups</p>
                <div class="mt-2 flex items-baseline gap-2">
                    <p class="text-3xl font-bold text-rose-600 dark:text-rose-400">{{ $metrics['overdue_followups'] }}</p>
                </div>
                <a href="{{ route('admin.leads.index', ['preset' => 'overdue']) }}" class="text-xs text-blue-600 hover:underline mt-1 inline-block">View Overdue &rarr;</a>
            </div>
        </div>

        <!-- Score Distribution -->
        <div class="rounded-xl border border-slate-200 bg-white shadow-xs dark:border-gray-800 dark:bg-gray-900 mt-4">
            <div class="border-b border-slate-200 px-5 py-4 dark:border-gray-800">
                <h3 class="text-base font-semibold text-slate-900 dark:text-white">Lead Score Distribution</h3>
            </div>
            <div class="p-5">
                <div class="flex items-end justify-between h-48 space-x-2">
                    @foreach(['0-20', '21-40', '41-60', '61-80', '81-100'] as $range)
                        @php 
                            $count = $metrics['score_distribution'][$range] ?? 0;
                            $max = max(array_values($metrics['score_distribution'])) ?: 1;
                            $height = ($count / $max) * 100;
                        @endphp
                        <div class="flex-1 flex flex-col items-center justify-end h-full">
                            <span class="text-xs text-slate-500 mb-1 dark:text-gray-400">{{ $count }}</span>
                            <div class="w-full bg-blue-500 rounded-t-sm hover:bg-blue-600 transition" style="height: {{ $height }}%;"></div>
                            <span class="text-xs font-medium text-slate-600 mt-2 dark:text-gray-300">{{ $range }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-admin::layouts>
