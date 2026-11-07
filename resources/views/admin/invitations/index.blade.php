@extends('layouts.admin')
@section('title', 'Guests')
@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <h1 class="font-serif text-4xl">Guests</h1>
    <a href="{{ route('admin.invitations.create') }}" class="rounded-full bg-forest text-ivory px-5 py-2">Add invitation</a>
</div>
<form method="GET" class="flex flex-wrap gap-2 mb-4">
    <input name="q" value="{{ request('q') }}" placeholder="Search name, phone or code" class="rounded-lg border border-charcoal/30 px-3 py-2 bg-white">
    <select name="status" class="rounded-lg border border-charcoal/30 px-3 py-2 bg-white">
        <option value="">All statuses</option>
        <option value="unsent" @selected(request('status') === 'unsent')>Link not sent yet</option>
        @foreach (['pending', 'attending', 'declined', 'revoked'] as $st)<option value="{{ $st }}" @selected(request('status') === $st)>{{ ucfirst($st) }}</option>@endforeach
    </select>
    <select name="category" class="rounded-lg border border-charcoal/30 px-3 py-2 bg-white">
        <option value="">Family &amp; friends</option>
        <option value="family" @selected(request('category') === 'family')>Family</option>
        <option value="friend" @selected(request('category') === 'friend')>Friends</option>
    </select>
    <button class="rounded-lg bg-forest text-ivory px-4 py-2">Filter</button>
</form>
<div class="overflow-x-auto bg-white rounded-xl shadow-sm">
<table class="w-full text-sm text-left">
    <thead class="bg-forest text-ivory"><tr>
        <th class="p-3">Name</th><th class="p-3">Code</th><th class="p-3">Category</th><th class="p-3">Allowance</th><th class="p-3">Status</th><th class="p-3">Coming</th><th class="p-3">Link sent</th><th class="p-3"></th>
    </tr></thead>
    <tbody>
    @forelse ($invitations as $i)
        <tr class="border-t border-charcoal/10 {{ $i->isRevoked() ? 'opacity-50' : '' }}">
            <td class="p-3"><a class="underline" href="{{ route('admin.invitations.show', $i) }}">{{ $i->name }}</a><div class="text-charcoal/60">{{ $i->phone }}</div></td>
            <td class="p-3 font-mono">{{ $i->code }}</td>
            <td class="p-3">{{ $i->category->label() }}</td>
            <td class="p-3">{{ $i->max_attendees }}</td>
            <td class="p-3">{{ $i->isRevoked() ? 'Revoked' : ucfirst($i->status) }}</td>
            <td class="p-3">{{ $i->rsvp?->attending ? $i->rsvp->guest_count : '-' }}</td>
            <td class="p-3 whitespace-nowrap">
                @if ($i->send_count === 0) Not sent
                @else {{ $i->send_count }}x, last {{ $i->last_sent_at?->timezone('Africa/Nairobi')->format('j M') }}
                @endif
            </td>
            <td class="p-3 text-right whitespace-nowrap">
                @if ($i->isRevoked())
                    <span class="text-charcoal/50">-</span>
                @elseif ($i->status === 'pending')
                    <form method="POST" action="{{ route('admin.invitations.send', $i) }}" target="_blank" class="inline">@csrf
                        <button class="underline">{{ $i->send_count === 0 ? 'Send invite link' : 'Send reminder' }}</button>
                    </form>
                @else
                    <a class="underline" target="_blank" rel="noopener" href="https://wa.me/{{ \App\Support\Phone::whatsapp($i->phone) }}">WhatsApp</a>
                @endif
            </td>
        </tr>
    @empty
        <tr><td colspan="8" class="p-6 text-center text-charcoal/60">No invitations found.</td></tr>
    @endforelse
    </tbody>
</table></div>
<div class="mt-4">{{ $invitations->links() }}</div>
@endsection
