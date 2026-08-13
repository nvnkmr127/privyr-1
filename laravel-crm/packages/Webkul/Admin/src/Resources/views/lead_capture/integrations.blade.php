<x-admin::layouts>
    <x-slot:title>
        Lead Capture Integrations
    </x-slot>

    <!-- Header -->
    <div class="scroll-reactive-sticky sticky top-[60px] z-[100] flex items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 mb-4">
        <div class="flex flex-col gap-1">
            <x-admin::breadcrumbs name="settings" />
            <div class="text-xl font-bold dark:text-white">
                🔌 Lead Capture Integrations & Multi-Source Webhooks
            </div>
        </div>
        <a href="{{ route('admin.settings.index') }}" class="secondary-button">
            ← Settings
        </a>
    </div>

    <!-- Integrations Grid -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($integrations as $item)
            <div class="rounded-lg border border-gray-300 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-2xl">{{ $item['icon'] }}</span>
                        <span class="rounded bg-green-100 px-2 py-0.5 text-xs font-bold text-green-800 dark:bg-green-950 dark:text-green-300">
                            {{ $item['status'] }}
                        </span>
                    </div>
                    <h4 class="text-base font-bold dark:text-white">{{ $item['name'] }}</h4>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $item['description'] }}</p>
                </div>
                <div class="mt-4 pt-3 border-t border-gray-200 dark:border-gray-800">
                    <div class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1">Webhook Endpoint</div>
                    <code class="block overflow-x-auto rounded bg-gray-100 p-2 text-xs font-mono text-gray-800 dark:bg-gray-950 dark:text-gray-300">
                        {{ $item['endpoint'] }}
                    </code>
                </div>
            </div>
        @endforeach
    </div>
</x-admin::layouts>
