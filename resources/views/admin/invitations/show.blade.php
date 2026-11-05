@extends('layouts.admin')
@section('title', $invitation->name)
@section('content')
<div class="flex flex-wrap items-start justify-between gap-3 mb-6">
    <div>
        <h1 class="font-serif text-4xl">{{ $invitation->name }}</h1>
        <p class="text-charcoal/70">{{ $invitation->category->label() }} &middot; {{ $invitation->phone }} @if ($invitation->email) &middot; {{ $invitation->email }} @endif</p>
        <p class="mt-1">Code <span class="font-mono font-bold text-xl">{{ $invitation->code }}</span> &middot; up to {{ $invitation->max_attendees }} people</p>
        @if ($invitation->isRevoked())<p class="mt-1 text-red-800 font-semibold">Revoked {{ $invitation->revoked_at->diffForHumans() }}</p>@endif
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.invitations.edit', $invitation) }}" class="rounded-full border border-forest px-5 py-2">Edit</a>
        @if ($invitation->isRevoked())
            <form method="POST" action="{{ route('admin.invitations.restore', $invitation) }}">@csrf @method('PATCH')<button class="rounded-full bg-forest text-ivory px-5 py-2">Restore</button></form>
        @else
            <form method="POST" action="{{ route('admin.invitations.destroy', $invitation) }}" onsubmit="return confirm('Revoke this invitation? Their code will stop working.')">@csrf @method('DELETE')<button class="rounded-full bg-red-800 text-white px-5 py-2">Revoke</button></form>
        @endif
    </div>
</div>

<div class="bg-white rounded-xl p-6 shadow-sm">
    <h2 class="font-serif text-2xl mb-1">RSVP</h2>
    <p class="text-charcoal/70 mb-4">
        @if ($invitation->rsvp) Responded {{ $invitation->responded_at->timezone('Africa/Nairobi')->format('j M Y, g:i A') }} @else No response yet. You can record one on their behalf. @endif
    </p>
    <form method="POST" action="{{ route('admin.invitations.respond', $invitation) }}" class="max-w-xl">
        @csrf @method('PUT')
        @include('partials.rsvp-fields', ['rsvp' => $invitation->rsvp, 'dark' => false])
        <button class="mt-6 rounded-full bg-forest text-ivory px-6 py-2">Save RSVP on behalf of guest</button>
    </form>
</div>
@endsection
