<x-moldable::layouts.app>
    <x-slot:title>
        Field Builder - Moldable Enterprise CRM
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-6">

        <!-- Top Page Header Bar -->
        <div class="scroll-reactive-sticky flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between bg-white/60 backdrop-blur-sm p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex flex-col gap-1">
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">
                        Field Builder
                    </h1>
                    <span class="rounded-full bg-slate-100 border border-slate-200/80 px-3 py-0.5 text-xs font-bold text-slate-800 shadow-2xs">
                        Moldable CRM v2.0
                    </span>
                </div>
                <p class="text-xs sm:text-sm font-medium text-slate-500">
                    Customize the fields and layout used across this workspace.
                </p>
            </div>

            <div class="flex items-center gap-3 flex-wrap">
                <div class="flex items-center gap-2.5 rounded-xl border border-slate-200 bg-white px-3 py-2 shadow-2xs">
                    <label for="entity-selector" class="text-xs font-semibold text-slate-500 whitespace-nowrap">Target Entity</label>
                    <select id="entity-selector" onchange="MoldableBuilder.setEntity(this.value)" class="rounded-lg border-0 py-0 pl-1 pr-6 text-xs font-bold text-slate-900 bg-transparent outline-none cursor-pointer">
                        @foreach(config('moldable.entities', ['leads' => ['name' => 'Leads'], 'persons' => ['name' => 'Persons'], 'organizations' => ['name' => 'Organizations'], 'products' => ['name' => 'Products'], 'quotes' => ['name' => 'Quotes']]) as $code => $meta)
                            <option value="{{ $code }}">{{ $meta['name'] ?? ucfirst($code) }}</option>
                        @endforeach
                    </select>
                </div>

                <button
                    type="button"
                    onclick="openAddFieldDrawer()"
                    class="rounded-xl bg-slate-900 hover:bg-black text-white px-5 py-2.5 text-xs sm:text-sm font-bold shadow-md shadow-slate-900/15 transition flex items-center gap-2 active:scale-[0.98]"
                >
                    <svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>Add New Field</span>
                </button>
            </div>
        </div>

        <!-- Entities Switcher Segmented Tabs Navigation Bar -->
        <div id="entity-pill-bar" class="flex items-center gap-2 overflow-x-auto rounded-2xl border border-slate-200/90 bg-white p-2.5 shadow-xs scrollbar-none">
            <span class="text-[11px] font-black text-slate-400 px-3 uppercase tracking-wider shrink-0">Entities</span>
            <div class="flex items-center gap-2 shrink-0">
                @foreach(config('moldable.entities', ['leads' => ['name' => 'Leads'], 'persons' => ['name' => 'Persons'], 'organizations' => ['name' => 'Organizations'], 'products' => ['name' => 'Products'], 'quotes' => ['name' => 'Quotes']]) as $code => $meta)
                    <button type="button" onclick="switchEntityPill(this, '{{ $code }}')" data-entity-pill="{{ $code }}" class="entity-pill-btn flex items-center gap-2 rounded-xl border {{ $loop->first ? 'border-slate-900 bg-slate-900 text-white shadow-xs' : 'border-slate-200/90 bg-slate-50/80 text-slate-700 hover:bg-slate-100 hover:text-slate-900' }} px-4 py-2 text-xs font-bold transition">
                        <span>{{ $meta['name'] ?? ucfirst($code) }}</span>
                        <span data-entity-count="{{ $code }}" class="rounded-md {{ $loop->first ? 'bg-white/20 text-white' : 'bg-slate-200/80 text-slate-700' }} px-1.5 py-0.5 text-[10px] font-black">0</span>
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Compact Studio 2-Column Grid (Presentation Groups & Resource Metrics) -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">

            <!-- Left Column (Col 7): Presentation Groups Layout Manager -->
            <div class="lg:col-span-7 xl:col-span-7">
                <div class="h-full rounded-2xl border border-slate-200/90 bg-white p-5 sm:p-6 shadow-xs flex flex-col justify-between space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3.5">
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Presentation Groups</h2>
                                <span id="groups-count-badge" class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-600">0 groups</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">Organize custom fields into structured visual sections</p>
                        </div>
                        <button type="button" onclick="openNewGroupModal()" class="rounded-xl border border-slate-200/90 bg-white px-3.5 py-1.5 text-xs font-bold text-slate-800 hover:bg-slate-50 transition flex items-center gap-1.5 shadow-2xs">
                            <svg class="w-3.5 h-3.5 text-slate-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Add Group
                        </button>
                    </div>

                    <!-- Groups Container -->
                    <div id="field-groups-nav" class="space-y-2.5 max-h-[300px] overflow-y-auto pr-1 custom-scrollbar">
                        <div class="py-6 text-center text-xs text-slate-400 animate-pulse">Loading presentation groups…</div>
                    </div>
                </div>
            </div>

            <!-- Right Column (Col 5): Resource Overview & Quick Types Palette -->
            <div class="lg:col-span-5 xl:col-span-5 space-y-4">

                <!-- Card 1: Compact Resource Overview & Metrics -->
                <div class="rounded-2xl border border-slate-200/90 bg-white p-5 sm:p-6 shadow-xs space-y-3.5">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Resource Overview</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Resource Metrics &middot; summary for current entity</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-xl border border-slate-200/80 bg-slate-50/60 p-3.5 flex items-center gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-900 text-white font-bold shadow-2xs">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <div>
                                <div class="text-xl font-black text-slate-900" id="stat-total-fields">0</div>
                                <div class="text-xs font-bold text-slate-800">Total Fields</div>
                            </div>
                        </div>

                        <div class="rounded-xl border border-slate-200/80 bg-slate-50/60 p-3.5 flex items-center gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-900 text-white font-bold shadow-2xs">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            </div>
                            <div>
                                <div class="text-xl font-black text-slate-900" id="stat-quick-add">0</div>
                                <div class="text-xs font-bold text-slate-800">Quick Add</div>
                            </div>
                        </div>

                        <div class="rounded-xl border border-slate-200/80 bg-slate-50/60 p-3.5 flex items-center gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-800 font-bold border border-slate-200/80">
                                <span class="text-xs font-black text-red-600">•</span>
                            </div>
                            <div>
                                <div class="text-xl font-black text-slate-900" id="stat-required-fields">0</div>
                                <div class="text-xs font-bold text-slate-800">Required</div>
                            </div>
                        </div>

                        <div class="rounded-xl border border-slate-200/80 bg-slate-50/60 p-3.5 flex items-center gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-800 font-bold border border-slate-200/80">
                                <span class="text-xs font-black text-slate-700">▤</span>
                            </div>
                            <div>
                                <div class="text-xl font-black text-slate-900" id="stat-groups-count">0</div>
                                <div class="text-xs font-bold text-slate-800">Groups</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Quick Field Types Action Palette -->
                <div class="rounded-2xl border border-slate-200/90 bg-white p-5 sm:p-6 shadow-xs space-y-3.5">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Quick Field Types</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Click to instantly create a field with preset type</p>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                        <button type="button" onclick="openAddFieldDrawer('text')" class="flex items-center gap-2.5 rounded-xl border border-slate-200/90 bg-white p-2.5 hover:border-slate-400 hover:bg-slate-50/80 transition text-left shadow-2xs">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-900 font-black text-xs">Aa</span>
                            <span class="text-xs font-bold text-slate-900">Text</span>
                        </button>
                        <button type="button" onclick="openAddFieldDrawer('select')" class="flex items-center gap-2.5 rounded-xl border border-slate-200/90 bg-white p-2.5 hover:border-slate-400 hover:bg-slate-50/80 transition text-left shadow-2xs">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-900 font-black text-xs">≡</span>
                            <span class="text-xs font-bold text-slate-900">Select</span>
                        </button>
                        <button type="button" onclick="openAddFieldDrawer('price')" class="flex items-center gap-2.5 rounded-xl border border-slate-200/90 bg-white p-2.5 hover:border-slate-400 hover:bg-slate-50/80 transition text-left shadow-2xs">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-900 font-black text-xs">$</span>
                            <span class="text-xs font-bold text-slate-900">Price</span>
                        </button>
                        <button type="button" onclick="openAddFieldDrawer('date')" class="flex items-center gap-2.5 rounded-xl border border-slate-200/90 bg-white p-2.5 hover:border-slate-400 hover:bg-slate-50/80 transition text-left shadow-2xs">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-900">
                                <svg class="w-3.5 h-3.5 text-slate-900 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </span>
                            <span class="text-xs font-bold text-slate-900">Date</span>
                        </button>
                        <button type="button" onclick="openAddFieldDrawer('lookup')" class="flex items-center gap-2.5 rounded-xl border border-slate-200/90 bg-white p-2.5 hover:border-slate-400 hover:bg-slate-50/80 transition text-left shadow-2xs">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-900">
                                <svg class="w-3.5 h-3.5 text-slate-900 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </span>
                            <span class="text-xs font-bold text-slate-900">Lookup</span>
                        </button>
                        <button type="button" onclick="openAddFieldDrawer('file')" class="flex items-center gap-2.5 rounded-xl border border-slate-200/90 bg-white p-2.5 hover:border-slate-400 hover:bg-slate-50/80 transition text-left shadow-2xs">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-900">
                                <svg class="w-3.5 h-3.5 text-slate-900 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                            </span>
                            <span class="text-xs font-bold text-slate-900">File</span>
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <!-- Custom Fields Workspace Canvas (Table List & Multi-Filter Toolbar) -->
        <div class="rounded-2xl border border-slate-200/90 bg-white p-5 sm:p-6 shadow-xs space-y-4">

            <!-- Sticky Search & Multi-Filter Toolbar -->
            <div class="sticky top-16 z-10 bg-white/95 backdrop-blur-md pb-3 border-b border-slate-100 space-y-3">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                    
                    <!-- Search Input Box -->
                    <div class="relative flex-1 min-w-[240px]">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        <input
                            type="text"
                            id="field-search-input"
                            placeholder="Search fields by name or code..."
                            oninput="MoldableBuilder.applyFilters()"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/70 py-2.5 pl-10 pr-10 text-xs sm:text-sm text-slate-900 outline-none transition focus:border-slate-400 focus:bg-white placeholder-slate-400"
                        />
                        <button
                            type="button"
                            id="clear-search-btn"
                            onclick="clearSearch()"
                            class="hidden absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 p-1"
                            aria-label="Clear search"
                        >
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- Multi-Filter Dropdowns Strip -->
                    <div class="flex items-center gap-2 flex-wrap">
                        <!-- Type Filter -->
                        <select id="filter-type-select" onchange="MoldableBuilder.applyFilters()" class="rounded-xl border border-slate-200 bg-slate-50/80 px-3 py-2 text-xs font-semibold text-slate-700 outline-none hover:bg-white focus:border-slate-400 transition cursor-pointer">
                            <option value="">Type: All</option>
                            <option value="text">Text</option>
                            <option value="textarea">Textarea</option>
                            <option value="price">Price</option>
                            <option value="select">Select</option>
                            <option value="multiselect">Multiselect</option>
                            <option value="checkbox">Checkbox</option>
                            <option value="boolean">Boolean</option>
                            <option value="date">Date</option>
                            <option value="datetime">Datetime</option>
                            <option value="lookup">Lookup</option>
                            <option value="email">Email</option>
                            <option value="phone">Phone</option>
                            <option value="address">Address</option>
                            <option value="file">File</option>
                            <option value="image">Image</option>
                        </select>

                        <!-- Group Filter -->
                        <select id="filter-group-select" onchange="MoldableBuilder.applyFilters()" class="rounded-xl border border-slate-200 bg-slate-50/80 px-3 py-2 text-xs font-semibold text-slate-700 outline-none hover:bg-white focus:border-slate-400 transition cursor-pointer">
                            <option value="">Group: All</option>
                        </select>

                        <!-- Status Filter -->
                        <select id="filter-status-select" onchange="MoldableBuilder.applyFilters()" class="rounded-xl border border-slate-200 bg-slate-50/80 px-3 py-2 text-xs font-semibold text-slate-700 outline-none hover:bg-white focus:border-slate-400 transition cursor-pointer">
                            <option value="">Status: All</option>
                            <option value="required">Required Only</option>
                            <option value="unique">Unique Only</option>
                            <option value="quick_add">Quick Add Only</option>
                        </select>

                        <button type="button" onclick="openAddFieldDrawer()" class="rounded-xl bg-slate-900 hover:bg-black text-white px-3.5 py-2 text-xs font-bold transition flex items-center gap-1.5 shrink-0">
                            <svg class="w-3.5 h-3.5 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Add Field
                        </button>
                    </div>
                </div>

                <!-- Active Filter Status & Sub-Header -->
                <div class="flex items-center justify-between pt-1 text-xs">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <span class="font-bold text-slate-700 flex items-center gap-1.5">
                            <span class="text-slate-400">Target Entity:</span>
                            <span id="selected-entity-badge" class="font-black text-slate-900">Leads</span>
                        </span>
                        <span class="text-slate-300">|</span>
                        <h2 id="active-group-header-title" class="font-medium text-slate-600 flex items-center gap-1.5">
                            <span>All Fields</span>
                        </h2>
                        <button type="button" id="reset-group-filter-btn" onclick="MoldableBuilder.clearGroupFilter()" class="hidden rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 text-[11px] font-bold px-2.5 py-0.5 transition">
                            Reset Group Filter
                        </button>
                    </div>
                    <span id="field-count-badge" class="rounded-full bg-slate-100 px-3 py-0.5 text-xs font-bold text-slate-700 shrink-0">0 fields</span>
                </div>
            </div>

            <!-- Fields Table Header Row (Desktop) -->
            <div class="hidden sm:grid grid-cols-12 gap-4 px-4 py-2 text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                <div class="col-span-5">Field Name & Code</div>
                <div class="col-span-2">Type</div>
                <div class="col-span-2">Group</div>
                <div class="col-span-2">Rules / Badges</div>
                <div class="col-span-1 text-right">Actions</div>
            </div>

            <!-- Fields Canvas List (Drag & Drop Reorderable) -->
            <div id="fields-list-container" class="space-y-2">
                <div class="py-8 text-center text-xs text-slate-400 animate-pulse">Loading fields…</div>
            </div>

            <!-- Search / Filter Empty State -->
            <div id="no-fields-matched" class="hidden py-12 text-center border border-dashed border-slate-200 rounded-2xl">
                <svg class="w-8 h-8 text-slate-300 mx-auto mb-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <p class="text-sm font-bold text-slate-700">No matching fields found</p>
                <p class="text-xs text-slate-400 mt-0.5">Try adjusting your search query or filters.</p>
                <button type="button" onclick="clearFilters()" class="mt-3 text-xs font-bold text-slate-900 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-xl transition">
                    Clear all filters
                </button>
            </div>

            <!-- Empty State (no fields yet for this entity) -->
            <div id="no-fields-yet" class="hidden py-14 text-center border border-dashed border-slate-200 rounded-2xl">
                <div class="h-12 w-12 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center mx-auto mb-3 text-lg font-black shadow-2xs">✦</div>
                <p class="text-sm font-bold text-slate-900">No custom fields yet</p>
                <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1">Create custom attributes to capture structured information for this entity.</p>
                <button type="button" onclick="openAddFieldDrawer()" class="mt-4 rounded-xl bg-slate-900 hover:bg-black text-white px-5 py-2 text-xs font-bold transition inline-flex items-center gap-1.5 shadow-sm">
                    <span>+ Add your first field</span>
                </button>
            </div>

            <!-- Bottom Add Field Footer Trigger -->
            <div class="pt-3 text-center border-t border-slate-100">
                <button type="button" onclick="openAddFieldDrawer()" class="text-xs font-bold text-slate-900 hover:underline inline-flex items-center gap-1.5">
                    <span class="text-sm font-black">+</span> Add New Field
                </button>
            </div>

        </div>

    </div>

    <!-- Assign Fields to Group Modal -->
    <div id="assign-group-modal" class="hidden fixed inset-0 z-[10001] overflow-y-auto bg-slate-900/60 backdrop-blur-sm">
        <div class="flex min-h-screen items-center justify-center p-4" onclick="if(event.target === this) closeAssignGroupModal()">
            <div class="w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/80">
                    <div>
                        <h3 id="assign-modal-title" class="text-sm font-bold text-slate-900">Manage Group Fields</h3>
                        <p class="text-xs text-slate-500">Select fields to assign to this presentation group</p>
                    </div>
                    <button type="button" onclick="closeAssignGroupModal()" aria-label="Close dialog" class="text-slate-400 hover:text-slate-700 p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="p-5 space-y-2 max-h-80 overflow-y-auto custom-scrollbar" id="assign-fields-checklist">
                    <!-- Checkboxes populated via JS -->
                </div>
                <div class="flex items-center justify-end gap-2.5 px-6 py-3.5 border-t border-slate-100 bg-slate-50/80">
                    <button type="button" onclick="closeAssignGroupModal()" class="rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 px-4 py-2 text-xs font-bold transition">Cancel</button>
                    <button type="button" id="save-group-assign-btn" onclick="saveGroupAssignment()" class="rounded-xl bg-slate-900 hover:bg-black text-white px-5 py-2 text-xs font-bold transition">Save Assignment</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Delete Confirmation Modal -->
    <div id="delete-confirm-modal" class="hidden fixed inset-0 z-[10002] overflow-y-auto bg-slate-900/60 backdrop-blur-sm">
        <div class="flex min-h-screen items-center justify-center p-4" onclick="if(event.target === this) closeDeleteModal()">
            <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden">
                <div class="p-6 space-y-4">
                    <div class="flex items-center gap-3.5">
                        <div class="h-10 w-10 rounded-xl bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900" id="delete-modal-title">Delete Custom Field?</h3>
                            <p class="text-xs text-slate-500">This action cannot be undone.</p>
                        </div>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed" id="delete-modal-message">
                        This field may be used in forms, mappings, and reports across your workspace. Deleting it will permanently remove stored attribute values.
                    </p>
                </div>
                <div class="flex items-center justify-end gap-2.5 px-6 py-3.5 border-t border-slate-100 bg-slate-50/80">
                    <button type="button" onclick="closeDeleteModal()" class="rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 px-4 py-2 text-xs font-bold transition">Cancel</button>
                    <button type="button" id="confirm-delete-btn" class="rounded-xl bg-red-600 hover:bg-red-700 text-white px-5 py-2 text-xs font-bold transition">Delete Field</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const MoldableBuilder = (function () {
            const API = '/v1/moldable';
            const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            const DEFAULT_GROUPS = ['Property Details', 'Customer Details', 'Project Details', 'Financial Details', 'Follow-up Details'];

            const TYPE_ICON = { 
                text: 'Aa', 
                textarea: '¶', 
                price: '$', 
                boolean: '◉', 
                select: '≡', 
                multiselect: '≣', 
                checkbox: '☑', 
                date: '📅', 
                datetime: '🕑', 
                lookup: '🔍', 
                email: '@', 
                phone: '☎', 
                address: '⌂', 
                file: '📎', 
                image: '🖼' 
            };

            let currentEntity = 'leads';
            let allFields = [];
            let currentGroups = [];
            let activeFilterGroupId = null;
            let activeAssignGroupId = null;
            let pendingDeleteId = null;
            let pendingDeleteType = null; // 'field' | 'group'

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

            function getFieldGroup(fieldId) {
                for (const grp of currentGroups) {
                    const assignedIds = (grp.group_attributes || grp.groupAttributes || []).map(ga => ga.attribute_id || (ga.attribute && ga.attribute.id));
                    if (assignedIds.includes(fieldId)) {
                        return grp.name;
                    }
                }
                return null;
            }

            function entityFields() {
                let fields = allFields.filter((f) => f.entity_type === currentEntity)
                    .sort((a, b) => (a.sort_order - b.sort_order) || (a.id - b.id));

                if (activeFilterGroupId) {
                    const grp = currentGroups.find(g => String(g.id) === String(activeFilterGroupId));
                    if (grp) {
                        const assignedIds = (grp.group_attributes || grp.groupAttributes || []).map(ga => ga.attribute_id || (ga.attribute && ga.attribute.id));
                        fields = fields.filter(f => assignedIds.includes(f.id));
                    }
                }

                return fields;
            }

            async function refresh() {
                try {
                    allFields = await api('/fields');
                } catch (err) {
                    document.getElementById('fields-list-container').innerHTML =
                        '<div class="py-8 text-center"><p class="text-xs text-red-500 font-semibold mb-2">' + esc(err.message) + '</p><button type="button" onclick="MoldableBuilder.refresh()" class="rounded-xl bg-slate-900 text-white px-4 py-1.5 text-xs font-bold">Retry</button></div>';
                    return;
                }
                updatePillCounts();
                await loadGroups();
                renderFields();
                populateGroupFilterDropdown();
            }

            function updatePillCounts() {
                document.querySelectorAll('[data-entity-count]').forEach((el) => {
                    const code = el.getAttribute('data-entity-count');
                    el.textContent = allFields.filter((f) => f.entity_type === code).length;
                });
            }

            function populateGroupFilterDropdown() {
                const select = document.getElementById('filter-group-select');
                if (!select) return;
                const prevVal = select.value;
                let html = '<option value="">Group: All</option>';
                currentGroups.forEach(g => {
                    if (g.id) {
                        html += `<option value="${g.id}">${esc(g.name)}</option>`;
                    }
                });
                select.innerHTML = html;
                select.value = prevVal || '';
            }

            function fieldCard(field) {
                const icon = TYPE_ICON[field.type] || 'Aa';
                const typeLabel = field.type.charAt(0).toUpperCase() + field.type.slice(1);
                const groupName = getFieldGroup(field.id);
                
                const badges =
                    (field.is_required ? '<span class="inline-flex items-center text-[10px] font-bold text-red-600 bg-red-50 border border-red-200/80 px-2 py-0.5 rounded-full shrink-0">• Required</span>' : '') +
                    (field.is_unique ? '<span class="inline-flex items-center text-[10px] font-bold text-indigo-600 bg-indigo-50 border border-indigo-200/80 px-2 py-0.5 rounded-full shrink-0" data-unique=1>Unique</span>' : '') +
                    (field.quick_add ? '<span class="inline-flex items-center text-[10px] font-bold text-slate-700 bg-slate-100 border border-slate-200/80 px-2 py-0.5 rounded-full shrink-0">Quick Add</span>' : '');

                return `
                <div class="field-item group flex flex-col sm:grid sm:grid-cols-12 gap-3 sm:gap-4 items-start sm:items-center rounded-xl border border-slate-200/80 bg-white p-3 sm:px-4 sm:py-3 transition-all hover:border-slate-400 hover:shadow-xs cursor-grab active:cursor-grabbing" draggable="true" ondragstart="handleDragStart(event)" ondragover="handleDragOver(event)" ondrop="handleDrop(event)" ondragend="handleDragEnd(event)" data-id="${field.id}" data-name="${esc(field.name)}" data-code="${esc(field.code)}" data-type="${esc(field.type)}" data-group="${esc(groupName || '')}" data-required="${field.is_required ? '1' : '0'}" data-unique="${field.is_unique ? '1' : '0'}" data-quick-add="${field.quick_add ? '1' : '0'}">
                    
                    <!-- Left / Col 5: Name & Code -->
                    <div class="sm:col-span-5 flex items-center gap-3 min-w-0 w-full">
                        <span class="text-slate-300 select-none text-xs font-bold hover:text-slate-600 cursor-grab px-0.5">⋮⋮</span>
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-800 font-bold text-xs border border-slate-200/70 shadow-2xs">${esc(icon)}</div>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs sm:text-sm font-bold text-slate-900 truncate" title="${esc(field.name)}">${esc(field.name)}</div>
                            <div class="text-[11px] text-slate-400 font-mono truncate">${esc(field.code)}</div>
                        </div>
                    </div>

                    <!-- Col 2: Type -->
                    <div class="sm:col-span-2 flex items-center">
                        <span class="inline-flex items-center rounded-lg bg-slate-100 px-2.5 py-1 text-[11px] font-bold text-slate-800 border border-slate-200/60">${esc(typeLabel)}</span>
                    </div>

                    <!-- Col 2: Group -->
                    <div class="sm:col-span-2 flex items-center min-w-0">
                        <span class="text-xs text-slate-600 font-medium truncate" title="${esc(groupName || 'Unassigned')}">${esc(groupName || '—')}</span>
                    </div>

                    <!-- Col 2: Badges -->
                    <div class="sm:col-span-2 flex items-center gap-1.5 flex-wrap">
                        ${badges || '<span class="text-[11px] text-slate-400 font-medium">Standard</span>'}
                    </div>

                    <!-- Col 1: Actions -->
                    <div class="sm:col-span-1 flex items-center justify-end gap-1 w-full sm:w-auto border-t sm:border-t-0 pt-2 sm:pt-0 border-slate-100">
                        <button type="button" onclick="MoldableBuilder.edit(${field.id})" title="Edit Field" aria-label="Edit Field" class="icon-edit p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-900 rounded-lg transition">
                            <svg class="w-4 h-4 text-slate-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        </button>
                        <button type="button" onclick="MoldableBuilder.remove(${field.id}, '${esc(field.name)}')" title="Delete Field" aria-label="Delete Field" class="icon-delete p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-600 rounded-lg transition">
                            <svg class="w-4 h-4 text-slate-600 hover:text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>

                </div>`;
            }

            function renderFields() {
                const container = document.getElementById('fields-list-container');
                const fields = entityFields();
                const totalEntityFields = allFields.filter((f) => f.entity_type === currentEntity);

                document.getElementById('stat-total-fields').textContent = totalEntityFields.length;
                document.getElementById('stat-quick-add').textContent = totalEntityFields.filter((f) => f.quick_add).length;
                document.getElementById('stat-required-fields').textContent = totalEntityFields.filter((f) => f.is_required).length;
                document.getElementById('stat-groups-count').textContent = currentGroups.length;
                document.getElementById('groups-count-badge').textContent = currentGroups.length + ' group' + (currentGroups.length === 1 ? '' : 's');

                const emptyYet = document.getElementById('no-fields-yet');
                if (totalEntityFields.length === 0) {
                    container.innerHTML = '';
                    emptyYet.classList.remove('hidden');
                    document.getElementById('no-fields-matched')?.classList.add('hidden');
                    document.getElementById('field-count-badge').textContent = '0 fields';
                    return;
                }
                emptyYet.classList.add('hidden');
                container.innerHTML = fields.map(fieldCard).join('');
                applyFilters();
            }

            function groupCard(group, count, fieldPreviews = []) {
                const isSelected = activeFilterGroupId && String(activeFilterGroupId) === String(group.id);
                const hasId = !!group.id;
                const previewText = fieldPreviews.length > 0 ? fieldPreviews.slice(0, 3).map(esc).join(' · ') + (fieldPreviews.length > 3 ? ' · ...' : '') : 'No fields assigned';

                return `
                <div class="group-tab-btn flex items-center justify-between rounded-xl border ${isSelected ? 'border-slate-900 ring-2 ring-slate-900/10 bg-slate-50/80' : 'border-slate-200/90 bg-white hover:border-slate-400'} p-3 transition shadow-2xs cursor-pointer" data-group-id="${group.id || ''}">
                    <div class="flex items-center gap-3 min-w-0 flex-1" onclick="MoldableBuilder.filterByGroup('${group.id || ''}', '${esc(group.name)}')">
                        <span class="text-slate-300 select-none text-xs font-bold">⋮⋮</span>
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-800 font-bold border border-slate-200/70 shadow-2xs">
                            <svg class="w-3.5 h-3.5 text-slate-800 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <h3 class="text-xs font-bold text-slate-900 truncate">${esc(group.name)}</h3>
                                <span class="rounded-md bg-slate-100 px-1.5 py-0.2 text-[10px] font-bold text-slate-600">${count}</span>
                            </div>
                            <p class="text-[11px] text-slate-400 truncate mt-0.5">${hasId ? previewText : 'Suggested group (click to save)'}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1 shrink-0 ml-2">
                        ${hasId ? `
                            <button type="button" onclick="event.stopPropagation(); MoldableBuilder.openAssignModal(${group.id})" title="Assign Fields" aria-label="Assign Fields" class="p-1.5 text-slate-400 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            </button>
                            <button type="button" onclick="event.stopPropagation(); MoldableBuilder.renameGroup(${group.id}, '${esc(group.name)}')" title="Rename Group" aria-label="Rename Group" class="p-1.5 text-slate-400 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            </button>
                            <button type="button" onclick="event.stopPropagation(); MoldableBuilder.confirmDeleteGroup(${group.id}, '${esc(group.name)}')" title="Delete Group" aria-label="Delete Group" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        ` : `
                            <button type="button" onclick="event.stopPropagation(); MoldableBuilder.createGroup('${esc(group.name)}')" class="text-[11px] font-bold text-slate-900 bg-slate-100 hover:bg-slate-200 px-2.5 py-1 rounded-lg transition">
                                Save
                            </button>
                        `}
                    </div>
                </div>`;
            }

            async function loadGroups() {
                const nav = document.getElementById('field-groups-nav');
                let groups = [];
                try {
                    groups = await api('/groups?entity_type=' + encodeURIComponent(currentEntity));
                } catch (err) {
                    groups = [];
                }
                currentGroups = Array.isArray(groups) ? groups : [];

                if (currentGroups.length === 0) {
                    nav.innerHTML = DEFAULT_GROUPS.map((name) => groupCard({ name, id: null }, 0)).join('');
                    return;
                }

                nav.innerHTML = currentGroups.map((g) => {
                    const assigned = (g.group_attributes || g.groupAttributes || []).map(ga => {
                        const attrId = ga.attribute_id || (ga.attribute && ga.attribute.id);
                        const match = allFields.find(f => f.id === attrId);
                        return match ? match.name : null;
                    }).filter(Boolean);

                    return groupCard(g, (g.group_attributes || g.groupAttributes || []).length, assigned);
                }).join('');
            }

            async function createGroup(name) {
                try {
                    await api('/groups', { method: 'POST', body: JSON.stringify({ name, entity_type: currentEntity }) });
                    await loadGroups();
                    populateGroupFilterDropdown();
                } catch (err) {
                    alert(err.message || 'Could not create group.');
                }
            }

            async function renameGroup(id, currentName) {
                const newName = prompt('Enter new group name:', currentName);
                if (!newName || !newName.trim() || newName.trim() === currentName) return;
                try {
                    await api('/groups/' + id, { method: 'PUT', body: JSON.stringify({ name: newName.trim() }) });
                    await loadGroups();
                    populateGroupFilterDropdown();
                } catch (err) {
                    alert(err.message || 'Could not rename group.');
                }
            }

            function confirmDeleteGroup(id, groupName) {
                pendingDeleteId = id;
                pendingDeleteType = 'group';
                const title = document.getElementById('delete-modal-title');
                const msg = document.getElementById('delete-modal-message');
                const btn = document.getElementById('confirm-delete-btn');

                if (title) title.textContent = `Delete Group "${groupName}"?`;
                if (msg) msg.textContent = 'Removing this presentation group will not delete any fields; assigned fields will remain in the workspace as unassigned.';
                if (btn) {
                    btn.textContent = 'Delete Group';
                    btn.onclick = executePendingDelete;
                }
                document.getElementById('delete-confirm-modal')?.classList.remove('hidden');
            }

            function filterByGroup(groupId, groupName) {
                if (!groupId) {
                    createGroup(groupName);
                    return;
                }
                activeFilterGroupId = groupId;
                const header = document.getElementById('active-group-header-title');
                const selector = document.getElementById('entity-selector');
                const entityText = selector ? selector.options[selector.selectedIndex].text : currentEntity;
                if (header) {
                    header.innerHTML = `<span>Group: <span class="font-bold text-slate-900">${esc(groupName)}</span></span>`;
                }
                document.getElementById('reset-group-filter-btn')?.classList.remove('hidden');
                
                const groupSelect = document.getElementById('filter-group-select');
                if (groupSelect) groupSelect.value = groupId;

                renderFields();
                loadGroups();
            }

            function clearGroupFilter() {
                activeFilterGroupId = null;
                const header = document.getElementById('active-group-header-title');
                if (header) {
                    header.innerHTML = `<span>All Fields</span>`;
                }
                document.getElementById('reset-group-filter-btn')?.classList.add('hidden');
                
                const groupSelect = document.getElementById('filter-group-select');
                if (groupSelect && groupSelect.value) groupSelect.value = '';

                renderFields();
                loadGroups();
            }

            function openAssignModal(groupId) {
                activeAssignGroupId = groupId;
                const group = currentGroups.find(g => g.id === groupId);
                if (!group) return;

                document.getElementById('assign-modal-title').textContent = 'Manage Fields: ' + group.name;
                const assignedIds = (group.group_attributes || group.groupAttributes || []).map(ga => ga.attribute_id || (ga.attribute && ga.attribute.id));

                const container = document.getElementById('assign-fields-checklist');
                const availableFields = allFields.filter(f => f.entity_type === currentEntity);

                if (availableFields.length === 0) {
                    container.innerHTML = '<p class="text-xs text-slate-400 py-4 text-center">No fields available for this entity. Create fields first.</p>';
                } else {
                    container.innerHTML = availableFields.map(f => `
                        <label class="flex items-center justify-between p-2.5 rounded-xl border border-slate-200/80 hover:bg-slate-50 cursor-pointer transition">
                            <div class="flex items-center gap-2.5">
                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-100 text-slate-800 text-xs font-bold">${esc(TYPE_ICON[f.type] || 'Aa')}</span>
                                <div>
                                    <div class="text-xs font-bold text-slate-900">${esc(f.name)}</div>
                                    <div class="text-[11px] text-slate-400 font-mono">${esc(f.code)}</div>
                                </div>
                            </div>
                            <input type="checkbox" class="assign-field-checkbox w-4 h-4 rounded text-slate-900 focus:ring-0 cursor-pointer" value="${f.id}" ${assignedIds.includes(f.id) ? 'checked' : ''} />
                        </label>
                    `).join('');
                }

                document.getElementById('assign-group-modal').classList.remove('hidden');
            }

            function closeAssignGroupModal() {
                document.getElementById('assign-group-modal').classList.add('hidden');
                activeAssignGroupId = null;
            }

            async function saveGroupAssignment() {
                if (!activeAssignGroupId) return;
                const checked = Array.from(document.querySelectorAll('.assign-field-checkbox:checked')).map(cb => parseInt(cb.value, 10));
                const btn = document.getElementById('save-group-assign-btn');
                if (btn) btn.disabled = true;
                try {
                    await api('/groups/' + activeAssignGroupId + '/assign', {
                        method: 'POST',
                        body: JSON.stringify({ attribute_ids: checked })
                    });
                    closeAssignGroupModal();
                    await loadGroups();
                    renderFields();
                } catch (err) {
                    alert(err.message || 'Could not save group assignment.');
                } finally {
                    if (btn) btn.disabled = false;
                }
            }

            function remove(id, fieldName) {
                pendingDeleteId = id;
                pendingDeleteType = 'field';
                const title = document.getElementById('delete-modal-title');
                const msg = document.getElementById('delete-modal-message');
                const btn = document.getElementById('confirm-delete-btn');

                if (title) title.textContent = `Delete Field "${fieldName || 'Custom Field'}"?`;
                if (msg) msg.textContent = 'This field may be used in forms, mappings, and reports. Deleting it will permanently remove stored attribute values for all records in this workspace.';
                if (btn) {
                    btn.textContent = 'Delete Field';
                    btn.onclick = executePendingDelete;
                }
                document.getElementById('delete-confirm-modal')?.classList.remove('hidden');
            }

            async function executePendingDelete() {
                const btn = document.getElementById('confirm-delete-btn');
                if (btn) btn.disabled = true;
                try {
                    if (pendingDeleteType === 'field' && pendingDeleteId) {
                        await api('/fields/' + pendingDeleteId, { method: 'DELETE' });
                        closeDeleteModal();
                        await refresh();
                    } else if (pendingDeleteType === 'group' && pendingDeleteId) {
                        await api('/groups/' + pendingDeleteId, { method: 'DELETE' });
                        if (activeFilterGroupId === pendingDeleteId) clearGroupFilter();
                        closeDeleteModal();
                        await loadGroups();
                        populateGroupFilterDropdown();
                    }
                } catch (err) {
                    alert(err.message || 'Could not delete.');
                } finally {
                    if (btn) btn.disabled = false;
                }
            }

            function edit(id) {
                const field = allFields.find((f) => String(f.id) === String(id));
                if (field) openAddFieldDrawer(null, field);
            }

            function setEntity(entity) {
                currentEntity = entity;
                activeFilterGroupId = null;
                const badge = document.getElementById('selected-entity-badge');
                const selector = document.getElementById('entity-selector');
                const label = selector ? selector.options[selector.selectedIndex].text : entity;
                if (badge) badge.textContent = label;
                if (selector && selector.value !== entity) selector.value = entity;

                // Sync entity tabs
                document.querySelectorAll('.entity-pill-btn').forEach((b) => {
                    const isCurrent = b.getAttribute('data-entity-pill') === entity;
                    b.className = isCurrent
                        ? 'entity-pill-btn flex items-center gap-2 rounded-xl border border-slate-900 bg-slate-900 text-white shadow-xs px-4 py-2 text-xs font-bold transition'
                        : 'entity-pill-btn flex items-center gap-2 rounded-xl border border-slate-200/90 bg-slate-50/80 text-slate-700 hover:bg-slate-100 hover:text-slate-900 px-4 py-2 text-xs font-bold transition';

                    const countEl = b.querySelector('[data-entity-count]');
                    if (countEl) {
                        countEl.className = isCurrent
                            ? 'rounded-md bg-white/20 text-white px-1.5 py-0.5 text-[10px] font-black'
                            : 'rounded-md bg-slate-200/80 text-slate-700 px-1.5 py-0.5 text-[10px] font-black';
                    }
                });

                clearGroupFilter();
            }

            function applyFilters() {
                const q = (document.getElementById('field-search-input')?.value || '').toLowerCase().trim();
                const typeFilter = (document.getElementById('filter-type-select')?.value || '').toLowerCase().trim();
                const groupFilterId = (document.getElementById('filter-group-select')?.value || '').trim();
                const statusFilter = (document.getElementById('filter-status-select')?.value || '').trim();

                const clearBtn = document.getElementById('clear-search-btn');
                if (clearBtn) clearBtn.classList.toggle('hidden', !q);

                const items = document.querySelectorAll('.field-item');
                let visibleCount = 0;

                // If group filter dropdown changed, sync activeFilterGroupId
                if (groupFilterId && groupFilterId !== String(activeFilterGroupId || '')) {
                    const grp = currentGroups.find(g => String(g.id) === groupFilterId);
                    if (grp) {
                        activeFilterGroupId = groupFilterId;
                        const header = document.getElementById('active-group-header-title');
                        if (header) header.innerHTML = `<span>Group: <span class="font-bold text-slate-900">${esc(grp.name)}</span></span>`;
                        document.getElementById('reset-group-filter-btn')?.classList.remove('hidden');
                    }
                } else if (!groupFilterId && activeFilterGroupId) {
                    activeFilterGroupId = null;
                    const header = document.getElementById('active-group-header-title');
                    if (header) header.innerHTML = `<span>All Fields</span>`;
                    document.getElementById('reset-group-filter-btn')?.classList.add('hidden');
                }

                items.forEach((item) => {
                    const name = (item.getAttribute('data-name') || '').toLowerCase();
                    const code = (item.getAttribute('data-code') || '').toLowerCase();
                    const type = (item.getAttribute('data-type') || '').toLowerCase();
                    const group = (item.getAttribute('data-group') || '');
                    const isReq = item.getAttribute('data-required') === '1';
                    const isUniq = item.getAttribute('data-unique') === '1';
                    const isQuick = item.getAttribute('data-quick-add') === '1';
                    const id = parseInt(item.getAttribute('data-id') || '0', 10);

                    const matchesQuery = !q || name.includes(q) || code.includes(q) || type.includes(q);
                    const matchesType = !typeFilter || type === typeFilter;
                    
                    let matchesGroup = true;
                    if (activeFilterGroupId) {
                        const grp = currentGroups.find(g => String(g.id) === String(activeFilterGroupId));
                        if (grp) {
                            const assignedIds = (grp.group_attributes || grp.groupAttributes || []).map(ga => ga.attribute_id || (ga.attribute && ga.attribute.id));
                            matchesGroup = assignedIds.includes(id);
                        }
                    }

                    let matchesStatus = true;
                    if (statusFilter === 'required') matchesStatus = isReq;
                    else if (statusFilter === 'unique') matchesStatus = isUniq;
                    else if (statusFilter === 'quick_add') matchesStatus = isQuick;

                    if (matchesQuery && matchesType && matchesGroup && matchesStatus) {
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

            return {
                api,
                refresh,
                remove,
                edit,
                createGroup,
                renameGroup,
                confirmDeleteGroup,
                filterByGroup,
                clearGroupFilter,
                openAssignModal,
                closeAssignGroupModal,
                saveGroupAssignment,
                setEntity,
                applyFilters,
                get entity() { return currentEntity; }
            };
        })();

        function switchEntityPill(btn, entityCode) {
            MoldableBuilder.setEntity(entityCode);
        }

        function selectGroupTab(element, groupName) {
            const groupId = element ? element.getAttribute('data-group-id') : null;
            MoldableBuilder.filterByGroup(groupId, groupName);
        }

        function openNewGroupModal() {
            const groupName = prompt('Enter New Presentation Group Name:');
            if (groupName && groupName.trim()) {
                MoldableBuilder.createGroup(groupName.trim());
            }
        }

        function filterFields(query) {
            const searchInput = document.getElementById('field-search-input');
            if (searchInput && searchInput.value !== query) {
                searchInput.value = query;
            }
            MoldableBuilder.applyFilters();
        }

        function clearSearch() {
            const input = document.getElementById('field-search-input');
            if (input) {
                input.value = '';
                MoldableBuilder.applyFilters();
                input.focus();
            }
        }

        function clearFilters() {
            const input = document.getElementById('field-search-input');
            const typeSel = document.getElementById('filter-type-select');
            const groupSel = document.getElementById('filter-group-select');
            const statusSel = document.getElementById('filter-status-select');
            if (input) input.value = '';
            if (typeSel) typeSel.value = '';
            if (groupSel) groupSel.value = '';
            if (statusSel) statusSel.value = '';
            MoldableBuilder.clearGroupFilter();
        }

        function closeAssignGroupModal() {
            MoldableBuilder.closeAssignGroupModal();
        }

        function saveGroupAssignment() {
            MoldableBuilder.saveGroupAssignment();
        }

        function closeDeleteModal() {
            document.getElementById('delete-confirm-modal')?.classList.add('hidden');
        }

        let draggedItem = null;
        let previousDOMState = null;

        function handleDragStart(e) {
            draggedItem = e.currentTarget;
            e.dataTransfer.effectAllowed = 'move';
            e.currentTarget.classList.add('opacity-40', 'ring-2', 'ring-slate-900');
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

        function handleDragEnd(e) {
            if (draggedItem) {
                draggedItem.classList.remove('opacity-40', 'ring-2', 'ring-slate-900');
            }
        }

        function handleDrop(e) {
            e.preventDefault();
            if (!draggedItem) return;
            draggedItem.classList.remove('opacity-40', 'ring-2', 'ring-slate-900');
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
                if (e.key === 'Escape') {
                    if (typeof closeAddFieldDrawer === 'function') closeAddFieldDrawer();
                    if (typeof closeAssignGroupModal === 'function') closeAssignGroupModal();
                    if (typeof closeDeleteModal === 'function') closeDeleteModal();
                }
            });
            MoldableBuilder.refresh();
        });
    </script>

    @include('moldable::builder.drawer')
</x-moldable::layouts.app>
