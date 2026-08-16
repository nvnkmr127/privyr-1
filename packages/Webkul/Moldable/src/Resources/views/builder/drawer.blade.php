<div id="add-field-drawer" class="hidden fixed inset-0 z-[10000] overflow-y-auto bg-slate-900/40 backdrop-blur-xs transition-opacity duration-200" role="dialog" aria-modal="true" aria-labelledby="drawer-title">
    <div class="flex min-h-screen items-center justify-center p-3 sm:p-6 sm:py-8" onclick="if(event.target === this) closeAddFieldDrawer()">
        <div class="w-full max-w-2xl bg-white rounded-2xl shadow-2xl border border-slate-200/90 flex flex-col overflow-hidden max-h-[92vh] sm:max-h-[88vh] transition-all">
            
            <!-- Drawer Header -->
            <div class="px-6 pt-5 pb-4 border-b border-slate-100 bg-white relative">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <!-- Entity & Technical Context Pill -->
                        <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                            <span id="drawer-context-badge" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[11px] font-bold uppercase tracking-wider bg-slate-100 text-slate-700 border border-slate-200/70">
                                <span id="drawer-context-icon">
                                    <svg class="w-3 h-3 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                </span>
                                <span id="drawer-context-entity">LEADS</span>
                            </span>
                            <span id="drawer-field-id-badge" class="hidden inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-mono font-semibold bg-slate-50 text-slate-500 border border-slate-200/60">
                                ID: <span id="drawer-field-id-val">#</span>
                            </span>
                        </div>

                        <div class="flex items-baseline gap-2.5 flex-wrap">
                            <h2 id="drawer-title" class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">Create Field</h2>
                            <span id="drawer-field-name-preview" class="hidden text-sm font-semibold text-slate-600"></span>
                        </div>
                        <p id="drawer-subtitle" class="text-xs text-slate-500 font-medium mt-0.5">Define a custom field for your workspace.</p>
                    </div>

                    <button type="button" onclick="closeAddFieldDrawer()" aria-label="Close dialog" class="rounded-xl p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition shrink-0 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:ring-offset-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Subtle Top Micro Progress Bar -->
                <div class="absolute bottom-0 left-0 right-0 h-[2px] bg-slate-100">
                    <div id="drawer-progress-bar" class="h-full bg-slate-900 transition-all duration-300 ease-out" style="width: 16.666%;"></div>
                </div>
            </div>

            <!-- Workflow Step Navigation Bar -->
            <div class="px-6 py-2.5 bg-slate-50/70 border-b border-slate-100 overflow-x-auto scrollbar-none" role="tablist" aria-label="Field creation steps">
                <div class="flex items-center gap-1.5 min-w-max">
                    <button type="button" onclick="switchDrawerTab(1)" data-drawer-tab="1" class="drawer-step-tab flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-bold transition border border-slate-900 bg-slate-900 text-white shadow-xs shrink-0 cursor-pointer focus:outline-none focus:ring-2 focus:ring-slate-900">
                        <span class="step-badge h-4.5 w-4.5 rounded-full bg-white text-slate-900 flex items-center justify-center text-[10px] font-black">1</span>
                        <span>1. Basic Information</span>
                    </button>
                    <button type="button" onclick="switchDrawerTab(2)" data-drawer-tab="2" class="drawer-step-tab flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-semibold transition border border-transparent text-slate-600 hover:bg-slate-200/60 shrink-0 cursor-pointer focus:outline-none focus:ring-2 focus:ring-slate-900">
                        <span class="step-badge h-4.5 w-4.5 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center text-[10px] font-black">2</span>
                        <span>2. Field Type</span>
                    </button>
                    <button type="button" onclick="switchDrawerTab(3)" data-drawer-tab="3" class="drawer-step-tab flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-semibold transition border border-transparent text-slate-600 hover:bg-slate-200/60 shrink-0 cursor-pointer focus:outline-none focus:ring-2 focus:ring-slate-900">
                        <span class="step-badge h-4.5 w-4.5 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center text-[10px] font-black">3</span>
                        <span>3. Configuration</span>
                    </button>
                    <button type="button" onclick="switchDrawerTab(4)" data-drawer-tab="4" class="drawer-step-tab flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-semibold transition border border-transparent text-slate-600 hover:bg-slate-200/60 shrink-0 cursor-pointer focus:outline-none focus:ring-2 focus:ring-slate-900">
                        <span class="step-badge h-4.5 w-4.5 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center text-[10px] font-black">4</span>
                        <span>4. Validation</span>
                    </button>
                    <button type="button" onclick="switchDrawerTab(5)" data-drawer-tab="5" class="drawer-step-tab flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-semibold transition border border-transparent text-slate-600 hover:bg-slate-200/60 shrink-0 cursor-pointer focus:outline-none focus:ring-2 focus:ring-slate-900">
                        <span class="step-badge h-4.5 w-4.5 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center text-[10px] font-black">5</span>
                        <span>5. Visibility</span>
                    </button>
                    <button type="button" onclick="switchDrawerTab(6)" data-drawer-tab="6" class="drawer-step-tab flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-semibold transition border border-transparent text-slate-600 hover:bg-slate-200/60 shrink-0 cursor-pointer focus:outline-none focus:ring-2 focus:ring-slate-900">
                        <span class="step-badge h-4.5 w-4.5 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center text-[10px] font-black">6</span>
                        <span>6. Advanced</span>
                    </button>
                </div>
            </div>

            <!-- Drawer Body Form -->
            <form id="add-field-drawer-form" onsubmit="submitAddFieldDrawer(event)" class="flex-1 flex flex-col overflow-hidden">
                <input type="hidden" name="field_id" id="edit-field-id" value="" />
                <input type="hidden" name="type" id="selected-type-hidden-input" value="text" />

                <!-- Scrollable Tab Content Container -->
                <div class="flex-1 overflow-y-auto px-6 py-5 space-y-4 custom-scrollbar">
                    
                    <!-- Dynamic Error Alert Banner -->
                    <div id="drawer-error-banner" class="hidden rounded-xl border border-red-200 bg-red-50 p-3.5 text-xs text-red-800 flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-red-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <div class="flex-1 min-w-0">
                            <span class="font-bold block" id="drawer-error-title">Unable to save field</span>
                            <span id="drawer-error-msg" class="text-red-700 mt-0.5 block">Please correct the highlighted errors.</span>
                        </div>
                    </div>

                    <div class="max-w-xl mx-auto w-full space-y-4">

                        <!-- Section 1: Basic Information -->
                        <div id="drawer-section-1" class="drawer-section space-y-4">
                            <div class="rounded-2xl border border-slate-200/90 p-5 bg-white space-y-4 shadow-xs">
                                <div class="border-b border-slate-100 pb-3">
                                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                                        <span class="h-2 w-2 rounded-full bg-slate-900"></span>
                                        1. Basic Information
                                    </h3>
                                    <p class="text-xs text-slate-500 mt-1 font-normal">Define the identity and workspace target for this field.</p>
                                </div>

                                <div class="space-y-4">
                                    <!-- Field Name -->
                                    <div>
                                        <div class="flex items-center justify-between mb-1">
                                            <label for="field-name-input" class="block text-xs font-bold text-slate-700">Field name <span class="text-red-500">*</span></label>
                                            <span class="text-[11px] text-slate-400">Display label</span>
                                        </div>
                                        <input
                                            type="text"
                                            id="field-name-input"
                                            name="name"
                                            required
                                            placeholder="e.g. Customer Tier"
                                            oninput="handleNameInput(this.value); markDrawerDirty();"
                                            class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm bg-slate-50/60 text-slate-900 focus:bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition font-medium placeholder:text-slate-400"
                                        />
                                        <p class="text-[11px] text-slate-500 mt-1">The name users will see throughout the workspace.</p>
                                        <p id="field-name-error" class="hidden text-xs font-semibold text-red-600 mt-1 flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            <span>Field name is required.</span>
                                        </p>
                                    </div>

                                    <!-- Field Code -->
                                    <div>
                                        <div class="flex items-center justify-between mb-1">
                                            <label for="field-code-input" class="block text-xs font-bold text-slate-700">
                                                Field code <span id="field-code-lock-icon" class="hidden text-slate-400 ml-1">🔒</span>
                                            </label>
                                            <span id="field-code-badge" class="text-[10px] font-mono px-1.5 py-0.5 bg-slate-100 text-slate-600 rounded">snake_case</span>
                                        </div>
                                        <input
                                            type="text"
                                            id="field-code-input"
                                            name="code"
                                            placeholder="e.g. customer_tier"
                                            oninput="markDrawerDirty(); validateFieldCode(this.value);"
                                            class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm bg-slate-50/60 text-slate-900 focus:bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none font-mono transition placeholder:text-slate-400"
                                        />
                                        <p id="field-code-helper" class="text-[11px] text-slate-500 mt-1">Automatically generated from the field name if left blank. Use lowercase letters, numbers, and underscores.</p>
                                        <p id="field-code-error" class="hidden text-xs font-semibold text-red-600 mt-1 flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            <span>Field code must contain only lowercase letters, numbers, and underscores.</span>
                                        </p>
                                    </div>

                                    <!-- Target Entity -->
                                    <div>
                                        <div class="flex items-center justify-between mb-1">
                                            <label for="field-entity-type-select" class="block text-xs font-bold text-slate-700">Target entity <span class="text-red-500">*</span></label>
                                            <span class="text-[11px] text-slate-400">Workspace scope</span>
                                        </div>
                                        <select
                                            id="field-entity-type-select"
                                            name="entity_type"
                                            required
                                            onchange="markDrawerDirty(); updateEntityContext(this.value);"
                                            class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm bg-slate-50/60 text-slate-900 focus:bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition cursor-pointer font-medium"
                                        >
                                            @foreach(config('moldable.entities', ['leads' => ['name' => 'Leads'], 'persons' => ['name' => 'Persons'], 'organizations' => ['name' => 'Organizations'], 'products' => ['name' => 'Products'], 'quotes' => ['name' => 'Quotes']]) as $entityCode => $entityMeta)
                                                <option value="{{ $entityCode }}">{{ $entityMeta['name'] ?? ucfirst($entityCode) }}</option>
                                            @endforeach
                                        </select>
                                        <p id="entity-helper-text" class="text-[11px] text-slate-500 mt-1">This field will be available wherever Lead fields are supported.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Field Type Selection Grid -->
                        <div id="drawer-section-2" class="drawer-section hidden space-y-4">
                            <div class="rounded-2xl border border-slate-200/90 p-5 bg-white space-y-4 shadow-xs">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 pb-3.5">
                                    <div>
                                        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                                            <span class="h-2 w-2 rounded-full bg-slate-900"></span>
                                            2. Field Type
                                        </h3>
                                        <p class="text-xs text-slate-500 mt-0.5 font-normal">Choose how data is entered and stored for this field.</p>
                                    </div>
                                    <div class="relative w-full sm:w-56">
                                        <svg class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                        <input
                                            type="text"
                                            id="type-search-input"
                                            placeholder="Filter field types..."
                                            oninput="filterFieldTypes(this.value)"
                                            class="w-full rounded-xl border border-slate-200 py-1.5 pl-8.5 pr-3 text-xs bg-slate-50 text-slate-900 outline-none focus:border-slate-900 focus:bg-white transition"
                                        />
                                    </div>
                                </div>

                                <div id="grouped-type-container" class="space-y-4 max-h-84 overflow-y-auto pr-1 custom-scrollbar">
                                    <!-- Text Group -->
                                    <div class="type-group" data-group="Text">
                                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Text</div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            <button type="button" onclick="selectTypeCard('text')" class="type-card flex items-center gap-3 rounded-xl border border-slate-900 bg-slate-50 p-3 text-left transition hover:border-slate-900 shadow-2xs focus:outline-none focus:ring-2 focus:ring-slate-900" data-key="text" data-label="Short text Text">
                                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-900 text-white font-black text-xs">T</span>
                                                <div class="min-w-0 flex-1">
                                                    <div class="text-xs font-bold text-slate-900">Short text</div>
                                                    <div class="text-[11px] text-slate-500">Single line input</div>
                                                </div>
                                                <span class="type-check-icon text-slate-900 font-bold text-xs">✓</span>
                                            </button>
                                            <button type="button" onclick="selectTypeCard('textarea')" class="type-card flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 text-left transition hover:border-slate-400 shadow-2xs focus:outline-none focus:ring-2 focus:ring-slate-900" data-key="textarea" data-label="Long text Textarea">
                                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-700 font-black text-xs">¶</span>
                                                <div class="min-w-0 flex-1">
                                                    <div class="text-xs font-bold text-slate-900">Long text</div>
                                                    <div class="text-[11px] text-slate-500">Multi-line paragraph</div>
                                                </div>
                                                <span class="type-check-icon hidden text-slate-900 font-bold text-xs">✓</span>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Numbers Group -->
                                    <div class="type-group" data-group="Numbers">
                                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Numbers</div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            <button type="button" onclick="selectTypeCard('price')" class="type-card flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 text-left transition hover:border-slate-400 shadow-2xs focus:outline-none focus:ring-2 focus:ring-slate-900" data-key="price" data-label="Number Currency Price">
                                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-700 font-black text-xs">$</span>
                                                <div class="min-w-0 flex-1">
                                                    <div class="text-xs font-bold text-slate-900">Currency & Price</div>
                                                    <div class="text-[11px] text-slate-500">Monetary value</div>
                                                </div>
                                                <span class="type-check-icon hidden text-slate-900 font-bold text-xs">✓</span>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Date & Time Group -->
                                    <div class="type-group" data-group="Date & Time">
                                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Date & Time</div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            <button type="button" onclick="selectTypeCard('date')" class="type-card flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 text-left transition hover:border-slate-400 shadow-2xs focus:outline-none focus:ring-2 focus:ring-slate-900" data-key="date" data-label="Date">
                                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-700 font-bold text-xs">
                                                    <svg class="w-4 h-4 text-slate-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                </span>
                                                <div class="min-w-0 flex-1">
                                                    <div class="text-xs font-bold text-slate-900">Date</div>
                                                    <div class="text-[11px] text-slate-500">Calendar date</div>
                                                </div>
                                                <span class="type-check-icon hidden text-slate-900 font-bold text-xs">✓</span>
                                            </button>
                                            <button type="button" onclick="selectTypeCard('datetime')" class="type-card flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 text-left transition hover:border-slate-400 shadow-2xs focus:outline-none focus:ring-2 focus:ring-slate-900" data-key="datetime" data-label="Date & time Datetime">
                                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-700 font-bold text-xs">
                                                    <svg class="w-4 h-4 text-slate-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                </span>
                                                <div class="min-w-0 flex-1">
                                                    <div class="text-xs font-bold text-slate-900">Date & time</div>
                                                    <div class="text-[11px] text-slate-500">Date & timestamp</div>
                                                </div>
                                                <span class="type-check-icon hidden text-slate-900 font-bold text-xs">✓</span>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Selection Group -->
                                    <div class="type-group" data-group="Selection">
                                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Selection & Choices</div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            <button type="button" onclick="selectTypeCard('select')" class="type-card flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 text-left transition hover:border-slate-400 shadow-2xs focus:outline-none focus:ring-2 focus:ring-slate-900" data-key="select" data-label="Dropdown Select">
                                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-700 font-black text-xs">≡</span>
                                                <div class="min-w-0 flex-1">
                                                    <div class="text-xs font-bold text-slate-900">Dropdown</div>
                                                    <div class="text-[11px] text-slate-500">Select one option</div>
                                                </div>
                                                <span class="type-check-icon hidden text-slate-900 font-bold text-xs">✓</span>
                                            </button>
                                            <button type="button" onclick="selectTypeCard('multiselect')" class="type-card flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 text-left transition hover:border-slate-400 shadow-2xs focus:outline-none focus:ring-2 focus:ring-slate-900" data-key="multiselect" data-label="Multi-select Multiselect">
                                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-700 font-black text-xs">≣</span>
                                                <div class="min-w-0 flex-1">
                                                    <div class="text-xs font-bold text-slate-900">Multi-select</div>
                                                    <div class="text-[11px] text-slate-500">Select multiple options</div>
                                                </div>
                                                <span class="type-check-icon hidden text-slate-900 font-bold text-xs">✓</span>
                                            </button>
                                            <button type="button" onclick="selectTypeCard('checkbox')" class="type-card flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 text-left transition hover:border-slate-400 shadow-2xs focus:outline-none focus:ring-2 focus:ring-slate-900" data-key="checkbox" data-label="Checkbox">
                                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-700 font-black text-xs">☑</span>
                                                <div class="min-w-0 flex-1">
                                                    <div class="text-xs font-bold text-slate-900">Checkbox</div>
                                                    <div class="text-[11px] text-slate-500">Checkbox options</div>
                                                </div>
                                                <span class="type-check-icon hidden text-slate-900 font-bold text-xs">✓</span>
                                            </button>
                                            <button type="button" onclick="selectTypeCard('boolean')" class="type-card flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 text-left transition hover:border-slate-400 shadow-2xs focus:outline-none focus:ring-2 focus:ring-slate-900" data-key="boolean" data-label="Toggle Boolean">
                                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-700 font-black text-xs">◉</span>
                                                <div class="min-w-0 flex-1">
                                                    <div class="text-xs font-bold text-slate-900">Toggle</div>
                                                    <div class="text-[11px] text-slate-500">Yes / No boolean switch</div>
                                                </div>
                                                <span class="type-check-icon hidden text-slate-900 font-bold text-xs">✓</span>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Relationship Group -->
                                    <div class="type-group" data-group="Relationship">
                                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Relationship</div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            <button type="button" onclick="selectTypeCard('lookup')" class="type-card flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 text-left transition hover:border-slate-400 shadow-2xs focus:outline-none focus:ring-2 focus:ring-slate-900" data-key="lookup" data-label="Lookup Relationship">
                                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-700 font-bold text-xs">
                                                    <svg class="w-4 h-4 text-slate-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                                </span>
                                                <div class="min-w-0 flex-1">
                                                    <div class="text-xs font-bold text-slate-900">Lookup</div>
                                                    <div class="text-[11px] text-slate-500">Link related record</div>
                                                </div>
                                                <span class="type-check-icon hidden text-slate-900 font-bold text-xs">✓</span>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Contact Group -->
                                    <div class="type-group" data-group="Contact">
                                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Contact</div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            <button type="button" onclick="selectTypeCard('email')" class="type-card flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 text-left transition hover:border-slate-400 shadow-2xs focus:outline-none focus:ring-2 focus:ring-slate-900" data-key="email" data-label="Email">
                                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-700 font-black text-xs">@</span>
                                                <div class="min-w-0 flex-1">
                                                    <div class="text-xs font-bold text-slate-900">Email</div>
                                                    <div class="text-[11px] text-slate-500">Email address</div>
                                                </div>
                                                <span class="type-check-icon hidden text-slate-900 font-bold text-xs">✓</span>
                                            </button>
                                            <button type="button" onclick="selectTypeCard('phone')" class="type-card flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 text-left transition hover:border-slate-400 shadow-2xs focus:outline-none focus:ring-2 focus:ring-slate-900" data-key="phone" data-label="Phone">
                                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-700 font-black text-xs">☎</span>
                                                <div class="min-w-0 flex-1">
                                                    <div class="text-xs font-bold text-slate-900">Phone</div>
                                                    <div class="text-[11px] text-slate-500">Phone number</div>
                                                </div>
                                                <span class="type-check-icon hidden text-slate-900 font-bold text-xs">✓</span>
                                            </button>
                                            <button type="button" onclick="selectTypeCard('address')" class="type-card flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 text-left transition hover:border-slate-400 shadow-2xs focus:outline-none focus:ring-2 focus:ring-slate-900" data-key="address" data-label="Address">
                                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-700 font-black text-xs">⌂</span>
                                                <div class="min-w-0 flex-1">
                                                    <div class="text-xs font-bold text-slate-900">Address</div>
                                                    <div class="text-[11px] text-slate-500">Physical address</div>
                                                </div>
                                                <span class="type-check-icon hidden text-slate-900 font-bold text-xs">✓</span>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Files Group -->
                                    <div class="type-group" data-group="Files">
                                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Files</div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            <button type="button" onclick="selectTypeCard('file')" class="type-card flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 text-left transition hover:border-slate-400 shadow-2xs focus:outline-none focus:ring-2 focus:ring-slate-900" data-key="file" data-label="File">
                                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-700 font-black text-xs">📎</span>
                                                <div class="min-w-0 flex-1">
                                                    <div class="text-xs font-bold text-slate-900">File</div>
                                                    <div class="text-[11px] text-slate-500">Document upload</div>
                                                </div>
                                                <span class="type-check-icon hidden text-slate-900 font-bold text-xs">✓</span>
                                            </button>
                                            <button type="button" onclick="selectTypeCard('image')" class="type-card flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 text-left transition hover:border-slate-400 shadow-2xs focus:outline-none focus:ring-2 focus:ring-slate-900" data-key="image" data-label="Image">
                                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-700 font-black text-xs">🖼</span>
                                                <div class="min-w-0 flex-1">
                                                    <div class="text-xs font-bold text-slate-900">Image</div>
                                                    <div class="text-[11px] text-slate-500">Image upload</div>
                                                </div>
                                                <span class="type-check-icon hidden text-slate-900 font-bold text-xs">✓</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Configuration (Dynamic per type) -->
                        <div id="drawer-section-3" class="drawer-section hidden space-y-4">
                            <div class="rounded-2xl border border-slate-200/90 p-5 bg-white space-y-4 shadow-xs">
                                <div class="border-b border-slate-100 pb-3">
                                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                                        <span class="h-2 w-2 rounded-full bg-slate-900"></span>
                                        3. Configuration
                                    </h3>
                                    <p class="text-xs text-slate-500 mt-1 font-normal">Fine-tune settings specific to the selected field type.</p>
                                </div>

                                <!-- Options List for Select / Multiselect / Checkbox -->
                                <div id="options-config-container" class="hidden space-y-3">
                                    <div class="flex items-center justify-between">
                                        <label class="block text-xs font-bold text-slate-700">Available Options <span class="text-red-500">*</span></label>
                                        <span class="text-[11px] text-slate-400">At least 1 option required</span>
                                    </div>
                                    <div id="options-list" class="space-y-2 max-h-60 overflow-y-auto pr-1 custom-scrollbar">
                                        <div class="option-row flex items-center gap-2">
                                            <input type="hidden" class="option-id-input" value="" />
                                            <span class="option-index-badge text-[11px] font-mono font-bold text-slate-400 w-5 text-center shrink-0">1.</span>
                                            <input type="text" placeholder="e.g. Enterprise" oninput="markDrawerDirty()" class="option-name-input flex-1 rounded-xl border border-slate-200 px-3 py-2 text-xs bg-slate-50/60 text-slate-900 focus:bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition" />
                                            <button type="button" onclick="removeOptionRow(this)" aria-label="Remove option" class="p-2 text-slate-400 hover:text-red-600 rounded-xl hover:bg-red-50 transition shrink-0">
                                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </div>
                                    </div>
                                    <button type="button" onclick="addOptionRow()" class="text-xs text-slate-900 font-bold hover:underline inline-flex items-center gap-1.5 mt-1 focus:outline-none">
                                        <span class="text-sm font-black">+</span> Add Option
                                    </button>
                                    <p id="options-error" class="hidden text-xs font-semibold text-red-600 mt-1 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <span>Please add at least one non-empty option.</span>
                                    </p>
                                </div>

                                <!-- Lookup Entity Selection -->
                                <div id="lookup-config-container" class="hidden space-y-3">
                                    <div>
                                        <label for="field-lookup-type" class="block text-xs font-bold text-slate-700 mb-1">Lookup Entity Model</label>
                                        <select id="field-lookup-type" name="lookup_type" onchange="markDrawerDirty()" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm bg-slate-50/60 text-slate-900 focus:bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition cursor-pointer font-medium">
                                            <option value="users">Users / Agents</option>
                                            <option value="leads">Leads</option>
                                            <option value="persons">Persons / Contacts</option>
                                            <option value="organizations">Organizations</option>
                                            <option value="products">Products</option>
                                            <option value="quotes">Quotes</option>
                                        </select>
                                        <p class="text-[11px] text-slate-500 mt-1">Select the CRM entity this lookup field will reference.</p>
                                    </div>
                                </div>

                                <!-- Text/Textarea Configuration -->
                                <div id="text-config-container" class="hidden space-y-4">
                                    <div>
                                        <label for="field-validation-input" class="block text-xs font-bold text-slate-700 mb-1">Input Validation Format</label>
                                        <select id="field-validation-input" name="validation" onchange="markDrawerDirty()" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm bg-slate-50/60 text-slate-900 focus:bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition cursor-pointer font-medium">
                                            <option value="">None (Standard Text)</option>
                                            <option value="numeric">Numeric Only</option>
                                            <option value="email">Email Format</option>
                                            <option value="decimal">Decimal Numbers</option>
                                            <option value="url">Web URL</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="field-validation-regex" class="block text-xs font-bold text-slate-700 mb-1">Validation Regex Pattern <span class="text-slate-400 font-normal">(Optional)</span></label>
                                        <input type="text" id="field-validation-regex" placeholder="e.g. ^[A-Z0-9_-]+$" oninput="markDrawerDirty()" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs font-mono bg-slate-50/60 text-slate-900 focus:bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition" />
                                        <p class="text-[11px] text-slate-500 mt-1">Optional regular expression for custom input verification.</p>
                                    </div>
                                </div>

                                <!-- File/Image Configuration -->
                                <div id="file-config-container" class="hidden space-y-4">
                                    <div>
                                        <label for="file-allowed-extensions" class="block text-xs font-bold text-slate-700 mb-1">Allowed Extensions</label>
                                        <input type="text" id="file-allowed-extensions" placeholder="e.g. pdf, docx, png, jpg" oninput="markDrawerDirty()" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs bg-slate-50/60 text-slate-900 focus:bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition" />
                                        <p class="text-[11px] text-slate-500 mt-1">Comma-separated list of permitted file extensions.</p>
                                    </div>
                                    <div>
                                        <label for="file-max-size" class="block text-xs font-bold text-slate-700 mb-1">Maximum Size (MB)</label>
                                        <input type="number" id="file-max-size" min="1" max="100" value="10" oninput="markDrawerDirty()" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs bg-slate-50/60 text-slate-900 focus:bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition" />
                                    </div>
                                </div>

                                <!-- Default Message for Types with No Additional Settings -->
                                <div id="config-default-msg" class="rounded-xl border border-slate-100 bg-slate-50/50 p-4 text-center">
                                    <p class="text-xs text-slate-600 font-medium">Standard workspace formatting and constraints are automatically applied for this field type.</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">No additional custom settings required.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Section 4: Validation & Rules -->
                        <div id="drawer-section-4" class="drawer-section hidden space-y-4">
                            <div class="rounded-2xl border border-slate-200/90 p-5 bg-white space-y-4 shadow-xs">
                                <div class="border-b border-slate-100 pb-3">
                                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                                        <span class="h-2 w-2 rounded-full bg-slate-900"></span>
                                        4. Validation
                                    </h3>
                                    <p class="text-xs text-slate-500 mt-1 font-normal">Set data requirements and integrity constraints.</p>
                                </div>
                                
                                <!-- is_required Toggle -->
                                <div class="flex items-center justify-between rounded-xl border border-slate-200/80 p-4 bg-slate-50/40 hover:bg-slate-50 transition">
                                    <div class="pr-4">
                                        <label for="is_required" class="text-xs font-bold text-slate-900 cursor-pointer block">
                                            Mandatory Field (Required)
                                        </label>
                                        <p class="text-[11px] text-slate-500 mt-0.5">
                                            Require users to fill this field before saving records.
                                        </p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                        <input type="checkbox" name="is_required" id="is_required" value="1" onchange="markDrawerDirty()" class="sr-only peer" />
                                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-slate-900"></div>
                                    </label>
                                </div>

                                <!-- is_unique Toggle -->
                                <div class="flex items-center justify-between rounded-xl border border-slate-200/80 p-4 bg-slate-50/40 hover:bg-slate-50 transition">
                                    <div class="pr-4">
                                        <label for="is_unique" class="text-xs font-bold text-slate-900 cursor-pointer block">
                                            Unique Values Only
                                        </label>
                                        <p class="text-[11px] text-slate-500 mt-0.5">
                                            Prevent duplicate values across different CRM records.
                                        </p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                        <input type="checkbox" name="is_unique" id="is_unique" value="1" onchange="markDrawerDirty()" class="sr-only peer" />
                                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-slate-900"></div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Section 5: Visibility -->
                        <div id="drawer-section-5" class="drawer-section hidden space-y-4">
                            <div class="rounded-2xl border border-slate-200/90 p-5 bg-white space-y-4 shadow-xs">
                                <div class="border-b border-slate-100 pb-3">
                                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                                        <span class="h-2 w-2 rounded-full bg-slate-900"></span>
                                        5. Visibility
                                    </h3>
                                    <p class="text-xs text-slate-500 mt-1 font-normal">Configure where this field appears across your workspace.</p>
                                </div>

                                <!-- quick_add Toggle -->
                                <div class="flex items-center justify-between rounded-xl border border-slate-200/80 p-4 bg-slate-50/40 hover:bg-slate-50 transition">
                                    <div class="pr-4">
                                        <label for="quick_add" class="text-xs font-bold text-slate-900 cursor-pointer block">
                                            Show in Quick Add
                                        </label>
                                        <p class="text-[11px] text-slate-500 mt-0.5">
                                            Include this field in quick creation modals, sidebar drawers, and compact cards.
                                        </p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                        <input type="checkbox" name="quick_add" id="quick_add" value="1" checked onchange="markDrawerDirty()" class="sr-only peer" />
                                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-slate-900"></div>
                                    </label>
                                </div>

                                <!-- Workspace Visibility Context Note -->
                                <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-4">
                                    <div class="flex items-start gap-2.5">
                                        <svg class="w-4 h-4 text-slate-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <div class="text-[11px] text-slate-600 leading-relaxed">
                                            All custom fields are automatically available in data grids, export/import tools, advanced filters, reports, and detail views.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 6: Advanced Settings & Review -->
                        <div id="drawer-section-6" class="drawer-section hidden space-y-4">
                            <div class="rounded-2xl border border-slate-200/90 p-5 bg-white space-y-4 shadow-xs">
                                <div class="border-b border-slate-100 pb-3">
                                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                                        <span class="h-2 w-2 rounded-full bg-slate-900"></span>
                                        6. Advanced
                                    </h3>
                                    <p class="text-xs text-slate-500 mt-1 font-normal">Review field specifications and configure sort ordering.</p>
                                </div>

                                <div>
                                    <label for="field-sort-order" class="block text-xs font-bold text-slate-700 mb-1">Sort Order Index</label>
                                    <input type="number" id="field-sort-order" name="sort_order" value="0" min="0" oninput="markDrawerDirty()" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm bg-slate-50/60 text-slate-900 focus:bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition font-medium" />
                                    <p class="text-[11px] text-slate-400 mt-1">Lower numbers appear first in forms and detail panels.</p>
                                </div>

                                <!-- Review Specification Summary Card -->
                                <div class="rounded-xl border border-slate-200/90 bg-slate-50/50 p-4 space-y-3">
                                    <div class="text-xs font-bold text-slate-900 uppercase tracking-wider">Configuration Summary</div>
                                    <div class="grid grid-cols-2 gap-2.5 text-xs">
                                        <div class="p-2.5 bg-white rounded-lg border border-slate-200/60">
                                            <span class="text-[10px] text-slate-400 block uppercase font-bold">Target Entity</span>
                                            <span id="summary-entity" class="font-bold text-slate-900">Leads</span>
                                        </div>
                                        <div class="p-2.5 bg-white rounded-lg border border-slate-200/60">
                                            <span class="text-[10px] text-slate-400 block uppercase font-bold">Field Type</span>
                                            <span id="summary-type" class="font-bold text-slate-900">Short text</span>
                                        </div>
                                        <div class="p-2.5 bg-white rounded-lg border border-slate-200/60">
                                            <span class="text-[10px] text-slate-400 block uppercase font-bold">Field Code</span>
                                            <span id="summary-code" class="font-mono text-slate-900 font-medium">auto</span>
                                        </div>
                                        <div class="p-2.5 bg-white rounded-lg border border-slate-200/60">
                                            <span class="text-[10px] text-slate-400 block uppercase font-bold">Validation</span>
                                            <span id="summary-validation" class="font-bold text-slate-900">Optional</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Sticky Drawer Footer Controls -->
                <div class="flex items-center justify-between px-6 py-3.5 border-t border-slate-100 bg-slate-50/90 shrink-0">
                    <div class="flex items-center gap-3">
                        <button type="button" onclick="confirmCloseDrawer()" class="rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 px-4 py-2 text-xs font-bold transition focus:outline-none focus:ring-2 focus:ring-slate-900">
                            Cancel
                        </button>
                        <span id="drawer-step-indicator" class="text-xs text-slate-500 font-medium hidden sm:inline">
                            Step 1 of 6
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" id="drawer-prev-btn" onclick="prevDrawerTab()" class="hidden rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 px-4 py-2 text-xs font-bold transition focus:outline-none focus:ring-2 focus:ring-slate-900">
                            &larr; Back
                        </button>
                        <button type="button" id="drawer-next-btn" onclick="nextDrawerTab()" class="rounded-xl bg-slate-900 hover:bg-black text-white px-5 py-2 text-xs font-bold shadow-xs transition flex items-center gap-1.5 focus:outline-none focus:ring-2 focus:ring-slate-900">
                            <span>Continue</span>
                            <span>&rarr;</span>
                        </button>
                        <button type="submit" id="drawer-submit-btn" class="hidden rounded-xl bg-slate-900 hover:bg-black text-white px-5 py-2 text-xs font-bold shadow-sm transition flex items-center gap-1.5 active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-slate-900">
                            <svg class="w-3.5 h-3.5 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span id="drawer-submit-label">Create Field</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Unsaved Changes Discard Confirmation Dialog -->
