@props([
    'entity' => null,
    'entityControlName' => null,
])

<!-- Activity Button -->
<div>
    {!! view_render_event('admin.components.activities.actions.activity.create_btn.before') !!}

    <button
        class="flex h-20 w-full flex-col items-center justify-center gap-1.5 rounded-xl border border-transparent bg-purple-50 text-xs font-bold text-purple-600 transition-all hover:bg-purple-100"
        @click="$refs.actionComponent.openModal('mail')"
    >
        <span class="icon-activity text-2xl"></span>

        @lang('admin::app.components.activities.actions.activity.btn')
    </button>

    {!! view_render_event('admin.components.activities.actions.activity.create_btn.after') !!}

    {!! view_render_event('admin.components.activities.actions.activity.before') !!}

    <!-- Note Action Vue Component -->
    <v-activity
        ref="actionComponent"
        :entity="{{ json_encode($entity) }}"
        entity-control-name="{{ $entityControlName }}"
    ></v-activity>

    {!! view_render_event('admin.components.activities.actions.activity.after') !!}
</div>


@pushOnce('scripts')
    <script type="text/x-template" id="v-activity-template">
        <Teleport to="body">
            {!! view_render_event('admin.components.activities.actions.activity.form_controls.before') !!}

            <x-admin::form
                v-slot="{ meta, errors, handleSubmit, resetForm }"
                as="div"
                ref="modalForm"
            >
                <form @submit="handleSubmit($event, save)">
                    {!! view_render_event('admin.components.activities.actions.activity.form_controls.modal.before') !!}

                    <x-admin::drawer
                        ref="activityModal"
                        width="500px"
                    >
                        <x-slot:header>
                            {!! view_render_event('admin.components.activities.actions.activity.form_controls.modal.header.dropdown.before') !!}

                            <div class="flex items-center gap-3.5">
                                <div :class="['flex h-11 w-11 shrink-0 items-center justify-center rounded-full', selectedType.colorClass]">
                                    <span :class="[selectedType.icon, 'text-xl']"></span>
                                </div>
                                
                                <div class="flex items-center gap-2">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                        @lang('admin::app.components.activities.actions.activity.title')
                                    </h3>
                                    
                                    <x-admin::dropdown>
                                        <x-slot:toggle>
                                            <div class="flex cursor-pointer items-center gap-1 rounded-md border border-gray-200 bg-white px-2 py-1 text-sm font-semibold text-gray-600 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
                                                @{{ selectedType.label }}
                                                <span class="icon-down-arrow text-lg"></span>
                                            </div>
                                        </x-slot>

                                    <x-slot:menu>
                                        {!! view_render_event('admin.components.activities.actions.activity.form_controls.modal.header.dropdown.menu_item.before') !!}

                                        <x-admin::dropdown.menu.item
                                            ::class="{ 'bg-gray-100 dark:bg-gray-950': selectedType.value === type.value }"
                                            v-for="type in availableTypes"
                                            @click="selectedType = type"
                                        >
                                            @{{ type.label }}
                                        </x-admin::dropdown.menu.item>

                                        {!! view_render_event('admin.components.activities.actions.activity.form_controls.modal.header.dropdown.menu_item.after') !!}
                                    </x-slot>
                                    </x-admin::dropdown>
                                </div>
                            </div>

                            {!! view_render_event('admin.components.activities.actions.activity.form_controls.modal.header.dropdown.after') !!}
                        </x-slot>

                        <x-slot:content>
                            {!! view_render_event('admin.components.activities.actions.activity.form_controls.modal.content.controls.before') !!}

                            <!-- Activity Type -->
                            <x-admin::form.control-group.control
                                type="hidden"
                                name="type"
                                v-model="selectedType.value"
                            />

                            <!-- Id -->
                            <x-admin::form.control-group.control
                                type="hidden"
                                ::name="entityControlName"
                                ::value="entity.id"
                            />

                            <!-- Title -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required font-medium text-gray-700 dark:text-gray-300">
                                    @lang('admin::app.components.activities.actions.activity.title-control')
                                </x-admin::form.control-group.label>
                                
                                <x-admin::form.control-group.control
                                    type="text"
                                    name="title"
                                    rules="required|max:80"
                                    class="!rounded-xl"
                                    :label="trans('admin::app.components.activities.actions.activity.title-control')"
                                />

                                <x-admin::form.control-group.error control-name="title" />
                            </x-admin::form.control-group>

                            <!-- Description -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="font-medium text-gray-700 dark:text-gray-300">
                                    @lang('admin::app.components.activities.actions.activity.description')
                                </x-admin::form.control-group.label>
                                
                                <x-admin::form.control-group.control
                                    type="textarea"
                                    name="comment"
                                    rules="max:500"
                                    class="!h-[160px] resize-y !rounded-xl p-3"
                                    placeholder="Write details here..."
                                />

                                <x-admin::form.control-group.error control-name="comment" />
                            </x-admin::form.control-group>

                            <!-- Participants -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="font-medium text-gray-700 dark:text-gray-300">
                                    @lang('admin::app.components.activities.actions.activity.participants.title')
                                </x-admin::form.control-group.label>

                                <x-admin::activities.actions.activity.participants />
                            </x-admin::form.control-group>

                            <!-- Schedule Date -->
                            <div class="flex flex-col md:flex-row gap-4">
                                <!-- Started From -->
                                <x-admin::form.control-group class="w-full">
                                    <x-admin::form.control-group.label class="required font-medium text-gray-700 dark:text-gray-300">
                                        @lang('admin::app.components.activities.actions.activity.schedule-from')
                                    </x-admin::form.control-group.label>
                                    
                                    <x-admin::form.control-group.control
                                        type="datetime"
                                        name="schedule_from"
                                        rules="required"
                                        class="!rounded-xl"
                                        :label="trans('admin::app.components.activities.actions.activity.schedule-from')"
                                    />

                                    <x-admin::form.control-group.error control-name="schedule_from" />
                                </x-admin::form.control-group>
                                
                                <!-- Started To -->
                                <x-admin::form.control-group class="w-full">
                                    <x-admin::form.control-group.label class="required font-medium text-gray-700 dark:text-gray-300">
                                        @lang('admin::app.components.activities.actions.activity.schedule-to')
                                    </x-admin::form.control-group.label>
                                    
                                    <x-admin::form.control-group.control
                                        type="datetime"
                                        name="schedule_to"
                                        rules="required"
                                        class="!rounded-xl"
                                        :label="trans('admin::app.components.activities.actions.activity.schedule-to')"
                                    />

                                    <x-admin::form.control-group.error control-name="schedule_to" />
                                </x-admin::form.control-group>
                            </div>

                            <!-- Location -->
                            <x-admin::form.control-group v-if="selectedType.hasLocation" class="!mb-0">
                                <x-admin::form.control-group.label class="font-medium text-gray-700 dark:text-gray-300">
                                    @lang('admin::app.components.activities.actions.activity.location')
                                </x-admin::form.control-group.label>
                                
                                <x-admin::form.control-group.control
                                    type="text"
                                    name="location"
                                    class="!rounded-xl"
                                />
                            </x-admin::form.control-group>

                            {!! view_render_event('admin.components.activities.actions.activity.form_controls.modal.content.controls.after') !!}
                        </x-slot>

                        <x-slot:footer>
                            {!! view_render_event('admin.components.activities.actions.activity.form_controls.modal.footer.save_button.before') !!}

                            <div class="flex items-center gap-x-3">
                                <p
                                    class="cursor-pointer font-semibold text-gray-600 transition-all hover:underline dark:text-gray-300"
                                    @click="resetForm(); $refs.activityModal.close()"
                                >
                                    Cancel
                                </p>
                                
                                <x-admin::button
                                    ::button-class="'primary-button ' + ((isStoring || !meta.valid) ? 'opacity-50 cursor-not-allowed' : '')"
                                    :title="trans('admin::app.components.activities.actions.activity.save-btn')"
                                    ::loading-title="'Saving...'"
                                    ::loading="isStoring"
                                    ::disabled="isStoring || !meta.valid"
                                />
                            </div>

                            {!! view_render_event('admin.components.activities.actions.activity.form_controls.modal.footer.save_button.after') !!}
                        </x-slot>
                    </x-admin::drawer>

                    {!! view_render_event('admin.components.activities.actions.activity.form_controls.modal.after') !!}
                </form>
            </x-admin::form>

            {!! view_render_event('admin.components.activities.actions.activity.form_controls.after') !!}
        </Teleport>
    </script>

    <script type="module">
        app.component('v-activity', {
            template: '#v-activity-template',

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
                    
                    selectedType: {
                        label: "{{ trans('admin::app.components.activities.actions.activity.call') }}",
                        value: 'call'
                    },

                    availableTypes: [
                        {
                            label: "{{ trans('admin::app.components.activities.actions.activity.call') }}",
                            value: 'call',
                            icon: 'icon-call',
                            colorClass: 'bg-blue-50 text-blue-600 dark:bg-blue-950 dark:text-blue-400',
                            hasLocation: false
                        }, {
                            label: "{{ trans('admin::app.components.activities.actions.activity.meeting') }}",
                            value: 'meeting',
                            icon: 'icon-meeting',
                            colorClass: 'bg-purple-50 text-purple-600 dark:bg-purple-950 dark:text-purple-400',
                            hasLocation: true
                        }, {
                            label: "{{ trans('admin::app.components.activities.actions.activity.lunch') }}",
                            value: 'lunch',
                            icon: 'icon-activity',
                            colorClass: 'bg-amber-50 text-amber-600 dark:bg-amber-950 dark:text-amber-400',
                            hasLocation: true
                        },
                    ]
                }
            },

            watch: {
                selectedType: function (newType) {
                    if (!newType.hasLocation) {
                        // Reset location field safely when switching to a type that doesn't need it
                        const locationInput = this.$refs.modalForm.$el.querySelector('input[name="location"]');
                        if (locationInput) {
                            locationInput.value = '';
                            locationInput.dispatchEvent(new Event('input'));
                        }
                    }
                }
            },

            methods: {
                openModal(type) {
                    this.$refs.activityModal.open();
                },

                save(params, { resetForm, setErrors }) {
                    this.isStoring = true;

                    this.$axios.post("{{ route('admin.activities.store') }}", params)
                        .then (response => {
                            this.isStoring = false;

                            this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });

                            this.$emitter.emit('on-activity-added', response.data.data);

                            resetForm();

                            this.$refs.activityModal.close();
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