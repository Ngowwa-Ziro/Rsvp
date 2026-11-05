@extends('layouts.admin')
@section('title', 'QR code')
@section('content')
<h1 class="font-serif text-4xl mb-4">QR code</h1>
@if ($error)
    <div class="rounded-lg bg-amber-50 border-l-4 border-amber-600 p-4 max-w-xl">{{ $error }} <a class="underline" href="{{ route('admin.settings') }}">Open Settings</a></div>
@else
    <p class="mb-4">Points to: <a class="underline font-mono" href="{{ $url }}" target="_blank" rel="noopener">{{ $url }}</a><br>
    <span class="text-sm text-charcoal/70">Before printing, scan it with a phone and confirm it opens this exact page on the live site.</span></p>
    <div id="qr" class="bg-white p-4 inline-block rounded-xl shadow-sm w-72 h-72 [&>svg]:w-full [&>svg]:h-full">{!! $svg !!}</div>
    <div class="mt-4 flex gap-3">
        <a href="{{ route('admin.qr.svg') }}" class="rounded-full bg-forest text-ivory px-5 py-2">Download SVG</a>
        <button id="png" class="rounded-full border border-forest px-5 py-2">Download PNG (2048 px)</button>
    </div>
    <script>
    document.getElementById('png').addEventListener('click', () => {
        const svg = document.querySelector('#qr svg').cloneNode(true);
        svg.setAttribute('width', 2048); svg.setAttribute('height', 2048);
        const url = URL.createObjectURL(new Blob([new XMLSerializer().serializeToString(svg)], { type: 'image/svg+xml' }));
        const img = new Image();
        img.onload = () => {
            const c = Object.assign(document.createElement('canvas'), { width: 2048, height: 2048 });
            const ctx = c.getContext('2d'); ctx.fillStyle = '#fff'; ctx.fillRect(0, 0, 2048, 2048); ctx.drawImage(img, 0, 0, 2048, 2048);
            c.toBlob(b => { const a = document.createElement('a'); a.href = URL.createObjectURL(b); a.download = 'graduation-rsvp-qr.png'; a.click(); });
        };
        img.src = url;
    });
    </script>
@endif
@endsection
