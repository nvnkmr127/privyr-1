<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.leads.inbox.title')
    </x-slot>

    <!-- Page Header & View Switcher -->
    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between gap-4 max-md:flex-col max-md:items-start">
            <div class="flex items-center gap-3">
                <h1 class="text-xl font-bold text-gray-800 dark:text-white">
                    @lang('admin::app.leads.inbox.title')
                </h1>
                <span id="lead-count-badge" class="rounded-full bg-brandColor/10 px-2.5 py-0.5 text-xs font-semibold text-brandColor dark:bg-brandColor/20">
                    0 @lang('admin::app.leads.inbox.leads')
                </span>
            </div>

            <div class="flex items-center gap-3 max-md:w-full max-md:justify-between">
                @include('admin::leads.index.view-switcher', ['pipeline' => $pipelines->first()])

                <a
                    href="{{ route('admin.leads.create') }}"
                    class="primary-button flex items-center gap-2"
                >
                    <span class="icon-add text-base"></span>
                    @lang('admin::app.leads.index.create-btn-title')
                </a>
            </div>
        </div>

        <!-- Search & Filter Controls Bar -->
        <div class="flex flex-col gap-3 rounded-lg border bg-white p-3 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center gap-2">
                <!-- Search Input -->
                <div class="relative flex-1">
                    <span class="icon-search absolute left-3 top-1/2 -translate-y-1/2 text-xl text-gray-400"></span>
                    <input
                        type="text"
                        id="inbox-search-input"
                        placeholder="@lang('admin::app.leads.inbox.search-placeholder')"
                        class="w-full rounded-md border border-gray-300 py-2 pl-9 pr-3 text-sm focus:border-brandColor focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    />
                </div>

                <!-- Filter Toggle Drawer Button -->
                <button
                    type="button"
                    id="filter-drawer-toggle"
                    class="flex items-center gap-1.5 rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800"
                >
                    <span class="icon-filter text-lg"></span>
                    <span class="max-sm:hidden">@lang('admin::app.leads.inbox.filters')</span>
                </button>
            </div>

            <!-- Scrollable Preset Smart Tabs (Mobile First) -->
            <div class="no-scrollbar flex items-center gap-1.5 overflow-x-auto pb-1 pt-1">
                @php
                    $presets = [
                        'all' => trans('admin::app.leads.inbox.presets.all'),
                        'new' => trans('admin::app.leads.inbox.presets.new'),
                        'unread' => trans('admin::app.leads.inbox.presets.unread'),
                        'my_leads' => trans('admin::app.leads.inbox.presets.my_leads'),
                        'unassigned' => trans('admin::app.leads.inbox.presets.unassigned'),
                        'recently_contacted' => trans('admin::app.leads.inbox.presets.recently_contacted'),
                        'follow_up_due' => trans('admin::app.leads.inbox.presets.follow_up_due'),
                        'overdue_follow_ups' => trans('admin::app.leads.inbox.presets.overdue_follow_ups'),
                        'stale' => trans('admin::app.leads.inbox.presets.stale'),
                        'won' => trans('admin::app.leads.inbox.presets.won'),
                        'lost' => trans('admin::app.leads.inbox.presets.lost'),
                        'archived' => trans('admin::app.leads.inbox.presets.archived'),
                    ];
                @endphp

                @foreach ($presets as $key => $label)
                    <button
                        type="button"
                        data-preset="{{ $key }}"
                        class="preset-tab whitespace-nowrap rounded-full px-3.5 py-1.5 text-xs font-medium transition-all {{ $key === 'all' ? 'bg-brandColor text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700' }}"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Floating Mobile Bulk Action Toolbar (Hidden by default) -->
        <div id="bulk-action-bar" class="hidden items-center justify-between rounded-lg bg-gray-900 p-3 text-white shadow-lg dark:bg-gray-800">
            <div class="flex items-center gap-3">
                <input type="checkbox" id="select-all-inbox-checkbox" class="h-4 w-4 rounded border-gray-400 text-brandColor focus:ring-brandColor" />
                <span id="selected-count-label" class="text-xs font-semibold">0 selected</span>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" data-bulk-action="mark_read" class="rounded px-2 py-1 text-xs bg-gray-800 hover:bg-gray-700">@lang('admin::app.leads.inbox.actions.mark-read')</button>
                <button type="button" data-bulk-action="archive" class="rounded px-2 py-1 text-xs bg-gray-800 hover:bg-gray-700">@lang('admin::app.leads.inbox.actions.archive')</button>
                <button type="button" data-bulk-action="delete" class="rounded px-2 py-1 text-xs bg-red-600 hover:bg-red-700">@lang('admin::app.leads.inbox.actions.delete')</button>
            </div>
        </div>

        <!-- Lead Cards Container (Unified Mobile List View) -->
        <div id="lead-cards-list" class="flex flex-col gap-3">
            <div class="flex items-center justify-center p-8 text-gray-400">
                <span class="icon-spinner animate-spin text-3xl"></span>
            </div>
        </div>

        <!-- Pagination Bar -->
        <div id="inbox-pagination" class="flex items-center justify-between px-2 py-3 text-xs text-gray-500"></div>
    </div>

    <!-- Filter Modal Drawer -->
    <div id="filter-modal" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-sm transition-opacity">
        <div class="fixed inset-y-0 right-0 w-full max-w-xs bg-white p-5 shadow-xl dark:bg-gray-900 max-sm:max-w-full">
            <div class="flex items-center justify-between border-b pb-3 dark:border-gray-800">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">@lang('admin::app.leads.inbox.filters')</h3>
                <button type="button" id="close-filter-modal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <span class="icon-cross text-2xl"></span>
                </button>
            </div>

            <form id="inbox-filter-form" class="mt-4 flex flex-col gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300">@lang('admin::app.leads.inbox.fields.priority')</label>
                    <select name="priority" class="mt-1 w-full rounded-md border border-gray-300 p-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="">@lang('admin::app.leads.inbox.all')</option>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300">@lang('admin::app.leads.inbox.fields.source')</label>
                    <select name="lead_source_id" class="mt-1 w-full rounded-md border border-gray-300 p-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="">@lang('admin::app.leads.inbox.all')</option>
                        @foreach ($sources as $source)
                            <option value="{{ $source->id }}">{{ $source->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300">@lang('admin::app.leads.inbox.fields.owner')</label>
                    <select name="user_id" class="mt-1 w-full rounded-md border border-gray-300 p-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="">@lang('admin::app.leads.inbox.all')</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mt-4 flex items-center justify-end gap-2 border-t pt-4 dark:border-gray-800">
                    <button type="reset" id="reset-filters-btn" class="rounded-md border border-gray-300 px-3 py-1.5 text-xs text-gray-600 dark:border-gray-700 dark:text-gray-300">
                        @lang('admin::app.leads.inbox.reset')
                    </button>
                    <button type="submit" class="rounded-md bg-brandColor px-4 py-1.5 text-xs text-white shadow">
                        @lang('admin::app.leads.inbox.apply')
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Client-side Javascript logic for Inbox, Touch Swipe, Filters & Bulk Actions -->
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let activePreset = '{{ $currentPreset }}';
            let currentPage = 1;
            let selectedLeadIds = [];
            let searchDebounce = null;

            const cardsContainer = document.getElementById('lead-cards-list');
            const countBadge = document.getElementById('lead-count-badge');
            const searchInput = document.getElementById('inbox-search-input');
            const bulkBar = document.getElementById('bulk-action-bar');
            const selectedCountLabel = document.getElementById('selected-count-label');
            const selectAllCheckbox = document.getElementById('select-all-inbox-checkbox');

            // Fetch Leads Function
            function fetchLeads(page = 1) {
                currentPage = page;
                cardsContainer.innerHTML = '<div class="flex items-center justify-center p-8 text-gray-400"><span class="icon-spinner animate-spin text-3xl"></span></div>';

                const searchVal = searchInput.value;
                const form = document.getElementById('inbox-filter-form');
                const formData = new FormData(form);
                const queryParams = new URLSearchParams(formData);

                queryParams.set('preset', activePreset);
                queryParams.set('page', page);
                if (searchVal) queryParams.set('search', searchVal);

                fetch(`{{ route('admin.leads.inbox.data') }}?${queryParams.toString()}`)
                    .then(res => res.json())
                    .then(response => {
                        renderLeadCards(response.data);
                        updatePagination(response.meta);
                    })
                    .catch(() => {
                        cardsContainer.innerHTML = '<div class="p-4 text-center text-red-500 text-sm">Failed to load leads</div>';
                    });
            }

            // Render Lead Cards with Swipe & Touch interactions
            function renderLeadCards(leads) {
                cardsContainer.innerHTML = '';

                if (!leads || leads.length === 0) {
                    cardsContainer.innerHTML = `
                        <div class="flex flex-col items-center justify-center rounded-lg border border-dashed border-gray-300 p-8 text-center dark:border-gray-800">
                            <span class="icon-mail text-4xl text-gray-400 mb-2"></span>
                            <p class="text-sm font-semibold text-gray-600 dark:text-gray-300">No leads found</p>
                            <p class="text-xs text-gray-400 mt-1">Try selecting a different filter tab or search criteria.</p>
                        </div>
                    `;
                    countBadge.innerText = '0 leads';
                    return;
                }

                leads.forEach(lead => {
                    const card = document.createElement('div');
                    card.className = 'relative overflow-hidden rounded-xl border bg-white shadow-sm transition-all dark:border-gray-800 dark:bg-gray-900 group';
                    card.dataset.leadId = lead.id;

                    const priorityColors = {
                        urgent: 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300',
                        high: 'bg-orange-100 text-orange-700 dark:bg-orange-950 dark:text-orange-300',
                        medium: 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300',
                        low: 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'
                    };

                    const priorityBadge = lead.priority
                        ? `<span class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider ${priorityColors[lead.priority] || priorityColors.medium}">${lead.priority}</span>`
                        : '';

                    const unreadDot = lead.is_unread
                        ? '<span class="h-2.5 w-2.5 rounded-full bg-brandColor animate-pulse"></span>'
                        : '';

                    const personName = lead.person ? lead.person.name : 'No Contact Person';
                    const ownerName = lead.user ? lead.user.name : 'Unassigned';
                    const stageName = lead.stage ? lead.stage.name : 'Lead';

                    card.innerHTML = `
                        <!-- Action Reveal Backgrounds (Swipe Left / Swipe Right) -->
                        <div class="swipe-bg-left absolute inset-y-0 left-0 hidden w-1/2 items-center justify-start bg-emerald-600 pl-4 text-white font-medium text-xs">
                            <span class="icon-phone text-lg mr-1"></span> Call / Contacted
                        </div>
                        <div class="swipe-bg-right absolute inset-y-0 right-0 hidden w-1/2 items-center justify-end bg-blue-600 pr-4 text-white font-medium text-xs">
                            <span class="icon-calendar text-lg mr-1"></span> Follow-up
                        </div>

                        <!-- Card Content -->
                        <div class="card-content relative z-10 p-4 transition-transform bg-white dark:bg-gray-900">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" class="lead-select-checkbox h-4 w-4 rounded border-gray-300 text-brandColor focus:ring-brandColor" value="${lead.id}" ${selectedLeadIds.includes(lead.id) ? 'checked' : ''} />
                                    ${unreadDot}
                                    <a href="/admin/leads/view/${lead.id}" class="text-base font-bold text-gray-900 hover:text-brandColor dark:text-white">
                                        ${lead.title}
                                    </a>
                                </div>
                                ${priorityBadge}
                            </div>

                            <div class="mt-2 flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                                <span class="flex items-center gap-1">
                                    <span class="icon-user text-sm"></span> ${personName}
                                </span>
                                <span class="flex items-center gap-1">
                                    <span class="icon-kanban text-sm"></span> ${stageName}
                                </span>
                                <span class="flex items-center gap-1">
                                    <span class="icon-settings text-sm"></span> ${ownerName}
                                </span>
                            </div>

                            <div class="mt-3 flex items-center justify-between border-t pt-2 text-[11px] text-gray-400 dark:border-gray-800">
                                <span>Value: <strong>$${lead.lead_value || 0}</strong></span>
                                <div class="flex items-center gap-2">
                                    <button type="button" class="quick-action-btn hover:text-brandColor" data-action="mark_contacted" data-id="${lead.id}" title="Mark Contacted">
                                        <span class="icon-phone text-sm"></span>
                                    </button>
                                    <button type="button" class="quick-action-btn hover:text-brandColor" data-action="archive" data-id="${lead.id}" title="Archive">
                                        <span class="icon-mail text-sm"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;

                    // Add Touch Swipe Gesture Listeners
                    setupSwipeGestures(card, lead.id);
                    cardsContainer.appendChild(card);
                });

                // Attach checkbox & quick action listeners
                document.querySelectorAll('.lead-select-checkbox').forEach(cb => {
                    cb.addEventListener('change', handleCheckboxChange);
                });
                document.querySelectorAll('.quick-action-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        triggerSwipeAction(this.dataset.id, this.dataset.action);
                    });
                });
            }

            // Touch Swipe Handler for Mobile
            function setupSwipeGestures(cardElement, leadId) {
                let startX = 0;
                let currentX = 0;
                const content = cardElement.querySelector('.card-content');
                const bgLeft = cardElement.querySelector('.swipe-bg-left');
                const bgRight = cardElement.querySelector('.swipe-bg-right');

                content.addEventListener('touchstart', (e) => {
                    startX = e.touches[0].clientX;
                }, { passive: true });

                content.addEventListener('touchmove', (e) => {
                    currentX = e.touches[0].clientX;
                    const diff = currentX - startX;

                    if (diff > 20) {
                        bgLeft.classList.remove('hidden');
                        bgLeft.classList.add('flex');
                        bgRight.classList.add('hidden');
                        content.style.transform = `translateX(${Math.min(diff, 100)}px)`;
                    } else if (diff < -20) {
                        bgRight.classList.remove('hidden');
                        bgRight.classList.add('flex');
                        bgLeft.classList.add('hidden');
                        content.style.transform = `translateX(${Math.max(diff, -100)}px)`;
                    }
                }, { passive: true });

                content.addEventListener('touchend', () => {
                    const diff = currentX - startX;
                    content.style.transform = 'translateX(0px)';

                    if (diff > 80) {
                        triggerSwipeAction(leadId, 'mark_contacted');
                    } else if (diff < -80) {
                        triggerSwipeAction(leadId, 'archive');
                    }
                    startX = 0;
                    currentX = 0;
                });
            }

            // Single Swipe Action Execution
            function triggerSwipeAction(leadId, action) {
                fetch(`{{ route('admin.leads.inbox.swipe') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ lead_id: leadId, action: action })
                })
                .then(res => res.json())
                .then(() => fetchLeads(currentPage));
            }

            // Pagination Update
            function updatePagination(meta) {
                countBadge.innerText = `${meta.total} leads`;

                const pagContainer = document.getElementById('inbox-pagination');
                pagContainer.innerHTML = `
                    <span>Page ${meta.current_page} of ${meta.last_page} (${meta.total} total)</span>
                    <div class="flex gap-1">
                        <button type="button" class="rounded border px-2 py-1 ${meta.current_page === 1 ? 'opacity-50 cursor-not-allowed' : ''}" ${meta.current_page === 1 ? 'disabled' : ''} id="prev-page">Prev</button>
                        <button type="button" class="rounded border px-2 py-1 ${meta.current_page === meta.last_page ? 'opacity-50 cursor-not-allowed' : ''}" ${meta.current_page === meta.last_page ? 'disabled' : ''} id="next-page">Next</button>
                    </div>
                `;

                const prevBtn = document.getElementById('prev-page');
                const nextBtn = document.getElementById('next-page');
                if (prevBtn) prevBtn.addEventListener('click', () => fetchLeads(meta.current_page - 1));
                if (nextBtn) nextBtn.addEventListener('click', () => fetchLeads(meta.current_page + 1));
            }

            // Checkbox & Bulk Selection Handler
            function handleCheckboxChange() {
                selectedLeadIds = Array.from(document.querySelectorAll('.lead-select-checkbox:checked')).map(cb => parseInt(cb.value));

                if (selectedLeadIds.length > 0) {
                    bulkBar.classList.remove('hidden');
                    bulkBar.classList.add('flex');
                    selectedCountLabel.innerText = `${selectedLeadIds.length} selected`;
                } else {
                    bulkBar.classList.add('hidden');
                    bulkBar.classList.remove('flex');
                }
            }

            selectAllCheckbox.addEventListener('change', function () {
                const checkboxes = document.querySelectorAll('.lead-select-checkbox');
                checkboxes.forEach(cb => {
                    cb.checked = this.checked;
                });
                handleCheckboxChange();
            });

            // Bulk Action Execution
            document.querySelectorAll('[data-bulk-action]').forEach(btn => {
                btn.addEventListener('click', function () {
                    const action = this.dataset.bulkAction;
                    if (selectedLeadIds.length === 0) return;

                    fetch(`{{ route('admin.leads.inbox.bulk') }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ lead_ids: selectedLeadIds, action: action })
                    })
                    .then(res => res.json())
                    .then(() => {
                        selectedLeadIds = [];
                        selectAllCheckbox.checked = false;
                        handleCheckboxChange();
                        fetchLeads(currentPage);
                    });
                });
            });

            // Preset Tabs Event Listeners
            document.querySelectorAll('.preset-tab').forEach(tab => {
                tab.addEventListener('click', function () {
                    document.querySelectorAll('.preset-tab').forEach(t => {
                        t.className = 'preset-tab whitespace-nowrap rounded-full px-3.5 py-1.5 text-xs font-medium transition-all bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700';
                    });
                    this.className = 'preset-tab whitespace-nowrap rounded-full px-3.5 py-1.5 text-xs font-medium transition-all bg-brandColor text-white shadow-sm';
                    activePreset = this.dataset.preset;
                    fetchLeads(1);
                });
            });

            // Search Debounce
            searchInput.addEventListener('input', function () {
                clearTimeout(searchDebounce);
                searchDebounce = setTimeout(() => fetchLeads(1), 350);
            });

            // Filter Modal Toggle
            const filterModal = document.getElementById('filter-modal');
            document.getElementById('filter-drawer-toggle').addEventListener('click', () => {
                filterModal.classList.remove('hidden');
            });
            document.getElementById('close-filter-modal').addEventListener('click', () => {
                filterModal.classList.add('hidden');
            });
            document.getElementById('inbox-filter-form').addEventListener('submit', (e) => {
                e.preventDefault();
                filterModal.classList.add('hidden');
                fetchLeads(1);
            });
            document.getElementById('reset-filters-btn').addEventListener('click', () => {
                document.getElementById('inbox-filter-form').reset();
                filterModal.classList.add('hidden');
                fetchLeads(1);
            });

            // Initial load
            fetchLeads(1);
        });
    </script>
    @endpush
</x-admin::layouts>
