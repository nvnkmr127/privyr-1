@php
    $phone = collect($lead->person?->contact_numbers ?? [])->pluck('value')->filter()->first();
    $cleanPhone = preg_replace('/[^0-9]/', '', $phone ?? '');
    $email = collect($lead->person?->emails ?? [])->pluck('value')->filter()->first();
    $personName = $lead->person?->name ?? 'there';
    
    $waGreeting = rawurlencode("Hi {$personName}, thanks for reaching out regarding {$lead->title}. How can we assist you today?");
    $waBrochure = rawurlencode("Hi {$personName}, here is our latest brochure/catalogue: " . route('trackable.document', ['lead' => $lead->id, 'hash' => md5($lead->id . config('app.key'))]));
    $waFollowup = rawurlencode("Hi {$personName}, just following up on your inquiry about {$lead->title}. Let us know if you have any questions!");
@endphp

<div class="my-2 rounded-lg border border-brandColor/20 bg-brandColor/5 p-3 dark:border-brandColor/30 dark:bg-brandColor/10">
    <div class="mb-2 flex items-center justify-between">
        <span class="text-xs font-bold uppercase tracking-wider text-brandColor">
            ⚡ Quick Actions
        </span>
        @if ($phone)
            <span class="text-xs text-gray-500 dark:text-gray-400 font-mono">{{ $phone }}</span>
        @endif
    </div>

    @if ($phone)
        <div class="grid grid-cols-4 gap-2">
            <!-- One-Tap WhatsApp -->
            <a
                href="https://wa.me/{{ $cleanPhone }}?text={{ $waGreeting }}"
                target="_blank"
                class="flex flex-col items-center justify-center rounded bg-emerald-600 p-2 text-white hover:bg-emerald-700 transition shadow-sm"
                title="Send WhatsApp Message"
            >
                <span class="text-lg">💬</span>
                <span class="mt-1 text-[10px] font-bold">WhatsApp</span>
            </a>

            <!-- One-Tap Call -->
            <a
                href="tel:{{ $phone }}"
                class="flex flex-col items-center justify-center rounded bg-blue-600 p-2 text-white hover:bg-blue-700 transition shadow-sm"
                title="Call Phone"
            >
                <span class="text-lg">📞</span>
                <span class="mt-1 text-[10px] font-bold">Call</span>
            </a>

            <!-- One-Tap SMS -->
            <a
                href="sms:{{ $phone }}"
                class="flex flex-col items-center justify-center rounded bg-indigo-600 p-2 text-white hover:bg-indigo-700 transition shadow-sm"
                title="Send SMS"
            >
                <span class="text-lg">📱</span>
                <span class="mt-1 text-[10px] font-bold">SMS</span>
            </a>

            <!-- One-Tap Brochure Share -->
            <a
                href="https://wa.me/{{ $cleanPhone }}?text={{ $waBrochure }}"
                target="_blank"
                class="flex flex-col items-center justify-center rounded bg-amber-600 p-2 text-white hover:bg-amber-700 transition shadow-sm"
                title="Send Trackable Brochure Link"
            >
                <span class="text-lg">📄</span>
                <span class="mt-1 text-[10px] font-bold">Brochure</span>
            </a>
        </div>

        <!-- WhatsApp Quick Templates -->
        <div class="mt-3 flex flex-wrap gap-1 text-xs">
            <span class="text-[11px] font-medium text-gray-500 dark:text-gray-400 self-center">Templates:</span>
            <a
                href="https://wa.me/{{ $cleanPhone }}?text={{ $waGreeting }}"
                target="_blank"
                class="rounded bg-white px-2 py-1 text-[11px] font-medium text-gray-700 border hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-700"
            >
                👋 Greeting
            </a>
            <a
                href="https://wa.me/{{ $cleanPhone }}?text={{ $waBrochure }}"
                target="_blank"
                class="rounded bg-white px-2 py-1 text-[11px] font-medium text-gray-700 border hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-700"
            >
                📄 Share Brochure
            </a>
            <a
                href="https://wa.me/{{ $cleanPhone }}?text={{ $waFollowup }}"
                target="_blank"
                class="rounded bg-white px-2 py-1 text-[11px] font-medium text-gray-700 border hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-700"
            >
                🔄 Follow-up
            </a>
        </div>

        <!-- Quick Call Outcome Buttons -->
        <div class="mt-3 border-t border-brandColor/20 pt-2 flex flex-wrap items-center gap-1.5 text-xs">
            <span class="text-[11px] font-medium text-gray-500 dark:text-gray-400 self-center">Quick Outcome:</span>
            <button
                type="button"
                onclick="logOutcome('Interested')"
                class="rounded bg-emerald-100 px-2 py-1 text-[11px] font-bold text-emerald-800 hover:bg-emerald-200 dark:bg-emerald-950 dark:text-emerald-300"
            >
                ✅ Interested
            </button>
            <button
                type="button"
                onclick="logOutcome('Call Back Later')"
                class="rounded bg-amber-100 px-2 py-1 text-[11px] font-bold text-amber-800 hover:bg-amber-200 dark:bg-amber-950 dark:text-amber-300"
            >
                ⏰ Call Back
            </button>
            <button
                type="button"
                onclick="logOutcome('Not Interested')"
                class="rounded bg-rose-100 px-2 py-1 text-[11px] font-bold text-rose-800 hover:bg-rose-200 dark:bg-rose-950 dark:text-rose-300"
            >
                ❌ Not Interested
            </button>
        </div>
    @else
        <div class="text-xs italic text-gray-500 dark:text-gray-400">
            No contact phone number attached to lead. Add phone to enable one-tap WhatsApp & call actions.
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
                    comment: '📞 Call Outcome logged: ' + outcome,
                    lead_id: {{ $lead->id }}
                })
            }).then(() => window.location.reload());
        }
    }
</script>
