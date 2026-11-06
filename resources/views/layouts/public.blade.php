<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Graduation Celebration')</title>
    @php $ev = app(\App\Services\EventSettings::class); @endphp
    <meta property="og:type" content="website">
    <meta property="og:title" content="You are invited: {{ $ev->get('event_title') }}">
    <meta property="og:description" content="{{ $ev->get('graduate_name') }} · {{ $ev->startsAt()->format('l, j F Y') }} · {{ $ev->get('venue') }}">
    <meta property="og:url" content="{{ url('https://rsvp.ethiodeploy.app/rsvp') }}">
    <meta property="og:image" content="{{ asset('images/poster.jpg') }}?v=1">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="robots" content="noindex">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-forest text-ivory antialiased min-h-screen">
    @yield('content')
</body>
</html>
