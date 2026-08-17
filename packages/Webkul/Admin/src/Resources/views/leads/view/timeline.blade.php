@php
    $timelineEvents = $lead->getChronologicalTimeline();

    $typeCategoryMap = [
        'lead_created' => 'lifecycle',
        'activity_call' => 'calls',
        'activity_meeting' => 'meetings',
        'activity_note' => 'notes',
        'activity_task' => 'tasks',
        'activity_email' => 'emails',
        'email_sent' => 'emails',
        'activity_whatsapp' => 'whatsapp',
        'follow_up_scheduled' => 'followup',
        'system' => 'lifecycle',
        'activity_system' => 'lifecycle',
    ];

    $badgeStyles = [
        'lead_created' => [
            'badge' => 'bg-indigo-50 text-indigo-700 border-indigo-200/70 dark:bg-indigo-950/50 dark:text-indigo-300 dark:border-indigo-800',
            'marker' => 'bg-indigo-600 text-white ring-indigo-100 dark:ring-indigo-950',
            'icon' => 'icon-add',
            'label' => 'Created',
        ],
        'activity_call' => [
            'badge' => 'bg-blue-50 text-blue-700 border-blue-200/70 dark:bg-blue-950/50 dark:text-blue-300 dark:border-blue-800',
            'marker' => 'bg-blue-600 text-white ring-blue-100 dark:ring-blue-950',
            'icon' => 'icon-phone',
            'label' => 'Call',
        ],
        'activity_meeting' => [
            'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200/70 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-800',
            'marker' => 'bg-emerald-600 text-white ring-emerald-100 dark:ring-emerald-950',
            'icon' => 'icon-calendar',
            'label' => 'Meeting',
        ],
        'activity_note' => [
            'badge' => 'bg-amber-50 text-amber-700 border-amber-200/70 dark:bg-amber-950/50 dark:text-amber-300 dark:border-amber-800',
            'marker' => 'bg-amber-500 text-white ring-amber-100 dark:ring-amber-950',
            'icon' => 'icon-note',
            'label' => 'Note',
        ],
        'activity_task' => [
            'badge' => 'bg-purple-50 text-purple-700 border-purple-200/70 dark:bg-purple-950/50 dark:text-purple-300 dark:border-purple-800',
            'marker' => 'bg-purple-600 text-white ring-purple-100 dark:ring-purple-950',
            'icon' => 'icon-task',
            'label' => 'Task',
        ],
        'activity_email' => [
            'badge' => 'bg-sky-50 text-sky-700 border-sky-200/70 dark:bg-sky-950/50 dark:text-sky-300 dark:border-sky-800',
            'marker' => 'bg-sky-600 text-white ring-sky-100 dark:ring-sky-950',
            'icon' => 'icon-mail',
            'label' => 'Email',
        ],
        'email_sent' => [
            'badge' => 'bg-sky-50 text-sky-700 border-sky-200/70 dark:bg-sky-950/50 dark:text-sky-300 dark:border-sky-800',
            'marker' => 'bg-sky-600 text-white ring-sky-100 dark:ring-sky-950',
            'icon' => 'icon-mail',
            'label' => 'Email Sent',
        ],
        'activity_whatsapp' => [
            'badge' => 'bg-green-50 text-green-700 border-green-200/70 dark:bg-green-950/50 dark:text-green-300 dark:border-green-800',
            'marker' => 'bg-green-600 text-white ring-green-100 dark:ring-green-950',
            'icon' => 'icon-message',
            'label' => 'WhatsApp',
        ],
        'follow_up_scheduled' => [
            'badge' => 'bg-orange-50 text-orange-700 border-orange-200/70 dark:bg-orange-950/50 dark:text-orange-300 dark:border-orange-800',
            'marker' => 'bg-orange-500 text-white ring-orange-100 dark:ring-orange-950',
            'icon' => 'icon-calendar',
            'label' => 'Follow-up',
        ],
        'activity_system' => [
            'badge' => 'bg-slate-100 text-slate-700 border-slate-200/70 dark:bg-gray-800 dark:text-slate-300 dark:border-gray-700',
            'marker' => 'bg-slate-600 text-white ring-slate-100 dark:ring-gray-800',
            'icon' => 'icon-activity',
            'label' => 'System Log',
        ],
        'default' => [
            'badge' => 'bg-slate-100 text-slate-700 border-slate-200/70 dark:bg-gray-800 dark:text-slate-300 dark:border-gray-700',
            'marker' => 'bg-slate-700 text-white ring-slate-100 dark:ring-gray-800',
            'icon' => 'icon-activity',
            'label' => 'Activity',
        ],
    ];
@endphp

