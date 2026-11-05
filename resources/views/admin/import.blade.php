@extends('layouts.admin')
@section('title', 'Import')
@section('content')
<h1 class="font-serif text-4xl mb-4">Import invitations</h1>
<p class="mb-4">Upload a CSV with columns <code class="bg-white px-1">name, phone, email, category, max_attendees</code>. Category is <code>family</code> or <code>friend</code>. Leave <code>max_attendees</code> blank to use the category default. Codes are generated automatically; rows with an existing phone number are skipped.</p>
@if (session('import_errors'))
    <div class="mb-4 rounded-lg bg-amber-50 border-l-4 border-amber-600 p-4"><strong>Skipped rows:</strong>
        <ul class="list-disc pl-5">@foreach (session('import_errors') as $err)<li>{{ $err }}</li>@endforeach</ul></div>
@endif
<form method="POST" action="{{ route('admin.import.store') }}" enctype="multipart/form-data" class="bg-white rounded-xl p-6 shadow-sm max-w-xl space-y-4">
    @csrf
    <input type="file" name="file" accept=".csv,text/csv" required>
    <button class="rounded-full bg-forest text-ivory px-6 py-2 block">Import</button>
</form>
@endsection
