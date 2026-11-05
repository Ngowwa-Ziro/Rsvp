<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 12)->unique();
            $table->string('name');
            $table->string('phone', 20)->unique();
            $table->string('email')->nullable();
            $table->string('category', 10)->index();
            $table->unsignedTinyInteger('max_attendees');
            $table->string('status', 10)->default('pending')->index();
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('revoked_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('rsvps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('attending');
            $table->unsignedTinyInteger('guest_count')->default(0);
            $table->string('dietary', 255)->nullable();
            $table->text('message')->nullable();
            $table->timestamps();
        });

        Schema::create('rsvp_guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rsvp_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('rsvp_guests');
        Schema::dropIfExists('rsvps');
        Schema::dropIfExists('invitations');
    }
};
