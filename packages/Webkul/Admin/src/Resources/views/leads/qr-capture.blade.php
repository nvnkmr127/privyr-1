<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $connector->name }} - Contact Form</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans flex items-center justify-center min-h-screen p-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="bg-blue-600 p-6 text-white text-center">
            <h2 class="text-2xl font-bold">{{ $connector->name }}</h2>
            <p class="text-xs text-blue-100 mt-1">Please enter your details below to get in touch with us.</p>
        </div>

        <form action="{{ route('public.lead_capture.qr_store', ['token' => $connector->webhook_token]) }}" method="POST" class="p-6 flex flex-col gap-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase">Your Name *</label>
                <input type="text" name="name" required placeholder="John Doe" class="mt-1 w-full rounded-lg border border-gray-300 p-3 text-sm focus:border-blue-600 focus:outline-none" />
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase">Mobile Number *</label>
                <input type="tel" name="phone" required placeholder="+91 9876543210" class="mt-1 w-full rounded-lg border border-gray-300 p-3 text-sm focus:border-blue-600 focus:outline-none" />
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase">Email Address</label>
                <input type="email" name="email" placeholder="john@example.com" class="mt-1 w-full rounded-lg border border-gray-300 p-3 text-sm focus:border-blue-600 focus:outline-none" />
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase">Requirement / Notes</label>
                <textarea name="description" rows="3" placeholder="Tell us what you are looking for..." class="mt-1 w-full rounded-lg border border-gray-300 p-3 text-sm focus:border-blue-600 focus:outline-none"></textarea>
            </div>

            <button type="submit" class="mt-2 w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg shadow-lg transition-all">
                Submit Lead Request
            </button>
        </form>
    </div>
</body>
</html>
