<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value'];

    /**
     * Get a setting value by key.
     */
    public static function get($key, $default = null)
    {
        return Cache::rememberForever('setting_' . $key, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set a setting value by key.
     */
    public static function set($key, $value)
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
        
        Cache::forget('setting_' . $key);
        
        return $setting;
    }

    /**
     * Get the full URL for the site logo.
     */
    public static function getLogoUrl()
    {
        $logo = self::get('site_logo');
        if (!$logo) {
            return asset('img/HelpTK--C.png');
        }

        if (str_starts_with($logo, 'img/')) {
            return asset($logo);
        }

        return asset('storage/' . $logo);
    }
}
