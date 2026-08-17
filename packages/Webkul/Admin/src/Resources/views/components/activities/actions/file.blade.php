@props([
    'entity' => null,
    'entityControlName' => null,
])

<!-- File Button -->
<div>
    {!! view_render_event('admin.components.activities.actions.file.create_btn.before') !!}

    <button
        class="flex h-20 w-full flex-col items-center justify-center gap-1.5 rounded-xl border border-transparent bg-blue-50 text-xs font-bold text-blue-600 transition-all hover:bg-blue-100"
        @click="$refs.fileActionComponent.openModal('mail')"
    >
        <span class="icon-file text-2xl"></span>

        @lang('admin::app.components.activities.actions.file.btn')
    </button>

    {!! view_render_event('admin.components.activities.actions.file.create_btn.after') !!}

    {!! view_render_event('admin.components.activities.actions.file.before') !!}

    <!-- File Action Vue Component -->
    <v-file-activity
        ref="fileActionComponent"
        :entity="{{ json_encode($entity) }}"
        entity-control-name="{{ $entityControlName }}"
    ></v-file-activity>

    {!! view_render_event('admin.components.activities.actions.file.after') !!}
</div>

@pushOnce('scripts')
    <script type="text/x-template" id="v-file-activity-template">
        <Teleport to="body">
            {!! view_render_event('admin.components.activities.actions.file.form_controls.before') !!}

            <x-admin::form
                v-slot="{ meta, errors, handleSubmit, resetForm }"
                as="div"
                ref="modalForm"
            >
                <form @submit="handleSubmit($event, save)">
                    {!! view_render_event('admin.components.activities.actions.file.form_controls.modal.before') !!}

                    <x-admin::drawer
                        ref="fileActivityModal"
                        width="500px"
                    >
                        <x-slot:header>
                            {!! view_render_event('admin.components.activities.actions.file.form_controls.modal.header.title.before') !!}

                            <div class="flex items-center gap-3.5">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-blue-50 text-blue-600 dark:bg-blue-950 dark:text-blue-400">
                                    <span class="icon-file text-xl"></span>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    @lang('admin::app.components.activities.actions.file.title')
                                </h3>
                            </div>

                            {!! view_render_event('admin.components.activities.actions.file.form_controls.modal.header.title.after') !!}
                        </x-slot>

                        <x-slot:content>
                            {!! view_render_event('admin.components.activities.actions.file.form_controls.modal.content.controls.before') !!}

                            <!-- Activity Type -->
                            <x-admin::form.control-group.control
                                type="hidden"
                                name="type"
                                value="file"
                            />
                            
                            <!-- Id -->
                            <x-admin::form.control-group.control
                                type="hidden"
                                ::name="entityControlName"
                                ::value="entity.id"
                            />

                            <!-- Title -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="font-medium text-gray-700 dark:text-gray-300">
                                    @lang('admin::app.components.activities.actions.file.title-control')
                                </x-admin::form.control-group.label>
                                
                                <x-admin::form.control-group.control
                                    type="text"
                                    name="title"
                                    class="!rounded-xl"
                                />
                            </x-admin::form.control-group>

                            <!-- Description -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="font-medium text-gray-700 dark:text-gray-300">
                                    @lang('admin::app.components.activities.actions.file.description')
                                </x-admin::form.control-group.label>
                                
                                <x-admin::form.control-group.control
                                    type="textarea"
                                    name="comment"
                                    class="!h-[160px] resize-y !rounded-xl p-3"
                                    placeholder="Write details here..."
                                />
                            </x-admin::form.control-group>
                            
                            <!-- File Name -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="font-medium text-gray-700 dark:text-gray-300">
                                    @lang('admin::app.components.activities.actions.file.name')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="name"
                                    class="!rounded-xl"
                                />
                            </x-admin::form.control-group>

                            <!-- File -->
                            <x-admin::form.control-group class="!mb-0">
                                <x-admin::form.control-group.label class="required font-medium text-gray-700 dark:text-gray-300">
                                    @lang('admin::app.components.activities.actions.file.file')
                                </x-admin::form.control-group.label>
                                
                                <v-field
                                    name="file"
                                    rules="required"
                                    v-slot="{ handleChange, handleBlur, errorMessage }"
                                >
                                    <input
                                        type="file"
                                        id="file"
                                        class="hidden"
                                        accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.zip"
                                        @change="e => { handleChange(e); handleFileChange(e); }"
                                        @blur="handleBlur"
                                        ref="fileInput"
                                    />
                                    
                                    <!-- Custom UI -->
                                    <div 
                                        class="mt-2 flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 p-6 text-center transition-all hover:border-blue-400 dark:border-gray-700 dark:bg-gray-900"
                                        :class="{'border-blue-500 bg-blue-50/50': isDragging}"
                                        @dragover.prevent="isDragging = true"
                                        @dragleave.prevent="isDragging = false"
                                        @drop.prevent="e => { isDragging = false; handleDrop(e, handleChange); }"
                                        v-if="!selectedFile"
                                    >
                                        <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                                            <span class="icon-file text-xl"></span>
                                        </div>
                                        <p class="mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                                            <button type="button" class="text-blue-600 hover:underline dark:text-blue-400" @click="$refs.fileInput.click()">Click to upload</button> or drag and drop
                                        </p>
                                        <p class="text-xs text-gray-500">PDF, DOC, XLS, CSV, ZIP or Images (Max. 10MB)</p>
                                    </div>

                                    <!-- Selected state -->
                                    <div v-else class="mt-2 flex items-center justify-between rounded-xl border border-gray-200 bg-white p-3 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                                        <div class="flex items-center gap-3 overflow-hidden">
                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950 dark:text-blue-400">
                                                <span class="icon-file text-lg"></span>
                                            </div>
                                            <div class="overflow-hidden">
                                                <p class="truncate text-sm font-medium text-gray-700 dark:text-gray-300">@{{ selectedFile.name }}</p>
                                                <p class="text-xs text-gray-500">@{{ formatFileSize(selectedFile.size) }}</p>
                                            </div>
                                        </div>
                                        <button type="button" @click="() => { clearFile(); handleChange(null); }" class="shrink-0 rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-red-600 dark:hover:bg-gray-800">
                                            <span class="icon-delete text-lg"></span>
                                        </button>
                                    </div>
                                    
                                    <span class="mt-1 block text-xs text-red-600" v-if="errorMessage">@{{ errorMessage }}</span>
                                </v-field>
                            </x-admin::form.control-group>

                            {!! view_render_event('admin.components.activities.actions.file.form_controls.modal.content.controls.after') !!}
                        </x-slot>

                        <x-slot:footer>
                            {!! view_render_event('admin.components.activities.actions.file.form_controls.modal.footer.save_buton.before') !!}

                            <div class="flex items-center gap-x-3">
                                <p
                                    class="cursor-pointer font-semibold text-gray-600 transition-all hover:underline dark:text-gray-300"
                                    @click="resetForm(); $refs.fileActivityModal.close()"
                                >
                                    Cancel
                                </p>
                                
                                <x-admin::button
                                    ::button-class="'primary-button ' + ((isStoring || !meta.valid) ? 'opacity-50 cursor-not-allowed' : '')"
                                    :title="trans('admin::app.components.activities.actions.file.save-btn')"
                                    ::loading-title="'Uploading...'"
                                    ::loading="isStoring"
                                    ::disabled="isStoring || !meta.valid"
                                />
                            </div>

                            {!! view_render_event('admin.components.activities.actions.file.form_controls.modal.footer.save_buton.after') !!}
                        </x-slot>
                    </x-admin::drawer>

                    {!! view_render_event('admin.components.activities.actions.file.form_controls.modal.after') !!}
                </form>
            </x-admin::form>

            {!! view_render_event('admin.components.activities.actions.file.form_controls.after') !!}
        </Teleport>
    </script>

    <script type="module">
        app.component('v-file-activity', {
            template: '#v-file-activity-template',

            props: {
                entity: {
                    type: Object,
                    required: true,
                    default: () => {}
                },

                entityControlName: {
                    type: String,
                    required: true,
                    default: ''
                }
            },

            data: function () {
                return {
                    isStoring: false,
                    isDragging: false,
                    selectedFile: null,
                }
            },

            methods: {
                formatFileSize(bytes) {
                    if (bytes === 0) return '0 Bytes';
                    const k = 1024;
                    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
                },

                handleFileChange(event) {
                    const files = event.target.files;
                    if (files && files.length > 0) {
                        this.selectedFile = files[0];
                    }
                },

                handleDrop(event, handleChange) {
                    const files = event.dataTransfer.files;
                    if (files && files.length > 0) {
                        this.selectedFile = files[0];
                        // Assign to hidden input so form submission picks it up
                        this.$refs.fileInput.files = files;
                        handleChange(files[0]);
                    }
                },

                clearFile() {
                    this.selectedFile = null;
                    this.$refs.fileInput.value = '';
                },
                openModal(type) {
                    this.$refs.fileActivityModal.open();
                },

                save(params, { resetForm, setErrors }) {
                    this.isStoring = true;

                    this.$axios.post("{{ route('admin.activities.store') }}", params, {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    })
                    .then (response => {
                        this.isStoring = false;

                        this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });

                        this.$emitter.emit('on-activity-added', response.data.data);

                        this.clearFile();
                        resetForm();

                        this.$refs.fileActivityModal.close();
                    })
                    .catch (error => {
                        this.isStoring = false;

                        if (error.response.status == 422) {
                            setErrors(error.response.data.errors);
                        } else {
                            this.$emitter.emit('add-flash', { type: 'error', message: error.response.data.message });
                        }
                    });
                },
            },
        });
    </script>
@endPushOnce