<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('access_logs', function (Blueprint $table) {
            $table->id('AccessLogID');
            $table->unsignedBigInteger('UserID')->nullable()->index();
            $table->string('Username', 150)->nullable()->index();
            $table->string('EventType', 60)->index(); // Login Success, Login Failed, Lockout, 2FA Failed, Logout, Registration
            $table->string('Status', 20)->index();    // Success, Failed, Blocked
            $table->string('IpAddress', 45)->nullable();
            $table->text('UserAgent')->nullable();
            $table->text('Details')->nullable();
            $table->timestamp('CreatedAt')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('access_logs');
    }
};