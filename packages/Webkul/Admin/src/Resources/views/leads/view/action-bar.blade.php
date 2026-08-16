@php
    $phone = collect($lead->person?->contact_numbers ?? [])->pluck('value')->filter()->first();
    $cleanPhone = preg_replace('/[^0-9]/', '', $phone ?? '');
    $email = collect($lead->person?->emails ?? [])->pluck('value')->filter()->first();
    $personName = $lead->person?->name ?? 'there';
    
    $waGreeting = rawurlencode("Hi {$personName}, thanks for reaching out regarding {$lead->title}. How can we assist you today?");
    $waBrochure = rawurlencode("Hi {$personName}, here is our latest brochure/catalogue: " . route('trackable.document', ['lead' => $lead->id, 'hash' => md5($lead->id . config('app.key'))]));
    $waFollowup = rawurlencode("Hi {$personName}, just following up on your inquiry about {$lead->title}. Let us know if you have any questions!");
@endphp

<div>
    <div class="mb-4 flex items-center justify-between">
        <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">
            Quick Actions
        </span>
        @if ($phone)
            <span class="text-[10px] font-bold tracking-widest text-slate-500">{{ $phone }}</span>
        @endif
    </div>

    @if ($phone)
        <div class="grid grid-cols-4 gap-3">
            <!-- One-Tap WhatsApp -->
            <a
                href="https://wa.me/{{ $cleanPhone }}?text={{ $waGreeting }}"
                target="_blank"
                class="flex flex-col items-center justify-center rounded-xl bg-white border border-slate-200 p-3 hover:border-green-400 hover:shadow-2xs transition group"
                title="Send WhatsApp Message"
            >
                <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                <span class="mt-2 text-[10px] font-bold uppercase tracking-wider text-slate-800">WhatsApp</span>
            </a>

            <!-- One-Tap Call -->
            <a
                href="tel:{{ $phone }}"
                class="flex flex-col items-center justify-center rounded-xl bg-white border border-slate-200 p-3 hover:border-blue-400 hover:shadow-2xs transition group"
                title="Call Phone"
            >
                <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                <span class="mt-2 text-[10px] font-bold uppercase tracking-wider text-slate-800">Call</span>
            </a>

            <!-- One-Tap SMS -->
            <a
                href="sms:{{ $phone }}"
                class="flex flex-col items-center justify-center rounded-xl bg-white border border-slate-200 p-3 hover:border-purple-400 hover:shadow-2xs transition group"
                title="Send SMS"
            >
                <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                <span class="mt-2 text-[10px] font-bold uppercase tracking-wider text-slate-800">SMS</span>
            </a>

            <!-- One-Tap Brochure Share -->
            <a
                href="https://wa.me/{{ $cleanPhone }}?text={{ $waBrochure }}"
                target="_blank"
                class="flex flex-col items-center justify-center rounded-xl bg-white border border-slate-200 p-3 hover:border-orange-400 hover:shadow-2xs transition group"
                title="Send Trackable Brochure Link"
            >
                <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                <span class="mt-2 text-[10px] font-bold uppercase tracking-wider text-slate-800">Brochure</span>
            </a>
        </div>

        <!-- WhatsApp Quick Templates -->
        <div class="mt-4 flex flex-wrap gap-2 text-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest self-center">Templates:</span>
            <a
                href="https://wa.me/{{ $cleanPhone }}?text={{ $waGreeting }}"
                target="_blank"
                class="rounded-lg bg-white px-3 py-1.5 text-[10px] font-bold text-slate-700 border border-slate-200 hover:border-slate-400 hover:text-slate-900 transition"
            >
                Greeting
            </a>
            <a
                href="https://wa.me/{{ $cleanPhone }}?text={{ $waBrochure }}"
                target="_blank"
                class="rounded-lg bg-white px-3 py-1.5 text-[10px] font-bold text-slate-700 border border-slate-200 hover:border-slate-400 hover:text-slate-900 transition"
            >
                Share Brochure
            </a>
            <a
                href="https://wa.me/{{ $cleanPhone }}?text={{ $waFollowup }}"
                target="_blank"
                class="rounded-lg bg-white px-3 py-1.5 text-[10px] font-bold text-slate-700 border border-slate-200 hover:border-slate-400 hover:text-slate-900 transition"
            >
                Follow-up
            </a>
        </div>

        <!-- Quick Call Outcome Buttons -->
        <div class="mt-6 border-t border-slate-100 pt-5 flex flex-wrap items-center gap-2 text-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest self-center mr-2">Outcome</span>
            <button
                type="button"
                onclick="logOutcome('Interested')"
                class="rounded-lg bg-green-50 px-3 py-1.5 text-[10px] font-bold text-green-600 hover:bg-green-100 transition"
            >
                Interested
            </button>
            <button
                type="button"
                onclick="logOutcome('Call Back Later')"
                class="rounded-lg bg-amber-50 px-3 py-1.5 text-[10px] font-bold text-amber-600 hover:bg-amber-100 transition"
            >
                Call Back
            </button>
            <button
                type="button"
                onclick="logOutcome('Not Interested')"
                class="rounded-lg bg-red-50 px-3 py-1.5 text-[10px] font-bold text-red-600 hover:bg-red-100 transition"
            >
                Not Interested
            </button>
        </div>
    @else
        <div class="text-[11px] font-medium text-slate-500 bg-slate-100 p-3 rounded-xl border border-slate-200 border-dashed">
            No contact phone number attached. Add a phone number to enable one-tap actions.
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
