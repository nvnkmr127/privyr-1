@php
    $phone = collect($lead->person?->contact_numbers ?? [])->pluck('value')->filter()->first();
    $cleanPhone = preg_replace('/[^0-9]/', '', $phone ?? '');
    $email = collect($lead->person?->emails ?? [])->pluck('value')->filter()->first();
    $personName = $lead->person?->name ?? 'there';
    $leadTitle = $lead->title ?? $personName;
    $leadValue = $lead->lead_value ? core()->formatBasePrice($lead->lead_value) : null;
    $pipelineName = $lead->pipeline?->name ?? 'Default Pipeline';
    $stageName = $lead->stage?->name ?? 'New';
    $sourceName = $lead->source?->name ?? 'Direct';
    $ownerName = $lead->user?->name ?? 'Unassigned';

    $waGreeting = rawurlencode("Hi {$personName}, thanks for reaching out regarding {$leadTitle}. How can we assist you today?");
    $waBrochure = rawurlencode("Hi {$personName}, here is our latest brochure/catalogue: " . route('trackable.document', ['lead' => $lead->id, 'hash' => md5($lead->id . config('app.key'))]));
    $waFollowup = rawurlencode("Hi {$personName}, just following up on your inquiry about {$leadTitle}. Let us know if you have any questions!");
@endphp

