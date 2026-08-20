<x-admin::layouts>
    <x-slot:title>
        Nurturing Leads
    </x-slot>

    <div class="flex w-full flex-col gap-4">
        <div class="scroll-reactive-sticky sticky top-0 z-[1000] flex items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div class="flex flex-col gap-2">
                <div class="flex items-center gap-3">
                    <h1 class="text-xl font-bold dark:text-white">
                        Nurturing Leads
                    </h1>
                </div>
            </div>

            <div class="flex items-center gap-x-2.5">
                <x-admin::datagrid.export :src="route('admin.leads.nurturing')" />
            </div>
        </div>

        <div class="[&>*>*>*.toolbarRight]:max-lg:w-full [&>*>*>*.toolbarRight]:max-lg:justify-between [&>*>*>*.toolbarRight]:max-md:gap-y-2 [&>*>*>*.toolbarRight]:max-md:flex-wrap [&>*>*:nth-child(1)]:max-lg:!flex-wrap">
            <x-admin::datagrid :src="route('admin.leads.nurturing')" />
        </div>

        {!! view_render_event('admin.leads.index.content.after') !!}
    </div>
</x-admin::layouts>
