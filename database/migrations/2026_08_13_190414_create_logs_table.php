<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('logs', function (Blueprint $table) {
        $table->id('LogID');
        $table->unsignedBigInteger('UserID');
        $table->unsignedBigInteger('ProductID');
        $table->string('ActionType');
        $table->integer('Quantity');
        $table->decimal('UnitPrice', 10, 2);
        $table->decimal('TotalPrice', 10, 2);
        $table->dateTime('LogDate');
        $table->text('LogDescription')->nullable();
        $table->uuid('BatchID'); 
        $table->foreign('UserID')->references('UserID')->on('accounts')->onDelete('cascade');
        $table->foreign('ProductID')->references('ProductID')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
