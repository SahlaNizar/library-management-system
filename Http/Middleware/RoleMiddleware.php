<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }
        
        if (Auth::user()->role != $role) {
            // Instead of 403, redirect to appropriate dashboard
            if (Auth::user()->role == 'admin') {
                return redirect('/dashboard');
            } elseif (Auth::user()->role == 'librarian') {
                return redirect('/librarian/dashboard');
            } elseif (Auth::user()->role == 'teacher') {
                return redirect('/teacher/dashboard');
            } else {
                return redirect('/student/dashboard');
            }
        }
        
        return $next($request);
    }
}