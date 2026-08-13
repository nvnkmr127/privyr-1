@php
    $timelineEvents = $lead->getChronologicalTimeline();
@endphp

<div class="rounded-lg border bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
    <div class="flex items-center justify-between border-b pb-3 dark:border-gray-800">
        <div class="flex items-center gap-2">
            <span class="icon-calendar text-xl text-brandColor"></span>
            <h2 class="text-base font-bold text-gray-900 dark:text-white">
                Chronological Activity Timeline
            </h2>
        </div>
        <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">
            {{ $timelineEvents->count() }} Events
        </span>
    </div>

    <!-- Timeline Event Stream -->
    <div class="relative mt-5 pl-6 before:absolute before:left-3 before:top-2 before:bottom-2 before:w-0.5 before:bg-gray-200 dark:before:bg-gray-800">
        @forelse ($timelineEvents as $event)
            <div class="relative mb-6 last:mb-0 group">
                <!-- Node Icon Marker -->
                <div class="absolute -left-6 top-0 flex h-6 w-6 items-center justify-center rounded-full border-2 border-white bg-brandColor text-white shadow dark:border-gray-900">
                    <span class="{{ $event['icon'] }} text-xs"></span>
                </div>

                <!-- Event Content Card -->
                <div class="rounded-lg border bg-gray-50/50 p-3 transition-all hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-800/50 dark:hover:bg-gray-800">
                    <div class="flex items-center justify-between gap-2">
                        <span class="rounded px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $event['badge_color'] }}">
                            {{ $event['title'] }}
                        </span>
                        <span class="text-[11px] font-medium text-gray-400">
                            {{ $event['timestamp']->diffForHumans() }} ({{ $event['timestamp']->format('M d, Y h:i A') }})
                        </span>
                    </div>

                    @if (!empty($event['description']))
                        <div class="mt-2 text-xs text-gray-700 dark:text-gray-300">
                            {!! nl2br(e($event['description'])) !!}
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="p-6 text-center text-xs text-gray-400">
                No activity events recorded on timeline yet.
            </div>
        @endforelse
    </div>
</div>
