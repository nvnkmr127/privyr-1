<div class="p-4 border-b border-slate-200/80">
    <h2 class="text-sm font-semibold text-gray-800 dark:text-white flex items-center gap-2">
        <i class="fa-solid fa-stopwatch text-slate-400"></i> Service Level Agreements
    </h2>
</div>

<div class="p-4 flex flex-col gap-3 text-sm">
    @php
        $activeSlas = $lead->slas()->whereIn('status', ['On Track', 'Due Soon', 'Breached'])->orderBy('due_at', 'asc')->get();
    @endphp

    @if($activeSlas->isEmpty())
        <div class="text-center text-gray-500 italic py-2">
            No active SLAs for this lead.
        </div>
    @else
        @foreach($activeSlas as $sla)
            <div class="flex flex-col gap-1 pb-3 border-b border-gray-100 last:border-0 last:pb-0">
                <div class="flex justify-between items-center">
                    <span class="font-medium text-gray-800 dark:text-gray-200">{{ $sla->sla_type }} SLA</span>
                    @if($sla->status === 'On Track')
                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-bold text-emerald-800">On Track</span>
                    @elseif($sla->status === 'Due Soon')
                        <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-bold text-amber-800">Due Soon</span>
                    @elseif($sla->status === 'Breached')
                        <span class="inline-flex items-center rounded-full bg-rose-100 px-2 py-0.5 text-xs font-bold text-rose-800">Breached</span>
                    @endif
                </div>
                
                <div class="flex justify-between text-xs mt-1">
                    <span class="text-gray-500">Started:</span>
                    <span class="text-gray-700 font-medium">{{ core()->formatDate($sla->started_at, 'M d, h:i A') }}</span>
                </div>
                
                <div class="flex justify-between text-xs mt-0.5">
                    <span class="text-gray-500">Due:</span>
                    <span class="{{ $sla->status === 'Breached' ? 'text-rose-600 font-bold' : 'text-gray-700 font-medium' }}">
                        {{ core()->formatDate($sla->due_at, 'M d, h:i A') }}
                    </span>
                </div>
                
                @if($sla->status === 'Breached' && $sla->breached_at)
                <div class="flex justify-between text-xs mt-0.5">
                    <span class="text-gray-500">Breached At:</span>
                    <span class="text-rose-600 font-bold">{{ core()->formatDate($sla->breached_at, 'M d, h:i A') }}</span>
                </div>
                @endif
            </div>
        @endforeach
    @endif
</div>
