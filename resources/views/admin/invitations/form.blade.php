@extends('layouts.admin')
@section('title', $invitation->exists ? 'Edit invitation' : 'New invitation')
@section('content')
<h1 class="font-serif text-4xl mb-6">{{ $invitation->exists ? 'Edit invitation' : 'New invitation' }}</h1>
<form method="POST" action="{{ $invitation->exists ? route('admin.invitations.update', $invitation) : route('admin.invitations.store') }}" class="bg-white rounded-xl p-6 shadow-sm max-w-xl space-y-4">
    @csrf @if ($invitation->exists) @method('PUT') @endif
    <div><label for="name" class="block font-medium mb-1">Invitee or household name</label>
        <input id="name" name="name" value="{{ old('name', $invitation->name) }}" required class="w-full rounded-lg border border-charcoal/30 px-3 py-2"></div>
    <div><label for="phone" class="block font-medium mb-1">Phone (Kenyan mobile)</label>
        <input id="phone" name="phone" value="{{ old('phone', $invitation->phone) }}" required placeholder="0712 345 678" class="w-full rounded-lg border border-charcoal/30 px-3 py-2"></div>
    <div><label for="email" class="block font-medium mb-1">Email (optional)</label>
        <input id="email" type="email" name="email" value="{{ old('email', $invitation->email) }}" class="w-full rounded-lg border border-charcoal/30 px-3 py-2"></div>
    <div><label for="category" class="block font-medium mb-1">Category</label>
        <select id="category" name="category" class="w-full rounded-lg border border-charcoal/30 px-3 py-2">
            @foreach (\App\Enums\GuestCategory::cases() as $c)
                <option value="{{ $c->value }}" @selected(old('category', $invitation->category?->value) === $c->value)>{{ $c->label() }} (default {{ $event->maxFor($c) }} people in total)</option>
            @endforeach
        </select></div>
    <div><label for="max" class="block font-medium mb-1">Max people, including invitee <span class="font-normal text-charcoal/60">(leave blank for the category default)</span></label>
        <input id="max" type="number" min="1" max="20" name="max_attendees" value="{{ old('max_attendees', $invitation->exists ? $invitation->max_attendees : '') }}" class="w-32 rounded-lg border border-charcoal/30 px-3 py-2"></div>
    <button class="rounded-full bg-forest text-ivory px-6 py-2">Save</button>
</form>
@endsection
