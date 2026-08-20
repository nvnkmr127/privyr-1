<x-admin::layouts>
    <x-slot:title>
        {{ menu()->getLabel('leads', 'admin::app.leads.index.title') }}
    </x-slot>

    <div class="flex w-full flex-col gap-4">
        <!-- Header -->
        {!! view_render_event('admin.leads.index.header.before') !!}

        <div class="scroll-reactive-sticky sticky top-0 z-[1000] flex items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div class="flex flex-col gap-2">
                {!! view_render_event('admin.leads.index.header.left.before') !!}

                <div class="flex items-center gap-3">
                    <h1 class="text-xl font-bold dark:text-white">
                        {{ menu()->getLabel('leads', 'admin::app.leads.index.title') }}
                    </h1>
                    <span class="rounded-full bg-slate-100 border border-slate-200 px-3 py-1 text-xs font-medium text-slate-700">
                        {{ app('Webkul\Lead\Repositories\PipelineRepository')->all()->count() }} Pipelines
                    </span>
                </div>
                <!-- Breadcrumb's -->
                <x-admin::breadcrumbs name="leads" />
                
                {!! view_render_event('admin.leads.index.header.left.after') !!}
            </div>

            <div class="flex items-center gap-x-2.5">
                {!! view_render_event('admin.leads.index.header.right.before') !!}
                
                <!-- Upload File for Lead Creation -->
                @if(core()->getConfigData('general.magic_ai.doc_generation.enabled'))
                    @include('admin::leads.index.upload')
                @endif

                <!-- Export Modal -->
                <x-admin::datagrid.export :src="route('admin.leads.index')" />

                <!-- Create button for Leads -->
                @if (bouncer()->hasPermission('leads.create'))
                    <a
                        href="{{ route('admin.leads.import') }}"
                        class="secondary-button"
                    >
                        @lang('admin::app.leads.index.import')
                    </a>

                    <a
                        href="{{ route('admin.leads.create', request()->query()) }}"
                        class="primary-button"
                    >
                        @lang('admin::app.leads.index.create-btn')
                    </a>
                @endif

                {!! view_render_event('admin.leads.index.header.right.after') !!}
            </div>
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
