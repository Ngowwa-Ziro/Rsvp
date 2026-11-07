<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            // 0 = link never sent, 1 = invitation sent, 2+ = reminders sent
            $table->unsignedTinyInteger('send_count')->default(0)->after('status')->index();
            $table->timestamp('last_sent_at')->nullable()->after('send_count');
        });
    }

    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->dropColumn(['send_count', 'last_sent_at']);
        });
    }
};
