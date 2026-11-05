<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use App\Services\EventSettings;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Event defaults (editable later in Admin > Settings). Only inserts missing keys.
        foreach (EventSettings::DEFAULTS as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }

        // Admin account comes from .env, never from code.
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');
        if (! $email || ! $password) {
            $this->command?->warn('ADMIN_EMAIL / ADMIN_PASSWORD not set in .env - no admin user created.');
            return;
        }
        if (strlen($password) < 12) {
            $this->command?->error('ADMIN_PASSWORD must be at least 12 characters.');
            return;
        }
        User::updateOrCreate(['email' => $email], [
            'name' => 'Administrator',
            'password' => Hash::make($password),
        ]);
    }
}
