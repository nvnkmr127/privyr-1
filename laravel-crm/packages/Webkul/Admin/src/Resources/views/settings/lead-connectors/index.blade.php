<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.settings.lead-connectors.title')
    </x-slot>

    <div class="flex flex-col gap-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    @lang('admin::app.settings.lead-connectors.title')
                </h1>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    @lang('admin::app.settings.lead-connectors.subtitle')
                </p>
            </div>

            <button
                type="button"
                id="create-connector-btn"
                class="primary-button flex items-center gap-2"
            >
                <span class="icon-add text-xl"></span>
                @lang('admin::app.settings.lead-connectors.add-connector')
            </button>
        </div>

        <!-- Connectors List Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse ($connectors as $connector)
                <div class="flex flex-col justify-between rounded-xl border bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div>
                        <div class="flex items-start justify-between">
                            <div>
                                <span class="rounded-full bg-brandColor/10 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-brandColor">
                                    {{ $connector->source_type }}
                                </span>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white mt-2">
                                    {{ $connector->name }}
                                </h3>
                            </div>
                            <span class="h-3 w-3 rounded-full {{ $connector->is_active ? 'bg-emerald-500' : 'bg-gray-300' }}"></span>
                        </div>

                        <div class="mt-4 flex flex-col gap-1.5 text-xs text-gray-600 dark:text-gray-400">
                            <div><strong>Captured Leads:</strong> {{ $connector->captured_count }}</div>
                            <div><strong>Duplicate Rule:</strong> {{ ucfirst($connector->duplicate_action) }}</div>
                            <div><strong>Last Payload:</strong> {{ $connector->last_received_at ? $connector->last_received_at->diffForHumans() : 'Never' }}</div>
                        </div>

                        <!-- Webhook URL Box -->
                        <div class="mt-4 rounded bg-gray-50 p-2.5 dark:bg-gray-800">
                            <label class="block text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase">Webhook / API URL</label>
                            <div class="mt-1 flex items-center justify-between text-xs font-mono text-gray-800 dark:text-gray-200">
                                <input type="text" readonly value="{{ route('api.v1.lead_capture.webhook', ['token' => $connector->webhook_token]) }}" class="w-full bg-transparent outline-none truncate pr-2" />
                                <button type="button" onclick="navigator.clipboard.writeText('{{ route('api.v1.lead_capture.webhook', ['token' => $connector->webhook_token]) }}')" class="text-brandColor hover:underline text-[10px] font-sans font-bold">Copy</button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 flex items-center justify-between border-t pt-3 dark:border-gray-800">
                        <a href="{{ route('public.lead_capture.qr_form', ['token' => $connector->webhook_token]) }}" target="_blank" class="text-xs font-semibold text-brandColor hover:underline">
                            QR Link
                        </a>
                        <form action="{{ route('admin.settings.lead_connectors.delete', $connector->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this connector?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">Delete</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-full flex flex-col items-center justify-center rounded-xl border border-dashed p-10 text-center dark:border-gray-800">
                    <span class="icon-settings text-4xl text-gray-400 mb-2"></span>
                    <p class="text-base font-semibold text-gray-700 dark:text-gray-300">No Lead Connectors Configured</p>
                    <p class="text-xs text-gray-400 mt-1">Connect Meta, Google Ads, IndiaMART, JustDial, 99acres, MagicBricks, Housing, Sulekha, or Webhooks to capture leads automatically.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Create Connector Modal -->
    <div id="connector-modal" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl dark:bg-gray-900">
            <div class="flex items-center justify-between border-b pb-3 dark:border-gray-800">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Add New Lead Connector</h3>
                <button type="button" id="close-modal-btn" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <span class="icon-cross text-2xl"></span>
                </button>
            </div>

            <form action="{{ route('admin.settings.lead_connectors.store') }}" method="POST" class="mt-4 flex flex-col gap-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300">Connector Name</label>
                    <input type="text" name="name" required placeholder="e.g. Meta Facebook Ads Lead Form" class="mt-1 w-full rounded-md border border-gray-300 p-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300">Source Platform</label>
                    <select name="source_type" class="mt-1 w-full rounded-md border border-gray-300 p-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="meta_ads">Meta Lead Ads (Facebook / Instagram)</option>
                        <option value="google_ads">Google Lead Form Ads</option>
                        <option value="indiamart">IndiaMART Direct API</option>
                        <option value="justdial">JustDial Webhook</option>
                        <option value="realestate_99acres">99acres Real Estate Portal</option>
                        <option value="magicbricks">MagicBricks Portal</option>
                        <option value="housing">Housing.com</option>
                        <option value="sulekha">Sulekha Business Leads</option>
                        <option value="qr">Mobile QR Code Form</option>
                        <option value="webhook">Generic Webhook / Zapier / Make</option>
                        <option value="rest_api">Custom REST API Push</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300">Duplicate Handling Rule</label>
                    <select name="duplicate_action" class="mt-1 w-full rounded-md border border-gray-300 p-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="update">Update Existing Contact Details</option>
                        <option value="attach_contact">Attach New Lead to Existing Contact</option>
                        <option value="skip">Skip Duplicate Ingestion</option>
                    </select>
                </div>

                <div class="flex items-center justify-end gap-2 border-t pt-4 dark:border-gray-800">
                    <button type="button" id="cancel-modal-btn" class="rounded-md border border-gray-300 px-4 py-2 text-xs font-semibold text-gray-600 dark:border-gray-700 dark:text-gray-300">
                        Cancel
                    </button>
                    <button type="submit" class="rounded-md bg-brandColor px-5 py-2 text-xs font-semibold text-white shadow">
                        Connect Source
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('connector-modal');
            document.getElementById('create-connector-btn').addEventListener('click', () => modal.classList.remove('hidden'));
            document.getElementById('close-modal-btn').addEventListener('click', () => modal.classList.add('hidden'));
            document.getElementById('cancel-modal-btn').addEventListener('click', () => modal.classList.add('hidden'));
        });
    </script>
    @endpush
</x-admin::layouts>
