<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * Helper class for managing application settings
 */
class SettingHelper
{
    /**
     * Get a setting value by key
     * 
     * @param string|array $key The setting key or array of [key => default] pairs
     * @param mixed $default Default value if setting doesn't exist
     * @return mixed The setting value or the default value
     */
    public static function get($key, $default = null)
    {
        // If an array is passed, return an array of settings
        if (is_array($key)) {
            $settings = [];
            foreach ($key as $k => $defaultValue) {
                $settings[$k] = self::get($k, $defaultValue);
            }
            return $settings;
        }
        
        // Try to get from cache first
        $cachedValue = Cache::get('setting_' . $key);
        if ($cachedValue !== null) {
            return $cachedValue;
        }
        
        // If not in cache, query the database
        $setting = DB::table('settings')->where('key', $key)->first();
        
        if ($setting) {
            $value = self::decodeValue($setting->value, $setting->type);
            // Cache the setting for future use (1 hour)
            Cache::put('setting_' . $key, $value, now()->addHour());
            return $value;
        }
        
        return $default;
    }
    
    /**
     * Set a setting value
     * 
     * @param string|array $key The setting key or array of key-value pairs
     * @param mixed $value The setting value (if key is string)
     * @return bool Success status
     */
    public static function set($key, $value = null)
    {
        // If an array is passed, set multiple settings
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                self::set($k, $v);
            }
            return true;
        }
        
        $type = self::getValueType($value);
        $encodedValue = self::encodeValue($value);
        
        // Update or insert the setting
        DB::table('settings')->updateOrInsert(
            ['key' => $key],
            [
                'value' => $encodedValue,
                'type' => $type,
                'updated_at' => now()
            ]
        );
        
        // Update the cache
        Cache::put('setting_' . $key, $value, now()->addHour());
        
        return true;
    }
    
    /**
     * Determine the type of a value
     * 
     * @param mixed $value
     * @return string The value type
     */
    private static function getValueType($value)
    {
        if (is_null($value)) {
            return 'null';
        } elseif (is_bool($value)) {
            return 'boolean';
        } elseif (is_numeric($value)) {
            return is_float($value) ? 'float' : 'integer';
        } elseif (is_array($value) || is_object($value)) {
            return 'array';
        } else {
            return 'string';
        }
    }
    
    /**
     * Encode a value for storage
     * 
     * @param mixed $value
     * @return string The encoded value
     */
    private static function encodeValue($value)
    {
        if (is_array($value) || is_object($value)) {
            return json_encode($value);
        } elseif (is_bool($value)) {
            return $value ? '1' : '0';
        } elseif (is_null($value)) {
            return '';
        }
        
        return (string) $value;
    }
    
    /**
     * Decode a value from storage
     * 
     * @param string $value The stored value
     * @param string $type The value type
     * @return mixed The decoded value
     */
    private static function decodeValue($value, $type)
    {
        switch ($type) {
            case 'boolean':
                return (bool) $value;
            case 'integer':
                return (int) $value;
            case 'float':
                return (float) $value;
            case 'array':
                return json_decode($value, true);
            case 'null':
                return null;
            default:
                return $value;
        }
    }
}

// The helper function is now defined in app/Helpers/functions.php
