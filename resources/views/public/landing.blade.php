@extends('layouts.public')
@section('title', $e['event_title'].' - '.$e['graduate_name'])

@section('content')
@php $photo = file_exists(public_path('images/graduate.jpg')); @endphp

<header class="relative overflow-hidden">
    <div class="max-w-5xl mx-auto px-6 pt-14 pb-16 grid md:grid-cols-2 gap-10 items-center">
        <div class="order-2 md:order-1 text-center md:text-left">
            <p class="rise uppercase tracking-[0.3em] text-copper text-sm mb-4">You are invited</p>
            <h1 class="rise rise-2 font-serif text-5xl md:text-6xl font-semibold leading-tight">{{ $e['graduate_name'] }}</h1>
            <p class="rise rise-2 font-serif italic text-3xl text-copper mt-2">{{ $e['event_title'] }}</p>

            <dl class="rise rise-3 mt-8 space-y-1 text-lg">
                <div><dt class="sr-only">Date</dt><dd class="font-medium">{{ $startsAt->format('l, j F Y') }}</dd></div>
                <div><dt class="sr-only">Time</dt><dd>{{ $startsAt->format('g:i A') }} (EAT)</dd></div>
                <div><dt class="sr-only">Venue</dt><dd>{{ $e['venue'] }}</dd></div>
            </dl>

            <div class="rise rise-3 mt-8 flex flex-wrap gap-3 justify-center md:justify-start">
                @if ($open)
                    <a href="#rsvp" class="rounded-full bg-copper-dark text-ivory font-semibold px-8 py-4 hover:bg-copper transition shadow-lg">Confirm attendance</a>
                @endif
                @if ($e['maps_url'])
                    <a href="{{ $e['maps_url'] }}" target="_blank" rel="noopener" class="rounded-full border border-ivory/60 px-6 py-4 hover:bg-ivory hover:text-forest transition">Get directions</a>
                @endif
                <a href="{{ route('rsvp.calendar') }}" class="rounded-full border border-ivory/60 px-6 py-4 hover:bg-ivory hover:text-forest transition">Add to calendar</a>
            </div>
        </div>

        <div class="order-1 md:order-2 flex justify-center">
            <div class="relative w-64 h-80 md:w-80 md:h-[26rem]">
                <div class="absolute inset-0 translate-x-3 translate-y-3 border-2 border-copper rounded-t-full"></div>
                @if ($photo)
                    <img src="{{ asset('images/graduate.jpg') }}" alt="{{ $e['graduate_name'] }}" class="relative w-full h-full object-cover rounded-t-full">
                @else
                    <div class="relative w-full h-full rounded-t-full bg-forest-light grid place-items-center font-serif text-7xl text-copper" aria-hidden="true">JZN</div>
                @endif
            </div>
        </div>
    </div>
</header>

<section class="bg-ivory text-forest">
    <div class="max-w-3xl mx-auto px-6 py-16 text-center">
        <p class="font-serif text-2xl md:text-3xl leading-relaxed">{{ $e['invitation_message'] }}</p>

        <div id="countdown" class="mt-10 grid grid-cols-4 gap-3 max-w-md mx-auto" data-target="{{ $startsAt->toIso8601String() }}" aria-live="off">
            @foreach (['days' => 'Days', 'hours' => 'Hours', 'minutes' => 'Minutes', 'seconds' => 'Seconds'] as $k => $l)
                <div class="rounded-lg bg-forest text-ivory py-4">
                    <div class="font-serif text-3xl" data-unit="{{ $k }}">--</div>
                    <div class="text-xs uppercase tracking-wider text-ivory/80">{{ $l }}</div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section id="rsvp" class="max-w-xl mx-auto px-6 py-16">
    <h2 class="font-serif text-4xl text-center mb-2">Confirm attendance</h2>
    @if ($open)
        <p class="text-center text-ivory/80 mb-8">Please reply by <strong>{{ $deadline->format('j F Y') }}</strong>. Enter the code from your invitation.</p>
        <form method="POST" action="{{ route('rsvp.lookup') }}" class="space-y-4">
            @csrf
            <label for="code" class="sr-only">Invitation code</label>
            <input id="code" name="code" value="{{ old('code', $prefill) }}" required autocomplete="off" autocapitalize="characters"
                   placeholder="Invitation code" maxlength="20"
                   class="w-full text-center tracking-[0.4em] uppercase text-2xl rounded-lg bg-forest-light border border-ivory/40 px-4 py-4 placeholder:tracking-normal placeholder:text-base placeholder-ivory/50">
            @error('code')<p class="text-red-200 bg-red-900/40 rounded p-3" role="alert">{{ $message }}</p>@enderror
            <button class="w-full rounded-full bg-copper-dark text-ivory font-semibold px-8 py-4 hover:bg-copper transition">Continue</button>
        </form>
    @else
        <p class="text-center text-lg">RSVPs closed on {{ $deadline->format('j F Y') }}. Please contact Joseph directly if you still wish to attend.</p>
    @endif
</section>

<footer class="text-center text-sm text-ivory/60 pb-10">{{ $e['graduate_name'] }} &middot; {{ $startsAt->format('j F Y') }}</footer>

<script>
(function () {
    const el = document.getElementById('countdown');
    const target = new Date(el.dataset.target).getTime();
    function tick() {
        let d = Math.max(0, target - Date.now());
        const parts = { days: 86400000, hours: 3600000, minutes: 60000, seconds: 1000 };
        for (const [k, ms] of Object.entries(parts)) {
            el.querySelector(`[data-unit=${k}]`).textContent = String(Math.floor(d / ms)).padStart(2, '0');
            d %= ms;
        }
    }
    tick(); setInterval(tick, 1000);
})();
</script>
@endsection
