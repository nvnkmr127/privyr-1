@php
    $timelineEvents = $lead->getChronologicalTimeline();
@endphp

<div class="bg-white dark:bg-gray-900">
    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
        <div class="flex items-center gap-2">
            <h2 class="text-xs font-bold uppercase tracking-widest text-slate-900">
                Chronological Activity Timeline
            </h2>
        </div>
        <span class="rounded-full bg-slate-100 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-500">
            {{ $timelineEvents->count() }} Events
        </span>
    </div>

    <!-- Timeline Event Stream -->
    <div class="relative mt-5 pl-6 before:absolute before:left-3 before:top-2 before:bottom-2 before:w-0.5 before:bg-gray-200 dark:before:bg-gray-800">
        @forelse ($timelineEvents as $event)
            <div class="relative mb-6 last:mb-0 group">
                <!-- Node Icon Marker -->
                <div class="absolute -left-6 top-0 flex h-6 w-6 items-center justify-center rounded-full border-2 border-white bg-slate-900 text-white shadow-sm">
                    <span class="{{ $event['icon'] }} text-[10px]"></span>
                </div>

                <!-- Event Content Card -->
                <div class="rounded-xl border border-slate-100 bg-white p-4 transition-all hover:bg-slate-50 hover:border-slate-200">
                    <div class="flex items-center justify-between gap-2">
                        <span class="rounded bg-slate-100 text-slate-700 px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider">
                            {{ $event['title'] }}
                        </span>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                            {{ $event['timestamp']->diffForHumans() }} <span class="hidden sm:inline">({{ $event['timestamp']->format('M d, Y h:i A') }})</span>
                        </span>
                    </div>

                    @if (!empty($event['description']))
                        <div class="mt-3 text-xs font-medium text-slate-600 leading-relaxed">
                            {!! nl2br(e($event['description'])) !!}
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="p-6 text-center text-xs font-bold uppercase tracking-widest text-slate-400">
                No activity events recorded on timeline yet.
            </div>
        @endforelse
    </div>
</div>
