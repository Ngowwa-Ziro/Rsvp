<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') - Graduation RSVP</title>
    <meta name="robots" content="noindex,nofollow">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-ivory text-charcoal min-h-screen">
<nav class="bg-forest text-ivory print:hidden">
    <div class="max-w-6xl mx-auto px-4 py-3 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm">
        <a href="{{ route('admin.dashboard') }}" class="font-serif text-xl font-bold">RSVP Admin</a>
        @foreach ([
            'admin.dashboard' => 'Dashboard', 'admin.invitations.index' => 'Guests', 'admin.invitations.create' => 'Add',
            'admin.import' => 'Import', 'admin.checkin' => 'Check-in', 'admin.register' => 'Register',
            'admin.qr' => 'QR code', 'admin.settings' => 'Settings',
        ] as $route => $label)
            <a href="{{ route($route) }}" class="hover:text-copper underline-offset-4 hover:underline">{{ $label }}</a>
        @endforeach
        <a href="{{ route('admin.export') }}" class="hover:text-copper">Export CSV</a>
        <form method="POST" action="{{ route('admin.logout') }}" class="ml-auto">@csrf
            <button class="hover:text-copper">Log out</button>
        </form>
    </div>
</nav>
<main class="max-w-6xl mx-auto px-4 py-8">
    @if (session('status'))
        <div class="mb-6 rounded-lg bg-white border-l-4 border-forest p-4 shadow-sm" role="status">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="mb-6 rounded-lg bg-red-50 border-l-4 border-red-700 p-4 text-red-900" role="alert">
            <ul class="list-disc pl-5">@foreach ($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
        </div>
    @endif
    @yield('content')
</main>
</body>
</html>