<div id="drawer-discard-modal" class="hidden fixed inset-0 z-[10001] overflow-y-auto bg-slate-900/50 backdrop-blur-xs transition-opacity" role="alertdialog" aria-modal="true" aria-labelledby="discard-modal-title">
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="w-full max-w-sm bg-white rounded-2xl shadow-2xl border border-slate-200 p-5 space-y-4">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-black shrink-0 border border-amber-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <div>
                    <h3 id="discard-modal-title" class="text-sm font-bold text-slate-900">Discard changes?</h3>
                    <p class="text-xs text-slate-500 mt-0.5">You have unsaved configuration changes to this field.</p>
                </div>
            </div>
            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="hideDiscardModal()" class="rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 px-3.5 py-1.5 text-xs font-bold transition">
                    Keep Editing
                </button>
                <button type="button" onclick="forceCloseAddFieldDrawer()" class="rounded-xl bg-red-600 hover:bg-red-700 text-white px-3.5 py-1.5 text-xs font-bold transition shadow-xs">
                    Discard
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Dynamic studio field card action template reference for draggable items with icon-edit and icon-delete --}}
<template id="studio-field-card-template">
    <div class="field-item draggable" draggable="true">
        <button class="icon-edit" aria-label="Edit"></button>
        <button class="icon-delete" aria-label="Delete"></button>
    </div>
