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
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-blue-50 to-indigo-100 shadow-sm ring-1 ring-blue-200/50 dark:from-blue-900/50 dark:to-indigo-950/50 dark:ring-blue-700/50">
                                    <span class="icon-file text-2xl text-blue-600 dark:text-blue-400"></span>
                                </div>
                                <h3 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">
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
                                <x-admin::form.control-group.label class="font-semibold text-gray-800 dark:text-gray-200">
                                    @lang('admin::app.components.activities.actions.file.title-control')
                                </x-admin::form.control-group.label>
                                
                                <x-admin::form.control-group.control
                                    type="text"
                                    name="title"
                                    class="w-full rounded border border-gray-300 bg-white px-2.5 py-2 text-sm font-normal text-gray-800 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                                    placeholder="Enter file title (optional)"
                                />
                            </x-admin::form.control-group>

                            <!-- Description -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="font-semibold text-gray-800 dark:text-gray-200">
                                    @lang('admin::app.components.activities.actions.file.description')
                                </x-admin::form.control-group.label>
                                
                                <x-admin::form.control-group.control
                                    type="textarea"
                                    name="comment"
                                    class="!h-[100px] w-full resize-y rounded border border-gray-300 bg-white px-2.5 py-2 text-sm font-normal text-gray-800 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                                    placeholder="Add a description..."
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
                                    class="w-full rounded border border-gray-300 bg-white px-2.5 py-2 text-sm font-normal text-gray-800 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                                />
                            </x-admin::form.control-group>

                            <!-- File -->
                            <x-admin::form.control-group class="!mb-0">
                                <x-admin::form.control-group.label class="required font-semibold text-gray-800 dark:text-gray-200">
                                    @lang('admin::app.components.activities.actions.file.file')
                                </x-admin::form.control-group.label>

                                <v-field name="file" rules="required" v-slot="{ handleChange, errorMessage, field }">
                                    <input 
                                        type="file"
                                        class="hidden"
                                        ref="fileInput"
                                        @change="e => { handleFileChange(e); handleChange(e); }"
                                    />
                                    
                                    <!-- Upload zone -->
                                    <div 
                                        :class="[
                                            'group mt-2 flex flex-col items-center justify-center rounded-xl border-2 border-dashed p-8 transition-all duration-200',
                                            isDragging 
                                                ? 'border-brandColor bg-brandColor/5 shadow-[0_0_15px_rgba(var(--brand-color),0.15)] dark:bg-brandColor/10' 
                                                : (errorMessage ? 'border-red-300 bg-red-50/50 hover:bg-red-50 dark:border-red-900 dark:bg-red-900/20' : 'border-gray-300 bg-gray-50/50 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-900/50 dark:hover:bg-gray-800')
                                        ]"
                                        @dragover.prevent="isDragging = true"
                                        @dragleave.prevent="isDragging = false"
                                        @drop.prevent="e => { isDragging = false; handleDrop(e, handleChange); }"
                                        v-if="!selectedFile"
                                    >
                                        <div :class="['mb-4 flex h-14 w-14 items-center justify-center rounded-full transition-transform duration-200 group-hover:scale-110', isDragging ? 'bg-brandColor text-white shadow-lg shadow-brandColor/30' : 'bg-blue-100 text-blue-600 shadow-sm dark:bg-blue-900/50 dark:text-blue-400']">
                                            <span class="icon-file text-2xl"></span>
                                        </div>
                                        <p class="mb-1 text-base font-semibold text-gray-700 dark:text-gray-200">
                                            <button type="button" class="text-brandColor hover:underline dark:text-blue-400" @click="$refs.fileInput.click()">Click to upload</button> <span class="font-normal text-gray-500">or drag and drop</span>
                                        </p>
                                        <p class="text-sm text-gray-500">PDF, DOC, XLS, CSV, ZIP or Images (Max. 10MB)</p>
                                    </div>

                                    <!-- Selected state -->
                                    <div v-else class="animate-fade-in mt-2 flex items-center justify-between rounded-xl border border-gray-200 bg-white p-4 shadow-sm ring-1 ring-gray-100 transition-all hover:shadow-md dark:border-gray-700 dark:bg-gray-900 dark:ring-gray-800">
                                        <div class="flex items-center gap-4 overflow-hidden">
                                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-blue-50 to-indigo-100 shadow-sm ring-1 ring-blue-200/50 text-blue-600 dark:from-blue-900/50 dark:to-indigo-950/50 dark:text-blue-400 dark:ring-blue-700/50">
                                                <span class="icon-file text-xl"></span>
                                            </div>
                                            <div class="overflow-hidden">
                                                <p class="truncate text-sm font-bold text-gray-900 dark:text-white">@{{ selectedFile.name }}</p>
                                                <p class="text-xs font-medium text-gray-500">@{{ formatFileSize(selectedFile.size) }}</p>
                                            </div>
                                        </div>
                                        <button type="button" @click="() => { clearFile(); handleChange(null); }" class="shrink-0 rounded-lg bg-gray-50 p-2.5 text-gray-400 transition-colors hover:bg-red-50 hover:text-red-600 focus:outline-none focus:ring-2 focus:ring-red-500/20 dark:bg-gray-800 dark:hover:bg-red-900/30">
                                            <span class="icon-delete text-xl"></span>
                                        </button>
                                    </div>
                                    
                                    <span class="mt-1 block text-xs text-red-600" v-if="errorMessage">@{{ errorMessage }}</span>
                                </v-field>
                            </x-admin::form.control-group>

                            {!! view_render_event('admin.components.activities.actions.file.form_controls.modal.content.controls.after') !!}
                        </x-slot>

                        <x-slot:footer>
                            {!! view_render_event('admin.components.activities.actions.file.form_controls.modal.footer.save_buton.before') !!}

                            <div class="flex items-center gap-x-4">
                                <button
                                    type="button"
                                    class="transparent-button px-4 py-2"
                                    @click="resetForm(); $refs.fileActivityModal.close()"
                                >
                                    Cancel
                                </button>
                                
                                <x-admin::button
                                    button-class="primary-button"
                                    :title="trans('admin::app.components.activities.actions.file.save-btn')"
                                    :loading-title="trans('admin::app.components.activities.actions.file.uploading')"
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