<div class="rounded-2xl border border-slate-200/80 bg-white p-4 sm:p-5 shadow-2xs dark:border-gray-800 dark:bg-gray-900">
    <!-- Top Meta Row: Breadcrumbs, Tags, Rotten indicator & Edit Action -->
    <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-slate-100 dark:border-gray-800">
        <div class="flex flex-wrap items-center gap-3 min-w-0">
            <x-admin::breadcrumbs
                name="leads.view"
                :entity="$lead"
            />

            @if (($days = $lead->rotten_days) > 0)
                @php
                    $lead->tags->prepend([
                        'name' => '<span class="icon-rotten text-base"></span>' . trans('admin::app.leads.view.rotten-days', ['days' => $days]),
                        'color' => '#FEE2E2'
                    ]);
                @endphp
            @endif

            {!! view_render_event('admin.leads.view.tags.before', ['lead' => $lead]) !!}

            <x-admin::tags
                :attach-endpoint="route('admin.leads.tags.attach', $lead->id)"
                :detach-endpoint="route('admin.leads.tags.detach', $lead->id)"
                :added-tags="$lead->tags"
            />

            {!! view_render_event('admin.leads.view.tags.after', ['lead' => $lead]) !!}
        </div>

        <div class="flex items-center gap-2">
            <!-- Qualify / Disqualify Toggle -->
            <x-admin::form
                :action="route('admin.leads.attributes.update', $lead->id)"
                method="PUT"
                class="inline-flex"
            >
                <input type="hidden" name="qualification_status" value="{{ $lead->qualification_status === 'qualified' ? 'unqualified' : 'qualified' }}" />
                <button
                    type="submit"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-semibold transition {{ $lead->qualification_status === 'qualified' ? 'bg-emerald-50 border-emerald-200 text-emerald-700 hover:bg-emerald-100' : 'bg-white text-slate-700 hover:bg-slate-50' }}"
                >
                    <span>{{ $lead->qualification_status === 'qualified' ? 'Qualified ✓' : 'Qualify Lead' }}</span>
                </button>
            </x-admin::form>

            <!-- Stop Nurture -->
            @if ($lead->active_nurture_status)
                <x-admin::form
                    :action="route('admin.leads.nurture.stop', $lead->id)"
                    method="POST"
                    class="inline-flex"
                >
                    <button
                        type="submit"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700 hover:bg-red-100 transition dark:border-red-800 dark:bg-red-950/50 dark:text-red-400 dark:hover:bg-red-900/50"
                        title="Stop active nurture sequence"
                    >
                        <span>Stop Nurture</span>
                    </button>
                </x-admin::form>
            @endif

            <!-- Duplicate Lead -->
            <a
                href="{{ route('admin.leads.duplicate', $lead->id) }}"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                title="Duplicate this Lead"
            >
                <span>Duplicate</span>
            </a>


            <!-- Assign Lead trigger -->
            @if (bouncer()->hasPermission('leads.edit'))
                <button
                    type="button"
                    onclick="document.getElementById('assignLeadModal').classList.remove('hidden')"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition dark:border-gray-700 dark:bg-gray-800"
                >
                    <span>Assign</span>
                </button>
            @endif

            <!-- Merge Lead trigger -->
            <button
                type="button"
                onclick="document.getElementById('mergeLeadModal').classList.remove('hidden')"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition dark:border-gray-700 dark:bg-gray-800"
            >
                <span>Merge</span>
            </button>

            @if (bouncer()->hasPermission('leads.edit'))
                <a
                    href="{{ route('admin.leads.edit', $lead->id) }}"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                    title="@lang('admin::app.leads.view.edit-btn')"
                >
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    <span>Edit Lead</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Main Summary Bar: Avatar, Title, Contact & Quick Actions -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 py-3">
        <!-- Lead Identity (Avatar + Name + Contact Summary) -->
        <div class="flex items-center gap-3.5 min-w-0">
            <x-admin::avatar
                :name="$lead->person?->name ?? $leadTitle"
                class="!w-11 !h-11 text-base font-bold shrink-0 rounded-xl"
            />

            <div class="flex flex-col min-w-0">
                <div class="flex items-center gap-2">
                    <h1 class="text-lg font-bold text-slate-900 dark:text-white truncate max-w-md sm:max-w-xl" title="{{ $leadTitle }}">
                        {{ $leadTitle }}
                    </h1>
                    @if ($leadValue)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60 dark:bg-emerald-950 dark:text-emerald-300 dark:border-emerald-800 shrink-0">
                            {{ $leadValue }}
                        </span>
                    @endif

                    <!-- Health State Badge -->
                    @php
                        $healthState = $lead->health_state;
                        $healthColor = match($healthState) {
                            'hot' => 'bg-orange-50 text-orange-700 border-orange-200/60 dark:bg-orange-950 dark:text-orange-300 dark:border-orange-800',
                            'warm' => 'bg-amber-50 text-amber-700 border-amber-200/60 dark:bg-amber-950 dark:text-amber-300 dark:border-amber-800',
                            'cold' => 'bg-blue-50 text-blue-700 border-blue-200/60 dark:bg-blue-950 dark:text-blue-300 dark:border-blue-800',
                            'at_risk' => 'bg-rose-50 text-rose-700 border-rose-200/60 dark:bg-rose-950 dark:text-rose-300 dark:border-rose-800',
                            'stale' => 'bg-slate-100 text-slate-700 border-slate-300/60 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700',
                            default => 'bg-slate-50 text-slate-700 border-slate-200/60',
                        };
                        $healthLabels = [
                            'hot' => 'Hot', 'warm' => 'Warm', 'cold' => 'Cold', 'at_risk' => 'At Risk', 'stale' => 'Stale'
                        ];
                    @endphp
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold border shrink-0 {{ $healthColor }}">
                        <i class="fa-solid fa-heart-pulse mr-1"></i> {{ $healthLabels[$healthState] ?? 'Unknown' }}
                    </span>

                    <!-- Score Badge with Popover -->
                    <div class="relative group inline-block shrink-0">
                        <span class="inline-flex cursor-help items-center px-2 py-0.5 rounded-md text-xs font-bold bg-slate-50 text-slate-700 border border-slate-200/60 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700">
                            Score: {{ $lead->lead_score ?? 0 }}
                        </span>
                        
                        <!-- Popover -->
                        <div class="absolute left-0 top-full mt-2 w-64 rounded-lg bg-white p-3 shadow-lg ring-1 ring-slate-900/5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50 dark:bg-gray-800 dark:ring-white/10">
                            <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 dark:text-gray-400">Score Reasons</h4>
                            <div class="space-y-1">
                                @forelse($lead->scoreLogs as $log)
                                    <div class="flex justify-between text-sm">
                                        <span class="text-slate-700 dark:text-gray-300">{{ str_replace(strstr($log->reason, ' ('), '', $log->reason) }}</span>
                                        <span class="font-medium {{ $log->points > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                            {{ $log->points > 0 ? '+' : '' }}{{ $log->points }}
                                        </span>
                                    </div>
                                @empty
                                    <div class="text-sm text-slate-500 dark:text-gray-400 italic">No active scoring rules matched.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Qualification Badge -->
                    @if ($lead->qualification_status === 'qualified')
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200/60 dark:bg-indigo-950 dark:text-indigo-300 dark:border-indigo-800 shrink-0">
                            Qualified
                        </span>
                    @endif

                    <!-- Nurture Badge -->
                    @if ($nurtureStatus = $lead->active_nurture_status)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200/60 dark:bg-purple-950 dark:text-purple-300 dark:border-purple-800 shrink-0">
                            <i class="fa-solid fa-bolt mr-1"></i> {{ $nurtureStatus }}
                        </span>
                    @endif

                    <!-- FollowUp State Badge -->
                    @php
                        $fuState = $lead->follow_up_state;
                        $fuColor = match($fuState) {
                            'Overdue', 'Needs Attention', 'Stale' => 'bg-rose-50 text-rose-700 border-rose-200/60 dark:bg-rose-950 dark:text-rose-300 dark:border-rose-800',
                            'Due Today' => 'bg-blue-50 text-blue-700 border-blue-200/60 dark:bg-blue-950 dark:text-blue-300 dark:border-blue-800',
                            default => 'bg-slate-100 text-slate-700 border-slate-200/60 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
                        };
                    @endphp
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold border shrink-0 {{ $fuColor }}">
                        {{ $fuState }}
                    </span>
                </div>

                <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 text-xs text-slate-500 mt-0.5 min-w-0">
                    @if ($lead->person)
                        <span class="font-semibold text-slate-700 dark:text-slate-300 truncate">
                            {{ $lead->person->name }}
                        </span>
                        @if ($lead->person->job_title || $lead->person->organization)
                            <span class="text-slate-300 dark:text-slate-600">•</span>
                            <span class="truncate">
                                {{ $lead->person->job_title }}
                                @if ($lead->person->job_title && $lead->person->organization) at @endif
                                {{ $lead->person->organization?->name }}
                            </span>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Action Triggers (WhatsApp, Call, SMS, Brochure, Note, Activity, File) -->
        <div class="flex flex-wrap items-center gap-1.5 shrink-0">
            {!! view_render_event('admin.leads.view.actions.before', ['lead' => $lead]) !!}

            @if ($lead->next_action)
                <!-- Complete Follow-up -->
                <x-admin::form
                    :action="route('admin.leads.follow_up.complete', $lead->id)"
                    method="POST"
                    class="inline-flex"
                >
                    <button
                        type="submit"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-2.5 py-1.5 text-xs font-bold text-emerald-700 hover:bg-emerald-100 transition dark:bg-emerald-950/50 dark:border-emerald-800 dark:text-emerald-300"
                        title="Complete active follow-up action"
                    >
                        <i class="fa-solid fa-check"></i>
                        <span>Complete Action</span>
                    </button>
                </x-admin::form>

                <!-- Snooze Follow-up -->
                <x-admin::form
                    :action="route('admin.leads.follow_up.snooze', $lead->id)"
                    method="POST"
                    class="inline-flex"
                >
                    <input type="hidden" name="date" value="{{ \Carbon\Carbon::now()->addDay()->format('Y-m-d H:i:s') }}" />
                    <button
                        type="submit"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-100 transition dark:bg-slate-800 dark:border-slate-700 dark:text-slate-300"
                        title="Snooze for 1 day"
                    >
                        <i class="fa-regular fa-clock"></i>
                        <span>Snooze</span>
                    </button>
                </x-admin::form>
            @endif

            @if ($phone)
                <!-- One-Tap WhatsApp -->
                <a
                    href="https://wa.me/{{ $cleanPhone }}?text={{ $waGreeting }}"
                    target="_blank"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-green-50 border border-green-200 px-2.5 py-1.5 text-xs font-bold text-green-700 hover:bg-green-100 transition dark:bg-green-950/50 dark:border-green-800 dark:text-green-300"
                    title="Send WhatsApp Message"
                >
                    <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                    <span>WhatsApp</span>
                </a>

                <!-- One-Tap Call -->
                <a
                    href="tel:{{ $phone }}"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-blue-50 border border-blue-200 px-2.5 py-1.5 text-xs font-bold text-blue-700 hover:bg-blue-100 transition dark:bg-blue-950/50 dark:border-blue-800 dark:text-blue-300"
                    title="Call {{ $phone }}"
                >
                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                    <span>Call</span>
                </a>

                <!-- One-Tap SMS -->
                <a
                    href="sms:{{ $phone }}"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-purple-50 border border-purple-200 px-2.5 py-1.5 text-xs font-bold text-purple-700 hover:bg-purple-100 transition dark:bg-purple-950/50 dark:border-purple-800 dark:text-purple-300"
                    title="Send SMS"
                >
                    <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    <span>SMS</span>
                </a>

                <!-- Share Brochure -->
                <a
                    href="https://wa.me/{{ $cleanPhone }}?text={{ $waBrochure }}"
                    target="_blank"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-orange-50 border border-orange-200 px-2.5 py-1.5 text-xs font-bold text-orange-700 hover:bg-orange-100 transition dark:bg-orange-950/50 dark:border-orange-800 dark:text-orange-300"
                    title="Share Brochure via WhatsApp"
                >
                    <svg class="w-4 h-4 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    <span>Brochure</span>
                </a>
            @endif

            @if (bouncer()->hasPermission('activities.create'))
                <x-admin::activities.actions.note
                    :entity="$lead"
                    entity-control-name="lead_id"
                />
                <x-admin::activities.actions.activity
                    :entity="$lead"
                    entity-control-name="lead_id"
                />
                <x-admin::activities.actions.file
                    :entity="$lead"
                    entity-control-name="lead_id"
                />
            @endif

            {!! view_render_event('admin.leads.view.actions.after', ['lead' => $lead]) !!}
        </div>
    </div>

    <!-- Metadata Strip: Status, Pipeline, Source, Owner, Email, Phone -->
    <div class="mt-2 pt-3 border-t border-slate-100 dark:border-gray-800 flex flex-wrap items-center gap-y-2 gap-x-4 text-xs">
        <!-- Lead Status -->
        <div class="flex items-center gap-1.5">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Status</span>
            <span class="font-bold text-slate-800 dark:text-slate-200 bg-slate-100 dark:bg-gray-800 px-2 py-0.5 rounded text-[11px]">
                {{ $stageName }}
            </span>
        </div>

        <span class="text-slate-300 dark:text-slate-700">•</span>

        <!-- Pipeline -->
        <div class="flex items-center gap-1.5">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Pipeline</span>
            <span class="font-semibold text-slate-700 dark:text-slate-300">
                {{ $pipelineName }}
            </span>
        </div>

        <span class="text-slate-300 dark:text-slate-700">•</span>

        <!-- Source -->
        <div class="flex items-center gap-1.5">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Source</span>
            <span class="font-semibold text-slate-700 dark:text-slate-300">
                {{ $sourceName }}
            </span>
        </div>

        <span class="text-slate-300 dark:text-slate-700">•</span>

        <!-- Owner -->
        <div class="flex items-center gap-1.5">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Owner</span>
            <span class="font-semibold text-slate-700 dark:text-slate-300">
                {{ $ownerName }}
            </span>
        </div>

        <span class="text-slate-300 dark:text-slate-700">•</span>

        <!-- Email -->
        <div class="flex items-center gap-1.5">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Email</span>
            @if ($email)
                <a
                    href="mailto:{{ $email }}"
                    class="font-semibold text-slate-700 hover:text-slate-900 hover:underline dark:text-slate-300 dark:hover:text-white"
                >
                    {{ $email }}
                </a>
            @else
                <span class="text-slate-400 italic">--</span>
            @endif
        </div>

        <span class="text-slate-300 dark:text-slate-700">•</span>

        <!-- Phone -->
        <div class="flex items-center gap-1.5">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Phone</span>
            @if ($phone)
                <a
                    href="tel:{{ $phone }}"
                    class="font-semibold text-slate-700 hover:text-slate-900 hover:underline dark:text-slate-300 dark:hover:text-white"
                >
                    {{ $phone }}
                </a>
            @else
                <span class="text-slate-400 italic">--</span>
            @endif
        </div>
    </div>

    <!-- Quick WhatsApp Templates & Call Outcome Bar (if phone exists) -->
    @if ($phone)
        <div class="mt-2.5 pt-2.5 border-t border-slate-100/80 dark:border-gray-800/80 flex flex-wrap items-center justify-between gap-2 text-xs">
            <!-- Templates -->
            <div class="flex flex-wrap items-center gap-1.5">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mr-1">Templates:</span>
                <a
                    href="https://wa.me/{{ $cleanPhone }}?text={{ $waGreeting }}"
                    target="_blank"
                    class="rounded-md bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-600 border border-slate-200 hover:border-slate-400 hover:text-slate-900 transition dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300"
                >
                    Greeting
                </a>
                <a
                    href="https://wa.me/{{ $cleanPhone }}?text={{ $waBrochure }}"
                    target="_blank"
                    class="rounded-md bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-600 border border-slate-200 hover:border-slate-400 hover:text-slate-900 transition dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300"
                >
                    Share Brochure
                </a>
                <a
                    href="https://wa.me/{{ $cleanPhone }}?text={{ $waFollowup }}"
                    target="_blank"
                    class="rounded-md bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-600 border border-slate-200 hover:border-slate-400 hover:text-slate-900 transition dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300"
                >
                    Follow-up
                </a>
            </div>

            <!-- Call Outcome Loggers -->
            <div class="flex flex-wrap items-center gap-1.5">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mr-1">Log Call:</span>
                <button
                    type="button"
                    onclick="logOutcome('Interested')"
                    class="rounded-md bg-green-50 px-2.5 py-1 text-[11px] font-bold text-green-700 hover:bg-green-100 transition dark:bg-green-950/50 dark:text-green-300"
                >
                    + Interested
                </button>
                <button
                    type="button"
                    onclick="logOutcome('Call Back Later')"
                    class="rounded-md bg-amber-50 px-2.5 py-1 text-[11px] font-bold text-amber-700 hover:bg-amber-100 transition dark:bg-amber-950/50 dark:text-amber-300"
                >
                    + Call Back
                </button>
                <button
                    type="button"
                    onclick="logOutcome('Not Interested')"
                    class="rounded-md bg-red-50 px-2.5 py-1 text-[11px] font-bold text-red-700 hover:bg-red-100 transition dark:bg-red-950/50 dark:text-red-300"
                >
                    + Not Interested
                </button>
            </div>
        </div>
    @endif
