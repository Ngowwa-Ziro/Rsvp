@extends('layouts.admin')
@section('title', 'Settings')
@section('content')
<h1 class="font-serif text-4xl mb-6">Event settings</h1>
@php
    $fields = [
        ['graduate_name', 'Graduate name', 'text'], ['event_title', 'Event title', 'text'],
        ['starts_at', 'Start (Nairobi time)', 'datetime-local'], ['venue', 'Venue', 'text'],
        ['maps_url', 'Google Maps URL for directions', 'url'], ['rsvp_deadline', 'RSVP deadline (inclusive)', 'date'],
        ['capacity', 'Overall attendance capacity (blank = unlimited)', 'number'],
        ['family_max', 'Family: max people per invitation, including invitee (invitee + 5 = 6)', 'number'],
        ['friend_max', 'Friends: max people per invitation, including invitee (invitee + 1 = 2)', 'number'],
        ['public_url', 'Permanent public site URL, https:// (used for the QR code)', 'url'],
    ];
@endphp
<form method="POST" action="{{ route('admin.settings.update') }}" class="bg-white rounded-xl p-6 shadow-sm max-w-2xl space-y-4">
    @csrf @method('PUT')
    @foreach ($fields as [$key, $label, $type])
        <div><label for="{{ $key }}" class="block font-medium mb-1">{{ $label }}</label>
        <input id="{{ $key }}" name="{{ $key }}" type="{{ $type }}" value="{{ old($key, $key === 'starts_at' ? str_replace(' ', 'T', $e[$key]) : $e[$key]) }}" class="w-full rounded-lg border border-charcoal/30 px-3 py-2"></div>
    @endforeach
    <div><label for="invitation_message" class="block font-medium mb-1">Invitation message</label>
        <textarea id="invitation_message" name="invitation_message" rows="4" class="w-full rounded-lg border border-charcoal/30 px-3 py-2">{{ old('invitation_message', $e['invitation_message']) }}</textarea></div>
    <p class="text-sm text-charcoal/70">Changing a category allowance affects new invitations only. Edit existing ones individually.</p>
    <button class="rounded-full bg-forest text-ivory px-6 py-2">Save settings</button>
</form>
@endsection