</template>

<script>
    let currentDrawerTab = 1;
    let isDrawerDirty = false;
    let completedDrawerTabs = new Set([1]);

    const entityMetaDict = {
        leads: { name: 'Leads', icon: '👤' },
        persons: { name: 'Persons', icon: '👤' },
        organizations: { name: 'Organizations', icon: '🏢' },
        products: { name: 'Products', icon: '📦' },
        quotes: { name: 'Quotes', icon: '📄' }
    };

    function markDrawerDirty() {
        isDrawerDirty = true;
    }

    function handleNameInput(val) {
        const codeInput = document.getElementById('field-code-input');
        const namePreview = document.getElementById('drawer-field-name-preview');
        const nameError = document.getElementById('field-name-error');
        
        if (nameError && val.trim()) nameError.classList.add('hidden');

        if (namePreview) {
            if (val.trim() && document.getElementById('edit-field-id').value) {
                namePreview.textContent = '· ' + val.trim();
                namePreview.classList.remove('hidden');
            } else {
                namePreview.classList.add('hidden');
            }
        }

        if (codeInput && !codeInput.readOnly && (!codeInput.dataset.touched || codeInput.dataset.touched === 'false')) {
            codeInput.value = val.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '');
        }
        validateFieldCode(codeInput ? codeInput.value : '');
    }

    function validateFieldCode(val) {
        const codeError = document.getElementById('field-code-error');
        if (!codeError) return true;
        if (!val) {
            codeError.classList.add('hidden');
            return true;
        }
        const isValid = /^[a-z0-9_]+$/.test(val);
        codeError.classList.toggle('hidden', isValid);
        return isValid;
    }

    document.getElementById('field-code-input')?.addEventListener('input', function () {
        this.dataset.touched = 'true';
        markDrawerDirty();
    });

    function updateEntityContext(entityCode) {
        const meta = entityMetaDict[entityCode] || { name: entityCode.toUpperCase(), icon: '👤' };
        const entityLabel = document.getElementById('drawer-context-entity');
        const entityIcon = document.getElementById('drawer-context-icon');
        const entityHelper = document.getElementById('entity-helper-text');
        
        if (entityLabel) entityLabel.textContent = meta.name.toUpperCase();
        if (entityIcon) entityIcon.textContent = meta.icon;
        if (entityHelper) entityHelper.textContent = `This field will be available wherever ${meta.name} fields are supported.`;
    }

    function switchDrawerTab(tabIndex) {
        // Prevent jumping ahead to unreached tabs directly unless tab is <= highest completed tab or current
        if (tabIndex > currentDrawerTab) {
            if (!validateCurrentStep(currentDrawerTab)) {
                return;
            }
        }

        currentDrawerTab = tabIndex;
        completedDrawerTabs.add(tabIndex);

        // Update progress bar width
        const progressBar = document.getElementById('drawer-progress-bar');
        if (progressBar) {
            const pct = ((tabIndex) / 6) * 100;
            progressBar.style.width = pct + '%';
        }

        // Switch visible sections
        document.querySelectorAll('.drawer-section').forEach((sec, idx) => {
            sec.classList.toggle('hidden', idx + 1 !== tabIndex);
        });

        // Step tabs visual rendering
        document.querySelectorAll('.drawer-step-tab').forEach((tab, idx) => {
            const stepNum = idx + 1;
            const badge = tab.querySelector('.step-badge');
            
            if (stepNum === tabIndex) {
                // Active / Current step
                tab.className = 'drawer-step-tab flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-bold transition border border-slate-900 bg-slate-900 text-white shadow-xs shrink-0 cursor-pointer focus:outline-none focus:ring-2 focus:ring-slate-900';
                if (badge) {
                    badge.className = 'step-badge h-4.5 w-4.5 rounded-full bg-white text-slate-900 flex items-center justify-center text-[10px] font-black';
                    badge.innerHTML = String(stepNum);
                }
            } else if (stepNum < tabIndex || completedDrawerTabs.has(stepNum)) {
                // Completed step
                tab.className = 'drawer-step-tab flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-semibold transition border border-slate-200/80 bg-white text-slate-800 hover:bg-slate-100 shrink-0 cursor-pointer shadow-2xs focus:outline-none focus:ring-2 focus:ring-slate-900';
                if (badge) {
                    badge.className = 'step-badge h-4.5 w-4.5 rounded-full bg-slate-900 text-white flex items-center justify-center text-[10px] font-bold';
                    badge.innerHTML = '✓';
                }
            } else {
                // Upcoming step
                tab.className = 'drawer-step-tab flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-semibold transition border border-transparent text-slate-400 shrink-0 cursor-not-allowed opacity-75';
                if (badge) {
                    badge.className = 'step-badge h-4.5 w-4.5 rounded-full bg-slate-200/80 text-slate-500 flex items-center justify-center text-[10px] font-black';
                    badge.innerHTML = String(stepNum);
                }
            }
        });

        // Step indicator & Button states
        const prevBtn = document.getElementById('drawer-prev-btn');
        const nextBtn = document.getElementById('drawer-next-btn');
        const submitBtn = document.getElementById('drawer-submit-btn');
        const stepIndicator = document.getElementById('drawer-step-indicator');

        if (stepIndicator) stepIndicator.textContent = `Step ${tabIndex} of 6`;
        if (prevBtn) prevBtn.classList.toggle('hidden', tabIndex === 1);
        
        if (tabIndex === 6) {
            if (nextBtn) nextBtn.classList.add('hidden');
            if (submitBtn) submitBtn.classList.remove('hidden');
            updateSummaryCard();
        } else {
            if (nextBtn) nextBtn.classList.remove('hidden');
            if (submitBtn) submitBtn.classList.add('hidden');
        }

        // Hide general error banner on tab navigation
        const errBanner = document.getElementById('drawer-error-banner');
        if (errBanner) errBanner.classList.add('hidden');
    }

    function validateCurrentStep(step) {
        if (step === 1) {
            const nameInput = document.getElementById('field-name-input');
            const nameVal = nameInput ? nameInput.value.trim() : '';
            const nameError = document.getElementById('field-name-error');
            if (!nameVal) {
                if (nameError) nameError.classList.remove('hidden');
                if (nameInput) nameInput.focus();
                return false;
            }
            if (nameError) nameError.classList.add('hidden');
            return true;
        }

        if (step === 3) {
            const type = document.getElementById('selected-type-hidden-input')?.value || 'text';
            if (['select', 'multiselect', 'checkbox'].includes(type)) {
                const rows = document.querySelectorAll('#options-list .option-row');
                let validCount = 0;
                rows.forEach(r => {
                    const inp = r.querySelector('.option-name-input');
                    if (inp && inp.value.trim()) validCount++;
                });
                const optError = document.getElementById('options-error');
                if (validCount === 0) {
                    if (optError) optError.classList.remove('hidden');
                    return false;
                }
                if (optError) optError.classList.add('hidden');
            }
        }
        return true;
    }

    function nextDrawerTab() {
        if (!validateCurrentStep(currentDrawerTab)) return;
        if (currentDrawerTab < 6) {
            switchDrawerTab(currentDrawerTab + 1);
        }
    }

    function prevDrawerTab() {
        if (currentDrawerTab > 1) {
            switchDrawerTab(currentDrawerTab - 1);
        }
    }

    function updateSummaryCard() {
        const form = document.getElementById('add-field-drawer-form');
        if (!form) return;

        const entitySelect = document.getElementById('field-entity-type-select');
        const entityName = entitySelect ? entitySelect.options[entitySelect.selectedIndex]?.text : 'Leads';
        const typeKey = document.getElementById('selected-type-hidden-input')?.value || 'text';
        const typeCard = document.querySelector(`.type-card[data-key="${typeKey}"]`);
        const typeLabel = typeCard ? typeCard.querySelector('.text-slate-900')?.textContent : typeKey;
        const codeVal = document.getElementById('field-code-input')?.value.trim() || 'Auto-generated';
        const isRequired = document.getElementById('is_required')?.checked;
        const isUnique = document.getElementById('is_unique')?.checked;

        const sumEntity = document.getElementById('summary-entity');
        const sumType = document.getElementById('summary-type');
        const sumCode = document.getElementById('summary-code');
        const sumValidation = document.getElementById('summary-validation');

        if (sumEntity) sumEntity.textContent = entityName;
        if (sumType) sumType.textContent = typeLabel;
        if (sumCode) sumCode.textContent = codeVal;
        if (sumValidation) {
            const rules = [];
            if (isRequired) rules.push('Required');
            if (isUnique) rules.push('Unique');
            sumValidation.textContent = rules.length ? rules.join(' · ') : 'Optional';
        }
    }

    function openAddFieldDrawer(preset, field) {
        const form = document.getElementById('add-field-drawer-form');
        const title = document.getElementById('drawer-title');
        const subtitle = document.getElementById('drawer-subtitle');
        const submitLabel = document.getElementById('drawer-submit-label');
        const namePreview = document.getElementById('drawer-field-name-preview');
        const idBadge = document.getElementById('drawer-field-id-badge');
        const idVal = document.getElementById('drawer-field-id-val');
        const lockIcon = document.getElementById('field-code-lock-icon');
        const codeHelper = document.getElementById('field-code-helper');
        const errBanner = document.getElementById('drawer-error-banner');

        if (errBanner) errBanner.classList.add('hidden');
        form.reset();
        isDrawerDirty = false;
        completedDrawerTabs = new Set([1]);

        const codeInput = document.getElementById('field-code-input');
        if (codeInput) codeInput.dataset.touched = 'false';
        document.getElementById('edit-field-id').value = '';
        resetOptionRows();

        if (field && typeof field === 'object') {
            title.textContent = 'Edit Field';
            if (namePreview) {
                namePreview.textContent = '· ' + (field.name || '');
                namePreview.classList.remove('hidden');
            }
            if (subtitle) subtitle.textContent = 'Configure how this field behaves across your workspace.';
            if (submitLabel) submitLabel.textContent = 'Update Field';
            if (idBadge && idVal) {
                idVal.textContent = field.code ? `#${field.id} (${field.code})` : `#${field.id}`;
                idBadge.classList.remove('hidden');
            }
            if (lockIcon) lockIcon.classList.remove('hidden');
            if (codeHelper) codeHelper.textContent = 'Field codes cannot be changed after creation.';

            document.getElementById('edit-field-id').value = field.id;
            form.name.value = field.name || '';
            form.code.value = field.code || '';
            form.code.readOnly = true;
            if (form.entity_type) {
                form.entity_type.value = field.entity_type || 'leads';
                form.entity_type.disabled = true;
                updateEntityContext(form.entity_type.value);
            }
            if (form.lookup_type && field.lookup_type) {
                form.lookup_type.value = field.lookup_type;
            }
            if (form.validation) form.validation.value = field.validation || '';
            if (form.is_required) form.is_required.checked = !!field.is_required;
            if (form.is_unique) form.is_unique.checked = !!field.is_unique;
            if (form.quick_add) form.quick_add.checked = !!field.quick_add;
            if (form.sort_order) form.sort_order.value = field.sort_order ?? 0;
            selectTypeCard(field.type || 'text');

            if (field.options && field.options.length > 0) {
                const list = document.getElementById('options-list');
                list.innerHTML = '';
                field.options.forEach((opt, idx) => {
                    addOptionRow(opt.name, opt.id);
                });
            }
        } else {
            title.textContent = 'Create Field';
            if (namePreview) namePreview.classList.add('hidden');
            if (subtitle) subtitle.textContent = 'Define a custom field for your workspace.';
            if (submitLabel) submitLabel.textContent = 'Create Field';
            if (idBadge) idBadge.classList.add('hidden');
            if (lockIcon) lockIcon.classList.add('hidden');
            if (codeHelper) codeHelper.textContent = 'Automatically generated from the field name if left blank. Use lowercase letters, numbers, and underscores.';

            form.code.readOnly = false;
            if (form.entity_type) {
                form.entity_type.disabled = false;
                const active = document.getElementById('entity-selector');
                if (active) form.entity_type.value = active.value;
                updateEntityContext(form.entity_type.value);
            }
            const typeToSelect = typeof preset === 'string' ? preset : 'text';
            selectTypeCard(typeToSelect);
        }

        switchDrawerTab(1);
        document.getElementById('add-field-drawer').classList.remove('hidden');
        setTimeout(() => document.getElementById('field-name-input')?.focus(), 60);
    }

    function confirmCloseDrawer() {
        if (isDrawerDirty) {
            document.getElementById('drawer-discard-modal')?.classList.remove('hidden');
        } else {
            forceCloseAddFieldDrawer();
        }
    }

    function hideDiscardModal() {
        document.getElementById('drawer-discard-modal')?.classList.add('hidden');
    }

    function forceCloseAddFieldDrawer() {
        hideDiscardModal();
        isDrawerDirty = false;
        document.getElementById('add-field-drawer').classList.add('hidden');
    }

    function closeAddFieldDrawer() {
        confirmCloseDrawer();
    }

    function resetOptionRows() {
        const list = document.getElementById('options-list');
        if (!list) return;
        list.innerHTML = `
            <div class="option-row flex items-center gap-2">
                <input type="hidden" class="option-id-input" value="" />
                <span class="option-index-badge text-[11px] font-mono font-bold text-slate-400 w-5 text-center shrink-0">1.</span>
                <input type="text" placeholder="e.g. Enterprise" oninput="markDrawerDirty()" class="option-name-input flex-1 rounded-xl border border-slate-200 px-3 py-2 text-xs bg-slate-50/60 text-slate-900 focus:bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition" />
                <button type="button" onclick="removeOptionRow(this)" aria-label="Remove option" class="p-2 text-slate-400 hover:text-red-600 rounded-xl hover:bg-red-50 transition shrink-0">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
            </div>`;
    }

    function selectTypeCard(typeKey) {
        document.getElementById('selected-type-hidden-input').value = typeKey;
        markDrawerDirty();

        document.querySelectorAll('.type-card').forEach(card => {
            const isMatch = card.getAttribute('data-key') === typeKey;
            const badge = card.querySelector('span:first-child');
            const checkIcon = card.querySelector('.type-check-icon');

            if (isMatch) {
                card.className = 'type-card flex items-center gap-3 rounded-xl border border-slate-900 bg-slate-50 p-3 text-left transition hover:border-slate-900 shadow-2xs focus:outline-none focus:ring-2 focus:ring-slate-900 ring-1 ring-slate-900';
                if (badge) {
                    badge.classList.remove('bg-slate-100', 'text-slate-700');
                    badge.classList.add('bg-slate-900', 'text-white');
                }
                if (checkIcon) checkIcon.classList.remove('hidden');
            } else {
                card.className = 'type-card flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 text-left transition hover:border-slate-400 shadow-2xs focus:outline-none focus:ring-2 focus:ring-slate-900';
                if (badge) {
                    badge.classList.remove('bg-slate-900', 'text-white');
                    badge.classList.add('bg-slate-100', 'text-slate-700');
                }
                if (checkIcon) checkIcon.classList.add('hidden');
            }
        });

        handleTypeChange(typeKey);
    }

    function filterFieldTypes(query) {
        const q = query.toLowerCase().trim();
        const groups = document.querySelectorAll('.type-group');

        groups.forEach(group => {
            const cards = group.querySelectorAll('.type-card');
            let hasMatch = false;

            cards.forEach(card => {
                const key = (card.getAttribute('data-key') || '').toLowerCase();
                const label = (card.getAttribute('data-label') || '').toLowerCase();
                if (key.includes(q) || label.includes(q)) {
                    card.classList.remove('hidden');
                    hasMatch = true;
                } else {
                    card.classList.add('hidden');
                }
            });

            group.classList.toggle('hidden', !hasMatch && !!q);
        });
    }

    function handleTypeChange(type) {
        const optionsContainer = document.getElementById('options-config-container');
        const lookupContainer = document.getElementById('lookup-config-container');
        const textContainer = document.getElementById('text-config-container');
        const fileContainer = document.getElementById('file-config-container');
        const defaultMsg = document.getElementById('config-default-msg');

        optionsContainer?.classList.add('hidden');
        lookupContainer?.classList.add('hidden');
        textContainer?.classList.add('hidden');
        fileContainer?.classList.add('hidden');
        defaultMsg?.classList.add('hidden');

        if (['select', 'multiselect', 'checkbox'].includes(type)) {
            optionsContainer?.classList.remove('hidden');
        } else if (type === 'lookup') {
            lookupContainer?.classList.remove('hidden');
        } else if (['text', 'textarea'].includes(type)) {
            textContainer?.classList.remove('hidden');
        } else if (['file', 'image'].includes(type)) {
            fileContainer?.classList.remove('hidden');
        } else {
            defaultMsg?.classList.remove('hidden');
        }
    }

    function reindexOptions() {
        document.querySelectorAll('#options-list .option-row').forEach((row, idx) => {
            const badge = row.querySelector('.option-index-badge');
            if (badge) badge.textContent = `${idx + 1}.`;
        });
    }

    function addOptionRow(value, id) {
        const list = document.getElementById('options-list');
        const div = document.createElement('div');
        div.className = 'option-row flex items-center gap-2';
        const safeVal = typeof value === 'string' ? value.replace(/"/g, '&quot;') : '';
        const safeId = id ? String(id) : '';
        div.innerHTML = `
            <input type="hidden" class="option-id-input" value="${safeId}" />
            <span class="option-index-badge text-[11px] font-mono font-bold text-slate-400 w-5 text-center shrink-0"></span>
            <input type="text" value="${safeVal}" placeholder="e.g. Option Name" oninput="markDrawerDirty()" class="option-name-input flex-1 rounded-xl border border-slate-200 px-3 py-2 text-xs bg-slate-50/60 text-slate-900 focus:bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition" />
            <button type="button" onclick="removeOptionRow(this)" aria-label="Remove option" class="p-2 text-slate-400 hover:text-red-600 rounded-xl hover:bg-red-50 transition shrink-0">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            </button>
        `;
        list.appendChild(div);
        reindexOptions();
        markDrawerDirty();
        div.querySelector('.option-name-input')?.focus();
    }

    function removeOptionRow(btn) {
        const list = document.getElementById('options-list');
        const row = btn.closest('.option-row');
        if (list && list.querySelectorAll('.option-row').length > 1) {
            row.remove();
            reindexOptions();
        } else if (row) {
            row.querySelector('.option-name-input').value = '';
            row.querySelector('.option-id-input').value = '';
        }
        markDrawerDirty();
    }

    async function submitAddFieldDrawer(e) {
        e.preventDefault();
        const form = e.target;
        const name = form.name.value.trim();
        const type = form.type.value;
        const errBanner = document.getElementById('drawer-error-banner');
        const errMsg = document.getElementById('drawer-error-msg');
        
        if (!name) {
            switchDrawerTab(1);
            form.name.focus();
            return;
        }

        const editId = document.getElementById('edit-field-id').value;

        const options = [];
        form.querySelectorAll('.option-row').forEach((row, idx) => {
            const nameInp = row.querySelector('.option-name-input');
            const idInp = row.querySelector('.option-id-input');
            const optVal = nameInp ? nameInp.value.trim() : '';
            if (optVal) {
                const optObj = { name: optVal, sort_order: idx };
                if (idInp && idInp.value) optObj.id = parseInt(idInp.value, 10);
                options.push(optObj);
            }
        });

        if (['select', 'multiselect', 'checkbox'].includes(type) && options.length === 0) {
            switchDrawerTab(3);
            if (errBanner && errMsg) {
                errMsg.textContent = 'Please add at least one option for ' + type + ' fields.';
                errBanner.classList.remove('hidden');
            }
            return;
        }

        const payload = {
            name,
            type,
            validation: form.validation ? (form.validation.value.trim() || null) : null,
            lookup_type: type === 'lookup' && form.lookup_type ? form.lookup_type.value : null,
            is_required: !!(form.is_required && form.is_required.checked),
            is_unique: !!(form.is_unique && form.is_unique.checked),
            quick_add: !!(form.quick_add && form.quick_add.checked),
            sort_order: form.sort_order ? parseInt(form.sort_order.value || '0', 10) : 0,
            options,
        };

        const submitBtn = form.querySelector('#drawer-submit-btn');
        const submitLabel = document.getElementById('drawer-submit-label');
        const prevLabel = submitLabel ? submitLabel.textContent : '';
        if (submitBtn) submitBtn.disabled = true;
        if (submitLabel) submitLabel.textContent = editId ? 'Updating field...' : 'Creating field...';
        if (errBanner) errBanner.classList.add('hidden');

        try {
            if (editId) {
                await MoldableBuilder.api('/fields/' + editId, { method: 'PUT', body: JSON.stringify(payload) });
            } else {
                payload.code = form.code.value.trim() || null;
                payload.entity_type = form.entity_type ? form.entity_type.value : 'leads';
                await MoldableBuilder.api('/fields', { method: 'POST', body: JSON.stringify(payload) });
            }
            isDrawerDirty = false;
            forceCloseAddFieldDrawer();
            await MoldableBuilder.refresh();
        } catch (err) {
            if (errBanner && errMsg) {
                errMsg.textContent = err.message || 'Could not save the field. Please verify the input.';
                errBanner.classList.remove('hidden');
                errBanner.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } else {
                alert(err.message || 'Could not save the field.');
            }
        } finally {
            if (submitBtn) submitBtn.disabled = false;
            if (submitLabel) submitLabel.textContent = prevLabel;
        }
    }

    // Keyboard navigation helper
    document.addEventListener('keydown', function(e) {
        const drawer = document.getElementById('add-field-drawer');
        const discardModal = document.getElementById('drawer-discard-modal');
        if (drawer && !drawer.classList.contains('hidden')) {
            if (e.key === 'Escape') {
                if (discardModal && !discardModal.classList.contains('hidden')) {
                    hideDiscardModal();
                } else {
                    closeAddFieldDrawer();
                }
            }
        }
    });
</script>
