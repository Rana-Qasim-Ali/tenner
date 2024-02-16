<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null; // Do not redirect for JSON requests
        }

        // Determine the guard based on the route being accessed
        $route = Route::currentRouteName();
        $guard = null;

        // Define guard based on the route name
        switch ($route) {
            case 'admin.*':
                $guard = 'admin';
                break;
            // Add more cases if you have other guards
            default:
                $guard = 'web';
                break;
        }

        // Redirect based on the guard
        if ($guard === 'admin') {
            return route('admin.login'); // Redirect to the admin login route
        } else {
            return route('login'); // Redirect to the default login route
        }
        // return $request->expectsJson() ? null : route('login');
    }
}
