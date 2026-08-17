{!! view_render_event('admin.leads.view.attributes.before', ['lead' => $lead]) !!}

@php
    $allAttributes = app('Webkul\Attribute\Repositories\AttributeRepository')->findWhere([
        'entity_type' => 'leads',
        ['code', 'NOTIN', ['title', 'description', 'lead_pipeline_id', 'lead_pipeline_stage_id']]
    ])->sortBy('sort_order');

    // Fetch Moldable Field Groups if present
    $moldableGroups = collect();
    try {
        if (class_exists(\Webkul\Moldable\Models\FieldGroup::class)) {
            $moldableGroups = \Webkul\Moldable\Models\FieldGroup::with('groupAttributes.attribute')
                ->where('entity_type', 'leads')
                ->orderBy('sort_order')
                ->get();
        }
    } catch (\Throwable $e) {
        $moldableGroups = collect();
    }

    $groupedAttributes = [];
    $assignedAttributeIds = [];

    // 1. Moldable configured groups
    foreach ($moldableGroups as $mGroup) {
        $groupAttrs = $mGroup->groupAttributes->map(fn($ga) => $ga->attribute)->filter();
        if ($groupAttrs->isNotEmpty()) {
            $groupedAttributes[$mGroup->name] = [
                'name' => $mGroup->name,
                'slug' => $mGroup->slug ?: \Illuminate\Support\Str::slug($mGroup->name),
                'attributes' => $groupAttrs,
                'is_custom' => true,
            ];
            foreach ($groupAttrs as $a) {
                $assignedAttributeIds[] = $a->id;
            }
        }
    }

    // 2. Key Details group (Value, Expected Close Date)
    $keyCodes = ['lead_value', 'expected_close_date'];
    $keyAttributes = $allAttributes->filter(function ($attr) use ($keyCodes, $assignedAttributeIds) {
        return ! in_array($attr->id, $assignedAttributeIds) && in_array($attr->code, $keyCodes);
    });

    // 3. Sales Information group (Type, Source, Owner)
    $salesCodes = ['lead_type_id', 'lead_source_id', 'user_id'];
    $salesAttributes = $allAttributes->filter(function ($attr) use ($salesCodes, $assignedAttributeIds) {
        return ! in_array($attr->id, $assignedAttributeIds) && in_array($attr->code, $salesCodes);
    });

    foreach ($keyAttributes as $a) {
        $assignedAttributeIds[] = $a->id;
    }
    foreach ($salesAttributes as $a) {
        $assignedAttributeIds[] = $a->id;
    }

    $standardGroups = [];
    if ($keyAttributes->isNotEmpty()) {
        $standardGroups['Key Details'] = [
            'name' => 'Key Details',
            'slug' => 'key-details',
            'attributes' => $keyAttributes,
            'is_custom' => false,
        ];
    }

    if ($salesAttributes->isNotEmpty()) {
        $standardGroups['Sales Information'] = [
            'name' => 'Sales Information',
            'slug' => 'sales-information',
            'attributes' => $salesAttributes,
            'is_custom' => false,
        ];
    }

    $groupedAttributes = array_merge($standardGroups, $groupedAttributes);

    // 4. Remaining Custom Fields
    $remainingCustom = $allAttributes->reject(fn($attr) => in_array($attr->id, $assignedAttributeIds));

    if ($remainingCustom->isNotEmpty()) {
        $groupedAttributes['Custom Fields'] = [
            'name' => 'Custom Fields',
            'slug' => 'custom-fields',
            'attributes' => $remainingCustom,
            'is_custom' => true,
        ];
    }
@endphp

