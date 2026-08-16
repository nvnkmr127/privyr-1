<!DOCTYPE html>
<html lang="en" class="h-full bg-[#F8FAFC] text-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Moldable CRM Studio' }}</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        dark: '#0F172A',
                    },
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
    <style>
        body { font-family: 'Inter', 'Roboto', 'Lato', 'Open Sans', system-ui, -apple-system, sans-serif; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; height: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 4px; }
    </style>
</head>
<body class="h-full bg-[#F8FAFC] text-slate-900 antialiased font-sans flex overflow-hidden">

    <!-- Pure Black & White Left Sidebar -->
    <aside id="admin-sidebar" class="w-64 bg-white border-r border-slate-200/80 flex flex-col justify-between h-screen sticky top-0 shrink-0 transition-all duration-300 z-30">
        
        <!-- Top Section & Logo -->
        <div class="p-5 space-y-6">
            <div class="flex items-center justify-between">
                <a href="/admin/moldable/builder" class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-slate-900 text-white flex items-center justify-center font-black text-xl shadow-sm">
                        M
                    </div>
                    <div>
                        <div class="text-sm font-black tracking-wider text-slate-900 uppercase">MOLDABLE</div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">CRM STUDIO</div>
                    </div>
                </a>
            </div>

            <!-- Workspace Selector Card -->
            <div class="rounded-xl border border-slate-200/90 bg-white p-3 flex items-center justify-between shadow-2xs cursor-pointer">
                <div class="flex items-center gap-3">
                    <div class="h-8 w-8 rounded-lg bg-slate-100 text-slate-800 font-bold text-xs flex items-center justify-center border border-slate-200">
                        AC
                    </div>
                    <div>
                        <div class="text-xs font-bold text-slate-900">Acme Global Workspace</div>
                        <div class="text-[10px] text-slate-400 font-medium">Enterprise CRM Studio</div>
                    </div>
                </div>
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </div>

                <!-- Navigation Links -->
                <nav class="space-y-1 custom-scrollbar max-h-[calc(100vh-200px)] overflow-y-auto pr-1">
                    <div class="px-3 text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-2 mt-2">MAIN MENU</div>
                    
                    @foreach (menu()->getItems('admin') as $menuItem)
                        @if(!in_array($menuItem->getKey(), ['settings', 'configuration']))
                            <a href="{{ $menuItem->getUrl() }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition {{ request()->url() == $menuItem->getUrl() ? 'bg-slate-900 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                                <span class="{{ $menuItem->getIcon() }} text-lg {{ request()->url() == $menuItem->getUrl() ? 'text-white' : 'text-slate-500' }}"></span>
                                <span>{{ $menuItem->getName() }}</span>
                            </a>
                        @endif
                    @endforeach

                    <div class="px-3 text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-2 mt-6">SETTINGS</div>
                    
                    @foreach (menu()->getItems('admin') as $menuItem)
                        @if(in_array($menuItem->getKey(), ['settings', 'configuration']))
                            <a href="{{ $menuItem->getUrl() }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition {{ request()->url() == $menuItem->getUrl() ? 'bg-slate-900 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                                <span class="{{ $menuItem->getIcon() }} text-lg {{ request()->url() == $menuItem->getUrl() ? 'text-white' : 'text-slate-500' }}"></span>
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
                    <span>Settings</span>
                    <span>></span>
                    <span>Attributes</span>
                    <span>></span>
                    <span class="text-slate-900 font-bold">Field Builder</span>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <button type="button" class="relative text-slate-500 hover:text-slate-900 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                </button>
                <div class="h-8 w-8 rounded-full bg-slate-900 text-white font-bold text-xs flex items-center justify-center">
                    ED
                </div>
            </div>
        </header>

        <!-- Scrollable Main Viewport -->
        <main class="flex-1 overflow-y-auto p-8 custom-scrollbar">
            {{ $slot }}
        </main>
    </div>

    <script>
        function toggleSidebarCollapse() {
            const sidebar = document.getElementById('admin-sidebar');
            if (sidebar) {
                sidebar.classList.toggle('-ml-64');
            }
        }
    </script>
</body>
</html>
