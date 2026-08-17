<x-admin::layouts>
    <x-slot:title>
        {{ menu()->getLabel('leads', 'admin::app.leads.index.title') }}
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-8">
        <!-- Header -->
        {!! view_render_event('admin.leads.index.header.before') !!}

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mt-4">
            {!! view_render_event('admin.leads.index.header.left.before') !!}

            <div class="flex flex-col gap-1">
                <div class="flex items-center gap-3">
                    <h1 class="text-xl font-bold text-gray-800 dark:text-white">
                        {{ menu()->getLabel('leads', 'admin::app.leads.index.title') }}
                    </h1>
                    <span class="rounded-full bg-slate-100 border border-slate-200 px-3 py-1 text-xs font-medium text-slate-700">
                        {{ app('Webkul\Lead\Repositories\PipelineRepository')->all()->count() }} Pipelines
                    </span>
                </div>
                <!-- Breadcrumb's -->
                <x-admin::breadcrumbs name="leads" />
            </div>

            {!! view_render_event('admin.leads.index.header.left.after') !!}

            {!! view_render_event('admin.leads.index.header.right.before') !!}

            <div class="flex items-center gap-4">
                <!-- Upload File for Lead Creation -->
                @if(core()->getConfigData('general.magic_ai.doc_generation.enabled'))
                    @include('admin::leads.index.upload')
                @endif

                <!-- Export Modal -->
                <x-admin::datagrid.export :src="route('admin.leads.index')" />

                <!-- Create button for Leads -->
                @if (bouncer()->hasPermission('leads.create'))
                    <a
                        href="{{ route('admin.leads.create', request()->query()) }}"
                        class="rounded-xl bg-slate-900 hover:bg-black text-white px-5 py-2.5 text-sm font-semibold shadow-md shadow-slate-900/10 transition flex items-center gap-2"
                    >
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        @lang('admin::app.leads.index.create-btn')
                    </a>
                @endif
            </div>

            {!! view_render_event('admin.leads.index.header.right.after') !!}
        </div>

        {!! view_render_event('admin.leads.index.header.after') !!}

        {!! view_render_event('admin.leads.index.content.before') !!}

        <!-- Content -->
        <div class="[&>*>*>*.toolbarRight]:max-lg:w-full [&>*>*>*.toolbarRight]:max-lg:justify-between [&>*>*>*.toolbarRight]:max-md:gap-y-2 [&>*>*>*.toolbarRight]:max-md:flex-wrap [&>*>*:nth-child(1)]:max-lg:!flex-wrap">
            @include('admin::leads.index.table')
        </div>

        {!! view_render_event('admin.leads.index.content.after') !!}
    </div>
</x-admin::layouts>
