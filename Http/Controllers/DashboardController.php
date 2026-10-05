<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect('/login');
        }

        if ($user->role === 'admin') {
            return view('dashboard');
        } elseif ($user->role === 'librarian') {
            return redirect('/librarian/dashboard');
        } elseif ($user->role === 'teacher') {
            return redirect('/teacher/dashboard');
        } else {
            return redirect('/student/dashboard');
        }
    }
}