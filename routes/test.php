<?php

// Test if the setting function is now available
Route::get('/test-setting', function () {
    // Try to set a setting
    setting('test_key', 'This is a test value');
    
    // Try to get the setting
    $value = setting('test_key');
    
    return "Setting value: " . $value;
});
