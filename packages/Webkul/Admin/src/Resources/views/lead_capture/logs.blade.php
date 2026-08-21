<x-admin::layouts>
    <x-slot:title>
        Lead Capture Logs
    </x-slot>

    <div class="flex flex-col gap-4">
        <!-- Header -->
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Lead Capture Logs</h1>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Every ingestion attempt across all connected sources — status, origin, resolved lead, timing and errors.</p>
            </div>
            <a href="{{ route('admin.lead_capture.integrations') }}" class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-bold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                &larr; Integrations
            </a>
        </div>

        <!-- Filters -->
        <form method="GET" class="flex flex-wrap items-end gap-3 rounded-xl border bg-white p-3 dark:border-gray-800 dark:bg-gray-900">
            <div>
                <label class="block text-[10px] font-bold uppercase text-gray-500 dark:text-gray-400">Connector</label>
                <select name="connector" class="mt-1 rounded-md border border-gray-300 p-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    <option value="">All</option>
                    @foreach ($connectors as $c)
                        <option value="{{ $c->id }}" @selected(request('connector') == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase text-gray-500 dark:text-gray-400">Status</label>
                <input type="text" name="status" value="{{ request('status') }}" placeholder="e.g. Created, Failed" class="mt-1 rounded-md border border-gray-300 p-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
            </div>
            <button type="submit" class="rounded-md bg-brandColor px-4 py-2 text-xs font-bold text-white shadow">Filter</button>
            @if (request('connector') || request('status'))
                <a href="{{ route('admin.lead_capture.integrations.logs') }}" class="text-xs font-semibold text-gray-500 hover:underline">Clear</a>
            @endif
        </form>

        <!-- Table -->
        <div class="overflow-x-auto rounded-xl border bg-white dark:border-gray-800 dark:bg-gray-900">
            <table class="w-full min-w-[820px] text-left text-xs">
                <thead class="border-b bg-gray-50 text-[10px] uppercase text-gray-500 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3 font-bold">When</th>
                        <th class="px-4 py-3 font-bold">Connector</th>
                        <th class="px-4 py-3 font-bold">Origin</th>
                        <th class="px-4 py-3 font-bold">Status</th>
                        <th class="px-4 py-3 font-bold">External ID</th>
                        <th class="px-4 py-3 font-bold">Lead</th>
                        <th class="px-4 py-3 font-bold">Time</th>
                        <th class="px-4 py-3 font-bold">Error</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($logs as $log)
                        @php
                            $s = strtolower($log->status ?? '');
                            $badge = match (true) {
                                str_contains($s, 'creat') || str_contains($s, 'updat') => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400',
                                str_contains($s, 'fail') || str_contains($s, 'reject') || str_contains($s, 'error') || str_contains($s, 'invalid') => 'bg-red-50 text-red-700 dark:bg-red-950/50 dark:text-red-400',
                                str_contains($s, 'duplicate') || str_contains($s, 'skip') => 'bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-400',
                                default => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
                            };
                        @endphp
                        <tr class="text-gray-700 dark:text-gray-300">
                            <td class="whitespace-nowrap px-4 py-3">{{ $log->created_at?->diffForHumans() }}</td>
                            <td class="px-4 py-3">{{ $log->connector?->name ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $log->origin ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $badge }}">{{ $log->status }}</span>
                            </td>
                            <td class="px-4 py-3 font-mono text-[11px]">{{ $log->external_id ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @if ($log->lead)
                                    <a href="{{ route('admin.leads.view', $log->lead->id) }}" class="font-semibold text-brandColor hover:underline">#{{ $log->lead->id }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">{{ $log->processing_time_ms !== null ? $log->processing_time_ms.' ms' : '—' }}</td>
                            <td class="max-w-[260px] truncate px-4 py-3 text-red-600 dark:text-red-400" title="{{ $log->error_message }}">{{ $log->error_message ?? '' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">No capture attempts logged yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>{{ $logs->links() }}</div>
    </div>
</x-admin::layouts>
