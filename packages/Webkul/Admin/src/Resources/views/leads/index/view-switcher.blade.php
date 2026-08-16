{!! view_render_event('admin.leads.index.view_switcher.before') !!}

<div class="flex items-center gap-4 max-md:w-full max-md:!justify-between">
    <x-admin::dropdown>
        <x-slot:toggle>
            {!! view_render_event('admin.leads.index.view_switcher.pipeline.button.before') !!}

            <button
                type="button"
                class="flex cursor-pointer appearance-none items-center justify-between gap-x-3 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-800 shadow-sm transition hover:bg-slate-50 focus:border-slate-300"
            >
                <span class="whitespace-nowrap">
                    {{ $pipeline->name }}
                </span>
                
                <span class="icon-down-arrow text-lg"></span>
            </button>

            {!! view_render_event('admin.leads.index.view_switcher.pipeline.button.after') !!}
        </x-slot>

        <x-slot:content class="!p-0">
            {!! view_render_event('admin.leads.index.view_switcher.pipeline.content.header.before') !!}

            <!-- Header -->
            <div class="flex items-center justify-between px-4 py-3">
                <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">
                    @lang('admin::app.leads.index.view-switcher.all-pipelines')
                </span>
            </div>

            {!! view_render_event('admin.leads.index.view_switcher.pipeline.content.header.after') !!}
            
            <!-- Pipeline Links -->
            @foreach (app('Webkul\Lead\Repositories\PipelineRepository')->all() as $tempPipeline)
                {!! view_render_event('admin.leads.index.view_switcher.pipeline.content.before', ['tempPipeline' => $tempPipeline]) !!}

                <a
                    href="{{ route('admin.leads.index', [
                        'pipeline_id' => $tempPipeline->id,
                        'view_type' => request('view_type')
                    ]) }}"
                    class="block px-4 py-2 text-xs font-bold text-slate-600 transition hover:bg-slate-50 hover:text-slate-900 {{ $pipeline->id == $tempPipeline->id ? 'bg-slate-50 text-slate-900' : '' }}"
                >
                    {{ $tempPipeline->name }}
                </a>

                {!! view_render_event('admin.leads.index.view_switcher.pipeline.content.after', ['tempPipeline' => $tempPipeline]) !!}
            @endforeach

            {!! view_render_event('admin.leads.index.view_switcher.pipeline.content.footer.before') !!}

            <!-- Footer -->
            <a
                href="{{ route('admin.settings.pipelines.create') }}"
                target="_blank"
                class="flex items-center justify-between border-t border-slate-200 px-4 py-3 text-xs font-bold text-slate-900 hover:bg-slate-50 transition"
            >
                <span>                    
                    @lang('admin::app.leads.index.view-switcher.create-new-pipeline')
                </span>
            </a>

            {!! view_render_event('admin.leads.index.view_switcher.pipeline.content.footer.after') !!}
        </x-slot>
    </x-admin::dropdown>

</div>

{!! view_render_event('admin.leads.index.view_switcher.after') !!}