@extends('layouts.admin')
@section('title', 'Check-in')
@section('content')
<h1 class="font-serif text-4xl mb-4">Event-day check-in</h1>
<form method="GET" class="mb-4 flex gap-2"><input name="q" value="{{ request('q') }}" placeholder="Search guest name" autofocus class="rounded-lg border border-charcoal/30 px-3 py-2 bg-white flex-1"><button class="rounded-lg bg-forest text-ivory px-4">Search</button></form>
<ul class="bg-white rounded-xl shadow-sm divide-y divide-charcoal/10">
@forelse ($guests as $g)
    <li class="p-3 flex items-center justify-between gap-3">
        <div><div class="font-medium">{{ $g->name }}</div><div class="text-sm text-charcoal/60">Invited as {{ $g->rsvp->invitation->name }}</div></div>
        <form method="POST" action="{{ route('admin.checkin.toggle', $g) }}">@csrf @method('PATCH')
            <button class="rounded-full px-5 py-2 {{ $g->checked_in_at ? 'bg-forest text-ivory' : 'border border-forest' }}">{{ $g->checked_in_at ? 'Checked in' : 'Check in' }}</button>
        </form>
    </li>
@empty
    <li class="p-6 text-center text-charcoal/60">No attending guests found.</li>
@endforelse
</ul>
@endsection
