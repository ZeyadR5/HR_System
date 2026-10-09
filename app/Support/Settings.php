<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

class Settings
{
    private static function getPath()
    {
        return storage_path('app/system_settings.json');
    }

    public static function get($key, $default = null)
    {
        $path = self::getPath();
        if (!File::exists($path)) {
            return $default;
        }

        $settings = json_decode(File::get($path), true) ?: [];
        return $settings[$key] ?? $default;
    }

    public static function set($key, $value)
    {
        $path = self::getPath();
        $settings = [];
        
        if (File::exists($path)) {
            $settings = json_decode(File::get($path), true) ?: [];
        }

        $settings[$key] = $value;
        File::put($path, json_encode($settings, JSON_PRETTY_PRINT));
    }
}
