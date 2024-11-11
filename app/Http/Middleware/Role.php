<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Role
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
          // Check if the user is authenticated
    if (!$request->user()) {
        return redirect('/login');
    }

    // Check if the user has the 'admin' role
    if ($request->user()->role !== 'admin') {
        // Redirect non-admin users to the restricted access page or any other appropriate route
        return redirect('/dashboard');
    }

    // Allow the request to proceed if the user is an admin
    return $next($request);
    }
}
