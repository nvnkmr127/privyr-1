<x-admin::layouts>
    <x-slot:title>
        Lead Routing Rules
    </x-slot>

    @php
        $userNames = $users->pluck('name', 'id');
        $typeLabels = ['value_gte' => 'Lead Value ≥', 'source_is' => 'Source contains'];
    @endphp

    <!-- Header -->
    <div class="scroll-reactive-sticky sticky top-[60px] z-[100] flex items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 mb-4">
        <div class="flex flex-col gap-1">
            <x-admin::breadcrumbs name="settings" />
            <div class="text-xl font-bold dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-bullseye text-blue-600"></i> Lead Assignment Rules
            </div>
        </div>
        <a href="{{ route('admin.settings.index') }}" class="secondary-button flex items-center gap-1">
            <i class="fa-solid fa-arrow-left text-xs"></i> Back to Settings
        </a>
    </div>

    <!-- Rules Table -->
    <div class="max-w-4xl overflow-x-auto rounded-lg border border-gray-300 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <table class="w-full text-sm">
            <thead class="border-b border-gray-200 text-left text-xs uppercase text-gray-500 dark:border-gray-700 dark:text-gray-400">
                <tr>
                    <th class="px-4 py-3">#</th>
                    <th class="px-4 py-3">Rule</th>
                    <th class="px-4 py-3">Condition</th>
                    <th class="px-4 py-3">Assign To</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rules as $rule)
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <td class="px-4 py-3 text-gray-500">{{ $rule->sort_order }}</td>
                        <td class="px-4 py-3 font-semibold dark:text-white">{{ $rule->name }}</td>
                        <td class="px-4 py-3 dark:text-gray-300">
                            {{ $typeLabels[$rule->condition_type] ?? $rule->condition_type }}
                            <span class="font-mono">{{ $rule->condition_value }}</span>
                        </td>
                        <td class="px-4 py-3 dark:text-gray-300">{{ $userNames[$rule->user_id] ?? 'Round-Robin Pool' }}</td>
                        <td class="px-4 py-3">
                            @if($rule->status)
                                <span class="rounded bg-green-100 px-2 py-0.5 text-xs font-bold text-green-800 dark:bg-green-950 dark:text-green-300">Active</span>
                            @else
                                <span class="rounded bg-gray-100 px-2 py-0.5 text-xs font-bold text-gray-600 dark:bg-gray-800 dark:text-gray-400">Off</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.settings.lead_routing', ['edit' => $rule->id]) }}" class="rounded bg-blue-100 px-2 py-1 text-xs font-bold text-blue-700 hover:bg-blue-200 dark:bg-blue-950 dark:text-blue-300 flex-inline items-center gap-1">
                                <i class="fa-solid fa-pen-to-square"></i> Edit
                            </a>
                            <form method="POST" action="{{ route('admin.settings.lead_routing.destroy', ['id' => $rule->id]) }}" class="inline" onsubmit="return confirm('Delete this rule?')">
                                @csrf
                                <button type="submit" class="rounded bg-red-100 px-2 py-1 text-xs font-bold text-red-700 hover:bg-red-200 dark:bg-red-950 dark:text-red-300 flex-inline items-center gap-1">
                                    <i class="fa-solid fa-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                            No routing rules yet — leads fall back to round-robin. Add a rule below.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Add / Edit Rule -->
    <div class="mt-6 max-w-4xl rounded-lg border border-gray-300 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <h3 class="mb-4 text-base font-bold dark:text-white flex items-center gap-2">
            @if($editing)
                <i class="fa-solid fa-pen-to-square text-blue-600"></i> Edit Rule
            @else
                <i class="fa-solid fa-plus text-emerald-600"></i> Add a Routing Rule
            @endif
        </h3>
        <form method="POST" action="{{ $editing ? route('admin.settings.lead_routing.update', ['id' => $editing->id]) : route('admin.settings.lead_routing.store') }}">
            @csrf
            <div class="flex flex-col gap-4 sm:flex-row">
                <div class="flex-[2]">
                    <label class="mb-1 block text-xs font-bold text-gray-700 dark:text-gray-300">Rule Name</label>
                    <input type="text" name="name" required value="{{ $editing->name ?? '' }}" placeholder="e.g. High Value VIP Leads"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                </div>
                <div class="flex-1">
                    <label class="mb-1 block text-xs font-bold text-gray-700 dark:text-gray-300">Sort Order</label>
                    <input type="number" name="sort_order" min="0" required value="{{ $editing->sort_order ?? 1 }}"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                </div>
            </div>
            <div class="mt-4 flex flex-col gap-4 sm:flex-row">
                <div class="flex-1">
                    <label class="mb-1 block text-xs font-bold text-gray-700 dark:text-gray-300">Condition</label>
                    <select name="condition_type" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="value_gte" @selected(($editing->condition_type ?? '') === 'value_gte')>Lead Value ≥</option>
                        <option value="source_is" @selected(($editing->condition_type ?? '') === 'source_is')>Source contains</option>
                    </select>
                </div>
                <div class="flex-1">
                    <label class="mb-1 block text-xs font-bold text-gray-700 dark:text-gray-300">Value</label>
                    <input type="text" name="condition_value" required value="{{ $editing->condition_value ?? '' }}" placeholder="e.g. 100000 or IndiaMART"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                </div>
                <div class="flex-1">
                    <label class="mb-1 block text-xs font-bold text-gray-700 dark:text-gray-300">Assign To</label>
                    <select name="user_id" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="">Round-Robin Pool</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected(($editing->user_id ?? null) == $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <label class="mt-4 flex items-center gap-2 text-sm dark:text-gray-300">
                <input type="checkbox" name="status" value="1" @checked($editing->status ?? true)>
                Active
            </label>
            <div class="mt-4 flex gap-2">
                <button type="submit" class="primary-button">{{ $editing ? 'Update Rule' : 'Add Rule' }}</button>
                @if($editing)
                    <a href="{{ route('admin.settings.lead_routing') }}" class="secondary-button">Cancel</a>
                @endif
            </div>
        </form>
    </div>
</x-admin::layouts>
