<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class SystemSetting extends Model
{
    protected $table = 'system_settings';

    protected $fillable = [
        'system_name',
        'logo_path',
    ];

    public static function current(): self
    {
        if (! Schema::hasTable('system_settings')) {
            return new self([
                'system_name' => "Alvin's Bike Repair Shop",
                'logo_path' => null,
            ]);
        }

        $cached = Cache::get('system_settings');

        if (is_array($cached) && array_key_exists('system_name', $cached)) {
            $instance = new self($cached);
            $instance->id = $cached['id'] ?? 1;
            $instance->exists = true;

            return $instance;
        }

        // Purge any stale or __PHP_Incomplete_Class cache payload
        Cache::forget('system_settings');

        $setting = self::first() ?? self::create([
            'system_name' => "Alvin's Bike Repair Shop",
            'logo_path' => null,
        ]);

        Cache::forever('system_settings', [
            'id' => $setting->id,
            'system_name' => $setting->system_name,
            'logo_path' => $setting->logo_path,
        ]);

        return $setting;
    }

    public static function clearCache(): void
    {
        Cache::forget('system_settings');
    }

    public function logoUrl(): string
    {
        if (! empty($this->logo_path) && File::exists(public_path($this->logo_path))) {
            return asset($this->logo_path);
        }

        return asset('images/JFM.png');
    }

    public function logoDataUri(): ?string
    {
        $path = (! empty($this->logo_path) && File::exists(public_path($this->logo_path)))
            ? public_path($this->logo_path)
            : public_path('images/JFM.png');

        if (! File::exists($path)) {
            return null;
        }

        $mime = File::mimeType($path) ?: 'image/png';
        $data = base64_encode(File::get($path));

        return "data:{$mime};base64,{$data}";
    }
}