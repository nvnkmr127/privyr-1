@props([
    'entity' => null,
    'entityControlName' => null,
])

<!-- Note Button -->
<div>
    {!! view_render_event('admin.components.activities.actions.note.create_btn.before') !!}

    <button
        class="flex h-20 w-full flex-col items-center justify-center gap-1.5 rounded-xl border border-transparent bg-amber-50 text-xs font-bold text-amber-600 transition-all hover:bg-amber-100"
        @click="$refs.noteActionComponent.openModal('mail')"
    >
        <span class="icon-note text-2xl"></span>

        @lang('admin::app.components.activities.actions.note.btn')
    </button>

    {!! view_render_event('admin.components.activities.actions.note.create_btn.after') !!}

    {!! view_render_event('admin.components.activities.actions.note.before') !!}

    <!-- Note Action Vue Component -->
    <v-note-activity
        ref="noteActionComponent"
        :entity="{{ json_encode($entity) }}"
        entity-control-name="{{ $entityControlName }}"
    ></v-note-activity>

    {!! view_render_event('admin.components.activities.actions.note.after') !!}
</div>

@pushOnce('scripts')
    <script type="text/x-template" id="v-note-activity-template">
        <Teleport to="body">
            {!! view_render_event('admin.components.activities.actions.note.form_controls.before') !!}

            <x-admin::form
                v-slot="{ meta, errors, handleSubmit, resetForm }"
                as="div"
                ref="modalForm"
            >
                <form @submit="handleSubmit($event, save)">
                    {!! view_render_event('admin.components.activities.actions.note.form_controls.modal.before') !!}

                    <x-admin::drawer 
                        ref="noteActivityModal"
                        width="500px"
                    >
                        <x-slot:header>
                            {!! view_render_event('admin.components.activities.actions.note.form_controls.modal.header.title.before') !!}

                            <div class="flex items-center gap-3">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-amber-50 to-orange-100 shadow-sm ring-1 ring-amber-200/50 dark:from-amber-900/50 dark:to-orange-950/50 dark:ring-amber-700/50">
                                    <span class="icon-note text-2xl text-amber-600 dark:text-amber-400"></span>
                                </div>
                                <h3 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">
                                    @lang('admin::app.components.activities.actions.note.title')
                                </h3>
                            </div>

                            {!! view_render_event('admin.components.activities.actions.note.form_controls.modal.header.title.after') !!}
                        </x-slot>

                        <x-slot:content>
                            {!! view_render_event('admin.components.activities.actions.note.form_controls.modal.header.content.controls.before') !!}

                            <!-- Activity Type -->
                            <x-admin::form.control-group.control
                                type="hidden"
                                name="type"
                                value="note"
                            />
                            
                            <!-- Id -->
                            <x-admin::form.control-group.control
                                type="hidden"
                                v-bind:name="entityControlName"
                                v-bind:value="entity.id"
                            />

                            <!-- Comment -->
                            <x-admin::form.control-group class="!mb-0">
                                <x-admin::form.control-group.label class="required font-semibold text-gray-800 dark:text-gray-200">
                                    @lang('admin::app.components.activities.actions.note.comment')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="textarea"
                                    name="comment"
                                    rules="required"
                                    class="!h-[180px] w-full resize-y rounded border border-gray-300 bg-white px-2.5 py-2 text-sm font-normal text-gray-800 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                                    :label="trans('admin::app.components.activities.actions.note.comment')"
                                    placeholder="Write your note here..."
                                  />

                                <x-admin::form.control-group.error control-name="comment" />
                            </x-admin::form.control-group>

                            {!! view_render_event('admin.components.activities.actions.note.form_controls.modal.header.content.controls.after') !!}
                        </x-slot>

                        <x-slot:footer>
                            {!! view_render_event('admin.components.activities.actions.note.form_controls.modal.header.footer.save_button.before') !!}

                            <div class="flex items-center gap-x-4">
                                <button
                                    type="button"
                                    class="transparent-button px-4 py-2"
                                    @click="resetForm(); $refs.noteActivityModal.close()"
                                >
                                    Cancel
                                </button>
                                
                                <x-admin::button
                                    button-class="primary-button"
                                    :title="trans('admin::app.components.activities.actions.note.save-btn')"
                                    :loading-title="trans('admin::app.components.activities.actions.note.saving')"
                                    ::loading="isStoring"
                                    ::disabled="isStoring || !meta.valid"
                                />
                            </div>

                            {!! view_render_event('admin.components.activities.actions.note.form_controls.modal.header.footer.save_button.after') !!}
                        </x-slot>
                    </x-admin::drawer>

                    {!! view_render_event('admin.components.activities.actions.note.form_controls.modal.after') !!}
                </form>
            </x-admin::form>

            {!! view_render_event('admin.components.activities.actions.note.form_controls.after') !!}
        </Teleport>
    </script>

    <script type="module">
        app.component('v-note-activity', {
            template: '#v-note-activity-template',

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
                }
            },

            methods: {
                openModal(type) {
                    this.$refs.noteActivityModal.open();
                },

                save(params, { resetForm, setErrors }) {
                    this.isStoring = true;

                    this.$axios.post("{{ route('admin.activities.store') }}", params)
                        .then (response => {
                            this.isStoring = false;

                            this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });

                            this.$emitter.emit('on-activity-added', response.data.data);

                            resetForm();

                            this.$refs.noteActivityModal.close();
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