<x-admin::layouts>
    <x-slot:title>
        Fields
    </x-slot>

    <div class="flex flex-col gap-6 p-2 sm:p-4">
        <!-- Header Bar -->
        <div class="sticky top-[60px] z-[10] flex flex-col gap-4 rounded-xl border border-gray-200 bg-white/80 p-5 backdrop-blur-md dark:border-gray-800 dark:bg-gray-900/80 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-col gap-1">
                <x-admin::breadcrumbs name="settings.attributes" />
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Fields
                </h1>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Manage and shape custom attributes for your workspace resources.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2">
                    <label for="entity-selector" class="text-xs font-semibold text-gray-500 dark:text-gray-400">Entity:</label>
                    <select id="entity-selector" class="rounded-lg border border-gray-300 dark:border-gray-700 py-1.5 px-3 text-xs font-semibold bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:ring-2 focus:ring-blue-500">
                        @foreach(config('moldable.entities', []) as $code => $meta)
                            <option value="{{ $code }}">{{ $meta['name'] ?? ucfirst($code) }}</option>
                        @endforeach
                    </select>
                </div>

                <button
                    type="button"
                    onclick="openAddFieldDrawer()"
                    class="primary-button inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold shadow-sm transition-all hover:scale-[1.02]"
                >
                    <span class="icon-add text-lg"></span>
                    + Add Field
                </button>
            </div>
        </div>

        <!-- Main Card Container -->
        <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
            <!-- Search & Filtering Section -->
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="relative w-full sm:w-96">
                    <span class="icon-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg"></span>
                    <input
                        type="text"
                        id="field-search-input"
                        placeholder="Search fields..."
                        oninput="filterFields(this.value)"
                        class="w-full rounded-lg border border-gray-300 bg-gray-50 py-2.5 pl-10 pr-4 text-sm text-gray-900 outline-none transition focus:border-blue-600 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:border-blue-500"
                    />
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Entity:</span>
                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-950 dark:text-blue-300">
                        Leads
                    </span>
                </div>
            </div>

            <!-- Field Groups Presentation Layer (MOLD-021) -->
            <div class="mb-6 flex items-center gap-2 overflow-x-auto border-b border-gray-200 pb-3 dark:border-gray-800 scrollbar-none">
                <span class="text-xs font-bold uppercase text-gray-400 dark:text-gray-500 mr-2">Groups:</span>
                <button type="button" class="rounded-full bg-blue-600 px-3.5 py-1.5 text-xs font-semibold text-white shadow-sm">Property Details</button>
                <button type="button" class="rounded-full bg-gray-100 dark:bg-gray-800 px-3.5 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700">Customer Details</button>
                <button type="button" class="rounded-full bg-gray-100 dark:bg-gray-800 px-3.5 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700">Project Details</button>
                <button type="button" class="rounded-full bg-gray-100 dark:bg-gray-800 px-3.5 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700">Financial Details</button>
                <button type="button" class="rounded-full bg-gray-100 dark:bg-gray-800 px-3.5 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700">Follow-up Details</button>
            </div>

            <!-- Field Group Section -->
            <div class="space-y-4">
                <div class="flex items-center justify-between border-b border-gray-200 pb-3 dark:border-gray-800">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-blue-600"></span>
                        Property Details (Lead Fields)
                    </h2>
                    <span id="field-count-badge" class="text-xs text-gray-500 dark:text-gray-400">4 fields</span>
                </div>

                <!-- Fields List with Drag & Drop (MOLD-020) -->
                <div id="fields-list-container" class="grid gap-3">
                    <!-- Property Type -->
                    <div class="field-item flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50/50 p-4 transition-all hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-800/40 cursor-grab active:cursor-grabbing" draggable="true" ondragstart="handleDragStart(event)" ondragover="handleDragOver(event)" ondrop="handleDrop(event)" data-id="1" data-name="Property Type" data-type="Select">
                        <div class="flex items-center gap-3">
                            <span class="text-gray-400 cursor-grab select-none">::</span>
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950 dark:text-indigo-300">
                                <span class="icon-chevron-down text-lg"></span>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Property Type</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 font-mono">property_type</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center rounded-md bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300">
                                Select
                            </span>
                        </div>
                    </div>

                    <!-- Budget -->
                    <div class="field-item flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50/50 p-4 transition-all hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-800/40 cursor-grab active:cursor-grabbing" draggable="true" ondragstart="handleDragStart(event)" ondragover="handleDragOver(event)" ondrop="handleDrop(event)" data-id="2" data-name="Budget" data-type="Price">
                        <div class="flex items-center gap-3">
                            <span class="text-gray-400 cursor-grab select-none">::</span>
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-300">
                                <span class="icon-currency-dollar text-lg"></span>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Budget</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 font-mono">budget</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                                Price
                            </span>
                        </div>
                    </div>

                    <!-- Location -->
                    <div class="field-item flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50/50 p-4 transition-all hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-800/40 cursor-grab active:cursor-grabbing" draggable="true" ondragstart="handleDragStart(event)" ondragover="handleDragOver(event)" ondrop="handleDrop(event)" data-id="3" data-name="Location" data-type="Text">
                        <div class="flex items-center gap-3">
                            <span class="text-gray-400 cursor-grab select-none">::</span>
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950 dark:text-blue-300">
                                <span class="icon-text text-lg"></span>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Location</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 font-mono">project_location</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center rounded-md bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 dark:bg-blue-950 dark:text-blue-300">
                                Text
                            </span>
                        </div>
                    </div>

                    <!-- Possession Date -->
                    <div class="field-item flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50/50 p-4 transition-all hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-800/40 cursor-grab active:cursor-grabbing" draggable="true" ondragstart="handleDragStart(event)" ondragover="handleDragOver(event)" ondrop="handleDrop(event)" data-id="4" data-name="Possession Date" data-type="Date">
                        <div class="flex items-center gap-3">
                            <span class="text-gray-400 cursor-grab select-none">::</span>
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-950 dark:text-amber-300">
                                <span class="icon-calendar text-lg"></span>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Possession Date</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 font-mono">possession_date</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center rounded-md bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700 dark:bg-amber-950 dark:text-amber-300">
                                Date
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div id="no-fields-matched" class="hidden py-12 text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">No fields matched your search.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Add Modal -->
    <div id="add-field-modal" class="hidden fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/50 backdrop-blur-sm p-4">
        <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-gray-900 dark:border dark:border-gray-800">
            <div class="flex items-center justify-between border-b pb-3 dark:border-gray-800">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Add Field</h3>
                <button type="button" onclick="document.getElementById('add-field-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    ✕
                </button>
            </div>
            <form id="add-field-form" class="mt-4 flex flex-col gap-4" onsubmit="handleAddFieldSubmit(event)">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">Field Label *</label>
                    <input type="text" name="name" required placeholder="e.g. Preferred Location" class="w-full rounded-lg border p-2.5 text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">Field Type *</label>
                    <select name="type" required class="w-full rounded-lg border p-2.5 text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                        <option value="text">Text</option>
                        <option value="textarea">Textarea</option>
                        <option value="price">Price</option>
                        <option value="boolean">Boolean</option>
                        <option value="select">Select</option>
                        <option value="multiselect">Multiselect</option>
                        <option value="date">Date</option>
                        <option value="datetime">Datetime</option>
                    </select>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('add-field-modal').classList.add('hidden')" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400">Cancel</button>
                    <button type="submit" class="primary-button px-4 py-2 text-sm">Save Field</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function filterFields(query) {
            const q = query.toLowerCase().trim();
            const items = document.querySelectorAll('.field-item');
            let visibleCount = 0;

            items.forEach(item => {
                const name = item.getAttribute('data-name').toLowerCase();
                const type = item.getAttribute('data-type').toLowerCase();
                if (name.includes(q) || type.includes(q)) {
                    item.classList.remove('hidden');
                    visibleCount++;
                } else {
                    item.classList.add('hidden');
                }
            });

            const emptyState = document.getElementById('no-fields-matched');
            const countBadge = document.getElementById('field-count-badge');

            if (emptyState) {
                if (visibleCount === 0) {
                    emptyState.classList.remove('hidden');
                } else {
                    emptyState.classList.add('hidden');
                }
            }

            if (countBadge) {
                countBadge.textContent = visibleCount + ' field' + (visibleCount === 1 ? '' : 's');
            }
        }

        function handleAddFieldSubmit(e) {
            e.preventDefault();
            const form = e.target;
            const name = form.name.value;
            const type = form.type.value;

            if (!name) return;

            const container = document.getElementById('fields-list-container');
            const newDiv = document.createElement('div');
            newDiv.className = 'field-item flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50/50 p-4 transition-all hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-800/40 dark:hover:border-gray-700';
            newDiv.setAttribute('data-name', name);
            newDiv.setAttribute('data-type', type.charAt(0).toUpperCase() + type.slice(1));
            
            const code = name.toLowerCase().replace(/\s+/g, '_');

            newDiv.innerHTML = `
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950 dark:text-blue-300">
                        <span class="icon-text text-lg"></span>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">\${name}</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-mono">\${code}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center rounded-md bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 dark:bg-blue-950 dark:text-blue-300">
                        \${type.charAt(0).toUpperCase() + type.slice(1)}
                    </span>
                </div>
            `;

            container.appendChild(newDiv);
            document.getElementById('add-field-modal').classList.add('hidden');
            form.reset();
        }

        let draggedItem = null;
        let previousDOMState = null;

        function handleDragStart(e) {
            draggedItem = e.currentTarget;
            e.dataTransfer.effectAllowed = 'move';
            e.currentTarget.classList.add('opacity-50');

            const container = document.getElementById('fields-list-container');
            previousDOMState = Array.from(container.children).map(node => node.cloneNode(true));
        }

        function handleDragOver(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            const target = e.currentTarget;
            if (target && target !== draggedItem && target.classList.contains('field-item')) {
                const container = document.getElementById('fields-list-container');
                const children = Array.from(container.children);
                const draggedIdx = children.indexOf(draggedItem);
                const targetIdx = children.indexOf(target);

                if (draggedIdx < targetIdx) {
                    container.insertBefore(draggedItem, target.nextSibling);
                } else {
                    container.insertBefore(draggedItem, target);
                }
            }
        }

        function handleDrop(e) {
            e.preventDefault();
            if (draggedItem) {
                draggedItem.classList.remove('opacity-50');

                const container = document.getElementById('fields-list-container');
                const items = Array.from(container.children);
                const orders = items.map((item, index) => ({
                    id: parseInt(item.getAttribute('data-id') || 0),
                    sort_order: index
                })).filter(o => o.id > 0);

                fetch('/v1/moldable/fields/reorder', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({ orders: orders })
                }).then(res => {
                    if (!res.ok) {
                        throw new Error('Reorder failed');
                    }
                }).catch(err => {
                    console.warn('Reorder failed, rolling back UI state:', err);
                    if (previousDOMState && container) {
                        container.innerHTML = '';
                        previousDOMState.forEach(node => container.appendChild(node));
                    }
                });

                draggedItem = null;
            }
        }
    </script>

    @include('moldable::builder.drawer')
</x-admin::layouts>
