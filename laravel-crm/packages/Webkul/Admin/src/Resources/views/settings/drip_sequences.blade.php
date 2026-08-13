<x-admin::layouts>
    <x-slot:title>
        Visual Drip Builder
    </x-slot>

    <!-- Header -->
    <div class="scroll-reactive-sticky sticky top-[60px] z-[100] flex items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 mb-4">
        <div class="flex flex-col gap-1">
            <x-admin::breadcrumbs name="settings" />
            <div class="text-xl font-bold dark:text-white">
                ⚡ Visual Drip Sequence Builder
            </div>
        </div>
        <a href="{{ route('admin.leads.index') }}" class="secondary-button">
            ← Back to Leads
        </a>
    </div>

    <!-- Timeline Container -->
    <div class="relative flex flex-col gap-4 max-w-3xl pl-6 before:absolute before:left-3 before:top-0 before:bottom-0 before:w-0.5 before:bg-gray-300 dark:before:bg-gray-700">
        @foreach($steps as $id => $step)
            <div class="relative rounded-lg border border-gray-300 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <span class="absolute -left-6 top-5 flex h-7 w-7 items-center justify-center rounded-full bg-blue-600 font-bold text-xs text-white ring-4 ring-white dark:ring-gray-900">
                    {{ $id }}
                </span>
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-base font-bold dark:text-white">{{ $step['name'] }}</h4>
                    <div class="flex items-center gap-2">
                        <span class="rounded bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                            ⏱️ Day {{ $step['day_offset'] }} Delay
                        </span>
                        <a href="{{ route('admin.settings.drip_sequences', ['edit' => $id]) }}" class="rounded bg-blue-100 px-2 py-0.5 text-xs font-bold text-blue-700 hover:bg-blue-200 dark:bg-blue-950 dark:text-blue-300" title="Edit step">
                            ✎
                        </a>
                        <form
                            method="POST"
                            action="{{ route('admin.settings.drip_sequences.destroy', ['id' => $id]) }}"
                            onsubmit="return confirm('Delete this step?')"
                        >
                            @csrf
                            <button type="submit" class="rounded bg-red-100 px-2 py-0.5 text-xs font-bold text-red-700 hover:bg-red-200 dark:bg-red-950 dark:text-red-300" title="Delete step">
                                ✕
                            </button>
                        </form>
                    </div>
                </div>
                <div class="rounded-md bg-gray-100 p-3 font-mono text-xs text-gray-800 dark:bg-gray-950 dark:text-gray-300">
                    {{ $step['template'] }}
                </div>
            </div>
        @endforeach
    </div>

    <!-- Add / Edit Step -->
    <div class="mt-6 max-w-3xl rounded-lg border border-gray-300 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <h3 class="mb-4 text-base font-bold dark:text-white">
            {{ $editing ? '✏️ Edit Follow-up Step' : '➕ Add a Follow-up Step' }}
        </h3>
        <form method="POST" action="{{ $editing ? route('admin.settings.drip_sequences.update', ['id' => $editing->id]) : route('admin.settings.drip_sequences.store') }}">
            @csrf
            <div class="flex flex-col gap-4 sm:flex-row">
                <div class="flex-[3]">
                    <label class="mb-1 block text-xs font-bold text-gray-700 dark:text-gray-300">Step Name</label>
                    <input type="text" name="name" required value="{{ $editing->name ?? '' }}" placeholder="e.g. Day 7 Testimonial Nudge"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                </div>
                <div class="flex-1">
                    <label class="mb-1 block text-xs font-bold text-gray-700 dark:text-gray-300">Day Offset</label>
                    <input type="number" name="day_offset" min="0" required value="{{ $editing->day_offset ?? 0 }}"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                </div>
            </div>
            <div class="mt-4">
                <label class="mb-1 block text-xs font-bold text-gray-700 dark:text-gray-300">Message</label>
                <textarea name="content" rows="3" required placeholder="Hi {name}, ..."
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">{{ $editing->content ?? '' }}</textarea>
            </div>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                Placeholders: {name} {title} {phone} {email} {value} {source} {agent_name} {brochure_link}
            </p>
            <div class="mt-4 flex gap-2">
                <button type="submit" class="primary-button">{{ $editing ? 'Update Step' : 'Add Step' }}</button>
                @if($editing)
                    <a href="{{ route('admin.settings.drip_sequences') }}" class="secondary-button">Cancel</a>
                @endif
            </div>
        </form>
    </div>
</x-admin::layouts>
