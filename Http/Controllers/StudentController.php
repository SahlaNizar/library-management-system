<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StudentController extends Controller
{
    public function index()
    {
        $studentId = Auth::id();
        
        // Get current borrowings safely
        $currentBorrowings = collect();
        if (Schema::hasTable('borrowings') && Schema::hasTable('books')) {
            $currentBorrowings = DB::table('borrowings')
                ->join('books', 'borrowings.book_id', '=', 'books.id')
                ->where('borrowings.user_id', $studentId)
                ->whereNull('borrowings.return_date')
                ->select('books.title', 'books.author', 'borrowings.borrow_date', 'borrowings.due_date')
                ->get();
        }
        
        // Get borrowing history safely
        $borrowingHistory = collect();
        if (Schema::hasTable('borrowings') && Schema::hasTable('books')) {
            $borrowingHistory = DB::table('borrowings')
                ->join('books', 'borrowings.book_id', '=', 'books.id')
                ->where('borrowings.user_id', $studentId)
                ->whereNotNull('borrowings.return_date')
                ->select('books.title', 'borrowings.borrow_date', 'borrowings.return_date', 'borrowings.fine_amount')
                ->orderBy('borrowings.return_date', 'desc')
                ->limit(10)
                ->get();
        }
        
        // Get badges safely
        $badges = collect();
        if (Schema::hasTable('user_badges') && Schema::hasTable('badges')) {
            $badges = DB::table('user_badges')
                ->join('badges', 'user_badges.badge_id', '=', 'badges.id')
                ->where('user_badges.user_id', $studentId)
                ->select('badges.*', 'user_badges.awarded_date')
                ->get();
        }
        
        // Get available books safely
        $availableBooks = collect();
        if (Schema::hasTable('books')) {
            $availableBooks = DB::table('books')
                ->where('available_copies', '>', 0)
                ->limit(10)
                ->get();
        }
        
        return view('student.dashboard', compact('currentBorrowings', 'borrowingHistory', 'badges', 'availableBooks'));
    }
}
