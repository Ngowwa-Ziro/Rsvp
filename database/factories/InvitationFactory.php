<?php

namespace Database\Factories;

use App\Enums\GuestCategory;
use App\Models\Invitation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class InvitationFactory extends Factory
{
    protected $model = Invitation::class;

    public function definition(): array
    {
        return [
            'code' => Str::upper(Str::random(6)),
            'name' => fake()->name(),
            'phone' => '+2547'.fake()->unique()->numerify('########'),
            'email' => null,
            'category' => GuestCategory::Family,
            'max_attendees' => 6,
            'status' => 'pending',
        ];
    }

    public function friend(): static
    {
        return $this->state(['category' => GuestCategory::Friend, 'max_attendees' => 2]);
    }
}