</div>

<script>
    function logOutcome(outcome) {
        if (confirm('Log call outcome: "' + outcome + '"?')) {
            fetch('{{ route("admin.activities.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    type: 'note',
                    comment: 'Call Outcome logged: ' + outcome,
                    lead_id: {{ $lead->id }}
                })
            }).then(() => window.location.reload());
        }
    }
</script>

<!-- Merge Lead Modal -->
<div id="mergeLeadModal" class="fixed inset-0 z-[2000] hidden flex items-center justify-center bg-black/50">
    <div class="bg-white dark:bg-gray-900 rounded-xl max-w-md w-full p-6 shadow-xl border dark:border-gray-800">
        <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2">Merge Lead</h3>
        <p class="text-xs text-slate-500 mb-4">Select another lead to merge into this one. Activities, tags, quotes, and products will be merged, and the chosen target lead will be permanently deleted.</p>
        
        <form action="{{ route('admin.leads.merge.store', $lead->id) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-xs font-bold text-slate-600 mb-1.5">Target Lead to Merge</label>
                @php
                    $workspaceId = session()->get('current_workspace_id');
                    $otherLeads = app(\Webkul\Lead\Repositories\LeadRepository::class)
                        ->scopeQuery(function($q) use ($lead, $workspaceId) {
                            $q = $q->where('id', '!=', $lead->id);
                            if ($workspaceId) {
                                $q->where('workspace_id', $workspaceId);
                            }
                            return $q;
                        })->all();
                @endphp
                <select name="target_lead_id" required class="w-full rounded border border-gray-300 px-3 py-2 text-xs font-medium dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                    <option value="">-- Select Target Lead --</option>
                    @foreach ($otherLeads as $oLead)
                        <option value="{{ $oLead->id }}">{{ $oLead->title }} ({{ $oLead->person?->name ?? 'No Contact' }})</option>
                    @endforeach
                </select>
            </div>
            
            <div class="flex justify-end gap-2.5">
                <button type="button" onclick="document.getElementById('mergeLeadModal').classList.add('hidden')" class="px-3 py-1.5 rounded border text-xs font-medium bg-gray-50 text-gray-700 hover:bg-gray-100">Cancel</button>
                <button type="submit" class="px-3 py-1.5 rounded text-xs font-bold bg-brandColor text-white shadow-sm hover:opacity-90">Confirm Merge</button>
            </div>
        </form>
    </div>
