<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.leads.view.title', ['title' => strip_tags($lead->title)])
    </x-slot>

    <!-- Content -->
    <div class="max-w-7xl mx-auto space-y-4 pt-4">

        <!-- Lead Summary Header -->
        {!! view_render_event('admin.leads.view.header.before', ['lead' => $lead]) !!}

        @include ('admin::leads.view.header')

        {!! view_render_event('admin.leads.view.header.after', ['lead' => $lead]) !!}

        <!-- Top Stages Navigation -->
        {!! view_render_event('admin.leads.view.stages.before', ['lead' => $lead]) !!}
        <div class="w-full">
            @include ('admin::leads.view.stages')
        </div>
        {!! view_render_event('admin.leads.view.stages.after', ['lead' => $lead]) !!}

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            <!-- Left Panel (Sidebar ~35% width, Sticky on Desktop) -->
            {!! view_render_event('admin.leads.view.left.before', ['lead' => $lead]) !!}

            <div class="lg:col-span-4 lg:sticky lg:top-4 self-start space-y-5">
                <!-- Lead Attributes Card -->
                <div class="rounded-2xl border border-slate-200/80 bg-white shadow-2xs">
                    @include ('admin::leads.view.attributes')
                </div>
            </div>

            {!! view_render_event('admin.leads.view.left.after', ['lead' => $lead]) !!}

            <!-- Right Panel (Activity Timeline Workspace ~65% width) -->
            {!! view_render_event('admin.leads.view.right.before', ['lead' => $lead]) !!}

            <div class="lg:col-span-8 flex flex-col gap-5">
                <!-- Activities -->
                {!! view_render_event('admin.leads.view.activities.before', ['lead' => $lead]) !!}

                <x-admin::activities
                    :endpoint="route('admin.leads.activities.index', $lead->id)"
                    :email-detach-endpoint="route('admin.leads.emails.detach', $lead->id)"
                    :activeType="request()->query('tab') ?? 'timeline'"
                    :extra-types="[
                        ['name' => 'timeline', 'label' => 'Timeline'],
                    ]"
                >
                    <!-- Timeline Tab -->
                    <x-slot:timeline>
                        <div class="p-6 animate-[on-fade_0.5s_ease-in-out]">
                            @include('admin::leads.view.timeline')
                        </div>
                    </x-slot:timeline>


                </x-admin::activities>

                {!! view_render_event('admin.leads.view.activities.after', ['lead' => $lead]) !!}
            </div>

            {!! view_render_event('admin.leads.view.right.after', ['lead' => $lead]) !!}
        </div>
    </div>
</x-admin::layouts>
