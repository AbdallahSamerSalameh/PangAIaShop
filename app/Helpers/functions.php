<?php

if (!function_exists('setting')) {
    /**
     * Helper function for getting/setting application settings
     * 
     * @param string|array $key The setting key or array of key-value pairs
     * @param mixed $value The setting value (if first param is string)
     * @return mixed The setting value or success status
     */
    function setting($key, $value = null)
    {
        // If a single value is passed as second parameter, set the setting
        if (is_string($key) && func_num_args() > 1) {
            return \App\Helpers\SettingHelper::set($key, $value);
        }
        
        // If an array is passed with a single parameter, set multiple settings
        if (is_array($key) && func_num_args() === 1) {
            return \App\Helpers\SettingHelper::set($key);
        }
        
        // Otherwise, get the setting
        return \App\Helpers\SettingHelper::get($key, $value);
    }
}

if (!function_exists('updateEnvFile')) {
    /**
     * Helper to update .env file
     *
     * @param  array  $data
     * @return bool
     */
    function updateEnvFile($data)
    {
        $envFile = base_path('.env');
        
        if (!file_exists($envFile)) {
            return false;
        }
        
        $envContents = file_get_contents($envFile);
        
        foreach ($data as $key => $value) {
            if (strpos($envContents, $key . '=') !== false) {
                $envContents = preg_replace('/' . $key . '=.*/', $key . '=' . $value, $envContents);
            } else {
                $envContents .= "\n" . $key . '=' . $value;
            }
        }
        
        file_put_contents($envFile, $envContents);
        
        return true;
    }
}
