@extends('layouts.public')
@section('title', 'RSVP received')

@section('content')
@php
    $when = $startsAt->format('l, j F Y \a\t g:i A');
    $wa = $rsvp->attending
        ? "I'll be at {$e['graduate_name']}'s {$e['event_title']} on {$when}, {$e['venue']}. ".route('rsvp.landing')
        : "I can't make {$e['graduate_name']}'s {$e['event_title']}, but I'm celebrating with him from afar!";
@endphp
<main class="min-h-screen grid place-items-center px-6 py-12">
    <div class="max-w-lg text-center rise">
        <div class="mx-auto w-16 h-16 rounded-full border-2 border-copper grid place-items-center text-copper text-3xl" aria-hidden="true">&#10003;</div>
        <h1 class="font-serif text-5xl mt-6">
            {{ $rsvp->attending ? 'See you there, '.strtok($invitation->name, ' ').'!' : 'Thank you, '.strtok($invitation->name, ' ').'.' }}
        </h1>

        @if ($rsvp->attending)
            <p class="mt-4 text-lg text-ivory/90">Your RSVP is confirmed for <strong>{{ $rsvp->guest_count }}</strong> {{ $rsvp->guest_count === 1 ? 'guest' : 'guests' }}:</p>
            <ul class="mt-2 text-ivory/80">@foreach ($rsvp->guests as $g)<li>{{ $g->name }}</li>@endforeach</ul>
        @else
            <p class="mt-4 text-lg text-ivory/90">We are sorry you cannot join us. Your response has been recorded.</p>
        @endif

        <div class="mt-8 rounded-xl bg-ivory text-forest p-6 text-left">
            <p class="font-serif text-2xl">{{ $e['event_title'] }}</p>
            <p class="mt-1">{{ $when }}</p>
            <p>{{ $e['venue'] }}</p>
        </div>

        <div class="mt-8 flex flex-wrap gap-3 justify-center">
            <a href="https://wa.me/?text={{ rawurlencode($wa) }}" target="_blank" rel="noopener" class="rounded-full bg-copper-dark px-6 py-3 font-semibold hover:bg-copper">Share on WhatsApp</a>
            @if ($rsvp->attending)
                <a href="{{ route('rsvp.calendar') }}" class="rounded-full border border-ivory/60 px-6 py-3 hover:bg-ivory hover:text-forest">Add to calendar</a>
                @if ($e['maps_url'])<a href="{{ $e['maps_url'] }}" target="_blank" rel="noopener" class="rounded-full border border-ivory/60 px-6 py-3 hover:bg-ivory hover:text-forest">Directions</a>@endif
            @endif
        </div>

        @if ($invitation->email && $emailEnabled)
            <p class="mt-6 text-sm text-ivory/70">A confirmation was sent to {{ $invitation->email }}.</p>
        @endif
        <p class="mt-6 text-sm text-ivory/70">Need to change something? <a class="underline" href="{{ route('rsvp.form') }}">Update your response</a> until {{ app(\App\Services\EventSettings::class)->deadline()->format('j F') }}.</p>
    </div>
</main>
@endsection
