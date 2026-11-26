<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('system_name', 120)->default("Alvin's Bike Repair Shop");
            $table->string('logo_path', 255)->nullable();
            $table->timestamps();
        });

        DB::table('system_settings')->insert([
            'system_name' => "Alvin's Bike Repair Shop",
            'logo_path' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};