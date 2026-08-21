<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.leads.import.title')
    </x-slot>

    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <!-- Back Button -->
            <a href="{{ route('admin.leads.index') }}">
                <div class="icon-left-arrow cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800"></div>
            </a>
            
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('admin::app.leads.import.title')
            </p>
        </div>
    </div>

    <!-- Import Wizard Component -->
    <v-lead-import-wizard></v-lead-import-wizard>

    @pushOnce('scripts')
        <script type="text/x-template" id="v-lead-import-wizard-template">
            <div class="mt-4 flex flex-col gap-4">
                
                <!-- Step 1: Upload -->
                <div v-if="step === 1" class="flex flex-col gap-4 rounded-lg bg-white p-4 shadow dark:bg-gray-900">
                    <p class="text-base font-semibold text-gray-800 dark:text-white">1. Upload CSV</p>
                    <input type="file" ref="fileInput" accept=".csv" @change="handleFileUpload" class="w-full rounded border p-2 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                    <button type="button" class="primary-button w-max" @click="uploadFile" :disabled="!file || isUploading">
                        @{{ isUploading ? 'Uploading...' : 'Next' }}
                    </button>
                </div>

                <!-- Step 2: Mapping -->
                <div v-if="step === 2" class="flex flex-col gap-4 rounded-lg bg-white p-4 shadow dark:bg-gray-900">
                    <p class="text-base font-semibold text-gray-800 dark:text-white">2. Map Columns</p>
                    <div class="grid grid-cols-2 gap-4">
                        <div v-for="header in headers" :key="header" class="flex items-center justify-between">
                            <span class="text-sm dark:text-gray-300">@{{ header }}</span>
                            <select v-model="mapping[header]" class="rounded border p-2 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                                <option value="">-- Ignore --</option>
                                <option value="title">Title (Required)</option>
                                <option value="name">Person Name</option>
                                <option value="emails">Email</option>
                                <option value="phones">Phone</option>
                                <option value="lead_source_id">Source</option>
                                <!-- In reality, populated by $attributes passed from backend -->
                            </select>
                        </div>
                    </div>
                    
                    <div class="mt-4 border-t pt-4 dark:border-gray-800">
                        <p class="mb-2 text-sm font-semibold dark:text-gray-300">Settings</p>
                        <div class="flex flex-col gap-2">
                            <label class="flex items-center gap-2 dark:text-gray-300">
                                <span>Duplicate Handling:</span>
                                <select v-model="settings.duplicate_action" class="rounded border p-1 dark:border-gray-800 dark:bg-gray-900">
                                    <option value="skip">Skip Duplicates</option>
                                    <option value="update">Update Existing</option>
                                    <option value="reject">Reject</option>
                                </select>
                            </label>
                            
                            <label class="flex items-center gap-2 dark:text-gray-300" v-if="settings.duplicate_action === 'update'">
                                <input type="checkbox" v-model="settings.overwrite_blank" class="rounded">
                                <span>Blank CSV values should overwrite existing values</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button type="button" class="secondary-button" @click="step = 1">Back</button>
                        <button type="button" class="primary-button" @click="validateMapping" :disabled="isValidating">
                            @{{ isValidating ? 'Validating...' : 'Next (Preview)' }}
                        </button>
                    </div>
                </div>

                <!-- Step 3: Preview -->
                <div v-if="step === 3" class="flex flex-col gap-4 rounded-lg bg-white p-4 shadow dark:bg-gray-900">
                    <p class="text-base font-semibold text-gray-800 dark:text-white">3. Preview & Confirm</p>
                    
                    <div class="rounded border p-4 bg-yellow-50 dark:bg-yellow-900 dark:border-yellow-700">
                        <p class="text-sm dark:text-gray-300">Total Scanned: @{{ validationResult.total_scanned }}</p>
                        <p class="text-sm dark:text-gray-300">Duplicates Detected: @{{ validationResult.duplicates_detected }}</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm dark:text-gray-300">
                            <thead class="border-b dark:border-gray-800">
                                <tr>
                                    <th v-for="col in Object.values(mapping).filter(v => v)" class="p-2">@{{ col }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="row in previewRows" class="border-b dark:border-gray-800">
                                    <td v-for="(col, key) in mapping" v-show="col" class="p-2">@{{ row[col] }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex gap-2 mt-4">
                        <button type="button" class="secondary-button" @click="step = 2">Back</button>
                        <button type="button" class="primary-button" @click="processImport" :disabled="isProcessing">
                            @{{ isProcessing ? 'Processing...' : 'Confirm & Import' }}
                        </button>
                    </div>
                </div>

                <!-- Step 4: Progress -->
                <div v-if="step === 4" class="flex flex-col gap-4 rounded-lg bg-white p-4 shadow dark:bg-gray-900">
                    <p class="text-base font-semibold text-gray-800 dark:text-white">4. Import Progress</p>
                    
                    <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                        <div class="bg-blue-600 h-2.5 rounded-full" :style="{ width: progress + '%' }"></div>
                    </div>
                    
                    <p class="text-sm dark:text-gray-300">Status: @{{ importState }}</p>
                    
                    <div v-if="importState === 'processed'" class="mt-4">
                        <p class="text-green-600 font-bold">Import Completed!</p>
                        <ul class="text-sm dark:text-gray-300 mt-2">
                            <li>Created: @{{ summary.created || 0 }}</li>
                            <li>Updated: @{{ summary.updated || 0 }}</li>
                            <li>Failed: @{{ summary.failed || 0 }}</li>
                            <li>Duplicates: @{{ summary.duplicates || 0 }}</li>
                        </ul>
                        <a href="{{ route('admin.leads.index') }}" class="primary-button mt-4 inline-block">Return to Leads</a>
                    </div>
                </div>

            </div>
        </script>

        <script type="module">
            app.component('v-lead-import-wizard', {
                template: '#v-lead-import-wizard-template',
                data() {
                    return {
                        step: 1,
                        file: null,
                        filePath: null,
                        headers: [],
                        previewRows: [],
                        mapping: {},
                        settings: {
                            duplicate_action: 'skip',
                            overwrite_blank: false
                        },
                        validationResult: {},
                        isUploading: false,
                        isValidating: false,
                        isProcessing: false,
                        batchId: null,
                        progress: 0,
                        importState: 'pending',
                        summary: {},
                        pollInterval: null
                    };
                },
                methods: {
                    handleFileUpload(event) {
                        this.file = event.target.files[0];
                    },
                    uploadFile() {
                        this.isUploading = true;
                        let formData = new FormData();
                        formData.append('file', this.file);
                        
                        this.$axios.post("{{ route('admin.leads.import.upload') }}", formData, {
                            headers: { 'Content-Type': 'multipart/form-data' }
                        }).then(response => {
                            this.filePath = response.data.file_path;
                            this.headers = response.data.headers;
                            this.previewRows = response.data.preview_rows;
                            
                            // Auto-map where possible
                            this.headers.forEach(h => {
                                let l = h.toLowerCase();
                                if (['title', 'name'].includes(l)) this.mapping[h] = 'title';
                                else if (['email', 'emails'].includes(l)) this.mapping[h] = 'emails';
                                else if (['phone', 'contact'].includes(l)) this.mapping[h] = 'phones';
                                else this.mapping[h] = '';
                            });
                            
                            this.step = 2;
                        }).catch(error => {
                            this.$emitter.emit('add-flash', { type: 'error', message: error.response.data.message || 'Upload failed' });
                        }).finally(() => {
                            this.isUploading = false;
                        });
                    },
                    validateMapping() {
                        this.isValidating = true;
                        this.$axios.post("{{ route('admin.leads.import.validate') }}", {
                            file_path: this.filePath,
                            mapping: this.mapping,
                            settings: this.settings
                        }).then(response => {
                            this.validationResult = response.data;
                            if (this.validationResult.valid) {
                                this.step = 3;
                            } else {
                                this.$emitter.emit('add-flash', { type: 'error', message: 'Mapping validation failed.' });
                            }
                        }).finally(() => {
                            this.isValidating = false;
                        });
                    },
                    processImport() {
                        this.isProcessing = true;
                        this.$axios.post("{{ route('admin.leads.import.process') }}", {
                            file_path: this.filePath,
                            mapping: this.mapping,
                            settings: this.settings
                        }).then(response => {
                            this.batchId = response.data.batch_id;
                            this.step = 4;
                            this.startPolling();
                        }).catch(error => {
                            this.$emitter.emit('add-flash', { type: 'error', message: 'Failed to start import.' });
                            this.isProcessing = false;
                        });
                    },
                    startPolling() {
                        this.pollInterval = setInterval(() => {
                            this.$axios.get("{{ url('admin/leads/import/status') }}/" + this.batchId)
                                .then(response => {
                                    this.progress = response.data.progress;
                                    this.importState = response.data.state;
                                    this.summary = response.data.summary;
                                    
                                    if (this.importState === 'processed' || this.importState === 'failed') {
                                        clearInterval(this.pollInterval);
                                    }
                                });
                        }, 2000);
                    }
                },
                beforeUnmount() {
                    if (this.pollInterval) clearInterval(this.pollInterval);
                }
            });
        </script>
    @endPushOnce
</x-admin::layouts>
