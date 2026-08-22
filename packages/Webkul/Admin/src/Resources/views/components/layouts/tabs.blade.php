@php
    $tabs = menu()->getCurrentActiveMenu('admin')?->getChildren();
@endphp

@if (
    $tabs
    && $tabs->isNotEmpty()
)
    <div class="tabs">
        <div class="mb-4 flex gap-4 border-b-2 pt-2 border-slate-200/80 dark:border-gray-800 max-sm:hidden">
            @foreach ($tabs as $tab)
                <a href="{{ $tab->getUrl() }}">
                    <div class="{{ $tab->isActive() ? "-mb-px border-slate-900 dark:border-brandColor border-b-2 text-slate-900 dark:text-white transition" : 'text-slate-500 dark:text-gray-400' }} pb-3.5 px-2.5 text-sm font-bold hover:text-slate-900 dark:hover:text-white cursor-pointer uppercase tracking-wider">
                        {{ $tab->getName() }}
                    </div>
                </a>
            @endforeach
        </div>
    </div>
@endif
