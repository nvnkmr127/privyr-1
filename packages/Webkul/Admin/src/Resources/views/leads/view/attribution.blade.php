<!-- Lead Attribution Widget -->
<div class="flex flex-col">
    <div class="flex items-center justify-between p-4 border-b border-slate-200/80 dark:border-slate-800">
        <h3 class="text-sm font-semibold text-slate-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-bullseye text-slate-400"></i> Attribution
        </h3>
    </div>
    
    <div class="p-4 space-y-4">
        <!-- First Touch -->
        <div class="rounded-lg bg-slate-50 p-3 border border-slate-100 dark:bg-slate-800/50 dark:border-slate-800">
            <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">First Touch</h4>
            <div class="grid grid-cols-2 gap-y-2 text-sm">
                <div class="text-slate-500">Source:</div>
                <div class="font-medium text-slate-700 dark:text-slate-300">{{ $lead->firstSource->name ?? '--' }}</div>
                
                <div class="text-slate-500">Origin:</div>
                <div class="font-medium text-slate-700 dark:text-slate-300">{{ $lead->first_origin ?? '--' }}</div>
                
                <div class="text-slate-500">Campaign:</div>
                <div class="font-medium text-slate-700 dark:text-slate-300">{{ $lead->first_campaign ?? '--' }}</div>
                
                <div class="text-slate-500">Medium:</div>
                <div class="font-medium text-slate-700 dark:text-slate-300">{{ $lead->first_medium ?? '--' }}</div>
            </div>
        </div>

        <!-- Latest Touch -->
        <div class="rounded-lg bg-slate-50 p-3 border border-slate-100 dark:bg-slate-800/50 dark:border-slate-800">
            <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Latest Touch</h4>
            <div class="grid grid-cols-2 gap-y-2 text-sm">
                <div class="text-slate-500">Source:</div>
                <div class="font-medium text-slate-700 dark:text-slate-300">{{ $lead->latestSource->name ?? '--' }}</div>
                
                <div class="text-slate-500">Origin:</div>
                <div class="font-medium text-slate-700 dark:text-slate-300">{{ $lead->latest_origin ?? '--' }}</div>
                
                <div class="text-slate-500">Campaign:</div>
                <div class="font-medium text-slate-700 dark:text-slate-300">{{ $lead->latest_campaign ?? '--' }}</div>
            </div>
        </div>
        
        <!-- History Summary -->
        @if($lead->attributionHistories->count() > 0)
            <div class="mt-4 border-t border-slate-100 dark:border-slate-800 pt-3">
                <h4 class="text-xs font-semibold text-slate-500 mb-2">History (Latest 3)</h4>
                <ul class="space-y-2">
                    @foreach($lead->attributionHistories->take(3) as $history)
                        <li class="text-xs text-slate-600 dark:text-slate-400 bg-white border border-slate-200 rounded p-2 shadow-sm dark:bg-slate-900 dark:border-slate-700">
                            <strong>{{ $history->origin ?: 'Unknown' }}</strong> via {{ $history->source->name ?? 'Unknown Source' }}
                            <div class="text-[10px] text-slate-400 mt-1">{{ $history->created_at->format('M d, Y h:i A') }}</div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</div>
