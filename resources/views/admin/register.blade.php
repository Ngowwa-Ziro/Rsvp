@extends('layouts.admin')
@section('title', 'Attendance register')
@section('content')
<div class="flex justify-between items-center mb-4 print:hidden">
    <h1 class="font-serif text-4xl">Attendance register</h1>
    <button onclick="window.print()" class="rounded-full bg-forest text-ivory px-5 py-2">Print</button>
</div>
<h1 class="hidden print:block text-2xl font-bold mb-2">Graduation Celebration - Attendance register ({{ $guests->count() }} guests)</h1>
<table class="w-full text-sm bg-white">
    <thead><tr class="border-b-2 border-charcoal text-left"><th class="p-2 w-10">#</th><th class="p-2">Guest</th><th class="p-2">Invitation</th><th class="p-2">Category</th><th class="p-2 w-24">Arrived</th></tr></thead>
    <tbody>
    @foreach ($guests as $n => $g)
        <tr class="border-b border-charcoal/20">
            <td class="p-2">{{ $n + 1 }}</td><td class="p-2">{{ $g->name }}</td>
            <td class="p-2">{{ $g->rsvp->invitation->name }}</td><td class="p-2">{{ $g->rsvp->invitation->category->label() }}</td>
            <td class="p-2">{!! $g->checked_in_at ? '&#10003;' : '' !!}<span class="inline-block w-5 h-5 border border-charcoal align-middle"></span></td>
        </tr>
    @endforeach
    </tbody>
</table>
@endsection
