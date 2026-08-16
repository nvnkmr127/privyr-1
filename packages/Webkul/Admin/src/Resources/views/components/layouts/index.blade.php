<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-full bg-[#F8FAFC] text-slate-900" dir="{{ in_array(app()->getLocale(), ['fa', 'ar']) ? 'rtl' : 'ltr' }}">
<head>
    {!! view_render_event('admin.layout.head.before') !!}
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ url()->to('/') }}">
    <meta http-equiv="content-language" content="{{ app()->getLocale() }}">
    <meta name="currency" content="{{ json_encode(['code' => config('app.currency'), 'symbol' => core()->currencySymbol(config('app.currency'))]) }}">
    <title>{{ $title ?? 'CRM Dashboard' }}</title>
    
    @stack('meta')
    
    {{ vite()->set(['src/Resources/assets/css/app.css', 'src/Resources/assets/js/app.js']) }}

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { dark: '#0F172A' },
                    fontFamily: {
                        sans: ['Inter', 'Roboto', 'Lato', 'Open Sans', 'system-ui', '-apple-system', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Lato:wght@300;400;700;900&family=Open+Sans:wght@300;400;500;600;700;800&family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    
    @stack('styles')
    
    <style>
        body { font-family: 'Inter', 'Roboto', 'Lato', 'Open Sans', system-ui, -apple-system, sans-serif !important; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; height: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 4px; }
        {!! core()->getConfigData('general.content.custom_scripts.custom_css') !!}
    </style>
    
    {!! view_render_event('admin.layout.head.after') !!}
</head>
<body class="h-full bg-[#F8FAFC] text-slate-900 antialiased font-sans flex overflow-hidden">
    {!! view_render_event('admin.layout.body.before') !!}

    <div id="app" class="flex w-full h-full">
        <x-admin::flash-group />
        <x-admin::modal.confirm />
        
        {!! view_render_event('admin.layout.content.before') !!}

        <!-- Pure Black & White Left Sidebar -->
        <aside id="admin-sidebar" class="w-64 bg-white border-r border-slate-200/80 flex flex-col justify-between h-screen sticky top-0 shrink-0 transition-all duration-300 z-30">
            <!-- Top Section & Logo -->
            <div class="p-5 space-y-6">
                <div class="flex items-center justify-between">
                    <a href="{{ route('admin.dashboard.index') }}" class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-slate-900 text-white flex items-center justify-center font-black text-xl shadow-sm">
                            C
                        </div>
                        <div>
                            <div class="text-sm font-black tracking-wider text-slate-900 uppercase">CRM</div>
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">DASHBOARD</div>
                        </div>
                    </a>
                </div>

                <!-- Navigation Links -->
                <nav class="space-y-1 custom-scrollbar max-h-[calc(100vh-200px)] overflow-y-auto pr-1">
                    <div class="px-3 text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-2 mt-2">MAIN MENU</div>
                    
                    @foreach (menu()->getItems('admin') as $menuItem)
                        @if(!in_array($menuItem->getKey(), ['settings', 'configuration']))
                            <a href="{{ $menuItem->getUrl() }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition {{ $menuItem->isActive() ? 'bg-slate-900 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                                <span class="{{ $menuItem->getIcon() }} text-lg {{ $menuItem->isActive() ? 'text-white' : 'text-slate-500' }}"></span>
                                <span>{{ $menuItem->getName() }}</span>
                            </a>
                        @endif
                    @endforeach

                    <div class="px-3 text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-2 mt-6">SETTINGS</div>
                    
                    @foreach (menu()->getItems('admin') as $menuItem)
                        @if(in_array($menuItem->getKey(), ['settings', 'configuration']))
                            <a href="{{ $menuItem->getUrl() }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition {{ $menuItem->isActive() ? 'bg-slate-900 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                                <span class="{{ $menuItem->getIcon() }} text-lg {{ $menuItem->isActive() ? 'text-white' : 'text-slate-500' }}"></span>
                                <span>{{ $menuItem->getName() }}</span>
                            </a>
                        @endif
                    @endforeach

                    <a href="/admin/moldable/builder" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition {{ request()->is('admin/moldable*') ? 'bg-slate-900 text-white shadow-md' : '' }}">
                        <svg class="w-5 h-5 {{ request()->is('admin/moldable*') ? 'text-white' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <span>Moldable Studio</span>
                    </a>
                </nav>
            </div>

            <!-- Sidebar User Footer -->
            <div class="p-4 border-t border-slate-200/80 bg-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="h-9 w-9 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold text-xs">
                        {{ substr(auth()->guard('user')->user()->name ?? 'A', 0, 1) }}
                    </div>
                    <div>
                        <div class="text-xs font-bold text-slate-900">{{ auth()->guard('user')->user()->name ?? 'Admin' }}</div>
                        <a href="{{ route('admin.session.destroy') }}" class="text-[10px] font-bold text-red-500 hover:underline">Logout</a>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content Body Viewport -->
        <div class="flex-1 flex flex-col h-screen overflow-hidden bg-[#F8FAFC]">
            <!-- Top App Sticky Header -->
            <header class="bg-white border-b border-slate-200/80 px-8 py-3.5 sticky top-0 z-20 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <button type="button" onclick="toggleSidebarCollapse()" class="text-slate-400 hover:text-slate-700 p-2 rounded-xl hover:bg-slate-100 transition md:hidden">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    <div class="flex items-center gap-2 text-xs font-medium text-slate-400">
                        <span class="text-slate-900 font-bold">{{ $title ?? 'Dashboard' }}</span>
                    </div>
                </div>
            </header>

            <!-- Scrollable Main Viewport -->
            <main class="flex-1 overflow-y-auto p-8 custom-scrollbar">
                {{ $slot }}
            </main>
        </div>
        
        {!! view_render_event('admin.layout.content.after') !!}
    </div>

    {!! view_render_event('admin.layout.body.after') !!}

    @stack('scripts')

    {!! view_render_event('admin.layout.vue-app-mount.before') !!}
    <script>
        function toggleSidebarCollapse() {
            const sidebar = document.getElementById('admin-sidebar');
            if (sidebar) {
                sidebar.classList.toggle('-ml-64');
            }
        }
        window.addEventListener("load", function(event) {
            app.mount("#app");
        });
    </script>
    {!! view_render_event('admin.layout.vue-app-mount.after') !!}
</body>
</html>
