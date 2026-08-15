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
        Schema::create('products', function (Blueprint $table) {
        $table->id('ProductID');
        $table->string('ProductName');
        $table->string('Category');
        $table->string('SubCategory');
        $table->decimal('Price', 10, 2);
        $table->bigInteger('Stocks')->default(0);
        $table->string('ImagePath')->nullable();
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
