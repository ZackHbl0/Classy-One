<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $table = 'settings';

    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    /**
     * Get a setting by key, with default fallback
     */
    public static function get(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, $value, string $group = 'general'): self
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );
    }

    /**
     * Return all default initial settings if table is empty
     */
    public static function initializeDefaults(): void
    {
        $defaults = [
            'school_name' => 'ClassyOne International Academy',
            'school_slogan' => 'Excellence, Innovation & Épanouissement Scolaire',
            'academic_year' => '2025 - 2026',
            'currency' => 'MAD (DH)',
            'school_logo' => 'classyone_logo.png',
            'contact_email' => 'contact@classyone.edu',
            'contact_phone' => '+212 5 22 33 44 55',
            'address' => '124 Boulevard d\'Anfa',
            'city' => 'Casablanca, Maroc',
            'website' => 'https://classyone.edu',
            'timezone' => 'Africa/Casablanca (GMT+1)',
            'auto_absence_alert' => '1',
            'auto_grade_notification' => '1',
            'maintenance_mode' => '0',
            'language' => 'fr',
        ];

        foreach ($defaults as $k => $v) {
            if (!self::where('key', $k)->exists()) {
                self::create([
                    'key' => $k,
                    'value' => $v,
                    'group' => in_array($k, ['auto_absence_alert', 'auto_grade_notification', 'maintenance_mode', 'timezone']) ? 'system' : 'general',
                ]);
            }
        }
    }
}