<div class="flex w-full flex-col gap-4 p-5 sm:p-6">
    <x-admin::accordion class="select-none !border-none">
        <x-slot:header class="!p-0">
            <div class="flex w-full items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">
                        @lang('admin::app.leads.view.attributes.title')
                    </h4>
                    <span class="rounded-full bg-slate-100 dark:bg-gray-800 px-2 py-0.5 text-[10px] font-bold text-slate-600 dark:text-slate-300">
                        {{ $allAttributes->count() }}
                    </span>
                </div>

                @if (bouncer()->hasPermission('leads.edit'))
                    <a
                        href="{{ route('admin.leads.edit', $lead->id) }}"
                        class="flex items-center justify-center rounded-lg p-1.5 text-slate-400 hover:bg-slate-50 hover:text-slate-900 transition dark:hover:bg-gray-800 dark:hover:text-white"
                        target="_blank"
                        title="@lang('admin::app.leads.view.edit-btn')"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    </a>
                @endif
            </div>
        </x-slot>

        <x-slot:content class="mt-4 !px-0 !pb-0">
            {!! view_render_event('admin.leads.view.attributes.form_controls.before', ['lead' => $lead]) !!}

            <x-admin::form
                v-slot="{ meta, errors, handleSubmit }"
                as="div"
                ref="modalForm"
            >
                <form @submit="handleSubmit($event, () => {})">
                    {!! view_render_event('admin.leads.view.attributes.form_controls.attributes.view.before', ['lead' => $lead]) !!}

                    @if ($lead->description)
                        <div class="mb-4 rounded-xl bg-slate-50/70 dark:bg-gray-800/40 p-3 border border-slate-100 dark:border-gray-800">
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1">
                                Description
                            </span>
                            <p class="text-xs text-slate-700 dark:text-slate-300 whitespace-pre-wrap leading-relaxed max-h-32 overflow-y-auto">
                                {{ $lead->description }}
                            </p>
                        </div>
                    @endif

                    <!-- Grouped Property Grids Component -->
                    <v-lead-grouped-attributes>
                        @foreach ($groupedAttributes as $groupKey => $groupData)
                            <div class="mb-5 last:mb-0" data-group-slug="{{ $groupData['slug'] }}">
                                <!-- Group Subheading -->
                                <div class="flex items-center justify-between pb-2 mb-3 border-b border-slate-100 dark:border-gray-800">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">
                                            {{ $groupData['name'] }}
                                        </span>
                                        <span class="text-[10px] font-semibold text-slate-400 dark:text-slate-500">
                                            ({{ $groupData['attributes']->count() }})
                                        </span>
                                    </div>
                                </div>

                                <!-- 2-Column Compact Property Grid -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                    @foreach ($groupData['attributes'] as $index => $attribute)
                                        @if (view()->exists($typeView = 'admin::components.attributes.view.' . $attribute->type))
                                            <div
                                                class="flex flex-col min-w-0 bg-slate-50/60 dark:bg-gray-800/30 p-2.5 rounded-xl border border-slate-100/90 dark:border-gray-800/80 transition hover:bg-slate-50 dark:hover:bg-gray-800/60 @if($groupData['is_custom'] && $index >= 8) group-extra-field hidden @endif"
                                            >
                                                <!-- Small Label -->
                                                <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-0.5 truncate" title="{{ $attribute->name }}">
                                                    {{ $attribute->name }}
                                                    @if ($attribute->is_required)
                                                        <span class="text-red-500">*</span>
                                                    @endif
                                                </span>

                                                <!-- Value / Inline Control -->
                                                <div class="min-w-0 break-words font-semibold text-xs text-slate-800 dark:text-slate-200">
                                                    @include ($typeView, [
                                                        'attribute' => $attribute,
                                                        'value' => isset($lead) ? $lead[$attribute->code] : null,
                                                        'allowEdit' => true,
                                                        'url' => route('admin.leads.attributes.update', $lead->id),
                                                    ])
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach

                                    <!-- Injected Metadata Fields -->
                                    @if ($groupData['name'] === 'Key Details')
                                        <!-- Priority -->
                                        <div class="flex flex-col min-w-0 bg-slate-50/60 dark:bg-gray-800/30 p-2.5 rounded-xl border border-slate-100/90 dark:border-gray-800/80 transition hover:bg-slate-50 dark:hover:bg-gray-800/60">
                                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-0.5 truncate">
                                                Priority
                                            </span>
                                            <div class="min-w-0 break-words font-semibold text-xs text-slate-800 dark:text-slate-200">
                                                <form action="{{ route('admin.leads.attributes.update', $lead->id) }}" method="POST" onchange="this.submit()">
                                                    @csrf
                                                    @method('PUT')
                                                    <select name="priority" class="bg-transparent border-none p-0 text-xs font-bold text-slate-700 dark:text-slate-300 focus:ring-0 cursor-pointer">
                                                        <option value="low" {{ $lead->priority === 'low' ? 'selected' : '' }}>Low</option>
                                                        <option value="medium" {{ $lead->priority === 'medium' ? 'selected' : '' }}>Medium</option>
                                                        <option value="high" {{ $lead->priority === 'high' ? 'selected' : '' }}>High</option>
                                                        <option value="urgent" {{ $lead->priority === 'urgent' ? 'selected' : '' }}>Urgent</option>
                                                    </select>
                                                </form>
                                            </div>
                                        </div>

                                        <!-- Location -->
                                        <div class="flex flex-col min-w-0 bg-slate-50/60 dark:bg-gray-800/30 p-2.5 rounded-xl border border-slate-100/90 dark:border-gray-800/80 transition hover:bg-slate-50 dark:hover:bg-gray-800/60">
                                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-0.5 truncate">
                                                Location
                                            </span>
                                            <div class="min-w-0 break-words font-semibold text-xs text-slate-800 dark:text-slate-200 py-1">
                                                <form action="{{ route('admin.leads.attributes.update', $lead->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="text" name="location" value="{{ $lead->location }}" onblur="this.form.submit()" onkeydown="if(event.key==='Enter') this.form.submit()" class="w-full bg-transparent border-none p-0 text-xs font-bold text-slate-700 dark:text-slate-300 focus:ring-0" placeholder="Set location..." />
                                                </form>
                                            </div>
                                        </div>

                                        <!-- Lead Score -->
                                        <div class="flex flex-col min-w-0 bg-slate-50/60 dark:bg-gray-800/30 p-2.5 rounded-xl border border-slate-100/90 dark:border-gray-800/80 transition hover:bg-slate-50 dark:hover:bg-gray-800/60">
                                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-0.5 truncate">
                                                Lead Score
                                            </span>
                                            <div class="min-w-0 break-words font-semibold text-xs text-slate-800 dark:text-slate-200 py-1">
                                                <span class="inline-flex items-center rounded-full bg-amber-50 text-amber-700 px-2 py-0.5 text-xs font-bold">{{ $lead->lead_score ?? 0 }} / 100</span>
                                            </div>
                                        </div>

                                        <!-- Qualification Status -->
                                        <div class="flex flex-col min-w-0 bg-slate-50/60 dark:bg-gray-800/30 p-2.5 rounded-xl border border-slate-100/90 dark:border-gray-800/80 transition hover:bg-slate-50 dark:hover:bg-gray-800/60">
                                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-0.5 truncate">
                                                Qualification
                                            </span>
                                            <div class="min-w-0 break-words font-semibold text-xs text-slate-800 dark:text-slate-200">
                                                <form action="{{ route('admin.leads.attributes.update', $lead->id) }}" method="POST" onchange="this.submit()">
                                                    @csrf
                                                    @method('PUT')
                                                    <select name="is_qualified" class="bg-transparent border-none p-0 text-xs font-bold text-slate-700 dark:text-slate-300 focus:ring-0 cursor-pointer">
                                                        <option value="0" {{ !$lead->is_qualified ? 'selected' : '' }}>Unqualified</option>
                                                        <option value="1" {{ $lead->is_qualified ? 'selected' : '' }}>Qualified</option>
                                                    </select>
                                                </form>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($groupData['name'] === 'Sales Information')
                                        <!-- UTM Source -->
                                        <div class="flex flex-col min-w-0 bg-slate-50/60 dark:bg-gray-800/30 p-2.5 rounded-xl border border-slate-100/90 dark:border-gray-800/80 transition hover:bg-slate-50 dark:hover:bg-gray-800/60">
                                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-0.5 truncate">
                                                UTM Source
                                            </span>
                                            <div class="min-w-0 break-words font-semibold text-xs text-slate-800 dark:text-slate-200 py-1">
                                                <form action="{{ route('admin.leads.attributes.update', $lead->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="text" name="utm_source" value="{{ $lead->utm_source }}" onblur="this.form.submit()" onkeydown="if(event.key==='Enter') this.form.submit()" class="w-full bg-transparent border-none p-0 text-xs font-bold text-slate-700 dark:text-slate-300 focus:ring-0" placeholder="utm_source" />
                                                </form>
                                            </div>
                                        </div>

                                        <!-- UTM Medium -->
                                        <div class="flex flex-col min-w-0 bg-slate-50/60 dark:bg-gray-800/30 p-2.5 rounded-xl border border-slate-100/90 dark:border-gray-800/80 transition hover:bg-slate-50 dark:hover:bg-gray-800/60">
                                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-0.5 truncate">
                                                UTM Medium
                                            </span>
                                            <div class="min-w-0 break-words font-semibold text-xs text-slate-800 dark:text-slate-200 py-1">
                                                <form action="{{ route('admin.leads.attributes.update', $lead->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="text" name="utm_medium" value="{{ $lead->utm_medium }}" onblur="this.form.submit()" onkeydown="if(event.key==='Enter') this.form.submit()" class="w-full bg-transparent border-none p-0 text-xs font-bold text-slate-700 dark:text-slate-300 focus:ring-0" placeholder="utm_medium" />
                                                </form>
                                            </div>
                                        </div>

                                        <!-- UTM Campaign -->
                                        <div class="flex flex-col min-w-0 bg-slate-50/60 dark:bg-gray-800/30 p-2.5 rounded-xl border border-slate-100/90 dark:border-gray-800/80 transition hover:bg-slate-50 dark:hover:bg-gray-800/60">
                                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-0.5 truncate">
                                                UTM Campaign
                                            </span>
                                            <div class="min-w-0 break-words font-semibold text-xs text-slate-800 dark:text-slate-200 py-1">
                                                <form action="{{ route('admin.leads.attributes.update', $lead->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="text" name="utm_campaign" value="{{ $lead->utm_campaign }}" onblur="this.form.submit()" onkeydown="if(event.key==='Enter') this.form.submit()" class="w-full bg-transparent border-none p-0 text-xs font-bold text-slate-700 dark:text-slate-300 focus:ring-0" placeholder="utm_campaign" />
                                                </form>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <!-- Show More / Show Less for Large Field Sets -->
                                @if ($groupData['is_custom'] && $groupData['attributes']->count() > 8)
                                    <div class="mt-3 flex justify-center">
                                        <button
                                            type="button"
                                            onclick="toggleGroupFields(this)"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-[11px] font-bold text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                                            data-expanded="false"
                                            data-more-text="Show {{ $groupData['attributes']->count() - 8 }} more fields"
                                            data-less-text="Show fewer fields"
                                        >
                                            <span class="toggle-label">Show {{ $groupData['attributes']->count() - 8 }} more fields</span>
                                            <svg class="w-3.5 h-3.5 transform transition-transform duration-200 toggle-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </v-lead-grouped-attributes>

                    {!! view_render_event('admin.leads.view.attributes.form_controls.attributes.view.after', ['lead' => $lead]) !!}
                </form>
            </x-admin::form>

            {!! view_render_event('admin.leads.view.attributes.form_controls.after', ['lead' => $lead]) !!}
        </x-slot>
    </x-admin::accordion>
</div>

{!! view_render_event('admin.leads.view.attributes.after', ['lead' => $lead]) !!}

<script>
    function toggleGroupFields(btn) {
        const groupContainer = btn.closest('[data-group-slug]');
        if (!groupContainer) return;

        const extraFields = groupContainer.querySelectorAll('.group-extra-field');
        const isExpanded = btn.getAttribute('data-expanded') === 'true';
        const label = btn.querySelector('.toggle-label');
        const chevron = btn.querySelector('.toggle-chevron');

        if (isExpanded) {
            extraFields.forEach(el => el.classList.add('hidden'));
            btn.setAttribute('data-expanded', 'false');
            if (label) label.textContent = btn.getAttribute('data-more-text');
            if (chevron) chevron.classList.remove('rotate-180');
        } else {
            extraFields.forEach(el => el.classList.remove('hidden'));
            btn.setAttribute('data-expanded', 'true');
            if (label) label.textContent = btn.getAttribute('data-less-text');
            if (chevron) chevron.classList.add('rotate-180');
        }
    }
</script>
