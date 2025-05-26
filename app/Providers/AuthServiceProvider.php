<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {        // Register a custom user provider to handle our custom password field name
        Auth::provider('custom-eloquent', function ($app, array $config) {
            // Get the hasher implementation from the container
            $hasher = $app->make('hash');
            
            // Return an instance of our custom user provider with the correct hasher
            return new class($hasher, $config['model']) extends EloquentUserProvider {
                public function validateCredentials(Authenticatable $user, array $credentials): bool
                {
                    // Get the plain text password from credentials
                    $plain = $credentials['password'];
                    
                    // Compare it with the hashed password (password_hash field)
                    return $this->hasher->check($plain, $user->getAuthPassword());
                }
            };
        });
    }
}
