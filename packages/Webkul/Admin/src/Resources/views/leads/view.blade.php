<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.leads.view.title', ['title' => strip_tags($lead->title)])
    </x-slot>

    <!-- Content -->
    <div class="max-w-7xl mx-auto space-y-6 pt-4">

        <!-- Top Stages Navigation -->
        {!! view_render_event('admin.leads.view.stages.before', ['lead' => $lead]) !!}
        <div class="w-full">
            @include ('admin::leads.view.stages')
        </div>
        {!! view_render_event('admin.leads.view.stages.after', ['lead' => $lead]) !!}

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            <!-- Left Panel (Sidebar) -->
            {!! view_render_event('admin.leads.view.left.before', ['lead' => $lead]) !!}

            <div class="lg:col-span-4 space-y-6">
                
                <!-- Lead Information & Actions Card -->
                <div class="flex flex-col gap-6 rounded-2xl border border-slate-200/80 bg-white shadow-2xs p-6">
                    <!-- Breadcrumbs -->
                    <div class="flex items-center justify-between">
                        <x-admin::breadcrumbs
                            name="leads.view"
                            :entity="$lead"
                        />
                        <div class="flex items-center gap-2 text-slate-400">
                            <span class="icon-tag text-xl cursor-pointer hover:text-slate-900 transition"></span>
                            <span class="icon-more text-2xl cursor-pointer hover:text-slate-900 transition"></span>
                        </div>
                    </div>

                    <div class="mb-2">
                        @if (($days = $lead->rotten_days) > 0)
                            @php
                                $lead->tags->prepend([
                                    'name' => '<span class="icon-rotten text-base"></span>' . trans('admin::app.leads.view.rotten-days', ['days' => $days]),
                                    'color' => '#FEE2E2'
                                ]);
                            @endphp
                        @endif

                        {!! view_render_event('admin.leads.view.tags.before', ['lead' => $lead]) !!}

                        <!-- Tags -->
                        <x-admin::tags
                            :attach-endpoint="route('admin.leads.tags.attach', $lead->id)"
                            :detach-endpoint="route('admin.leads.tags.detach', $lead->id)"
                            :added-tags="$lead->tags"
                        />

                        {!! view_render_event('admin.leads.view.tags.after', ['lead' => $lead]) !!}
                    </div>

                    {!! view_render_event('admin.leads.view.title.before', ['lead' => $lead]) !!}

                    <!-- Profile Header -->
                    <div class="flex items-center gap-4">
                        <x-admin::avatar :name="$lead->person?->name ?? 'Unknown'" class="!w-14 !h-14 text-xl font-bold" />
                        <div class="flex flex-col">
                            <h1 class="text-2xl font-black tracking-tight text-slate-900 leading-tight">
                                {{ $lead->person?->name ?? 'Unknown Contact' }}
                            </h1>
                            @if($lead->person?->job_title || $lead->person?->organization)
                                <div class="text-sm font-medium text-slate-500 mt-0.5">
                                    {{ $lead->person?->job_title }}
                                    @if($lead->person?->job_title && $lead->person?->organization) at @endif
                                    {{ $lead->person?->organization?->name }}
                                </div>
                            @elseif(collect($lead->person?->emails ?? [])->pluck('value')->filter()->first())
                                <div class="text-sm font-medium text-slate-500 mt-0.5">
                                    {{ collect($lead->person?->emails ?? [])->pluck('value')->filter()->first() }}
                                </div>
                            @endif
                        </div>
                    </div>

                    {!! view_render_event('admin.leads.view.title.after', ['lead' => $lead]) !!}

                    <!-- Quick Action Bar -->
                    @include('admin::leads.view.action-bar')

                    <!-- Activity Actions -->
                    <div class="grid grid-cols-3 gap-3">
                        {!! view_render_event('admin.leads.view.actions.before', ['lead' => $lead]) !!}

                        @if (bouncer()->hasPermission('activities.create'))
                            <x-admin::activities.actions.file
                                :entity="$lead"
                                entity-control-name="lead_id"
                            />
                            <x-admin::activities.actions.note
                                :entity="$lead"
                                entity-control-name="lead_id"
                            />
                            <x-admin::activities.actions.activity
                                :entity="$lead"
                                entity-control-name="lead_id"
                            />
                        @endif

                        {!! view_render_event('admin.leads.view.actions.after', ['lead' => $lead]) !!}
                    </div>
                </div>

                <!-- Lead Attributes Card -->
                <div class="rounded-2xl border border-slate-200/80 bg-white shadow-2xs">
                    @include ('admin::leads.view.attributes')
                </div>

                <!-- Contact Person Card -->
                <div class="rounded-2xl border border-slate-200/80 bg-white shadow-2xs">
                    @include ('admin::leads.view.person')
                </div>
            </div>

            {!! view_render_event('admin.leads.view.left.after', ['lead' => $lead]) !!}

            <!-- Right Panel -->
            {!! view_render_event('admin.leads.view.right.before', ['lead' => $lead]) !!}

            <div class="lg:col-span-8 flex flex-col gap-6">
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