</div>

<!-- Assign Lead Modal -->
<div id="assignLeadModal" class="fixed inset-0 z-[2000] hidden flex items-center justify-center bg-black/50">
    <div class="bg-white dark:bg-gray-900 rounded-xl max-w-md w-full p-6 shadow-xl border dark:border-gray-800">
        <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2">Assign Lead</h3>
        <p class="text-xs text-slate-500 mb-4">Assign this lead to a specific user or team.</p>
        
        <form action="{{ route('admin.leads.assign', $lead->id) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-xs font-bold text-slate-600 mb-1.5">Assign To User</label>
                @php
                    $users = app(\Webkul\User\Repositories\UserRepository::class)->all();
                @endphp
                <select name="user_id" class="w-full rounded border border-gray-300 px-3 py-2 text-xs font-medium dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                    <option value="">-- Unassigned --</option>
                    @foreach ($users as $u)
                        <option value="{{ $u->id }}" {{ $lead->user_id == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-xs font-bold text-slate-600 mb-1.5">Assign To Team</label>
                @php
                    $groups = app(\Webkul\User\Repositories\GroupRepository::class)->all();
                @endphp
                <select name="group_id" class="w-full rounded border border-gray-300 px-3 py-2 text-xs font-medium dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                    <option value="">-- Unassigned --</option>
                    @foreach ($groups as $g)
                        <option value="{{ $g->id }}" {{ $lead->group_id == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="flex justify-end gap-2.5">
                <button type="button" onclick="document.getElementById('assignLeadModal').classList.add('hidden')" class="px-3 py-1.5 rounded border text-xs font-medium bg-gray-50 text-gray-700 hover:bg-gray-100">Cancel</button>
                <button type="submit" class="px-3 py-1.5 rounded text-xs font-bold bg-brandColor text-white shadow-sm hover:opacity-90">Assign</button>
            </div>
        </form>
    </div>
</div>
