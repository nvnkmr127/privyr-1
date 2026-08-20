<x-admin::layouts>
    <!-- Page Title -->
    <x-slot:title>
        @lang('admin::app.settings.lead-assignment-rules.index.title')
    </x-slot>

    <div class="flex flex-col gap-4">
        <!-- Header Section -->
        <div class="scroll-reactive-sticky sticky top-[60px] z-[1000] flex items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div class="flex flex-col gap-2">
                <x-admin::breadcrumbs name="settings.lead_assignment_rules" />

                <div class="text-xl font-bold dark:text-white">
                    @lang('admin::app.settings.lead-assignment-rules.index.title')
                </div>
            </div>

            <div class="flex items-center gap-x-2.5">
                @if (bouncer()->hasPermission('settings.automation.lead_assignment_rules.create'))
                    <a href="{{ route('admin.settings.lead_assignment_rules.create') }}" class="primary-button">
                        @lang('admin::app.settings.lead-assignment-rules.index.create-btn')
                    </a>
                @endif
            </div>
        </div>
        
        <!-- DataGrid -->
        <x-admin::datagrid src="{{ route('admin.settings.lead_assignment_rules.index') }}" />
    </div>
</x-admin::layouts>
