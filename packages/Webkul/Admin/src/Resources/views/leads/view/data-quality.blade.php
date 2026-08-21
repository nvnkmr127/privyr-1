<div class="p-4 border-b border-gray-200 dark:border-gray-800 last:border-b-0">
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-sm font-semibold text-gray-800 dark:text-white">Data Quality</h2>
        
        @if ($lead->data_quality_state === 'complete')
            <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-bold text-emerald-800">Complete</span>
        @elseif ($lead->data_quality_state === 'needs_review')
            <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-bold text-amber-800">Needs Review</span>
        @else
            <span class="inline-flex items-center rounded-full bg-rose-100 px-2 py-0.5 text-xs font-bold text-rose-800">Incomplete</span>
        @endif
    </div>

    @php
        $issues = $lead->data_quality_issues ? json_decode($lead->data_quality_issues, true) : [];
    @endphp

    @if (!empty($issues))
        <div class="mb-3">
            <ul class="list-disc pl-4 text-sm text-rose-600 dark:text-rose-400 space-y-1">
                @foreach ($issues as $issue)
                    <li>{{ $issue }}</li>
                @endforeach
            </ul>
        </div>
        
        <a href="{{ route('admin.leads.edit', $lead->id) }}" class="inline-flex items-center justify-center w-full rounded-md bg-white px-3 py-1.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:hover:bg-gray-700 transition">
            <i class="fa-solid fa-wand-magic-sparkles mr-1.5 text-blue-500"></i> Fix Data
        </a>
    @else
        <p class="text-sm text-gray-500 dark:text-gray-400">All required fields and formatting look good.</p>
    @endif
</div>
