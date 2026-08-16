<div id="add-field-drawer" class="hidden fixed inset-0 z-[10000] overflow-y-auto bg-slate-900/60 backdrop-blur-sm transition-opacity" role="dialog" aria-modal="true" aria-labelledby="drawer-title">
    <div class="flex min-h-screen items-center justify-center p-4 sm:p-6" onclick="if(event.target === this) closeAddFieldDrawer()">
        <div class="w-full max-w-4xl bg-white rounded-2xl shadow-2xl border border-slate-200 flex flex-col overflow-hidden max-h-[92vh]">
            
            <!-- Drawer Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/70">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-900 text-white font-bold">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    </div>
                    <div>
                        <h2 id="drawer-title" class="text-base font-bold text-slate-900">Add Field</h2>
                        <p class="text-xs text-slate-400">Configure custom attribute settings</p>
                    </div>
                </div>
                <button type="button" onclick="closeAddFieldDrawer()" aria-label="Close dialog" class="rounded-xl p-2 text-slate-400 hover:bg-slate-200/60 hover:text-slate-700 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Tab / Step Navigation Bar -->
            <div class="flex items-center gap-1.5 px-6 py-2.5 bg-white border-b border-slate-100 overflow-x-auto scrollbar-none" role="tablist">
                <button type="button" onclick="switchDrawerTab(1)" data-drawer-tab="1" class="drawer-step-tab flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-bold transition border border-slate-900 bg-slate-900 text-white shadow-2xs">
                    <span class="h-4 w-4 rounded-full bg-white text-slate-900 flex items-center justify-center text-[10px] font-black">1</span>
                    <span>Basic Info</span>
                </button>
                <button type="button" onclick="switchDrawerTab(2)" data-drawer-tab="2" class="drawer-step-tab flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-semibold transition border border-transparent text-slate-600 hover:bg-slate-100">
                    <span class="h-4 w-4 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center text-[10px] font-black">2</span>
                    <span>Field Type</span>
                </button>
                <button type="button" onclick="switchDrawerTab(3)" data-drawer-tab="3" class="drawer-step-tab flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-semibold transition border border-transparent text-slate-600 hover:bg-slate-100">
                    <span class="h-4 w-4 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center text-[10px] font-black">3</span>
                    <span>Configuration</span>
                </button>
                <button type="button" onclick="switchDrawerTab(4)" data-drawer-tab="4" class="drawer-step-tab flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-semibold transition border border-transparent text-slate-600 hover:bg-slate-100">
                    <span class="h-4 w-4 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center text-[10px] font-black">4</span>
                    <span>Validation</span>
                </button>
                <button type="button" onclick="switchDrawerTab(5)" data-drawer-tab="5" class="drawer-step-tab flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-semibold transition border border-transparent text-slate-600 hover:bg-slate-100">
                    <span class="h-4 w-4 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center text-[10px] font-black">5</span>
                    <span>Visibility</span>
                </button>
                <button type="button" onclick="switchDrawerTab(6)" data-drawer-tab="6" class="drawer-step-tab flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-semibold transition border border-transparent text-slate-600 hover:bg-slate-100">
                    <span class="h-4 w-4 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center text-[10px] font-black">6</span>
                    <span>Advanced</span>
                </button>
            </div>

            <!-- Drawer Body Form -->
            <form id="add-field-drawer-form" onsubmit="submitAddFieldDrawer(event)" class="flex-1 flex flex-col overflow-hidden">
                <input type="hidden" name="field_id" id="edit-field-id" value="" />
                <input type="hidden" name="type" id="selected-type-hidden-input" value="text" />

                <!-- Scrollable Tab Content Container -->
                <div class="flex-1 overflow-y-auto p-6 space-y-6">

                    <!-- Section 1: Basic Information -->
                    <div id="drawer-section-1" class="drawer-section space-y-4">
                        <div class="rounded-2xl border border-slate-200 p-5 bg-white space-y-4">
                            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 pb-3">
                                <span class="h-2 w-2 rounded-full bg-slate-900"></span>
                                1. Basic Information
                            </h3>
                            <div class="space-y-4">
                                <div>
                                    <label for="field-name-input" class="block text-xs font-bold text-slate-700 mb-1.5">Field Label <span class="text-red-500">*</span></label>
                                    <input type="text" id="field-name-input" name="name" required placeholder="e.g. Project Budget" class="w-full rounded-xl border border-slate-200 p-3 text-xs bg-slate-50/50 text-slate-900 focus:bg-white focus:border-slate-400 outline-none transition" />
                                </div>
                                <div>
                                    <label for="field-code-input" class="block text-xs font-bold text-slate-700 mb-1.5">Field Code <span class="text-slate-400 font-normal">(Auto-generated snake_case if empty)</span></label>
                                    <input type="text" id="field-code-input" name="code" placeholder="e.g. project_budget" class="w-full rounded-xl border border-slate-200 p-3 text-xs bg-slate-50/50 text-slate-900 focus:bg-white focus:border-slate-400 outline-none font-mono transition" />
                                </div>
                                <div>
                                    <label for="field-entity-type-select" class="block text-xs font-bold text-slate-700 mb-1.5">Target Entity <span class="text-red-500">*</span></label>
                                    <select id="field-entity-type-select" name="entity_type" required class="w-full rounded-xl border border-slate-200 p-3 text-xs bg-slate-50/50 text-slate-900 focus:bg-white focus:border-slate-400 outline-none transition cursor-pointer">
                                        @foreach(config('moldable.entities', ['leads' => ['name' => 'Leads'], 'persons' => ['name' => 'Persons'], 'organizations' => ['name' => 'Organizations'], 'products' => ['name' => 'Products'], 'quotes' => ['name' => 'Quotes']]) as $entityCode => $entityMeta)
                                            <option value="{{ $entityCode }}">{{ $entityMeta['name'] ?? ucfirst($entityCode) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Field Type Selection Grid -->
                    <div id="drawer-section-2" class="drawer-section hidden space-y-4">
                        <div class="rounded-2xl border border-slate-200 p-5 bg-white space-y-4">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                                    <span class="h-2 w-2 rounded-full bg-slate-900"></span>
                                    2. Field Type Selection
                                </h3>
                                <div class="relative w-64">
                                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                    <input
                                        type="text"
                                        id="type-search-input"
                                        placeholder="Filter field types..."
                                        oninput="filterFieldTypes(this.value)"
                                        class="w-full rounded-xl border border-slate-200 py-1.5 pl-8 pr-3 text-xs bg-slate-50 text-slate-900 outline-none focus:border-slate-400 focus:bg-white transition"
                                    />
                                </div>
                            </div>

                            <div id="grouped-type-container" class="space-y-4 max-h-80 overflow-y-auto pr-1">
                                <!-- Text Group -->
                                <div class="type-group" data-group="Text">
                                    <h4 class="text-[11px] font-bold uppercase text-slate-400 mb-2">Text</h4>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                                        <button type="button" onclick="selectTypeCard('text')" class="type-card flex items-center gap-3 rounded-xl border border-slate-900 bg-slate-50 p-3 text-left transition hover:border-slate-900 shadow-2xs" data-key="text" data-label="Text">
                                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-900 text-white font-bold text-xs">T</span>
                                            <div>
                                                <div class="text-xs font-bold text-slate-900">Text</div>
                                                <div class="text-[10px] text-slate-400">Single line input</div>
                                            </div>
                                        </button>
                                        <button type="button" onclick="selectTypeCard('textarea')" class="type-card flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 text-left transition hover:border-slate-400 shadow-2xs" data-key="textarea" data-label="Textarea">
                                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-700 font-bold text-xs">¶</span>
                                            <div>
                                                <div class="text-xs font-bold text-slate-900">Textarea</div>
                                                <div class="text-[10px] text-slate-400">Multi-line text</div>
                                            </div>
                                        </button>
                                    </div>
                                </div>

                                <!-- Number Group -->
                                <div class="type-group" data-group="Number">
                                    <h4 class="text-[11px] font-bold uppercase text-slate-400 mb-2">Number & Currency</h4>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                                        <button type="button" onclick="selectTypeCard('price')" class="type-card flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 text-left transition hover:border-slate-400 shadow-2xs" data-key="price" data-label="Price">
                                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-700 font-bold text-xs">$</span>
                                            <div>
                                                <div class="text-xs font-bold text-slate-900">Price</div>
                                                <div class="text-[10px] text-slate-400">Currency & amount</div>
                                            </div>
                                        </button>
                                    </div>
                                </div>

                                <!-- Date & Time Group -->
                                <div class="type-group" data-group="Date & Time">
                                    <h4 class="text-[11px] font-bold uppercase text-slate-400 mb-2">Date & Time</h4>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                                        <button type="button" onclick="selectTypeCard('date')" class="type-card flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 text-left transition hover:border-slate-400 shadow-2xs" data-key="date" data-label="Date">
                                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-700 font-bold text-xs">
                                                <svg class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            </span>
                                            <div>
                                                <div class="text-xs font-bold text-slate-900">Date</div>
                                                <div class="text-[10px] text-slate-400">Calendar date</div>
                                            </div>
                                        </button>
                                        <button type="button" onclick="selectTypeCard('datetime')" class="type-card flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 text-left transition hover:border-slate-400 shadow-2xs" data-key="datetime" data-label="Datetime">
                                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-700 font-bold text-xs">
                                                <svg class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            </span>
                                            <div>
                                                <div class="text-xs font-bold text-slate-900">Datetime</div>
                                                <div class="text-[10px] text-slate-400">Date & timestamp</div>
                                            </div>
                                        </button>
                                    </div>
                                </div>

                                <!-- Selection Group -->
                                <div class="type-group" data-group="Selection">
                                    <h4 class="text-[11px] font-bold uppercase text-slate-400 mb-2">Selection & Choices</h4>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                                        <button type="button" onclick="selectTypeCard('select')" class="type-card flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 text-left transition hover:border-slate-400 shadow-2xs" data-key="select" data-label="Select">
                                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-700 font-bold text-xs">≡</span>
                                            <div>
                                                <div class="text-xs font-bold text-slate-900">Select</div>
                                                <div class="text-[10px] text-slate-400">Single choice list</div>
                                            </div>
                                        </button>
                                        <button type="button" onclick="selectTypeCard('multiselect')" class="type-card flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 text-left transition hover:border-slate-400 shadow-2xs" data-key="multiselect" data-label="Multiselect">
                                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-700 font-bold text-xs">≣</span>
                                            <div>
                                                <div class="text-xs font-bold text-slate-900">Multiselect</div>
                                                <div class="text-[10px] text-slate-400">Multiple choices</div>
                                            </div>
                                        </button>
                                        <button type="button" onclick="selectTypeCard('checkbox')" class="type-card flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 text-left transition hover:border-slate-400 shadow-2xs" data-key="checkbox" data-label="Checkbox">
                                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-700 font-bold text-xs">☑</span>
                                            <div>
                                                <div class="text-xs font-bold text-slate-900">Checkbox</div>
                                                <div class="text-[10px] text-slate-400">Checkbox options</div>
                                            </div>
                                        </button>
                                        <button type="button" onclick="selectTypeCard('boolean')" class="type-card flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 text-left transition hover:border-slate-400 shadow-2xs" data-key="boolean" data-label="Boolean">
                                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-700 font-bold text-xs">◉</span>
                                            <div>
                                                <div class="text-xs font-bold text-slate-900">Boolean</div>
                                                <div class="text-[10px] text-slate-400">Yes / No switch</div>
                                            </div>
                                        </button>
                                    </div>
                                </div>

                                <!-- Relationship Group -->
                                <div class="type-group" data-group="Relationship">
                                    <h4 class="text-[11px] font-bold uppercase text-slate-400 mb-2">Relationships</h4>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                                        <button type="button" onclick="selectTypeCard('lookup')" class="type-card flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 text-left transition hover:border-slate-400 shadow-2xs" data-key="lookup" data-label="Lookup">
                                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-700 font-bold text-xs">
                                                <svg class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                            </span>
                                            <div>
                                                <div class="text-xs font-bold text-slate-900">Lookup</div>
                                                <div class="text-[10px] text-slate-400">Related entity</div>
                                            </div>
                                        </button>
                                    </div>
                                </div>

                                <!-- Contact Group -->
                                <div class="type-group" data-group="Contact">
                                    <h4 class="text-[11px] font-bold uppercase text-slate-400 mb-2">Contact Info</h4>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                                        <button type="button" onclick="selectTypeCard('email')" class="type-card flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 text-left transition hover:border-slate-400 shadow-2xs" data-key="email" data-label="Email">
                                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-700 font-bold text-xs">@</span>
                                            <div>
                                                <div class="text-xs font-bold text-slate-900">Email</div>
                                                <div class="text-[10px] text-slate-400">Email address</div>
                                            </div>
                                        </button>
                                        <button type="button" onclick="selectTypeCard('phone')" class="type-card flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 text-left transition hover:border-slate-400 shadow-2xs" data-key="phone" data-label="Phone">
                                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-700 font-bold text-xs">☎</span>
                                            <div>
                                                <div class="text-xs font-bold text-slate-900">Phone</div>
                                                <div class="text-[10px] text-slate-400">Phone number</div>
                                            </div>
                                        </button>
                                        <button type="button" onclick="selectTypeCard('address')" class="type-card flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 text-left transition hover:border-slate-400 shadow-2xs" data-key="address" data-label="Address">
                                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-700 font-bold text-xs">⌂</span>
                                            <div>
                                                <div class="text-xs font-bold text-slate-900">Address</div>
                                                <div class="text-[10px] text-slate-400">Physical address</div>
                                            </div>
                                        </button>
                                    </div>
                                </div>

                                <!-- File Group -->
                                <div class="type-group" data-group="File">
                                    <h4 class="text-[11px] font-bold uppercase text-slate-400 mb-2">Media & Files</h4>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                                        <button type="button" onclick="selectTypeCard('file')" class="type-card flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 text-left transition hover:border-slate-400 shadow-2xs" data-key="file" data-label="File">
                                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-700 font-bold text-xs">📎</span>
                                            <div>
                                                <div class="text-xs font-bold text-slate-900">File</div>
                                                <div class="text-[10px] text-slate-400">Document upload</div>
                                            </div>
                                        </button>
                                        <button type="button" onclick="selectTypeCard('image')" class="type-card flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 text-left transition hover:border-slate-400 shadow-2xs" data-key="image" data-label="Image">
                                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-700 font-bold text-xs">🖼</span>
                                            <div>
                                                <div class="text-xs font-bold text-slate-900">Image</div>
                                                <div class="text-[10px] text-slate-400">Image upload</div>
                                            </div>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Configuration (Dynamic per type) -->
                    <div id="drawer-section-3" class="drawer-section hidden space-y-4">
                        <div class="rounded-2xl border border-slate-200 p-5 bg-white space-y-4">
                            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 pb-3">
                                <span class="h-2 w-2 rounded-full bg-slate-900"></span>
                                3. Type-Specific Configuration
                            </h3>

                            <!-- Options List for Select / Multiselect / Checkbox -->
                            <div id="options-config-container" class="hidden space-y-3">
                                <div class="flex items-center justify-between">
                                    <label class="block text-xs font-bold text-slate-700">Available Options <span class="text-red-500">*</span></label>
                                    <span class="text-[11px] text-slate-400">At least 1 option required</span>
                                </div>
                                <div id="options-list" class="space-y-2 max-h-64 overflow-y-auto pr-1">
                                    <div class="option-row flex items-center gap-2">
                                        <input type="hidden" class="option-id-input" value="" />
                                        <input type="text" placeholder="Option Name" class="option-name-input flex-1 rounded-xl border border-slate-200 p-2.5 text-xs bg-slate-50 text-slate-900 focus:bg-white focus:border-slate-400 outline-none transition" />
                                        <button type="button" onclick="removeOptionRow(this)" aria-label="Remove option" class="p-2 text-slate-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </div>
                                <button type="button" onclick="addOptionRow()" class="text-xs text-slate-900 font-bold hover:underline inline-flex items-center gap-1 mt-1">
                                    <span>+</span> Add Option
                                </button>
                            </div>

                            <!-- Lookup Entity Selection -->
                            <div id="lookup-config-container" class="hidden space-y-3">
                                <label for="field-lookup-type" class="block text-xs font-bold text-slate-700">Lookup Related Entity Model</label>
                                <select id="field-lookup-type" name="lookup_type" class="w-full rounded-xl border border-slate-200 p-3 text-xs bg-slate-50 text-slate-900 focus:bg-white focus:border-slate-400 outline-none transition cursor-pointer">
                                    <option value="users">Users (Staff / Admins)</option>
                                    <option value="persons">Persons (Contacts)</option>
                                    <option value="organizations">Organizations</option>
                                    <option value="leads">Leads</option>
                                    <option value="products">Products</option>
                                    <option value="quotes">Quotes</option>
                                </select>
                            </div>

                            <!-- Text/Textarea Pattern -->
                            <div id="text-config-container" class="hidden space-y-3">
                                <label for="field-text-pattern" class="block text-xs font-bold text-slate-700">Validation Pattern (Regex)</label>
                                <input type="text" id="field-text-pattern" name="text_pattern" placeholder="e.g. ^[A-Za-z0-9 _-]+$" class="w-full rounded-xl border border-slate-200 p-3 text-xs bg-slate-50 text-slate-900 focus:bg-white focus:border-slate-400 outline-none font-mono transition" />
                                <p class="text-[11px] text-slate-400">Optional regular expression for format matching.</p>
                            </div>

                            <!-- File / Image Upload Limits -->
                            <div id="file-config-container" class="hidden space-y-4">
                                <div>
                                    <label for="field-allowed-extensions" class="block text-xs font-bold text-slate-700 mb-1">Allowed Extensions</label>
                                    <input type="text" id="field-allowed-extensions" name="allowed_extensions" placeholder="e.g. pdf, docx, jpg, png" class="w-full rounded-xl border border-slate-200 p-3 text-xs bg-slate-50 text-slate-900 focus:bg-white focus:border-slate-400 outline-none font-mono transition" />
                                </div>
                                <div>
                                    <label for="field-max-size" class="block text-xs font-bold text-slate-700 mb-1">Maximum File Size (MB)</label>
                                    <input type="number" id="field-max-size" name="max_size_mb" placeholder="10" min="1" max="100" class="w-full rounded-xl border border-slate-200 p-3 text-xs bg-slate-50 text-slate-900 focus:bg-white focus:border-slate-400 outline-none transition" />
                                </div>
                            </div>

                            <p id="config-default-msg" class="text-xs text-slate-400 py-3">No extra configuration needed for this field type. You can proceed to Validation settings.</p>
                        </div>
                    </div>

                    <!-- Section 4: Validation Rules -->
                    <div id="drawer-section-4" class="drawer-section hidden space-y-4">
                        <div class="rounded-2xl border border-slate-200 p-5 bg-white space-y-4">
                            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 pb-3">
                                <span class="h-2 w-2 rounded-full bg-slate-900"></span>
                                4. Validation Rules
                            </h3>
                            <div class="space-y-3">
                                <!-- is_required Toggle -->
                                <div class="flex items-center justify-between rounded-xl border border-slate-200 p-3.5 bg-slate-50/60 hover:bg-slate-50 transition">
                                    <div>
                                        <label for="is_required" class="text-xs font-bold text-slate-900 cursor-pointer">
                                            Required Field
                                        </label>
                                        <p class="text-[11px] text-slate-400">
                                            Enforce input when saving records for this entity.
                                        </p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="is_required" id="is_required" value="1" class="sr-only peer" />
                                        <div class="w-10 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-slate-900"></div>
                                    </label>
                                </div>

                                <!-- is_unique Toggle -->
                                <div class="flex items-center justify-between rounded-xl border border-slate-200 p-3.5 bg-slate-50/60 hover:bg-slate-50 transition">
                                    <div>
                                        <label for="is_unique" class="text-xs font-bold text-slate-900 cursor-pointer">
                                            Unique Value Constraint
                                        </label>
                                        <p class="text-[11px] text-slate-400">
                                            Prevent duplicate values across all records for this entity.
                                        </p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="is_unique" id="is_unique" value="1" class="sr-only peer" />
                                        <div class="w-10 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-slate-900"></div>
                                    </label>
                                </div>

                                <!-- Custom validation string -->
                                <div class="pt-2">
                                    <label for="field-validation-input" class="block text-xs font-bold text-slate-700 mb-1.5">Custom Validation Rules</label>
                                    <input type="text" id="field-validation-input" name="validation" placeholder="e.g. numeric|min:0" class="w-full rounded-xl border border-slate-200 p-3 text-xs bg-slate-50 text-slate-900 focus:bg-white focus:border-slate-400 outline-none font-mono transition" />
                                    <p class="text-[11px] text-slate-400 mt-1">Pipe-separated Laravel validation rules (e.g. <code class="font-mono bg-slate-100 px-1 py-0.5 rounded">numeric|min:0|max:1000000</code>).</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 5: Visibility -->
                    <div id="drawer-section-5" class="drawer-section hidden space-y-4">
                        <div class="rounded-2xl border border-slate-200 p-5 bg-white space-y-4">
                            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 pb-3">
                                <span class="h-2 w-2 rounded-full bg-slate-900"></span>
                                5. Visibility & Forms
                            </h3>
                            <!-- quick_add Toggle -->
                            <div class="flex items-center justify-between rounded-xl border border-slate-200 p-3.5 bg-slate-50/60 hover:bg-slate-50 transition">
                                <div>
                                    <label for="quick_add" class="text-xs font-bold text-slate-900 cursor-pointer">
                                        Show in Quick Add
                                    </label>
                                    <p class="text-[11px] text-slate-400">
                                        Include this field in quick creation modals & compact lead cards.
                                    </p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="quick_add" id="quick_add" value="1" checked class="sr-only peer" />
                                    <div class="w-10 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-slate-900"></div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Section 6: Advanced Settings -->
                    <div id="drawer-section-6" class="drawer-section hidden space-y-4">
                        <div class="rounded-2xl border border-slate-200 p-5 bg-white space-y-4">
                            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 pb-3">
                                <span class="h-2 w-2 rounded-full bg-slate-900"></span>
                                6. Advanced Settings
                            </h3>
                            <div>
                                <label for="field-sort-order" class="block text-xs font-bold text-slate-700 mb-1.5">Sort Order</label>
                                <input type="number" id="field-sort-order" name="sort_order" value="0" min="0" class="w-full rounded-xl border border-slate-200 p-3 text-xs bg-slate-50 text-slate-900 focus:bg-white focus:border-slate-400 outline-none transition" />
                                <p class="text-[11px] text-slate-400 mt-1">Lower numbers appear first in lists and forms.</p>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Drawer Footer Navigation Controls -->
                <div class="flex items-center justify-between px-6 py-4 border-t border-slate-100 bg-slate-50/70">
                    <button type="button" id="drawer-prev-btn" onclick="prevDrawerTab()" class="hidden rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 px-4 py-2 text-xs font-bold transition">
                        &larr; Back
                    </button>
                    <div class="flex items-center gap-3 ml-auto">
                        <button type="button" onclick="closeAddFieldDrawer()" class="rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 px-4 py-2 text-xs font-bold transition">
                            Cancel
                        </button>
                        <button type="button" id="drawer-next-btn" onclick="nextDrawerTab()" class="rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-900 px-4 py-2 text-xs font-bold transition">
                            Next &rarr;
                        </button>
                        <button type="submit" id="drawer-submit-btn" class="rounded-xl bg-slate-900 hover:bg-black text-white px-5 py-2 text-xs font-bold shadow-md shadow-slate-900/10 transition flex items-center gap-2">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span id="drawer-submit-label">Save Field</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let currentDrawerTab = 1;

    function switchDrawerTab(tabIndex) {
        currentDrawerTab = tabIndex;
        document.querySelectorAll('.drawer-section').forEach((sec, idx) => {
            sec.classList.toggle('hidden', idx + 1 !== tabIndex);
        });

        document.querySelectorAll('.drawer-step-tab').forEach((tab, idx) => {
            const stepNum = idx + 1;
            const badge = tab.querySelector('span:first-child');
            if (stepNum === tabIndex) {
                tab.className = 'drawer-step-tab flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-bold transition border border-slate-900 bg-slate-900 text-white shadow-2xs';
                if (badge) badge.className = 'h-4 w-4 rounded-full bg-white text-slate-900 flex items-center justify-center text-[10px] font-black';
            } else {
                tab.className = 'drawer-step-tab flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-semibold transition border border-transparent text-slate-600 hover:bg-slate-100';
                if (badge) badge.className = 'h-4 w-4 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center text-[10px] font-black';
            }
        });

        const prevBtn = document.getElementById('drawer-prev-btn');
        const nextBtn = document.getElementById('drawer-next-btn');
        if (prevBtn) prevBtn.classList.toggle('hidden', tabIndex === 1);
        if (nextBtn) nextBtn.classList.toggle('hidden', tabIndex === 6);
    }

    function nextDrawerTab() {
        if (currentDrawerTab < 6) switchDrawerTab(currentDrawerTab + 1);
    }

    function prevDrawerTab() {
        if (currentDrawerTab > 1) switchDrawerTab(currentDrawerTab - 1);
    }

    // Opens the drawer. Pass a field object to edit an existing attribute,
    // or a type string (e.g. 'select') to preset the field type for a new one.
    function openAddFieldDrawer(preset, field) {
        const form = document.getElementById('add-field-drawer-form');
        const title = document.getElementById('drawer-title');
        const submitLabel = document.getElementById('drawer-submit-label');
        form.reset();
        document.getElementById('edit-field-id').value = '';
        resetOptionRows();
        switchDrawerTab(1);

        if (field && typeof field === 'object') {
            title.textContent = 'Edit Field (' + (field.name || '') + ')';
            if (submitLabel) submitLabel.textContent = 'Update Field';
            document.getElementById('edit-field-id').value = field.id;
            form.name.value = field.name || '';
            form.code.value = field.code || '';
            form.code.readOnly = true;
            if (form.entity_type) {
                form.entity_type.value = field.entity_type || 'leads';
                form.entity_type.disabled = true;
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
                field.options.forEach(opt => {
                    addOptionRow(opt.name, opt.id);
                });
            }
        } else {
            title.textContent = 'Add Field';
            if (submitLabel) submitLabel.textContent = 'Save Field';
            form.code.readOnly = false;
            if (form.entity_type) {
                form.entity_type.disabled = false;
                const active = document.getElementById('entity-selector');
                if (active) form.entity_type.value = active.value;
            }
            const typeToSelect = typeof preset === 'string' ? preset : 'text';
            selectTypeCard(typeToSelect);
            if (preset && typeof preset === 'string') {
                // If opened via quick type button, jump directly to basic info then configuration
                switchDrawerTab(1);
            }
        }

        document.getElementById('add-field-drawer').classList.remove('hidden');
        setTimeout(() => document.getElementById('field-name-input')?.focus(), 50);
    }

    function closeAddFieldDrawer() {
        document.getElementById('add-field-drawer').classList.add('hidden');
    }

    function resetOptionRows() {
        const list = document.getElementById('options-list');
        if (!list) return;
        list.innerHTML = `
            <div class="option-row flex items-center gap-2">
                <input type="hidden" class="option-id-input" value="" />
                <input type="text" placeholder="Option Name" class="option-name-input flex-1 rounded-xl border border-slate-200 p-2.5 text-xs bg-slate-50 text-slate-900 focus:bg-white focus:border-slate-400 outline-none transition" />
                <button type="button" onclick="removeOptionRow(this)" aria-label="Remove option" class="p-2 text-slate-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
            </div>`;
    }

    function selectTypeCard(typeKey) {
        document.getElementById('selected-type-hidden-input').value = typeKey;

        document.querySelectorAll('.type-card').forEach(card => {
            const isMatch = card.getAttribute('data-key') === typeKey;
            const badge = card.querySelector('span:first-child');
            if (isMatch) {
                card.className = 'type-card flex items-center gap-3 rounded-xl border border-slate-900 bg-slate-50 p-3 text-left transition hover:border-slate-900 shadow-2xs';
                if (badge) {
                    badge.classList.remove('bg-slate-100', 'text-slate-700');
                    badge.classList.add('bg-slate-900', 'text-white');
                }
            } else {
                card.className = 'type-card flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 text-left transition hover:border-slate-400 shadow-2xs';
                if (badge) {
                    badge.classList.remove('bg-slate-900', 'text-white');
                    badge.classList.add('bg-slate-100', 'text-slate-700');
                }
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

    function addOptionRow(value, id) {
        const list = document.getElementById('options-list');
        const div = document.createElement('div');
        div.className = 'option-row flex items-center gap-2';
        const safeVal = typeof value === 'string' ? value.replace(/"/g, '&quot;') : '';
        const safeId = id ? String(id) : '';
        div.innerHTML = `
            <input type="hidden" class="option-id-input" value="${safeId}" />
            <input type="text" value="${safeVal}" placeholder="Option Name" class="option-name-input flex-1 rounded-xl border border-slate-200 p-2.5 text-xs bg-slate-50 text-slate-900 focus:bg-white focus:border-slate-400 outline-none transition" />
            <button type="button" onclick="removeOptionRow(this)" aria-label="Remove option" class="p-2 text-slate-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            </button>
        `;
        list.appendChild(div);
    }

    function removeOptionRow(btn) {
        const list = document.getElementById('options-list');
        const row = btn.closest('.option-row');
        if (list && list.querySelectorAll('.option-row').length > 1) {
            row.remove();
        } else if (row) {
            row.querySelector('.option-name-input').value = '';
            row.querySelector('.option-id-input').value = '';
        }
    }

    async function submitAddFieldDrawer(e) {
        e.preventDefault();
        const form = e.target;
        const name = form.name.value.trim();
        const type = form.type.value;
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
            alert('Please add at least one option for ' + type + ' fields.');
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
        if (submitLabel) submitLabel.textContent = 'Saving...';

        try {
            if (editId) {
                await MoldableBuilder.api('/fields/' + editId, { method: 'PUT', body: JSON.stringify(payload) });
            } else {
                payload.code = form.code.value.trim() || null;
                payload.entity_type = form.entity_type ? form.entity_type.value : 'leads';
                await MoldableBuilder.api('/fields', { method: 'POST', body: JSON.stringify(payload) });
            }
            closeAddFieldDrawer();
            await MoldableBuilder.refresh();
        } catch (err) {
            alert(err.message || 'Could not save the field.');
        } finally {
            if (submitBtn) submitBtn.disabled = false;
            if (submitLabel) submitLabel.textContent = prevLabel;
        }
    }
</script>
