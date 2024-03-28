<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Check if user has any of the specified roles
        foreach ($roles as $role) {
            if (Auth::user()->role == $role) {
                return $next($request);
            }
        }

        // Redirect unauthorized users to the appropriate route
        switch (Auth::user()->role) {
            case 'admin':
                return redirect()->route('admin.dashboard')->with('error', 'You are not authorized to access this page.');
                break;
            case 'instructor':
                return redirect()->route('instructor.dashboard')->with('error', 'You are not authorized to access this page.');
                break;
            case 'user':
                return redirect()->route('dashboard')->with('error', 'You are not authorized to access this page.');
                break;
            default:
                return abort(403, 'Unauthorized');
        }
    }
}
