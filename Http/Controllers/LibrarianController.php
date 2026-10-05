<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class LibrarianController extends Controller
{
    public function index()
    {
        $totalBooks = DB::table('books')->count();
        $totalStudents = DB::table('users')->where('role', 'student')->count();
        $activeBorrowings = DB::table('borrowings')->whereNull('return_date')->count();
        $overdueBorrowings = DB::table('borrowings')
            ->whereNull('return_date')
            ->where('due_date', '<', date('Y-m-d'))
            ->count();
        
        return view('librarian.dashboard', compact('totalBooks', 'totalStudents', 'activeBorrowings', 'overdueBorrowings'));
    }
}