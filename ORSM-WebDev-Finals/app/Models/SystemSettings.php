<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemSettings extends Model
{
    use HasFactory;

    protected $table = 'system_settings';

    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    /**
     * Get a setting value by key
     */
    public static function get($key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        
        if (!$setting) {
            return $default;
        }

        return static::castValue($setting->value, $setting->type);
    }

    /**
     * Set a setting value by key
     */
    public static function set($key, $value, $type = 'string', $description = null)
    {
        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => is_array($value) || is_object($value) ? json_encode($value) : $value,
                'type' => $type,
                'description' => $description,
            ]
        );
    }

    /**
     * Delete a setting by key
     */
    public static function forget($key)
    {
        return static::where('key', $key)->delete();
    }

    /**
     * Cast value based on type
     */
    private static function castValue($value, $type)
    {
        switch ($type) {
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'number':
            case 'integer':
                return (int) $value;
            case 'float':
                return (float) $value;
            case 'json':
                return json_decode($value, true);
            case 'array':
                return is_array($value) ? $value : json_decode($value, true);
            case 'text':
            case 'string':
            default:
                return $value;
        }
    }

    /**
     * Get all shop settings
     */
    public static function getShopSettings()
    {
        return [
            'shop_name' => self::get('shop_name', 'My Store'),
            'shop_description' => self::get('shop_description', 'Welcome to our store'),
            'shop_email' => self::get('shop_email', 'info@store.com'),
            'shop_phone' => self::get('shop_phone', '+1-234-567-8900'),
            'shop_address' => self::get('shop_address', '123 Main St'),
            'shop_logo' => self::get('shop_logo', null),
            'primary_color' => self::get('primary_color', '#3498db'),
            'secondary_color' => self::get('secondary_color', '#2c3e50'),
            'currency' => self::get('currency', 'USD'),
            'tax_rate' => self::get('tax_rate', 0, 'number'),
        ];
    }

    /**
     * Update shop settings in bulk
     */
    public static function updateShopSettings(array $settings)
    {
        foreach ($settings as $key => $value) {
            self::set($key, $value);
        }
    }
}
