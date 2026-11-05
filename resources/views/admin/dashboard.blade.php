@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')
@php
    $cards = [
        ['Expected attendees (people)', $s['expected'], 'text-copper-dark'],
        ['Invitations issued', $s['issued'], ''],
        ['Confirmed invitations', $s['confirmed'], ''],
        ['Declined', $s['declined'], ''],
        ['Pending responses', $s['pending'], ''],
        ['Response rate', $s['response_rate'].'%', ''],
        ['Additional guests (plus-ones)', $s['additional_guests'], ''],
        ['Parties bringing extras', $s['parties_with_extras'], ''],
        ['Total allocated seats', $s['allocated'], ''],
        ['Remaining allocated seats', $s['remaining_allocated'], ''],
        ['Family attending', $s['family_people'], ''],
        ['Friends attending', $s['friend_people'], ''],
    ];
@endphp
<h1 class="font-serif text-4xl mb-6">Dashboard</h1>
<div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    @foreach ($cards as [$label, $value, $cls])
        <div class="bg-white rounded-xl p-5 shadow-sm">
            <div class="text-3xl font-serif font-bold {{ $cls }}">{{ $value }}</div>
            <div class="text-sm text-charcoal/80 mt-1">{{ $label }}</div>
        </div>
    @endforeach
</div>
<div class="mt-6 grid md:grid-cols-2 gap-4">
    <div class="bg-white rounded-xl p-5 shadow-sm">
        <h2 class="font-semibold mb-1">Venue capacity</h2>
        @if ($s['capacity'])
            <p>{{ $s['expected'] }} of {{ $s['capacity'] }} seats taken &middot; <strong>{{ $s['capacity_left'] }}</strong> left</p>
            <div class="mt-2 h-2 bg-charcoal/10 rounded"><div class="h-2 bg-forest rounded" style="width: {{ min(100, round($s['expected'] / $s['capacity'] * 100)) }}%"></div></div>
        @else
            <p class="text-charcoal/70">No capacity set. <a class="underline" href="{{ route('admin.settings') }}">Set one in Settings</a>.</p>
        @endif
    </div>
    <div class="bg-white rounded-xl p-5 shadow-sm">
        <h2 class="font-semibold mb-1">Follow-ups</h2>
        <p><a class="underline" href="{{ route('admin.invitations.index', ['status' => 'pending']) }}">{{ $s['pending'] }} guests haven't responded</a>. Open the list to send each a WhatsApp reminder.</p>
        <p class="mt-2 text-sm text-charcoal/70">Checked in on the day: {{ $s['checked_in'] }}</p>
    </div>
</div>
@endsection
