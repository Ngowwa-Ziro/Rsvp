<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\EventSettings;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function edit(EventSettings $event)
    {
        return view('admin.settings', ['e' => $event->all()]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'graduate_name' => ['required', 'string', 'max:120'],
            'event_title' => ['required', 'string', 'max:120'],
            'starts_at' => ['required', 'date_format:Y-m-d\TH:i'],
            'venue' => ['required', 'string', 'max:255'],
            'maps_url' => ['nullable', 'url', 'max:500'],
            'invitation_message' => ['required', 'string', 'max:1500'],
            'rsvp_deadline' => ['required', 'date_format:Y-m-d'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:5000'],
            'public_url' => ['nullable', 'url', 'max:200', 'regex:/^https:\/\//'],
            'family_max' => ['required', 'integer', 'min:1', 'max:20'],
            'friend_max' => ['required', 'integer', 'min:1', 'max:20'],
        ], ['public_url.regex' => 'The public URL must start with https://']);

        $data['starts_at'] = str_replace('T', ' ', $data['starts_at']);
        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        return back()->with('status', 'Settings saved.');
    }
}
