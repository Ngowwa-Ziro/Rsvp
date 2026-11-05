@extends('layouts.public')
@section('title', 'Your RSVP')

@section('content')
<main class="max-w-xl mx-auto px-6 py-12">
    <p class="uppercase tracking-[0.3em] text-copper text-sm text-center">{{ $e['event_title'] }}</p>
    <h1 class="font-serif text-4xl md:text-5xl text-center mt-2">Dear {{ $invitation->name }}</h1>
    <p class="text-center text-ivory/80 mt-3">
        {{ $startsAt->format('l, j F Y \a\t g:i A') }} &middot; {{ $e['venue'] }}<br>
        Your invitation covers up to <strong>{{ $invitation->max_attendees }}</strong> {{ $invitation->max_attendees === 1 ? 'person' : 'people' }}, including you.
    </p>

    @if ($rsvp)
        <p class="mt-6 rounded-lg bg-copper-dark/40 p-3 text-center text-sm">You have already responded. You can update your answer below.</p>
    @endif

    @if ($errors->any())
        <div class="mt-6 rounded-lg bg-red-900/50 border border-red-300/40 p-4" role="alert">
            <ul class="list-disc pl-5">@foreach ($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('rsvp.submit') }}" class="mt-10">
        @csrf
        @include('partials.rsvp-fields', ['dark' => true])

        <div class="mt-6">
            <label for="email" class="block font-medium mb-2">Email for a confirmation <span class="opacity-70 text-sm">(optional)</span></label>
            <input id="email" type="email" name="email" value="{{ old('email', $invitation->email) }}" class="w-full rounded-lg bg-forest-light border border-ivory/30 px-4 py-3 text-ivory">
        </div>

        <button class="mt-8 w-full rounded-full bg-copper-dark text-ivory font-semibold px-8 py-4 hover:bg-copper transition">
            {{ $rsvp ? 'Update my response' : 'Submit my response' }}
        </button>
    </form>
</main>
@endsection
