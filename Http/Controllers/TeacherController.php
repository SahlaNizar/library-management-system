<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeacherController extends Controller
{
    public function index()
    {
        // Get teacher's class
        $teacherClass = Auth::user()->grade_level ?? 'Grade 10';
        
        // Get students in that class
        $students = DB::table('users')
            ->where('role', 'student')
            ->where('grade_level', $teacherClass)
            ->get();
        
        $classAnalytics = [];
        
        foreach ($students as $student) {
            // Count total borrowed books
            $totalBorrowed = DB::table('borrowings')
                ->where('user_id', $student->id)
                ->count();
            
            // Get last borrow date
            $lastBorrow = DB::table('borrowings')
                ->where('user_id', $student->id)
                ->orderBy('borrow_date', 'desc')
                ->first();
            
            $daysInactive = 0;
            if ($lastBorrow) {
                $daysInactive = (strtotime(date('Y-m-d')) - strtotime($lastBorrow->borrow_date)) / (60 * 60 * 24);
            }
            
            // Check if has fines
            $hasFine = DB::table('borrowings')
                ->where('user_id', $student->id)
                ->sum('fine_amount') > 0;
            
            $classAnalytics[] = [
                'student' => $student,
                'total_borrowed' => $totalBorrowed,
                'is_disengaged' => ($daysInactive > 30),
                'has_fines' => $hasFine
            ];
        }
        
        return view('teacher.dashboard', compact('classAnalytics', 'teacherClass'));
    }
}