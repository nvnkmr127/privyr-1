@push('scripts')
    <script type="text/x-template" id="v-lead-capture-integrations-template">

        <div class="flex flex-col lg:flex-row min-h-[75vh] lg:h-[calc(100vh-140px)] w-full overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-2xs dark:border-slate-800 dark:bg-slate-900">
            <!-- Sidebar: Source List (stacks above the wizard on small screens) -->
            <div class="w-full lg:w-80 flex-shrink-0 border-b lg:border-b-0 lg:border-r border-slate-200 bg-slate-50/50 flex flex-col max-h-72 lg:max-h-none dark:border-slate-800 dark:bg-slate-900/50">
                <div class="p-4 border-b border-slate-200 dark:border-slate-800">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Lead Sources</h3>
                </div>

                <div class="flex-1 overflow-y-auto p-2 space-y-1 custom-scrollbar">
                    <button
                        v-for="item in integrations"
                        :key="item.source_type"
                        type="button"
                        @click="selectSource(item)"
                        class="w-full flex items-center gap-3 p-3 rounded-xl text-left transition-all duration-200"
                        :class="activeSource?.source_type === item.source_type ? 'bg-white shadow-sm border border-slate-200 ring-1 ring-black/5 dark:bg-slate-800 dark:border-slate-700 dark:ring-white/10' : 'hover:bg-slate-100 dark:hover:bg-slate-800 border border-transparent'"
                    >
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-700 font-bold border border-slate-200/60 text-lg " v-html="item.icon"></div>
                        <div class="min-w-0 flex-1">
                            <div class="text-sm font-bold text-slate-900 dark:text-white truncate">@{{ item.name }}</div>
                            <div class="text-xs text-slate-500 truncate">@{{ kindLabel(item.kind) }}</div>
                        </div>
                        <span v-if="item.connectors.length" class="shrink-0 rounded-full bg-brandColor/10 text-brandColor text-[10px] font-bold px-2 py-0.5">@{{ item.connectors.length }}</span>
                    </button>
                </div>
            </div>

            <!-- Main Pane -->
            <div class="flex-1 flex flex-col bg-white dark:bg-slate-900 relative overflow-hidden min-h-0">
                <!-- Empty State -->
                <div v-if="!activeSource" class="flex-1 flex flex-col items-center justify-center text-center p-8 min-h-0">
                    <div class="h-20 w-20 rounded-full bg-slate-50 flex items-center justify-center mb-4 dark:bg-slate-800">
                        <i class="fa-solid fa-sliders text-4xl text-slate-300"></i>
                    </div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">Configure Lead Sources</h2>
                    <p class="text-sm text-slate-500 mt-2 max-w-md">Pick a source. Each one has its own setup — webhook, embeddable form, or an OAuth connection.</p>
                </div>

                <div v-else class="flex-1 flex flex-col h-full overflow-hidden min-h-0">
                    <!-- Header -->
                    <div class="flex items-center justify-between px-8 py-5 border-b border-slate-200 dark:border-slate-800 shrink-0">
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 font-semibold border border-gray-200/60 dark:border-gray-700 text-xl" v-html="activeSource.icon"></div>
                            <div>
                                <h2 class="text-lg font-bold text-slate-900 dark:text-white">@{{ activeSource.name }}</h2>
                                <p class="text-xs text-slate-500">@{{ kindLabel(activeSource.kind) }}</p>
                            </div>
                        </div>
                        <span class="rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-wide"
                              :class="kindBadgeClass(activeSource.kind)">@{{ activeSource.kind }}</span>
                    </div>

                    <div class="flex-1 overflow-y-auto custom-scrollbar">
                        <!-- Existing connectors for this source -->
                        <div v-if="localConnectors.length" class="px-8 pt-6">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Existing connectors</h3>
                            <div class="space-y-2">
                                <div v-for="c in localConnectors" :key="c.id" class="rounded-xl border border-slate-200 dark:border-slate-800 p-3">
                                    <div class="flex items-center justify-between gap-3 flex-wrap">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="h-2.5 w-2.5 rounded-full shrink-0" :class="statusDot(c)"></span>
                                            <span class="text-sm font-bold text-slate-900 dark:text-white truncate">@{{ c.name }}</span>
                                            <span class="text-xs text-slate-500 shrink-0">· @{{ c.captured_count }} leads</span>
                                            <span v-if="c.connection.state" class="text-[10px] font-semibold shrink-0 rounded px-1.5 py-0.5" :class="connBadge(c)">@{{ connLabel(c) }}</span>
                                        </div>
                                        <div class="flex items-center gap-1 text-xs shrink-0">
                                            <button type="button" @click="copyPrimary(c)" class="px-2 py-1 rounded font-semibold text-brandColor hover:bg-brandColor/10">@{{ activeSource.kind === 'embed' ? 'Copy embed' : 'Copy URL' }}</button>
                                            <button type="button" @click="testConnector(c)" class="px-2 py-1 rounded font-semibold text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800" :disabled="isTesting">Test</button>
                                            <a v-if="activeSource.kind === 'embed'" :href="c.form_url" target="_blank" class="px-2 py-1 rounded font-semibold text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">Open</a>
                                            <button type="button" v-if="metaConnect(c)" @click="openOAuth(c)" class="px-2 py-1 rounded font-semibold text-[#1877F2] hover:bg-blue-50">@{{ c.connection.state === 'connected' ? 'Reconnect' : 'Connect' }}</button>
                                            <button v-if="c.is_active" type="button" @click="disconnectConnector(c)" :disabled="busyId === c.id" class="px-2 py-1 rounded font-semibold text-red-600 hover:bg-red-50 disabled:opacity-50">Disconnect</button>
                                            <button v-else type="button" @click="reconnectConnector(c)" :disabled="busyId === c.id" class="px-2 py-1 rounded font-semibold text-emerald-600 hover:bg-emerald-50 disabled:opacity-50">Enable</button>
                                        </div>
                                    </div>
                                    <div v-if="testFor === c.id && testResult" class="mt-2 text-xs rounded bg-slate-50 dark:bg-slate-800/60 p-2 text-slate-600 dark:text-slate-300">
                                        <span v-if="testResult.would_create_lead" class="text-emerald-600 font-semibold"><i class="fa-solid fa-check text-xs align-middle mr-1"></i>Test passed.</span>
                                        <span v-else class="text-amber-600 font-semibold"><i class="fa-solid fa-triangle-exclamation text-xs align-middle mr-1"></i>Would skip (duplicate).</span>
                                        Maps to “@{{ testResult.name || '—' }}” · @{{ testResult.email || 'no email' }} · @{{ testResult.phone || 'no phone' }} <i class="fa-solid fa-arrow-right text-[10px] align-middle mx-1"></i> @{{ testResult.pipeline || 'default pipeline' }}. @{{ testResult.duplicate ? 'Duplicate contact detected.' : 'No duplicate.' }}
                                    </div>
                                </div>
                            </div>
                            <div class="mt-6 mb-2 border-t border-dashed border-slate-200 dark:border-slate-800"></div>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Add another connector</h3>
                        </div>

                        <!-- Wizard -->
                        <div class="flex gap-10 px-8 py-6">
                            <!-- Progress -->
                            <div class="w-44 hidden lg:block shrink-0">
                                <ul class="space-y-5">
                                    <li v-for="(stepName, index) in steps" :key="index" class="flex items-center gap-3">
                                        <div class="flex h-6 w-6 items-center justify-center rounded-full border-2 text-[10px] font-bold"
                                             :class="isPastStep(index) ? 'border-brandColor bg-brandColor text-white' : (isCurrentStep(index) ? 'border-brandColor text-brandColor' : 'border-slate-200 text-slate-400 dark:border-slate-700')">
                                            <i v-if="isPastStep(index)" class="fa-solid fa-check text-[10px]"></i>
                                            <span v-else>@{{ index + 1 }}</span>
                                        </div>
                                        <span class="text-sm font-semibold" :class="isCurrentStep(index) ? 'text-slate-900 dark:text-white' : 'text-slate-400'">@{{ stepName }}</span>
                                    </li>
                                </ul>
                            </div>

                            <!-- Step content -->
                            <div class="flex-1 max-w-2xl min-w-0">

                                <!-- STEP 1: Details (all kinds) -->
                                <div v-if="isCurrentStep(0)">
                                    <h3 class="text-lg font-bold mb-1 text-slate-900 dark:text-white">Connection details</h3>
                                    <p class="text-sm text-slate-500 mb-5">Name this connection and choose where its leads land.</p>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Connection Name</label>
                                            <input type="text" v-model="config.name" class="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-slate-400 focus:bg-white placeholder-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Duplicate Handling</label>
                                            <select v-model="config.duplicateAction" class="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-slate-400 focus:bg-white placeholder-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                                                <option value="update">Update existing contact details</option>
                                                <option value="attach_contact">Attach new lead to existing contact</option>
                                                <option value="skip">Skip duplicate ingestion</option>
                                            </select>
                                        </div>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Pipeline</label>
                                                <select v-model="config.pipeline" class="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-slate-400 focus:bg-white placeholder-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                                                    <option value="">Default</option>
                                                    <option v-for="p in pipelines" :key="p.id" :value="p.id">@{{ p.name }}</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Assign To</label>
                                                <select v-model="config.assignee" class="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-slate-400 focus:bg-white placeholder-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                                                    <option value="">Round Robin</option>
                                                    <option v-for="u in users" :key="u.id" :value="u.id">@{{ u.name }}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- STEP 2 (webhook): Field Mapping -->
                                <div v-if="isCurrentStep(1) && activeSource.kind === 'webhook'">
                                    <h3 class="text-lg font-bold mb-1 text-slate-900 dark:text-white">Map fields</h3>
                                    <p class="text-sm text-slate-500 mb-5">Map incoming payload keys to CRM attributes. Unmapped keys are auto-detected by name.</p>
                                    <div class="space-y-3">
                                        <div v-for="(pf, i) in providerFields" :key="i" class="flex items-center gap-3">
                                            <div class="w-1/2 p-2.5 bg-slate-50 dark:bg-slate-800/50 rounded-xl text-sm font-mono text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">@{{ pf }}</div>
                                            <i class="fa-solid fa-arrow-right text-slate-400"></i>
                                            <select v-model="config.mapping[pf]" class="w-1/2 rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-slate-400 focus:bg-white placeholder-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                                                <option value="">-- Ignore --</option>
                                                <option value="person.name">Name</option>
                                                <option value="person.emails">Email</option>
                                                <option value="person.phones">Phone</option>
                                                <option value="title">Title / Requirement</option>
                                                <option value="lead_value">Budget / Value</option>
                                                <option value="description">Notes</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- STEP 2 (embed): Form Setup -->
                                <div v-if="isCurrentStep(1) && activeSource.kind === 'embed'">
                                    <h3 class="text-lg font-bold mb-1 text-slate-900 dark:text-white">Form setup</h3>
                                    <p class="text-sm text-slate-500 mb-5">Configure the hosted form your visitors will see.</p>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Form Title</label>
                                            <input type="text" v-model="config.embed.title" class="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-slate-400 focus:bg-white placeholder-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Subtitle</label>
                                            <input type="text" v-model="config.embed.subtitle" class="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-slate-400 focus:bg-white placeholder-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                                        </div>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Button Text</label>
                                                <input type="text" v-model="config.embed.button_text" class="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-slate-400 focus:bg-white placeholder-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Redirect After Submit</label>
                                                <input type="url" v-model="config.embed.redirect_url" placeholder="Optional — thank-you URL" class="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-slate-400 focus:bg-white placeholder-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Fields</label>
                                            <div class="flex gap-5 text-sm">
                                                <label class="flex items-center gap-2 text-slate-400"><input type="checkbox" checked disabled class="rounded text-brandColor"> Name</label>
                                                <label class="flex items-center gap-2 text-slate-700 dark:text-slate-300"><input type="checkbox" v-model="config.embed.fields.phone" class="rounded text-brandColor focus:ring-brandColor"> Phone</label>
                                                <label class="flex items-center gap-2 text-slate-700 dark:text-slate-300"><input type="checkbox" v-model="config.embed.fields.email" class="rounded text-brandColor focus:ring-brandColor"> Email</label>
                                                <label class="flex items-center gap-2 text-slate-700 dark:text-slate-300"><input type="checkbox" v-model="config.embed.fields.message" class="rounded text-brandColor focus:ring-brandColor"> Message</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- STEP 2 (oauth): Connect -->
                                <div v-if="isCurrentStep(1) && activeSource.kind === 'oauth'">
                                    <h3 class="text-lg font-bold mb-1 text-slate-900 dark:text-white">Connect your account</h3>
                                    <p class="text-sm text-slate-500 mb-5">@{{ activeSource.instructions }}</p>
                                    <div v-if="result" class="rounded-xl border border-slate-200 dark:border-slate-800 p-5">
                                        <div v-if="result.connection.state === 'connected'" class="flex items-center gap-2 text-emerald-600 font-semibold">
                                            <i class="fa-solid fa-circle-check"></i> Connected: @{{ result.connection.page }}
                                        </div>
                                        <template v-else>
                                            <div v-if="result.connection.state === 'unconfigured'" class="text-sm text-amber-600">
                                                @{{ result.connection.message }}
                                            </div>
                                            <button type="button" @click="openOAuth(result)" class="inline-flex items-center gap-2 rounded-md bg-[#1877F2] px-4 py-2 text-sm font-bold text-white hover:opacity-90">
                                                <i class="fa-brands fa-facebook-f mr-1"></i> Connect Facebook Page
                                            </button>
                                            <p class="mt-3 text-xs text-slate-500">The webhook is already live and will receive leads once a Page is connected. You can also finish now and connect later.</p>
                                        </template>
                                    </div>
                                </div>

                                <!-- STEP 3: Result (all kinds) -->
                                <div v-if="isCurrentStep(2) && result">
                                    <div class="flex items-center gap-2 text-emerald-600 font-bold mb-4"><i class="fa-solid fa-circle-check text-lg"></i> Connector ready</div>

                                    <!-- webhook / oauth: endpoint -->
                                    <template v-if="activeSource.kind !== 'embed'">
                                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Webhook URL</label>
                                        <div class="flex items-center gap-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2.5 mb-3">
                                            <input type="text" readonly :value="result.webhook_url" class="w-full bg-transparent outline-none text-xs font-mono text-slate-800 dark:text-slate-200 truncate">
                                            <button type="button" @click="copy(result.webhook_url, 'Webhook URL')" class="text-brandColor text-xs font-bold shrink-0">Copy</button>
                                        </div>
                                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Sample request</label>
                                        <pre class="rounded-xl bg-slate-900 text-slate-100 text-xs p-3 overflow-x-auto"><code>@{{ curlSnippet(result) }}</code></pre>
                                    </template>

                                    <!-- embed: snippet + live preview -->
                                    <template v-else>
                                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Embed snippet</label>
                                        <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 p-3 mb-2">
                                            <pre class="text-xs font-mono text-slate-800 dark:text-slate-200 whitespace-pre-wrap break-all">@{{ embedSnippet(result) }}</pre>
                                        </div>
                                        <div class="flex gap-2 mb-4">
                                            <button type="button" @click="copy(embedSnippet(result), 'Embed snippet')" class="text-xs font-bold text-brandColor">Copy embed</button>
                                            <button type="button" @click="copy(iframeSnippet(result), 'Iframe snippet')" class="text-xs font-bold text-slate-500">Copy iframe instead</button>
                                            <a :href="result.form_url" target="_blank" class="text-xs font-bold text-slate-500">Open live form <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i></a>
                                        </div>
                                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Live preview</label>
                                        <iframe :src="result.form_url + '?embed=1'" class="w-full h-80 rounded-xl border border-slate-200 dark:border-slate-800"></iframe>
                                    </template>

                                    <p class="mt-4 text-xs text-slate-500">@{{ activeSource.instructions }}</p>

                                    <div class="mt-4 flex items-center gap-3">
                                        <button type="button" @click="testConnector(result)" :disabled="isTesting" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50 transition">
                                            <i v-if="isTesting" class="fa-solid fa-spinner animate-spin mr-1"></i> Run test (dry run)
                                        </button>
                                        <a :href="connectorsUrl" class="text-xs font-semibold text-brandColor hover:underline">Manage all connectors <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i></a>
                                    </div>
                                    <div v-if="testFor === result.id && testResult" class="mt-3 text-xs rounded bg-slate-50 dark:bg-slate-800/60 p-3 text-slate-600 dark:text-slate-300">
                                        <span v-if="testResult.would_create_lead" class="text-emerald-600 font-semibold"><i class="fa-solid fa-check text-xs align-middle mr-1"></i>Test passed.</span>
                                        <span v-else class="text-amber-600 font-semibold"><i class="fa-solid fa-triangle-exclamation text-xs align-middle mr-1"></i>Would skip (duplicate).</span>
                                        Maps to “@{{ testResult.name }}” · @{{ testResult.email }} · @{{ testResult.phone }} <i class="fa-solid fa-arrow-right text-[10px] align-middle mx-1"></i> @{{ testResult.pipeline || 'default pipeline' }}. No test data was stored.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-8 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 flex items-center justify-between shrink-0">
                        <button type="button" class="secondary-button" @click="handleBack"><i v-if="currentStep > 1" class="fa-solid fa-arrow-left text-xs mr-1"></i> @{{ getBackText() }}</button>
                        <button type="button" class="primary-button" @click="nextStep" :disabled="!canProceed || isSaving">
                            <i v-if="isSaving" class="fa-solid fa-spinner animate-spin mr-1"></i>
                            @{{ getContinueText() }} <i v-if="currentStep !== steps.length && !(currentStep === saveAt && !result)" class="fa-solid fa-arrow-right text-xs ml-1"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </script>

    <script type="module">
        app.component('v-lead-capture-integrations', {
            template: '#v-lead-capture-integrations-template',

            props: ['integrations', 'pipelines', 'users', 'workspaceId'],

            data() {
                return {
                    activeSource: null,
                    localConnectors: [],
                    currentStep: 1,
                    result: null,
                    isSaving: false,
                    isTesting: false,
                    busyId: null,
                    testResult: null,
                    testFor: null,
                    connectorsUrl: "{{ route('admin.settings.lead_connectors.index') }}",
                    providerFields: ['full_name', 'email', 'phone_number', 'company_name', 'message'],
                    config: this.freshConfig(),
                };
            },

            computed: {
                steps() {
                    if (!this.activeSource) return [];
                    if (this.activeSource.kind === 'embed') return ['Details', 'Form Setup', 'Embed & Test'];
                    if (this.activeSource.kind === 'oauth') return ['Details', 'Connect', 'Endpoint & Test'];
                    return ['Details', 'Field Mapping', 'Endpoint & Test'];
                },
                saveAt() {
                    return this.activeSource?.kind === 'oauth' ? 1 : 2;
                },
                canProceed() {
                    if (this.currentStep === 1) return !!(this.config.name && this.config.name.trim());
                    return true;
                },
            },

            methods: {
                freshConfig() {
                    return {
                        name: '',
                        duplicateAction: 'update',
                        pipeline: '',
                        assignee: '',
                        mapping: {
                            full_name: 'person.name',
                            email: 'person.emails',
                            phone_number: 'person.phones',
                            company_name: '',
                            message: 'description',
                        },
                        embed: {
                            title: '',
                            subtitle: "Please enter your details below and we'll get in touch.",
                            button_text: 'Submit',
                            redirect_url: '',
                            fields: { email: true, phone: true, message: true },
                        },
                    };
                },
                kindLabel(kind) {
                    return { webhook: 'Webhook push', embed: 'Embeddable form', oauth: 'OAuth connection' }[kind] || kind;
                },
                kindBadgeClass(kind) {
                    return {
                        webhook: 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-300',
                        embed: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-300',
                        oauth: 'bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-300',
                    }[kind] || 'bg-slate-100 text-slate-600';
                },
                isPastStep(i) { return this.currentStep > i + 1; },
                isCurrentStep(i) { return this.currentStep === i + 1; },
                getBackText() { return this.currentStep > 1 ? 'Back' : 'Close'; },
                getContinueText() {
                    if (this.currentStep === this.steps.length) return 'Done';
                    if (this.currentStep === this.saveAt && !this.result) return 'Create Integration';
                    return 'Continue';
                },
                selectSource(item) {
                    this.activeSource = item;
                    this.localConnectors = item.connectors ? item.connectors.slice() : [];
                    this.resetWizard();
                },
                resetWizard() {
                    this.currentStep = 1;
                    this.result = null;
                    this.testResult = null;
                    this.testFor = null;
                    this.config = this.freshConfig();
                    this.config.name = this.activeSource ? this.activeSource.name : '';
                    this.config.embed.title = this.config.name;
                },
                handleBack() {
                    if (this.currentStep > 1) this.currentStep--;
                    else this.activeSource = null;
                },
                openOAuth(c) {
                    const url = c.connection && c.connection.connect_url ? c.connection.connect_url : null;
                    if (!url) return;
                    const width = 600;
                    const height = 700;
                    const left = (window.innerWidth - width) / 2;
                    const top = (window.innerHeight - height) / 2;
                    const win = window.open(url, 'oauth', `width=${width},height=${height},top=${top},left=${left}`);
                    const timer = setInterval(() => {
                        if (win && win.closed) {
                            clearInterval(timer);
                            if (this.currentStep === 2 && this.activeSource?.kind === 'oauth' && this.result && c.id === this.result.id) {
                                this.nextStep();
                            } else {
                                window.location.reload();
                            }
                        }
                    }, 500);
                },
                nextStep() {
                    if (this.currentStep === this.steps.length) { this.resetWizard(); return; }
                    if (this.currentStep === this.saveAt && !this.result) { this.save(); return; }
                    this.currentStep++;
                },
                save() {
                    this.isSaving = true;
                    const payload = {
                        name: this.config.name,
                        source_type: this.activeSource.source_type,
                        duplicate_action: this.config.duplicateAction,
                        pipeline: this.config.pipeline || null,
                        assignee: this.config.assignee || null,
                    };
                    if (this.activeSource.kind === 'webhook') {
                        payload.mapping = this.config.mapping;
                    } else if (this.activeSource.kind === 'embed') {
                        payload.embed_config = this.config.embed;
                    }

                    this.$axios.post("{{ route('admin.lead_capture.integrations.store') }}", payload, this.tenantConfig())
                        .then(res => {
                            this.result = res.data.connector;
                            this.localConnectors.unshift(res.data.connector);
                            this.currentStep++;
                            this.$emitter.emit('add-flash', { type: 'success', message: res.data.message });
                        })
                        .catch(err => {
                            this.$emitter.emit('add-flash', { type: 'error', message: this.errMsg(err, 'Failed to create integration.') });
                        })
                        .finally(() => { this.isSaving = false; });
                },
                testConnector(c) {
                    this.isTesting = true;
                    this.testFor = c.id;
                    this.testResult = null;
                    this.$axios.post(`{{ url('admin/lead-capture/integrations') }}/${c.id}/test`, {}, this.tenantConfig())
                        .then(res => { this.testResult = res.data.preview; })
                        .catch(err => {
                            this.$emitter.emit('add-flash', { type: 'error', message: this.errMsg(err, 'Test failed.') });
                        })
                        .finally(() => { this.isTesting = false; });
                },
                disconnectConnector(c) {
                    if (this.busyId) return; // guard against double-clicks
                    if (! window.confirm(`Disconnect “${c.name}”? It will stop receiving leads until re-enabled.`)) return;

                    this.busyId = c.id;
                    this.$axios.post(`{{ url('admin/lead-capture/integrations') }}/${c.id}/disconnect`, {}, this.tenantConfig())
                        .then(res => {
                            Object.assign(c, res.data.connector);
                            this.$emitter.emit('add-flash', { type: 'success', message: res.data.message });
                        })
                        .catch(err => {
                            this.$emitter.emit('add-flash', { type: 'error', message: this.errMsg(err, 'Disconnect failed.') });
                        })
                        .finally(() => { this.busyId = null; });
                },
                reconnectConnector(c) {
                    if (this.busyId) return;

                    this.busyId = c.id;
                    this.$axios.post(`{{ url('admin/lead-capture/integrations') }}/${c.id}/reconnect`, {}, this.tenantConfig())
                        .then(res => {
                            Object.assign(c, res.data.connector);
                            this.$emitter.emit('add-flash', { type: 'success', message: res.data.message });
                        })
                        .catch(err => {
                            this.$emitter.emit('add-flash', { type: 'error', message: this.errMsg(err, 'Re-enable failed.') });
                        })
                        .finally(() => { this.busyId = null; });
                },
                metaConnect(c) {
                    return c.connection && c.connection.connect_url ? c.connection.connect_url : null;
                },
                statusDot(c) {
                    if (!c.is_active) return 'bg-slate-300';
                    if (c.connection.state === 'needs_connect' || c.connection.state === 'unconfigured') return 'bg-amber-400';
                    return 'bg-emerald-500';
                },
                connLabel(c) {
                    return {
                        active: 'Active', connected: 'Connected', needs_connect: 'Needs connect',
                        unconfigured: 'Setup required', disconnected: 'Disconnected',
                    }[c.connection.state] || c.connection.state;
                },
                connBadge(c) {
                    if (!c.is_active || c.connection.state === 'disconnected') return 'bg-slate-100 text-slate-500';
                    if (c.connection.state === 'needs_connect' || c.connection.state === 'unconfigured') return 'bg-amber-100 text-amber-700';
                    return 'bg-emerald-100 text-emerald-700';
                },
                copyPrimary(c) {
                    if (this.activeSource.kind === 'embed') this.copy(this.embedSnippet(c), 'Embed snippet');
                    else this.copy(c.webhook_url, 'Webhook URL');
                },
                copy(text, label) {
                    navigator.clipboard.writeText(text);
                    this.$emitter.emit('add-flash', { type: 'success', message: (label || 'Copied') + ' copied.' });
                },
                embedSnippet(c) {
                    return `<script src="${c.embed_js_url}"><\/script>\n<div data-lead-capture="${c.token}"></div>`;
                },
                iframeSnippet(c) {
                    return `<iframe src="${c.form_url}?embed=1" style="width:100%;border:0;min-height:520px" title="Lead form"></iframe>`;
                },
                curlSnippet(c) {
                    return `curl -X POST "${c.webhook_url}" \\\n  -H "Content-Type: application/json" \\\n  -d '{"name":"Jane Doe","email":"jane@example.com","phone":"+1 555 0100"}'`;
                },
                errMsg(err, fallback) {
                    return (err.response && err.response.data && err.response.data.message) || fallback;
                },
                // Tenant context: echo the resolved workspace on every request so
                // all calls stay scoped to the same tenant (and cache-consistent).
                tenantConfig() {
                    return { headers: { 'X-Workspace-Id': this.workspaceId } };
                },
            },
        });
    </script>
@endpush
