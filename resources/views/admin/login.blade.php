<!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin login</title><meta name="robots" content="noindex,nofollow">
@vite(['resources/css/app.css'])</head>
<body class="bg-forest text-ivory min-h-screen grid place-items-center px-6">
<form method="POST" action="{{ route('admin.login') }}" class="w-full max-w-sm space-y-4">
    @csrf
    <h1 class="font-serif text-4xl text-center">Administrator</h1>
    @error('email')<p class="bg-red-900/50 rounded p-3" role="alert">{{ $message }}</p>@enderror
    <div><label for="email" class="block mb-1">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus class="w-full rounded-lg bg-forest-light border border-ivory/30 px-4 py-3"></div>
    <div><label for="password" class="block mb-1">Password</label>
        <input id="password" type="password" name="password" required class="w-full rounded-lg bg-forest-light border border-ivory/30 px-4 py-3"></div>
    <label class="flex items-center gap-2"><input type="checkbox" name="remember"> Keep me signed in</label>
    <button class="w-full rounded-full bg-copper-dark py-3 font-semibold hover:bg-copper">Sign in</button>
</form></body></html>
