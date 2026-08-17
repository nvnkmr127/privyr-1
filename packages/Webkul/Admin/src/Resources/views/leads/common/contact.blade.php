{!! view_render_event('admin.leads.create.contact_person.form_controls.before') !!}

<v-contact-component :data="person"></v-contact-component>

{!! view_render_event('admin.leads.create.contact_person.form_controls.after') !!}

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-contact-component-template"
    >
        <!-- Duplicate Warning Alert -->
        <div v-if="duplicateWarning" class="p-3 mb-4 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-xs font-semibold flex items-center gap-2">
            <span class="text-sm">⚠️</span>
            <span>Warning: A contact named "@{{ duplicateWarning.name }}" with this email/phone already exists in the workspace.</span>
        </div>

        <!-- Person Search Lookup -->
        <x-admin::form.control-group>
            <x-admin::form.control-group.label class="required">
                @lang('admin::app.leads.common.contact.name')
            </x-admin::form.control-group.label>

            <x-admin::lookup
                ::src="src"
                name="person[id]"
                ::params="params"
                ::rules="nameValidationRule"
                :label="trans('admin::app.leads.common.contact.name')"
                ::value="{id: person.id, name: person.name}"
                :placeholder="trans('admin::app.leads.common.contact.name-search-placeholder')"
                @on-selected="addPerson"
                :can-add-new="true"
                ::search-keys="['name', 'emails', 'contact_numbers']"
            />

            <x-admin::form.control-group.control
                type="hidden"
                name="person[name]"
                v-model="person.name"
                v-if="person.name"
            />

            <x-admin::form.control-group.error control-name="person[id]" />
        </x-admin::form.control-group>

        <!-- Person Email -->
        <x-admin::form.control-group>
            <x-admin::form.control-group.label class="required">
                @lang('admin::app.leads.common.contact.email')
            </x-admin::form.control-group.label>

            <x-admin::attributes.edit.email />

            <v-email-component
                :attribute="{'id': person?.id, 'code': 'person[emails]', 'name': 'Email'}"
                validations="required"
                :value="person.emails"
                :is-disabled="person?.id ? true : false"
            ></v-email-component>
        </x-admin::form.control-group>

        <!-- Person Contact Numbers -->
        <x-admin::form.control-group>
            <x-admin::form.control-group.label>
                @lang('admin::app.leads.common.contact.contact-number')
            </x-admin::form.control-group.label>

            <x-admin::attributes.edit.phone />

            <v-phone-component
                :attribute="{'id': person?.id, 'code': 'person[contact_numbers]', 'name': 'Contact Numbers'}"
                :value="person.contact_numbers"
                :is-disabled="person?.id ? true : false"
            ></v-phone-component>
        </x-admin::form.control-group>

        <!-- Person Organization -->
        <x-admin::form.control-group>
            <x-admin::form.control-group.label>
                @lang('admin::app.leads.common.contact.organization')
            </x-admin::form.control-group.label>

            @php
                $organizationAttribute = app('Webkul\Attribute\Repositories\AttributeRepository')->findOneWhere([
                    'entity_type' => 'persons',
                    'code' => 'organization_id'
                ]);

                $organizationAttribute->code = 'person[' . $organizationAttribute->code . ']';
            @endphp

            <x-admin::attributes.edit.lookup />

            <v-lookup-component
                :key="person.organization?.id"
                :attribute='@json($organizationAttribute)'
                :value="person.organization"
                :is-disabled="person?.id ? true : false"
                can-add-new="true"
            ></v-lookup-component>
        </x-admin::form.control-group>
    </script>

    <script type="module">
        app.component('v-contact-component', {
            template: '#v-contact-component-template',

            props: ['data'],

            data () {
                return {
                    is_searching: false,

                    person: this.data ? this.data : {
                        'name': ''
                    },

                    persons: [],

                    duplicateWarning: null,
                }
            },

            watch: {
                person: {
                    handler: function (newPerson) {
                        if (newPerson && ! newPerson.id) {
                            this.checkDuplicateDebounced();
                        } else {
                            this.duplicateWarning = null;
                        }
                    },
                    deep: true
                }
            },

            created() {
                this.checkDuplicateDebounced = this.debounce(this.checkDuplicate, 500);
            },

            computed: {
                src() {
                    return "{{ route('admin.contacts.persons.search') }}";
                },

                params() {
                    return {
                        params: {
                            query: this.person['name']
                        }
                    }
                },

                nameValidationRule() {
                    return this.person.name ? '' : 'required';
                }
            },

            methods: {
                addPerson (person) {
                    this.person = person;
                },

                debounce(func, delay) {
                    let timer;
                    return function(...args) {
                        clearTimeout(timer);
                        timer = setTimeout(() => func.apply(this, args), delay);
                    };
                },

                checkDuplicate() {
                    const email = this.person.emails && this.person.emails[0] ? this.person.emails[0].value : '';
                    const phone = this.person.contact_numbers && this.person.contact_numbers[0] ? this.person.contact_numbers[0].value : '';

                    if (! email && ! phone) {
                        this.duplicateWarning = null;
                        return;
                    }

                    this.$axios.get('{{ route('admin.leads.check_duplicate') }}', {
                        params: { email, phone }
                    })
                    .then(response => {
                        if (response.data.is_duplicate) {
                            this.duplicateWarning = response.data.contact;
                        } else {
                            this.duplicateWarning = null;
                        }
                    })
                    .catch(() => {
                        this.duplicateWarning = null;
                    });
                }
            }
        });
    </script>
@endPushOnce