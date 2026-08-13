<div id="add-field-drawer" class="hidden fixed inset-0 z-[1000] overflow-hidden bg-gray-900/50 backdrop-blur-sm transition-opacity">
    <div class="fixed inset-y-0 right-0 flex max-w-full pl-10">
        <div class="w-screen max-w-xl bg-white dark:bg-gray-900 shadow-2xl border-l border-gray-200 dark:border-gray-800 flex flex-col justify-between">
            <!-- Drawer Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/50">
                <div class="flex items-center gap-3">
                    <span class="icon-add text-xl text-blue-600 dark:text-blue-400"></span>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Add Field</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Configure custom attribute settings</p>
                    </div>
                </div>
                <button type="button" onclick="closeAddFieldDrawer()" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-200">
                    ✕
                </button>
            </div>

            <!-- Drawer Content (Sections) -->
            <div class="flex-1 overflow-y-auto p-6 space-y-6">
                <form id="add-field-drawer-form" onsubmit="submitAddFieldDrawer(event)" class="space-y-6">

                    <!-- Section 1: Basic Information -->
                    <div class="rounded-xl border border-gray-200 dark:border-gray-800 p-5 bg-white dark:bg-gray-900 space-y-4">
                        <h3 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2 border-b border-gray-100 dark:border-gray-800 pb-2">
                            <span class="h-2 w-2 rounded-full bg-blue-600"></span>
                            1. Basic Information
                        </h3>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Field Label *</label>
                                <input type="text" name="name" required placeholder="e.g. Project Budget" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 p-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Field Code (Auto-generated if empty)</label>
                                <input type="text" name="code" placeholder="e.g. project_budget" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 p-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 font-mono text-xs" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Target Entity *</label>
                                <select name="entity_type" required class="w-full rounded-lg border border-gray-300 dark:border-gray-700 p-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                    <option value="leads" selected>Leads</option>
                                    <option value="persons">Persons</option>
                                    <option value="organizations">Organizations</option>
                                    <option value="products">Products</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Field Type -->
                    <div class="rounded-xl border border-gray-200 dark:border-gray-800 p-5 bg-white dark:bg-gray-900 space-y-4">
                        <h3 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2 border-b border-gray-100 dark:border-gray-800 pb-2">
                            <span class="h-2 w-2 rounded-full bg-indigo-600"></span>
                            2. Field Type
                        </h3>

                        <!-- Searchable Type Selector -->
                        <div class="space-y-3">
                            <div class="relative">
                                <span class="icon-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></span>
                                <input
                                    type="text"
                                    id="type-search-input"
                                    placeholder="Search field types (e.g. Text, Select, Date)..."
                                    oninput="filterFieldTypes(this.value)"
                                    class="w-full rounded-lg border border-gray-300 dark:border-gray-700 py-2 pl-9 pr-3 text-xs bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white outline-none focus:border-blue-500"
                                />
                            </div>

                            <input type="hidden" name="type" id="selected-type-hidden-input" value="text" />

                            <!-- Grouped Type Grid -->
                            <div id="grouped-type-container" class="space-y-4 max-h-72 overflow-y-auto pr-1">
                                <!-- Text Group -->
                                <div class="type-group" data-group="Text">
                                    <h4 class="text-[11px] font-bold uppercase text-gray-400 dark:text-gray-500 mb-2">Text</h4>
                                    <div class="grid grid-cols-2 gap-2">
                                        <button type="button" onclick="selectTypeCard('text')" class="type-card flex items-center gap-2.5 rounded-lg border border-blue-500 bg-blue-50/40 dark:bg-blue-950/40 p-2.5 text-left transition hover:border-blue-500" data-key="text" data-label="Text">
                                            <span class="icon-text text-base text-blue-600 dark:text-blue-400"></span>
                                            <div>
                                                <div class="text-xs font-semibold text-gray-900 dark:text-white">Text</div>
                                                <div class="text-[10px] text-gray-400">Single line string</div>
                                            </div>
                                        </button>
                                        <button type="button" onclick="selectTypeCard('textarea')" class="type-card flex items-center gap-2.5 rounded-lg border border-gray-200 dark:border-gray-800 p-2.5 text-left transition hover:border-blue-500" data-key="textarea" data-label="Textarea">
                                            <span class="icon-align-left text-base text-gray-500"></span>
                                            <div>
                                                <div class="text-xs font-semibold text-gray-900 dark:text-white">Textarea</div>
                                                <div class="text-[10px] text-gray-400">Multi-line text</div>
                                            </div>
                                        </button>
                                    </div>
                                </div>

                                <!-- Number Group -->
                                <div class="type-group" data-group="Number">
                                    <h4 class="text-[11px] font-bold uppercase text-gray-400 dark:text-gray-500 mb-2">Number</h4>
                                    <div class="grid grid-cols-2 gap-2">
                                        <button type="button" onclick="selectTypeCard('price')" class="type-card flex items-center gap-2.5 rounded-lg border border-gray-200 dark:border-gray-800 p-2.5 text-left transition hover:border-blue-500" data-key="price" data-label="Price">
                                            <span class="icon-currency-dollar text-base text-emerald-500"></span>
                                            <div>
                                                <div class="text-xs font-semibold text-gray-900 dark:text-white">Price</div>
                                                <div class="text-[10px] text-gray-400">Currency & amount</div>
                                            </div>
                                        </button>
                                    </div>
                                </div>

                                <!-- Date & Time Group -->
                                <div class="type-group" data-group="Date & Time">
                                    <h4 class="text-[11px] font-bold uppercase text-gray-400 dark:text-gray-500 mb-2">Date & Time</h4>
                                    <div class="grid grid-cols-2 gap-2">
                                        <button type="button" onclick="selectTypeCard('date')" class="type-card flex items-center gap-2.5 rounded-lg border border-gray-200 dark:border-gray-800 p-2.5 text-left transition hover:border-blue-500" data-key="date" data-label="Date">
                                            <span class="icon-calendar text-base text-amber-500"></span>
                                            <div>
                                                <div class="text-xs font-semibold text-gray-900 dark:text-white">Date</div>
                                                <div class="text-[10px] text-gray-400">Calendar date</div>
                                            </div>
                                        </button>
                                        <button type="button" onclick="selectTypeCard('datetime')" class="type-card flex items-center gap-2.5 rounded-lg border border-gray-200 dark:border-gray-800 p-2.5 text-left transition hover:border-blue-500" data-key="datetime" data-label="Datetime">
                                            <span class="icon-clock text-base text-amber-500"></span>
                                            <div>
                                                <div class="text-xs font-semibold text-gray-900 dark:text-white">Datetime</div>
                                                <div class="text-[10px] text-gray-400">Date with timestamp</div>
                                            </div>
                                        </button>
                                    </div>
                                </div>

                                <!-- Selection Group -->
                                <div class="type-group" data-group="Selection">
                                    <h4 class="text-[11px] font-bold uppercase text-gray-400 dark:text-gray-500 mb-2">Selection</h4>
                                    <div class="grid grid-cols-2 gap-2">
                                        <button type="button" onclick="selectTypeCard('select')" class="type-card flex items-center gap-2.5 rounded-lg border border-gray-200 dark:border-gray-800 p-2.5 text-left transition hover:border-blue-500" data-key="select" data-label="Select">
                                            <span class="icon-chevron-down text-base text-indigo-500"></span>
                                            <div>
                                                <div class="text-xs font-semibold text-gray-900 dark:text-white">Select</div>
                                                <div class="text-[10px] text-gray-400">Single select list</div>
                                            </div>
                                        </button>
                                        <button type="button" onclick="selectTypeCard('multiselect')" class="type-card flex items-center gap-2.5 rounded-lg border border-gray-200 dark:border-gray-800 p-2.5 text-left transition hover:border-blue-500" data-key="multiselect" data-label="Multiselect">
                                            <span class="icon-list text-base text-indigo-500"></span>
                                            <div>
                                                <div class="text-xs font-semibold text-gray-900 dark:text-white">Multiselect</div>
                                                <div class="text-[10px] text-gray-400">Multiple option choices</div>
                                            </div>
                                        </button>
                                        <button type="button" onclick="selectTypeCard('checkbox')" class="type-card flex items-center gap-2.5 rounded-lg border border-gray-200 dark:border-gray-800 p-2.5 text-left transition hover:border-blue-500" data-key="checkbox" data-label="Checkbox">
                                            <span class="icon-check-square text-base text-indigo-500"></span>
                                            <div>
                                                <div class="text-xs font-semibold text-gray-900 dark:text-white">Checkbox</div>
                                                <div class="text-[10px] text-gray-400">Checkbox options</div>
                                            </div>
                                        </button>
                                        <button type="button" onclick="selectTypeCard('boolean')" class="type-card flex items-center gap-2.5 rounded-lg border border-gray-200 dark:border-gray-800 p-2.5 text-left transition hover:border-blue-500" data-key="boolean" data-label="Boolean">
                                            <span class="icon-toggle text-base text-indigo-500"></span>
                                            <div>
                                                <div class="text-xs font-semibold text-gray-900 dark:text-white">Boolean</div>
                                                <div class="text-[10px] text-gray-400">Yes/No toggle</div>
                                            </div>
                                        </button>
                                    </div>
                                </div>

                                <!-- Relationship Group -->
                                <div class="type-group" data-group="Relationship">
                                    <h4 class="text-[11px] font-bold uppercase text-gray-400 dark:text-gray-500 mb-2">Relationship</h4>
                                    <div class="grid grid-cols-2 gap-2">
                                        <button type="button" onclick="selectTypeCard('lookup')" class="type-card flex items-center gap-2.5 rounded-lg border border-gray-200 dark:border-gray-800 p-2.5 text-left transition hover:border-blue-500" data-key="lookup" data-label="Lookup">
                                            <span class="icon-search text-base text-purple-500"></span>
                                            <div>
                                                <div class="text-xs font-semibold text-gray-900 dark:text-white">Lookup</div>
                                                <div class="text-[10px] text-gray-400">Related model reference</div>
                                            </div>
                                        </button>
                                    </div>
                                </div>

                                <!-- Contact Group -->
                                <div class="type-group" data-group="Contact">
                                    <h4 class="text-[11px] font-bold uppercase text-gray-400 dark:text-gray-500 mb-2">Contact</h4>
                                    <div class="grid grid-cols-2 gap-2">
                                        <button type="button" onclick="selectTypeCard('email')" class="type-card flex items-center gap-2.5 rounded-lg border border-gray-200 dark:border-gray-800 p-2.5 text-left transition hover:border-blue-500" data-key="email" data-label="Email">
                                            <span class="icon-mail text-base text-rose-500"></span>
                                            <div>
                                                <div class="text-xs font-semibold text-gray-900 dark:text-white">Email</div>
                                                <div class="text-[10px] text-gray-400">Email address</div>
                                            </div>
                                        </button>
                                        <button type="button" onclick="selectTypeCard('phone')" class="type-card flex items-center gap-2.5 rounded-lg border border-gray-200 dark:border-gray-800 p-2.5 text-left transition hover:border-blue-500" data-key="phone" data-label="Phone">
                                            <span class="icon-phone text-base text-rose-500"></span>
                                            <div>
                                                <div class="text-xs font-semibold text-gray-900 dark:text-white">Phone</div>
                                                <div class="text-[10px] text-gray-400">Phone number</div>
                                            </div>
                                        </button>
                                        <button type="button" onclick="selectTypeCard('address')" class="type-card flex items-center gap-2.5 rounded-lg border border-gray-200 dark:border-gray-800 p-2.5 text-left transition hover:border-blue-500" data-key="address" data-label="Address">
                                            <span class="icon-map-pin text-base text-rose-500"></span>
                                            <div>
                                                <div class="text-xs font-semibold text-gray-900 dark:text-white">Address</div>
                                                <div class="text-[10px] text-gray-400">Location address</div>
                                            </div>
                                        </button>
                                    </div>
                                </div>

                                <!-- File Group -->
                                <div class="type-group" data-group="File">
                                    <h4 class="text-[11px] font-bold uppercase text-gray-400 dark:text-gray-500 mb-2">File</h4>
                                    <div class="grid grid-cols-2 gap-2">
                                        <button type="button" onclick="selectTypeCard('file')" class="type-card flex items-center gap-2.5 rounded-lg border border-gray-200 dark:border-gray-800 p-2.5 text-left transition hover:border-blue-500" data-key="file" data-label="File">
                                            <span class="icon-paperclip text-base text-teal-500"></span>
                                            <div>
                                                <div class="text-xs font-semibold text-gray-900 dark:text-white">File</div>
                                                <div class="text-[10px] text-gray-400">Document upload</div>
                                            </div>
                                        </button>
                                        <button type="button" onclick="selectTypeCard('image')" class="type-card flex items-center gap-2.5 rounded-lg border border-gray-200 dark:border-gray-800 p-2.5 text-left transition hover:border-blue-500" data-key="image" data-label="Image">
                                            <span class="icon-image text-base text-teal-500"></span>
                                            <div>
                                                <div class="text-xs font-semibold text-gray-900 dark:text-white">Image</div>
                                                <div class="text-[10px] text-gray-400">Image upload</div>
                                            </div>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Configuration -->
                    <div id="section-configuration" class="rounded-xl border border-gray-200 dark:border-gray-800 p-5 bg-white dark:bg-gray-900 space-y-4">
                        <h3 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2 border-b border-gray-100 dark:border-gray-800 pb-2">
                            <span class="h-2 w-2 rounded-full bg-amber-600"></span>
                            3. Configuration
                        </h3>

                        <!-- Select / Multiselect / Checkbox Options Config -->
                        <div id="options-config-container" class="hidden space-y-3">
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Select Options</label>
                            <div id="options-list" class="space-y-2">
                                <div class="flex items-center gap-2">
                                    <input type="text" placeholder="Option Name" class="option-name-input flex-1 rounded-lg border border-gray-300 dark:border-gray-700 p-2 text-xs bg-white dark:bg-gray-800 text-gray-900 dark:text-white" />
                                    <button type="button" onclick="removeOptionRow(this)" class="text-red-500 text-xs hover:underline">Remove</button>
                                </div>
                            </div>
                            <button type="button" onclick="addOptionRow()" class="text-xs text-blue-600 font-semibold hover:underline">+ Add Option</button>
                        </div>

                        <!-- Lookup Target Entity Config -->
                        <div id="lookup-config-container" class="hidden space-y-3">
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Lookup Entity Model</label>
                            <select name="lookup_type" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 p-2 text-xs bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                <option value="users">Users</option>
                                <option value="persons">Persons</option>
                                <option value="products">Products</option>
                                <option value="leads">Leads</option>
                            </select>
                        </div>

                        <!-- Text / Textarea Format Config -->
                        <div id="text-config-container" class="hidden space-y-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Validation Regex Pattern</label>
                                <input type="text" name="text_pattern" placeholder="e.g. ^[A-Za-z0-9]+$" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 p-2 text-xs bg-white dark:bg-gray-800 text-gray-900 dark:text-white font-mono" />
                            </div>
                        </div>

                        <!-- File / Image Upload Limits Config -->
                        <div id="file-config-container" class="hidden space-y-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Allowed Extensions</label>
                                <input type="text" name="allowed_extensions" placeholder="e.g. jpg, png, pdf, docx" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 p-2 text-xs bg-white dark:bg-gray-800 text-gray-900 dark:text-white font-mono" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Maximum Size (MB)</label>
                                <input type="number" name="max_size_mb" placeholder="10" min="1" max="100" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 p-2 text-xs bg-white dark:bg-gray-800 text-gray-900 dark:text-white" />
                            </div>
                        </div>

                        <p id="config-default-msg" class="text-xs text-gray-400">No extra configuration needed for this field type.</p>
                    </div>

                    <!-- Section 4: Validation -->
                    <div class="rounded-xl border border-gray-200 dark:border-gray-800 p-5 bg-white dark:bg-gray-900 space-y-4">
                        <h3 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2 border-b border-gray-100 dark:border-gray-800 pb-2">
                            <span class="h-2 w-2 rounded-full bg-emerald-600"></span>
                            4. Validation
                        </h3>
                        <div class="space-y-4">
                            <!-- is_required Toggle -->
                            <div class="flex items-center justify-between rounded-lg border border-gray-100 dark:border-gray-800 p-3 bg-gray-50/40 dark:bg-gray-800/30">
                                <div>
                                    <label for="is_required" class="text-xs font-semibold text-gray-900 dark:text-white cursor-pointer">
                                        Required Field
                                    </label>
                                    <p class="text-[10px] text-gray-500 dark:text-gray-400">
                                        Enforce mandatory input when saving entity records.
                                    </p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_required" id="is_required" value="1" class="sr-only peer" />
                                    <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:after:border-gray-600 peer-checked:bg-blue-600"></div>
                                </label>
                            </div>

                            <!-- is_unique Toggle -->
                            <div class="flex items-center justify-between rounded-lg border border-gray-100 dark:border-gray-800 p-3 bg-gray-50/40 dark:bg-gray-800/30">
                                <div>
                                    <label for="is_unique" class="text-xs font-semibold text-gray-900 dark:text-white cursor-pointer">
                                        Unique Value Constraint
                                    </label>
                                    <p class="text-[10px] text-gray-500 dark:text-gray-400">
                                        Prevent duplicate values across all records for this entity.
                                    </p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_unique" id="is_unique" value="1" class="sr-only peer" />
                                    <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:after:border-gray-600 peer-checked:bg-blue-600"></div>
                                </label>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Custom Validation Rules</label>
                                <input type="text" name="validation" placeholder="e.g. numeric|min:0" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 p-2 text-xs bg-white dark:bg-gray-800 text-gray-900 dark:text-white font-mono" />
                            </div>
                        </div>
                    </div>

                    <!-- Section 5: Visibility -->
                    <div class="rounded-xl border border-gray-200 dark:border-gray-800 p-5 bg-white dark:bg-gray-900 space-y-4">
                        <h3 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2 border-b border-gray-100 dark:border-gray-800 pb-2">
                            <span class="h-2 w-2 rounded-full bg-purple-600"></span>
                            5. Visibility
                        </h3>
                        <!-- quick_add Toggle -->
                        <div class="flex items-center justify-between rounded-lg border border-gray-100 dark:border-gray-800 p-3 bg-gray-50/40 dark:bg-gray-800/30">
                            <div>
                                <label for="quick_add" class="text-xs font-semibold text-gray-900 dark:text-white cursor-pointer">
                                    Show in Quick Add
                                </label>
                                <p class="text-[10px] text-gray-500 dark:text-gray-400">
                                    Include this field in quick entity creation forms & modals.
                                </p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="quick_add" id="quick_add" value="1" checked class="sr-only peer" />
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:after:border-gray-600 peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                    </div>

                    <!-- Section 6: Advanced -->
                    <div class="rounded-xl border border-gray-200 dark:border-gray-800 p-5 bg-white dark:bg-gray-900 space-y-4">
                        <h3 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2 border-b border-gray-100 dark:border-gray-800 pb-2">
                            <span class="h-2 w-2 rounded-full bg-slate-600"></span>
                            6. Advanced
                        </h3>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Sort Order</label>
                            <input type="number" name="sort_order" value="0" min="0" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 p-2 text-xs bg-white dark:bg-gray-800 text-gray-900 dark:text-white" />
                        </div>
                    </div>

                    <!-- Drawer Footer Controls -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-800">
                        <button type="button" onclick="closeAddFieldDrawer()" class="px-4 py-2 text-xs font-semibold text-gray-600 dark:text-gray-400 hover:bg-gray-100 rounded-lg">Cancel</button>
                        <button type="submit" class="primary-button px-5 py-2 text-xs font-semibold shadow-sm">Save Field</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function openAddFieldDrawer() {
        document.getElementById('add-field-drawer').classList.remove('hidden');
    }

    function closeAddFieldDrawer() {
        document.getElementById('add-field-drawer').classList.add('hidden');
    }

    function selectTypeCard(typeKey) {
        document.getElementById('selected-type-hidden-input').value = typeKey;

        document.querySelectorAll('.type-card').forEach(card => {
            if (card.getAttribute('data-key') === typeKey) {
                card.classList.add('border-blue-500', 'bg-blue-50/40', 'dark:bg-blue-950/40');
                card.classList.remove('border-gray-200', 'dark:border-gray-800');
            } else {
                card.classList.remove('border-blue-500', 'bg-blue-50/40', 'dark:bg-blue-950/40');
                card.classList.add('border-gray-200', 'dark:border-gray-800');
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
                const key = card.getAttribute('data-key').toLowerCase();
                const label = card.getAttribute('data-label').toLowerCase();
                if (key.includes(q) || label.includes(q)) {
                    card.classList.remove('hidden');
                    hasMatch = true;
                } else {
                    card.classList.add('hidden');
                }
            });

            if (hasMatch || !q) {
                group.classList.remove('hidden');
            } else {
                group.classList.add('hidden');
            }
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

    function addOptionRow() {
        const list = document.getElementById('options-list');
        const div = document.createElement('div');
        div.className = 'flex items-center gap-2';
        div.innerHTML = `
            <input type="text" placeholder="Option Name" class="option-name-input flex-1 rounded-lg border border-gray-300 dark:border-gray-700 p-2 text-xs bg-white dark:bg-gray-800 text-gray-900 dark:text-white" />
            <button type="button" onclick="removeOptionRow(this)" class="text-red-500 text-xs hover:underline">Remove</button>
        `;
        list.appendChild(div);
    }

    function removeOptionRow(btn) {
        btn.parentElement.remove();
    }

    function submitAddFieldDrawer(e) {
        e.preventDefault();
        const form = e.target;
        const name = form.name.value;
        const type = form.type.value;

        if (!name) return;

        // Collect options if present
        const optionInputs = form.querySelectorAll('.option-name-input');
        const options = [];
        optionInputs.forEach((inp, idx) => {
            if (inp.value.trim()) {
                options.push({ name: inp.value.trim(), sort_order: idx });
            }
        });

        // Add to list container on page
        const container = document.getElementById('fields-list-container');
        if (container) {
            const newDiv = document.createElement('div');
            newDiv.className = 'field-item flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50/50 p-4 transition-all hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-800/40 dark:hover:border-gray-700';
            newDiv.setAttribute('data-name', name);
            newDiv.setAttribute('data-type', type.charAt(0).toUpperCase() + type.slice(1));
            
            const code = form.code.value.trim() || name.toLowerCase().replace(/\s+/g, '_');

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
        }

        closeAddFieldDrawer();
        form.reset();
        if (typeof filterFields === 'function') {
            filterFields(document.getElementById('field-search-input')?.value || '');
        }
    }
</script>
