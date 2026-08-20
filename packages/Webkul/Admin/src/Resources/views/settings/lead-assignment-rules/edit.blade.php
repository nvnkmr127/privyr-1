<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.settings.lead-assignment-rules.edit.title')
    </x-slot>

    <x-admin::form :action="route('admin.settings.lead_assignment_rules.update', $rule->id)" method="PUT">
        <!-- Header -->
        <div class="flex items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div class="flex flex-col gap-2">
                <x-admin::breadcrumbs name="settings.lead_assignment_rules.edit" />
                <div class="text-xl font-bold dark:text-white">
                    @lang('admin::app.settings.lead-assignment-rules.edit.title')
                </div>
            </div>

            <div class="flex items-center gap-x-2.5">
                <a href="{{ route('admin.settings.lead_assignment_rules.index') }}" class="transparent-button">
                    @lang('admin::app.settings.lead-assignment-rules.edit.back-btn')
                </a>
                <button type="submit" class="primary-button">
                    @lang('admin::app.settings.lead-assignment-rules.edit.save-btn')
                </button>
            </div>
        </div>

        <!-- Content -->
        <div class="mt-3 flex gap-2.5 max-xl:flex-wrap">
            <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
                <div class="box-shadow rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('admin::app.settings.lead-assignment-rules.edit.general-info')
                    </p>

                    <!-- Name -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('admin::app.settings.lead-assignment-rules.edit.name')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            name="name"
                            rules="required"
                            :value="$rule->name"
                            :label="trans('admin::app.settings.lead-assignment-rules.edit.name')"
                        />

                        <x-admin::form.control-group.error control-name="name" />
                    </x-admin::form.control-group>

                    <!-- Sort Order -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('admin::app.settings.lead-assignment-rules.edit.priority')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            name="sort_order"
                            rules="required|numeric"
                            :value="$rule->sort_order"
                            :label="trans('admin::app.settings.lead-assignment-rules.edit.priority')"
                        />

                        <x-admin::form.control-group.error control-name="sort_order" />
                    </x-admin::form.control-group>
                    
                    <!-- Is Active -->
                    <x-admin::form.control-group class="!mb-0 flex items-center gap-2">
                        <x-admin::form.control-group.control
                            type="checkbox"
                            name="is_active"
                            id="is_active"
                            value="1"
                            :checked="(boolean) $rule->is_active"
                        />

                        <label for="is_active" class="cursor-pointer text-sm text-gray-600 dark:text-gray-300">
                            @lang('admin::app.settings.lead-assignment-rules.edit.is-active')
                        </label>
                    </x-admin::form.control-group>
                </div>
                
                <div class="box-shadow rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('admin::app.settings.lead-assignment-rules.edit.conditions-info')
                    </p>

                    <!-- Condition Type -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('admin::app.settings.lead-assignment-rules.edit.condition-type')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="select"
                            name="condition_type"
                            rules="required"
                            :value="$rule->condition_type"
                        >
                            <option value="and">All Conditions Must Match (AND)</option>
                            <option value="or">Any Condition Must Match (OR)</option>
                        </x-admin::form.control-group.control>

                        <x-admin::form.control-group.error control-name="condition_type" />
                    </x-admin::form.control-group>

                    <!-- Conditions -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('admin::app.settings.lead-assignment-rules.edit.conditions') (JSON Format)
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="textarea"
                            name="conditions"
                            rules="required"
                            :value="json_encode($rule->conditions, JSON_PRETTY_PRINT)"
                            class="font-mono text-xs"
                            rows="5"
                        />
                        <x-admin::form.control-group.error control-name="conditions" />
                    </x-admin::form.control-group>
                </div>
            </div>

            <!-- Right Panel -->
            <div class="flex w-[360px] max-w-full flex-col gap-2 max-sm:w-full">
                <div class="box-shadow rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('admin::app.settings.lead-assignment-rules.edit.assignment-info')
                    </p>

                    <!-- Assignment Type -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('admin::app.settings.lead-assignment-rules.edit.assignment-type')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="select"
                            name="assignment_type"
                            rules="required"
                            id="assignment_type"
                            :value="$rule->assignment_type"
                        >
                            <option value="user">Assign to User</option>
                            <option value="team">Assign to Team</option>
                        </x-admin::form.control-group.control>

                        <x-admin::form.control-group.error control-name="assignment_type" />
                    </x-admin::form.control-group>

                    <!-- Entity ID -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('admin::app.settings.lead-assignment-rules.edit.entity-id') (User/Team ID)
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            name="entity_id"
                            rules="required|numeric"
                            :value="$rule->entity_id"
                        />

                        <x-admin::form.control-group.error control-name="entity_id" />
                    </x-admin::form.control-group>
                </div>
            </div>
        </div>
    </x-admin::form>
</x-admin::layouts>
