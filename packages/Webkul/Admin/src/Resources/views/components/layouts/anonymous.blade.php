<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-full bg-[#F8FAFC] text-slate-900" dir="{{ in_array(app()->getLocale(), ['fa', 'ar']) ? 'rtl' : 'ltr' }}">
<head>
    {!! view_render_event('admin.layout.head') !!}
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-url" content="{{ url()->to('/') }}">
    <meta http-equiv="content-language" content="{{ app()->getLocale() }}">
    <meta name="currency" content="{{ json_encode(['code' => config('app.currency'), 'symbol' => core()->currencySymbol(config('app.currency'))]) }}">
    <title>{{ $title ?? 'CRM' }}</title>
    
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
        {!! core()->getConfigData('general.content.custom_scripts.custom_css') !!}
    </style>
</head>
<body class="h-full bg-[#F8FAFC] text-slate-900 antialiased font-sans flex items-center justify-center p-4">
    {!! view_render_event('admin.layout.body.before') !!}

    <div id="app" class="w-full max-w-md mx-auto">
        <x-admin::flash-group />
        
        {!! view_render_event('admin.layout.content.before') !!}

        <!-- Page Content Blade Component -->
        {{ $slot }}

        {!! view_render_event('admin.layout.content.after') !!}
    </div>

    {!! view_render_event('admin.layout.body.after') !!}

    @stack('scripts')

    {!! view_render_event('admin.layout.vue-app-mount.before') !!}
    <script>
        window.addEventListener("load", function(event) {
            app.mount("#app");
        });
    </script>
    {!! view_render_event('admin.layout.vue-app-mount.after') !!}
    
    <script type="text/javascript">
        {!! core()->getConfigData('general.content.custom_scripts.custom_javascript') !!}
    </script>
</body>
</html>