<div class="bg-white dark:bg-gray-900" id="lead-timeline-container">
    <!-- Header & Filter Strip -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 dark:border-gray-800 pb-4">
        <div class="flex items-center gap-2.5">
            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900 dark:text-white">
                Chronological Activity Timeline
            </h2>
            <span class="rounded-full bg-slate-100 dark:bg-gray-800 px-2.5 py-0.5 text-[11px] font-bold text-slate-600 dark:text-slate-300">
                {{ $timelineEvents->count() }}
            </span>
        </div>

        <!-- Filter Chips -->
        <div class="flex flex-wrap items-center gap-1.5 text-xs" id="timeline-filters">
            <button
                type="button"
                onclick="filterTimeline('all', this)"
                class="timeline-filter-btn rounded-lg px-2.5 py-1 text-[11px] font-bold transition bg-slate-900 text-white dark:bg-white dark:text-slate-900"
                data-filter="all"
            >
                All ({{ $timelineEvents->count() }})
            </button>
            <button
                type="button"
                onclick="filterTimeline('notes', this)"
                class="timeline-filter-btn rounded-lg border border-slate-200 px-2.5 py-1 text-[11px] font-semibold text-slate-600 hover:bg-slate-50 transition dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                data-filter="notes"
            >
                Notes
            </button>
            <button
                type="button"
                onclick="filterTimeline('calls', this)"
                class="timeline-filter-btn rounded-lg border border-slate-200 px-2.5 py-1 text-[11px] font-semibold text-slate-600 hover:bg-slate-50 transition dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                data-filter="calls"
            >
                Calls
            </button>
            <button
                type="button"
                onclick="filterTimeline('meetings', this)"
                class="timeline-filter-btn rounded-lg border border-slate-200 px-2.5 py-1 text-[11px] font-semibold text-slate-600 hover:bg-slate-50 transition dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                data-filter="meetings"
            >
                Meetings
            </button>
            <button
                type="button"
                onclick="filterTimeline('emails', this)"
                class="timeline-filter-btn rounded-lg border border-slate-200 px-2.5 py-1 text-[11px] font-semibold text-slate-600 hover:bg-slate-50 transition dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                data-filter="emails"
            >
                Emails
            </button>
        </div>
    </div>

    <!-- Timeline Stream -->
    <div class="relative mt-6 pl-7 before:absolute before:left-3.5 before:top-3 before:bottom-3 before:w-0.5 before:bg-slate-200 dark:before:bg-gray-800">
        @forelse ($timelineEvents as $event)
            @php
                $category = $typeCategoryMap[$event['type']] ?? 'other';
                $style = $badgeStyles[$event['type']] ?? $badgeStyles['default'];
                $description = $event['description'] ?? '';
                if ($event['type'] === 'activity_system') {
                    $decoded = json_decode($description, true);
                    if ($decoded) {
                        $oldVal = $decoded['old']['label'] ?? $decoded['old']['value'] ?? $decoded['old']['old'] ?? '';
                        $newVal = $decoded['new']['label'] ?? $decoded['new']['value'] ?? '';
                        $attribute = $decoded['attribute'] ?? 'attribute';
                        if (is_array($oldVal)) { $oldVal = implode(', ', array_map(fn($v) => is_array($v) ? json_encode($v) : $v, $oldVal)); }
                        if (is_array($newVal)) { $newVal = implode(', ', array_map(fn($v) => is_array($v) ? json_encode($v) : $v, $newVal)); }
                        
                        $attrName = ucfirst(str_replace('_', ' ', $attribute));
                        if ($attribute === 'lead_pipeline_stage_id') {
                            $attrName = 'Stage';
                        } elseif ($attribute === 'user_id') {
                            $attrName = 'Owner';
                        }
                        
                        if (empty($oldVal) && !empty($newVal)) {
                            $description = "Set <strong>" . e($attrName) . "</strong> to <strong>" . e($newVal) . "</strong>";
                        } elseif (!empty($oldVal) && empty($newVal)) {
                            $description = "Cleared <strong>" . e($attrName) . "</strong> (was <strong>" . e($oldVal) . "</strong>)";
                        } else {
                            $description = "Changed <strong>" . e($attrName) . "</strong> from <strong>" . e($oldVal) . "</strong> to <strong>" . e($newVal) . "</strong>";
                        }
                    }
                }
                $descLength = strlen(trim(strip_tags($description)));
            @endphp

            <div
                class="timeline-item relative mb-6 last:mb-0 group"
                data-category="{{ $category }}"
            >
                <!-- Marker -->
                <div class="absolute -left-7 top-1 flex h-7 w-7 items-center justify-center rounded-full ring-4 {{ $style['marker'] }} shadow-xs transition-transform group-hover:scale-110">
                    <span class="{{ $style['icon'] }} text-xs"></span>
                </div>

                <!-- Event Card -->
                <div class="rounded-2xl border border-slate-200/80 bg-white p-4 sm:p-5 shadow-2xs hover:shadow-xs hover:border-slate-300 transition-all dark:bg-gray-900 dark:border-gray-800">
                    <!-- Top Row -->
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider border {{ $style['badge'] }}">
                                {{ $style['label'] }}
                            </span>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white truncate max-w-md sm:max-w-lg" title="{{ $event['title'] }}">
                                {{ $event['title'] }}
                            </h3>
                        </div>

                        <!-- Timestamp -->
                        <div class="flex items-center gap-1.5 text-xs text-slate-400 dark:text-slate-500 font-medium shrink-0">
                            <span class="font-bold text-slate-600 dark:text-slate-300">
                                {{ $event['timestamp']->diffForHumans() }}
                            </span>
                            <span class="hidden sm:inline text-[11px] text-slate-400">
                                ({{ $event['timestamp']->format('M d, Y • h:i A') }})
                            </span>
                        </div>
                    </div>

                    <!-- Description Body -->
                    @if (! empty($description))
                        <div class="mt-3 pt-3 border-t border-slate-100 dark:border-gray-800/80 text-xs sm:text-[13px] font-medium text-slate-700 dark:text-slate-300 leading-relaxed break-words whitespace-pre-wrap">
                            @if ($event['type'] === 'activity_system')
                                {!! nl2br($description) !!}
                            @elseif ($descLength > 280)
                                <div class="timeline-desc-clamped max-h-20 overflow-hidden relative transition-all duration-200">
                                    {!! nl2br(e($description)) !!}
                                    <div class="timeline-desc-fade absolute bottom-0 inset-x-0 h-8 bg-gradient-to-t from-white dark:from-gray-900 to-transparent pointer-events-none"></div>
                                </div>
                                <button
                                    type="button"
                                    onclick="toggleTimelineDesc(this)"
                                    class="mt-1 text-[11px] font-bold text-slate-900 dark:text-slate-200 hover:underline inline-flex items-center gap-1"
                                >
                                    <span>Read more</span>
                                    <svg class="w-3 h-3 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </button>
                            @else
                                {!! nl2br(e($description)) !!}
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-slate-200/80 border-dashed bg-slate-50/50 p-8 text-center dark:bg-gray-800/20 dark:border-gray-800">
                <div class="flex flex-col items-center gap-2 max-w-sm mx-auto">
                    <span class="icon-activity text-3xl text-slate-400 dark:text-slate-500"></span>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200">
                        No activity events recorded yet
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        All calls, notes, tasks, emails, and lead milestone events will appear here in chronological order.
                    </p>
                </div>
            </div>
        @endforelse

        <!-- Empty Filter Result Placeholder -->
        <div id="timeline-filter-empty" class="hidden rounded-2xl border border-slate-200/80 border-dashed bg-slate-50/50 p-8 text-center dark:bg-gray-800/20 dark:border-gray-800">
            <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                No events found matching this filter.
            </p>
        </div>
    </div>
