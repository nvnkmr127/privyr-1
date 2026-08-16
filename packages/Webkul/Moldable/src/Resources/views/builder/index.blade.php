<x-moldable::layouts.app>
    <x-slot:title>
        Field Builder - Moldable Enterprise CRM
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-8">

        <!-- Top Page Header Bar -->
        <div class="scroll-reactive-sticky flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-col gap-1">
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-black tracking-tight text-slate-900">
                        Field Builder
                    </h1>
                    <span class="rounded-full bg-slate-100 border border-slate-200 px-3 py-1 text-xs font-bold text-slate-700">
                        Moldable CRM v2.0
                    </span>
                </div>
                <p class="text-xs font-medium text-slate-400">
                    Create and manage custom fields and presentation groups for your entities.
                </p>
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-2 shadow-2xs">
                    <label for="entity-selector" class="text-xs font-medium text-slate-400">Target Entity</label>
                    <select id="entity-selector" onchange="MoldableBuilder.setEntity(this.value)" class="rounded-lg border-0 py-0 pl-1 pr-6 text-xs font-bold text-slate-900 bg-transparent outline-none cursor-pointer">
                        @foreach(config('moldable.entities', ['leads' => ['name' => 'Leads'], 'persons' => ['name' => 'Persons'], 'organizations' => ['name' => 'Organizations'], 'products' => ['name' => 'Products'], 'quotes' => ['name' => 'Quotes']]) as $code => $meta)
                            <option value="{{ $code }}">{{ $meta['name'] ?? ucfirst($code) }}</option>
                        @endforeach
                    </select>
                </div>

                <button
                    type="button"
                    onclick="openAddFieldDrawer()"
                    class="rounded-xl bg-slate-900 hover:bg-black text-white px-5 py-2.5 text-xs font-bold shadow-md shadow-slate-900/10 transition flex items-center gap-2"
                >
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Add Field
                </button>
            </div>
        </div>

        <!-- Entities Switcher Navigation Tabs Bar -->
        <div id="entity-pill-bar" class="flex items-center gap-3 overflow-x-auto rounded-2xl border border-slate-200/80 bg-white p-3 shadow-2xs scrollbar-none">
            <span class="text-xs font-black text-slate-900 px-3 uppercase tracking-wider">Entities</span>
            <div class="flex items-center gap-2">
                @foreach(config('moldable.entities', ['leads' => ['name' => 'Leads'], 'persons' => ['name' => 'Persons'], 'organizations' => ['name' => 'Organizations'], 'products' => ['name' => 'Products'], 'quotes' => ['name' => 'Quotes']]) as $code => $meta)
                    <button type="button" onclick="switchEntityPill(this, '{{ $code }}')" data-entity-pill="{{ $code }}" class="entity-pill-btn flex items-center gap-2 rounded-xl border {{ $loop->first ? 'border-slate-900 bg-white text-slate-900' : 'border-slate-200 bg-slate-50 text-slate-600 hover:bg-slate-100' }} px-4 py-2 text-xs font-bold shadow-2xs transition">
                        <span>{{ $meta['name'] ?? ucfirst($code) }}</span>
                        <span data-entity-count="{{ $code }}" class="rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-700">0</span>
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Middle 2-Column Grid (Presentation Groups & Overview/Quick Field Types) -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">

            <!-- Left Box: Presentation Groups -->
            <div class="lg:col-span-7 xl:col-span-7">
                <div class="h-full rounded-2xl border border-slate-200/80 bg-white p-6 shadow-2xs flex flex-col justify-between space-y-6">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <div>
                            <h2 class="text-sm font-bold text-slate-900">Presentation Groups</h2>
                            <p class="text-xs text-slate-400 mt-0.5">Organize fields into meaningful sections</p>
                        </div>
                        <button type="button" onclick="openNewGroupModal()" class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Add Group
                        </button>
                    </div>

                    <!-- Groups are loaded from /v1/moldable/groups for the active entity. -->
                    <div id="field-groups-nav" class="space-y-3">
                        <div class="py-6 text-center text-xs text-slate-400">Loading groups…</div>
                    </div>
                </div>
            </div>

            <!-- Right Box Stack: Resource Overview & Quick Field Types -->
            <div class="lg:col-span-5 xl:col-span-5 space-y-6">

                <!-- Card 1: Resource Overview / Metrics -->
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-2xs space-y-4">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Resource Overview</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Resource Metrics &middot; field builder summary</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="rounded-2xl border border-slate-200/80 bg-white p-4 flex items-center gap-4 shadow-2xs">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-800 font-bold border border-slate-200/60">
                                <svg class="w-5 h-5 text-slate-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <div>
                                <div class="text-2xl font-black text-slate-900" id="stat-total-fields">0</div>
                                <div class="text-xs font-bold text-slate-800">Total Fields</div>
                                <div class="text-[10px] text-slate-400">For this entity</div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200/80 bg-white p-4 flex items-center gap-4 shadow-2xs">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-800 font-bold border border-slate-200/60">
                                <svg class="w-5 h-5 text-slate-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            </div>
                            <div>
                                <div class="text-2xl font-black text-slate-900" id="stat-quick-add">0</div>
                                <div class="text-xs font-bold text-slate-800">Quick Add Fields</div>
                                <div class="text-[10px] text-slate-400">Added for faster entry</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Quick Field Types -->
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-2xs space-y-4">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Quick Field Types</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Add commonly used field types</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <button type="button" onclick="openAddFieldDrawer('text')" class="flex items-center gap-3 rounded-xl border border-slate-200/80 bg-white p-3 hover:border-slate-400 transition text-left shadow-2xs">
                            <span class="font-bold text-xs text-slate-800 bg-slate-100 p-2 rounded-lg">T</span>
                            <span class="text-xs font-bold text-slate-900">Text</span>
                        </button>
                        <button type="button" onclick="openAddFieldDrawer('select')" class="flex items-center gap-3 rounded-xl border border-slate-200/80 bg-white p-3 hover:border-slate-400 transition text-left shadow-2xs">
                            <span class="font-bold text-xs text-slate-800 bg-slate-100 p-2 rounded-lg">≡</span>
                            <span class="text-xs font-bold text-slate-900">Select</span>
                        </button>
                        <button type="button" onclick="openAddFieldDrawer('price')" class="flex items-center gap-3 rounded-xl border border-slate-200/80 bg-white p-3 hover:border-slate-400 transition text-left shadow-2xs">
                            <span class="font-bold text-xs text-slate-800 bg-slate-100 p-2 rounded-lg">$</span>
                            <span class="text-xs font-bold text-slate-900">Price</span>
                        </button>
                        <button type="button" onclick="openAddFieldDrawer('date')" class="flex items-center gap-3 rounded-xl border border-slate-200/80 bg-white p-3 hover:border-slate-400 transition text-left shadow-2xs">
                            <svg class="w-4 h-4 text-slate-800 bg-slate-100 p-1.5 rounded-lg box-content" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span class="text-xs font-bold text-slate-900">Date</span>
                        </button>
                        <button type="button" onclick="openAddFieldDrawer('lookup')" class="flex items-center gap-3 rounded-xl border border-slate-200/80 bg-white p-3 hover:border-slate-400 transition text-left shadow-2xs">
                            <svg class="w-4 h-4 text-slate-800 bg-slate-100 p-1.5 rounded-lg box-content" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            <span class="text-xs font-bold text-slate-900">Lookup</span>
                        </button>
                        <button type="button" onclick="openAddFieldDrawer('file')" class="flex items-center gap-3 rounded-xl border border-slate-200/80 bg-white p-3 hover:border-slate-400 transition text-left shadow-2xs">
                            <svg class="w-4 h-4 text-slate-800 bg-slate-100 p-1.5 rounded-lg box-content" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                            <span class="text-xs font-bold text-slate-900">File</span>
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <!-- Bottom Attributes List Canvas Card -->
        <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-2xs space-y-6">

            <!-- Search Bar & Active Entity Indicator -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-slate-100 pb-5">
                <div class="relative flex-1 max-w-lg">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input
                        type="text"
                        id="field-search-input"
                        placeholder="Search attributes by title or code..."
                        oninput="filterFields(this.value)"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/60 py-2.5 pl-10 pr-4 text-xs text-slate-900 outline-none transition focus:border-slate-400 focus:bg-white placeholder-slate-400"
                    />
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-xs font-medium text-slate-400">Active Entity:</span>
                    <span id="selected-entity-badge" class="rounded-xl bg-white px-3.5 py-1.5 text-xs font-bold text-slate-900 border border-slate-200 shadow-2xs">
                        Leads
                    </span>
                </div>
            </div>

            <!-- Group Header Section -->
            <div class="flex items-center justify-between">
                <h2 id="active-group-header-title" class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    <span>All Fields ( Leads )</span>
                </h2>
                <span id="field-count-badge" class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">0 fields</span>
            </div>

            <!-- Fields Canvas List (Drag & Drop Reorderable) — populated from the API -->
            <div id="fields-list-container" class="space-y-3">
                <div class="py-6 text-center text-xs text-slate-400">Loading fields…</div>
            </div>

            <!-- Bottom Add Field Button Center Link -->
            <div class="pt-4 text-center">
                <button type="button" onclick="openAddFieldDrawer()" class="text-xs font-bold text-slate-900 hover:underline inline-flex items-center gap-1.5">
                    <span class="text-sm">+</span> Add New Field
                </button>
            </div>

            <!-- Search Empty State -->
            <div id="no-fields-matched" class="hidden py-12 text-center border border-dashed border-slate-200 rounded-2xl">
                <svg class="w-8 h-8 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <p class="text-xs text-slate-500">No matching fields found for your query.</p>
            </div>

            <!-- Empty State (no fields yet) -->
            <div id="no-fields-yet" class="hidden py-12 text-center border border-dashed border-slate-200 rounded-2xl">
                <p class="text-xs text-slate-500">No custom fields for this entity yet. Click <span class="font-bold">Add Field</span> to create one.</p>
            </div>

        </div>

    </div>

    <script>
        const MoldableBuilder = (function () {
            const API = '/v1/moldable';
            const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            // Seed group names used when an entity has no saved presentation groups yet:
            // Property Details, Customer Details, Project Details, Financial Details, Follow-up Details.
            const DEFAULT_GROUPS = ['Property Details', 'Customer Details', 'Project Details', 'Financial Details', 'Follow-up Details'];

            const TYPE_ICON = { text: 'T', textarea: '¶', price: '$', boolean: '◉', select: '≡', multiselect: '≣', checkbox: '☑', date: '📅', datetime: '🕑', lookup: '🔍', email: '@', phone: '☎', address: '⌂', file: '📎', image: '🖼' };

            let currentEntity = 'leads';
            let allFields = [];

            function api(path, opts = {}) {
                return fetch(API + path, {
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    ...opts,
                }).then(async (res) => {
                    const isJson = (res.headers.get('content-type') || '').includes('json');
                    const body = isJson ? await res.json().catch(() => null) : null;
                    if (!res.ok) {
                        const msg = (body && body.message) || ('Request failed (' + res.status + ')');
                        throw Object.assign(new Error(msg), { status: res.status, body });
                    }
                    return body;
                });
            }

            function esc(s) {
                return String(s == null ? '' : s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
            }

            function entityFields() {
                return allFields.filter((f) => f.entity_type === currentEntity)
                    .sort((a, b) => (a.sort_order - b.sort_order) || (a.id - b.id));
            }

            async function refresh() {
                try {
                    allFields = await api('/fields');
                } catch (err) {
                    document.getElementById('fields-list-container').innerHTML =
                        '<div class="py-6 text-center text-xs text-red-500">' + esc(err.message) + '</div>';
                    return;
                }
                updatePillCounts();
                renderFields();
                loadGroups();
            }

            function updatePillCounts() {
                document.querySelectorAll('[data-entity-count]').forEach((el) => {
                    const code = el.getAttribute('data-entity-count');
                    el.textContent = allFields.filter((f) => f.entity_type === code).length;
                });
            }

            function fieldCard(field) {
                const icon = TYPE_ICON[field.type] || '•';
                const typeLabel = field.type.charAt(0).toUpperCase() + field.type.slice(1);
                const badges =
                    (field.is_required ? '<span class="text-[10px] font-bold text-red-600 bg-red-50 px-2 py-0.5 rounded-full">• Required</span>' : '') +
                    (field.is_unique ? '<span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full" data-unique=1>Unique</span>' : '') +
                    (field.quick_add ? '<span class="text-[10px] font-bold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-full">Quick Add</span>' : '');

                return `
                <div class="field-item flex items-center justify-between rounded-2xl border border-slate-200/80 bg-white p-4 transition-all hover:border-slate-400 hover:shadow-2xs cursor-grab active:cursor-grabbing" draggable="true" ondragstart="handleDragStart(event)" ondragover="handleDragOver(event)" ondrop="handleDrop(event)" data-id="${field.id}" data-name="${esc(field.name)}" data-type="${esc(typeLabel)}">
                    <div class="flex items-center gap-4">
                        <span class="text-slate-300 select-none text-xs font-bold hover:text-slate-500">⋮⋮</span>
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-700 font-bold border border-slate-200/60">${esc(icon)}</div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-xs font-bold text-slate-900">${esc(field.name)}</h3>
                                ${badges}
                            </div>
                            <p class="text-[11px] text-slate-400 font-mono mt-0.5">${esc(field.code)}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="inline-flex items-center rounded-lg bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">${esc(typeLabel)}</span>
                        <div class="flex items-center gap-2 border-l border-slate-100 pl-3">
                            <button type="button" onclick="MoldableBuilder.edit(${field.id})" title="Edit Field" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition">
                                <svg class="w-4 h-4 text-slate-600 icon-edit" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            </button>
                            <button type="button" onclick="MoldableBuilder.remove(${field.id})" title="Delete Field" class="rounded-lg p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-600 transition">
                                <svg class="w-4 h-4 text-slate-600 icon-delete" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>`;
            }

            function renderFields() {
                const container = document.getElementById('fields-list-container');
                const fields = entityFields();

                document.getElementById('stat-total-fields').textContent = fields.length;
                document.getElementById('stat-quick-add').textContent = fields.filter((f) => f.quick_add).length;
                document.getElementById('field-count-badge').textContent = fields.length + ' field' + (fields.length === 1 ? '' : 's');

                const emptyYet = document.getElementById('no-fields-yet');
                if (fields.length === 0) {
                    container.innerHTML = '';
                    emptyYet.classList.remove('hidden');
                    return;
                }
                emptyYet.classList.add('hidden');
                container.innerHTML = fields.map(fieldCard).join('');
                filterFields(document.getElementById('field-search-input')?.value || '');
            }

            function groupCard(group, count) {
                return `
                <div onclick="selectGroupTab(this, '${esc(group.name)}')" class="group-tab-btn flex items-center justify-between rounded-xl border border-slate-200/80 p-4 hover:border-slate-400 bg-white transition cursor-pointer shadow-2xs" data-group-id="${group.id || ''}">
                    <div class="flex items-center gap-4">
                        <span class="text-slate-300 select-none text-xs font-bold">⋮⋮</span>
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-700 font-bold border border-slate-200/60">
                            <svg class="w-5 h-5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-xs font-bold text-slate-900">${esc(group.name)}</h3>
                            <p class="text-[11px] text-slate-400 mt-0.5">${group.id ? 'Presentation group' : 'Suggested group (not saved yet)'}</p>
                        </div>
                    </div>
                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">${count}</span>
                </div>`;
            }

            async function loadGroups() {
                const nav = document.getElementById('field-groups-nav');
                let groups = [];
                try {
                    groups = await api('/groups?entity_type=' + encodeURIComponent(currentEntity));
                } catch (err) {
                    // Groups need a workspace; if that fails, fall back to suggested defaults.
                    groups = [];
                }
                if (!Array.isArray(groups) || groups.length === 0) {
                    nav.innerHTML = DEFAULT_GROUPS.map((name) => groupCard({ name, id: null }, 0)).join('');
                    return;
                }
                nav.innerHTML = groups.map((g) => groupCard(g, (g.group_attributes || g.groupAttributes || []).length)).join('');
            }

            async function createGroup(name) {
                try {
                    await api('/groups', { method: 'POST', body: JSON.stringify({ name, entity_type: currentEntity }) });
                    await loadGroups();
                } catch (err) {
                    alert(err.message || 'Could not create group.');
                }
            }

            async function remove(id) {
                if (!confirm('Delete this field? Existing values for it will no longer be shown.')) return;
                try {
                    await api('/fields/' + id, { method: 'DELETE' });
                    await refresh();
                } catch (err) {
                    alert(err.message || 'Could not delete the field.');
                }
            }

            function edit(id) {
                const field = allFields.find((f) => String(f.id) === String(id));
                if (field) openAddFieldDrawer(null, field);
            }

            function setEntity(entity) {
                currentEntity = entity;
                const badge = document.getElementById('selected-entity-badge');
                const selector = document.getElementById('entity-selector');
                const label = selector ? selector.options[selector.selectedIndex].text : entity;
                if (badge) badge.textContent = label;
                if (selector && selector.value !== entity) selector.value = entity;
                const header = document.getElementById('active-group-header-title');
                if (header) header.querySelector('span').textContent = 'All Fields ( ' + label + ' )';
                renderFields();
                loadGroups();
            }

            return { api, refresh, remove, edit, createGroup, setEntity, get entity() { return currentEntity; } };
        })();

        function switchEntityPill(btn, entityCode) {
            document.querySelectorAll('.entity-pill-btn').forEach((b) => {
                b.className = 'entity-pill-btn flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 transition';
            });
            btn.className = 'entity-pill-btn flex items-center gap-2 rounded-xl border border-slate-900 bg-white px-4 py-2 text-xs font-bold text-slate-900 transition shadow-2xs';
            MoldableBuilder.setEntity(entityCode);
        }

        function selectGroupTab(element, groupName) {
            const header = document.getElementById('active-group-header-title');
            const selector = document.getElementById('entity-selector');
            const entityText = selector ? selector.options[selector.selectedIndex].text : 'Leads';
            if (header) {
                header.innerHTML = `<svg class="w-3.5 h-3.5 text-slate-400 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg> <span>${groupName} ( ${entityText} )</span>`;
            }
        }

        function openNewGroupModal() {
            const groupName = prompt('Enter New Field Group Name:');
            if (groupName && groupName.trim()) {
                MoldableBuilder.createGroup(groupName.trim());
            }
        }

        function filterFields(query) {
            const q = query.toLowerCase().trim();
            const items = document.querySelectorAll('.field-item');
            let visibleCount = 0;
            items.forEach((item) => {
                const name = (item.getAttribute('data-name') || '').toLowerCase();
                const type = (item.getAttribute('data-type') || '').toLowerCase();
                if (name.includes(q) || type.includes(q)) {
                    item.classList.remove('hidden');
                    visibleCount++;
                } else {
                    item.classList.add('hidden');
                }
            });
            const emptyState = document.getElementById('no-fields-matched');
            const countBadge = document.getElementById('field-count-badge');
            if (emptyState) emptyState.classList.toggle('hidden', !(visibleCount === 0 && items.length > 0));
            if (countBadge) countBadge.textContent = visibleCount + ' field' + (visibleCount === 1 ? '' : 's');
        }

        let draggedItem = null;
        let previousDOMState = null;

        function handleDragStart(e) {
            draggedItem = e.currentTarget;
            e.dataTransfer.effectAllowed = 'move';
            e.currentTarget.classList.add('opacity-50');
            const container = document.getElementById('fields-list-container');
            previousDOMState = Array.from(container.children).map((node) => node.cloneNode(true));
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
            if (!draggedItem) return;
            draggedItem.classList.remove('opacity-50');
            const container = document.getElementById('fields-list-container');
            const orders = Array.from(container.children)
                .map((item, index) => ({ id: parseInt(item.getAttribute('data-id') || 0, 10), sort_order: index }))
                .filter((o) => o.id > 0);

            MoldableBuilder.api('/fields/reorder', { method: 'POST', body: JSON.stringify({ orders }) })
                .catch((err) => {
                    console.warn('Reorder failed, rolling back UI state:', err);
                    if (previousDOMState && container) {
                        container.innerHTML = '';
                        previousDOMState.forEach((node) => container.appendChild(node));
                    }
                });
            draggedItem = null;
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && typeof closeAddFieldDrawer === 'function') closeAddFieldDrawer();
            });
            MoldableBuilder.refresh();
        });
    </script>

    @include('moldable::builder.drawer')
</x-moldable::layouts.app>
