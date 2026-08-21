<div class="p-4">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
            <span class="icon-check-circle text-blue-600 dark:text-blue-400"></span>
            Qualification Framework
        </h3>
        <div>
            @if ($lead->qualification_status === 'qualified')
                <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800 dark:bg-emerald-900 dark:text-emerald-300">
                    Qualified
                </span>
            @elseif ($lead->qualification_status === 'disqualified')
                <span class="inline-flex items-center rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-medium text-rose-800 dark:bg-rose-900 dark:text-rose-300">
                    Disqualified
                </span>
            @else
                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-800 dark:bg-slate-700 dark:text-slate-300">
                    In Review
                </span>
            @endif
        </div>
    </div>

    <!-- Missing Required Fields Warning -->
    @php
        $qualificationService = app(\Webkul\Lead\Services\LeadQualificationService::class);
        $missingFields = $qualificationService->getMissingFields($lead);
    @endphp

    @if ($lead->qualification_status !== 'qualified' && $lead->qualification_status !== 'disqualified')
        @if (count($missingFields) > 0)
            <div class="rounded-md bg-amber-50 p-3 mb-4 dark:bg-amber-900/30">
                <div class="flex">
                    <div class="shrink-0 text-amber-400">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-amber-800 dark:text-amber-300">Missing qualification data</h3>
                        <div class="mt-1 text-xs text-amber-700 dark:text-amber-400">
                            <p>The following required fields must be filled before qualifying this lead:</p>
                            <ul class="list-disc pl-5 mt-1 space-y-1 font-semibold">
                                @foreach($missingFields as $missing)
                                    <li>{{ ucwords(str_replace('_', ' ', $missing)) }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="rounded-md bg-blue-50 p-3 mb-4 dark:bg-blue-900/30">
                <div class="flex">
                    <div class="shrink-0 text-blue-400">
                        <i class="fa-solid fa-info-circle"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-xs text-blue-800 dark:text-blue-300">
                            Ready for qualification. All required fields are present.
                        </p>
                    </div>
                </div>
            </div>
        @endif
    @endif

    @if ($lead->qualification_status === 'disqualified')
        <div class="rounded-md bg-rose-50 p-3 mb-4 dark:bg-rose-900/30">
            <div class="flex">
                <div class="shrink-0 text-rose-400">
                    <i class="fa-solid fa-ban"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-rose-800 dark:text-rose-300">Disqualified</h3>
                    <div class="mt-1 text-xs text-rose-700 dark:text-rose-400">
                        <p><strong>Reason:</strong> {{ $lead->latestQualification->reason ?? 'Not specified' }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Actions -->
    <div class="mt-4 flex flex-col gap-2 border-t border-slate-200 pt-4 dark:border-slate-700">
        @if ($lead->qualification_status === 'in_review' || is_null($lead->qualification_status))
            <x-admin::form
                v-slot="{ meta, errors, handleSubmit }"
                as="div"
            >
                <form @submit="handleSubmit($event, qualifyLead)">
                    <button type="submit" class="w-full justify-center inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed" {{ count($missingFields) > 0 ? 'disabled' : '' }}>
                        <i class="fa-solid fa-check"></i> Qualify Lead
                    </button>
                </form>
            </x-admin::form>
            <button type="button" onclick="document.getElementById('disqualify-modal').classList.remove('hidden')" class="w-full justify-center inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                <i class="fa-solid fa-xmark"></i> Disqualify
            </button>
        @elseif ($lead->qualification_status === 'qualified' || $lead->qualification_status === 'disqualified')
            <x-admin::form
                v-slot="{ meta, errors, handleSubmit }"
                as="div"
            >
                <form @submit="handleSubmit($event, requalifyLead)">
                    <button type="submit" class="w-full justify-center inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                        <i class="fa-solid fa-arrow-rotate-left"></i> Requalify (Move to In Review)
                    </button>
                </form>
            </x-admin::form>
        @endif
    </div>
</div>

<!-- Disqualify Modal -->
<div id="disqualify-modal" class="hidden relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-500/75 transition-opacity" aria-hidden="true"></div>
    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md dark:bg-gray-800">
                <x-admin::form
                    v-slot="{ meta, errors, handleSubmit }"
                    as="div"
                >
                    <form @submit="handleSubmit($event, disqualifyLead)">
                        <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4 dark:bg-gray-800">
                            <div class="sm:flex sm:items-start">
                                <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                    <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-gray-100" id="modal-title">Disqualify Lead</h3>
                                    <div class="mt-2 space-y-4">
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            Please provide a reason for disqualifying this lead.
                                        </p>
                                        <div>
                                            <label class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">Reason</label>
                                            <select name="reason" class="mt-1 block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-blue-600 sm:text-sm sm:leading-6 dark:bg-gray-900 dark:text-gray-100 dark:ring-gray-700" v-validate="'required'">
                                                <option value="">Select reason...</option>
                                                @foreach(config('lead.qualification.disqualification_reasons', []) as $key => $label)
                                                    <option value="{{ $label }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            <span class="text-red-500 text-xs mt-1 block" v-show="errors.has('reason')">@{{ errors.first('reason') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 dark:bg-gray-700">
                            <button type="submit" class="inline-flex w-full justify-center rounded-md bg-rose-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-rose-500 sm:ml-3 sm:w-auto">Disqualify</button>
                            <button type="button" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto dark:bg-gray-800 dark:text-gray-100 dark:ring-gray-600 dark:hover:bg-gray-700" onclick="document.getElementById('disqualify-modal').classList.add('hidden')">Cancel</button>
                        </div>
                    </form>
                </x-admin::form>
            </div>
        </div>
    </div>
</div>

<script>
    function qualifyLead(params, { resetForm, setErrors }) {
        this.$axios.post("{{ route('admin.leads.qualify', $lead->id) }}")
            .then((response) => {
                this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });
                window.location.reload();
            })
            .catch((error) => {
                this.$emitter.emit('add-flash', { type: 'error', message: error.response.data.message });
            });
    }

    function disqualifyLead(params, { resetForm, setErrors }) {
        this.$axios.post("{{ route('admin.leads.disqualify', $lead->id) }}", params)
            .then((response) => {
                document.getElementById('disqualify-modal').classList.add('hidden');
                this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });
                window.location.reload();
            })
            .catch((error) => {
                if (error.response.status == 422) {
                    setErrors(error.response.data.errors);
                } else {
                    this.$emitter.emit('add-flash', { type: 'error', message: error.response.data.message });
                }
            });
    }

    function requalifyLead(params, { resetForm, setErrors }) {
        this.$axios.post("{{ route('admin.leads.requalify', $lead->id) }}")
            .then((response) => {
                this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });
                window.location.reload();
            })
            .catch((error) => {
                this.$emitter.emit('add-flash', { type: 'error', message: error.response.data.message });
            });
    }
</script>