</div>

<script>
    function filterTimeline(category, btn) {
        const container = document.getElementById('lead-timeline-container');
        if (!container) return;

        // Update active filter button styling
        const buttons = container.querySelectorAll('.timeline-filter-btn');
        buttons.forEach(b => {
            b.classList.remove('bg-slate-900', 'text-white', 'dark:bg-white', 'dark:text-slate-900');
            b.classList.add('border', 'border-slate-200', 'text-slate-600', 'dark:border-gray-700', 'dark:text-gray-300');
        });

        btn.classList.remove('border', 'border-slate-200', 'text-slate-600', 'dark:border-gray-700', 'dark:text-gray-300');
        btn.classList.add('bg-slate-900', 'text-white', 'dark:bg-white', 'dark:text-slate-900');

        // Filter items
        const items = container.querySelectorAll('.timeline-item');
        let visibleCount = 0;

        items.forEach(item => {
            if (category === 'all' || item.getAttribute('data-category') === category) {
                item.classList.remove('hidden');
                visibleCount++;
            } else {
                item.classList.add('hidden');
            }
        });

        const emptyMsg = document.getElementById('timeline-filter-empty');
        if (emptyMsg) {
            if (visibleCount === 0 && items.length > 0) {
                emptyMsg.classList.remove('hidden');
            } else {
                emptyMsg.classList.add('hidden');
            }
        }
    }

    function toggleTimelineDesc(btn) {
        const clampedDiv = btn.previousElementSibling;
        if (!clampedDiv) return;

        const isExpanded = clampedDiv.classList.contains('max-h-none');
        const fade = clampedDiv.querySelector('.timeline-desc-fade');
        const textSpan = btn.querySelector('span');
        const chevron = btn.querySelector('svg');

        if (isExpanded) {
            clampedDiv.classList.remove('max-h-none');
            clampedDiv.classList.add('max-h-20');
            if (fade) fade.classList.remove('hidden');
            if (textSpan) textSpan.textContent = 'Read more';
            if (chevron) chevron.classList.remove('rotate-180');
        } else {
            clampedDiv.classList.remove('max-h-20');
            clampedDiv.classList.add('max-h-none');
            if (fade) fade.classList.add('hidden');
            if (textSpan) textSpan.textContent = 'Read less';
            if (chevron) chevron.classList.add('rotate-180');
        }
    }
</script>
