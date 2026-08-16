@php
    $config = $config ?? [
        'title' => $connector->name,
        'subtitle' => 'Please enter your details below and we\'ll get in touch.',
        'button_text' => 'Submit',
        'fields' => ['email' => true, 'phone' => true, 'message' => true],
    ];
    $embedded = $embedded ?? false;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $config['title'] }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="font-sans {{ $embedded ? 'bg-transparent p-0' : 'bg-gray-100 flex items-center justify-center min-h-screen p-4' }}">
    <div class="w-full {{ $embedded ? '' : 'max-w-md' }} bg-white rounded-2xl shadow-xl overflow-hidden mx-auto">
        <div class="bg-blue-600 p-6 text-white text-center">
            <h2 class="text-2xl font-bold">{{ $config['title'] }}</h2>
            @if (! empty($config['subtitle']))
                <p class="text-xs text-blue-100 mt-1">{{ $config['subtitle'] }}</p>
            @endif
        </div>

        @if ($errors->any())
            <div class="bg-red-50 text-red-700 text-xs px-6 py-3 border-b border-red-100">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('public.lead_capture.qr_store', ['token' => $connector->webhook_token]) }}{{ $embedded ? '?embed=1' : '' }}" method="POST" class="p-6 flex flex-col gap-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase">Your Name *</label>
                <input type="text" name="name" required value="{{ old('name') }}" placeholder="John Doe" class="mt-1 w-full rounded-lg border border-gray-300 p-3 text-sm focus:border-blue-600 focus:outline-none" />
            </div>

            @if ($config['fields']['phone'] ?? true)
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase">Mobile Number *</label>
                    <input type="tel" name="phone" required value="{{ old('phone') }}" placeholder="+91 9876543210" class="mt-1 w-full rounded-lg border border-gray-300 p-3 text-sm focus:border-blue-600 focus:outline-none" />
                </div>
            @endif

            @if ($config['fields']['email'] ?? true)
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase">Email Address{{ ($config['fields']['email'] ?? true) ? ' *' : '' }}</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="john@example.com" class="mt-1 w-full rounded-lg border border-gray-300 p-3 text-sm focus:border-blue-600 focus:outline-none" />
                </div>
            @endif

            @if ($config['fields']['message'] ?? true)
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase">Requirement / Notes</label>
                    <textarea name="description" rows="3" placeholder="Tell us what you are looking for..." class="mt-1 w-full rounded-lg border border-gray-300 p-3 text-sm focus:border-blue-600 focus:outline-none">{{ old('description') }}</textarea>
                </div>
            @endif

            <button type="submit" class="mt-2 w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg shadow-lg transition-all">
                {{ $config['button_text'] ?? 'Submit' }}
            </button>
        </form>
    </div>

    @if ($embedded)
        {{-- Report our height to the embedding page so the iframe can auto-resize. --}}
        <script>
            (function () {
                var token = @json($connector->webhook_token);
                function postHeight() {
                    var h = document.documentElement.scrollHeight;
                    parent.postMessage({ lcToken: token, lcHeight: h }, '*');
                }
                window.addEventListener('load', postHeight);
                window.addEventListener('resize', postHeight);
                if (window.ResizeObserver) {
                    new ResizeObserver(postHeight).observe(document.body);
                }
            })();
        </script>
    @endif
</body>
</html>
