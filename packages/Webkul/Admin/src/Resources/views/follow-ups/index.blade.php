<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.follow-ups.title')
    </x-slot>

    <div class="flex flex-col gap-4">
        <!-- Header -->
        <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div class="flex flex-col gap-2">
                <div class="flex cursor-pointer items-center text-xl font-bold">
                    @lang('admin::app.follow-ups.title')
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-gray-600 dark:text-gray-400">@lang('admin::app.follow-ups.productivity-dashboard')</span>
                </div>
            </div>
        </div>

        <div class="flex gap-4">
            <!-- Stats -->
            <div class="flex w-1/4 flex-col gap-4 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="text-lg font-bold">@lang('admin::app.follow-ups.today')</div>
                <div class="text-3xl font-bold text-blue-600">{{ Webkul\Activity\Models\ActivityProxy::modelClass()::pending()->whereDate('schedule_from', today())->count() }}</div>
            </div>

            <div class="flex w-1/4 flex-col gap-4 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="text-lg font-bold text-red-600">@lang('admin::app.follow-ups.overdue')</div>
                <div class="text-3xl font-bold text-red-600">{{ Webkul\Activity\Models\ActivityProxy::modelClass()::overdue()->count() }}</div>
            </div>

            <div class="flex w-1/4 flex-col gap-4 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="text-lg font-bold">@lang('admin::app.follow-ups.upcoming')</div>
                <div class="text-3xl font-bold">{{ Webkul\Activity\Models\ActivityProxy::modelClass()::upcoming()->whereDate('schedule_from', '>', today())->count() }}</div>
            </div>

            <div class="flex w-1/4 flex-col gap-4 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="text-lg font-bold text-green-600">@lang('admin::app.follow-ups.completed-today')</div>
                <div class="text-3xl font-bold text-green-600">{{ Webkul\Activity\Models\ActivityProxy::modelClass()::completedToday()->count() }}</div>
            </div>
        </div>

        <!-- DataGrid -->
        <x-admin::datagrid src="{{ route('admin.follow_ups.index') }}" />
    </div>
</x-admin::layouts>
