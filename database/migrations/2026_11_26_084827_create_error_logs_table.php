<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('error_logs', function (Blueprint $table) {
            $table->id('ErrorLogID');
            $table->unsignedBigInteger('UserID')->nullable()->index();
            $table->string('Username', 150)->nullable();
            $table->string('Level', 30)->default('Error')->index(); // Error, HTTP Error, Warning
            $table->string('ExceptionClass', 255)->nullable();
            $table->text('Message');
            $table->string('File', 500)->nullable();
            $table->integer('Line')->nullable();
            $table->text('Url')->nullable();
            $table->string('Method', 15)->nullable();
            $table->string('IpAddress', 45)->nullable();
            $table->longText('StackTrace')->nullable();
            $table->timestamp('CreatedAt')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('error_logs');
    }
};