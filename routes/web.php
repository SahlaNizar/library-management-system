<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

// ============================================================
// HOME PAGE
// ============================================================
Route::get('/', function () {
    return view('home');
});

// ============================================================
// AUTHENTICATION - LOGIN & REGISTER
// ============================================================
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        $user = Auth::user();
        
        if ($user->role == 'admin') {
            return redirect('/dashboard');
        } elseif ($user->role == 'librarian') {
            return redirect('/librarian/dashboard');
        } elseif ($user->role == 'teacher') {
            return redirect('/teacher/dashboard');
        } else {
            return redirect('/student/dashboard');
        }
    }

    return back()->withErrors(['email' => 'Invalid credentials']);
});

Route::post('/logout', function () {
    Auth::logout();
    return redirect('/login');
})->name('logout');

// ============================================================
// REGISTER
// ============================================================
Route::get('/register', function () {
    return view('auth.register');
});

Route::post('/register', function (Request $request) {
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:6|confirmed',
        'role' => 'required|in:student,teacher',
    ]);

    DB::table('users')->insert([
        'name' => $request->name,
        'email' => $request->email,
        'password' => bcrypt($request->password),
        'role' => $request->role,
        'grade_level' => $request->grade_level,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return redirect('/login')->with('success', 'Registration successful! Please login.');
});

// ============================================================
// ADMIN DASHBOARD
// ============================================================
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth');

// ============================================================
// LIBRARIAN DASHBOARD
// ============================================================
Route::get('/librarian/dashboard', function () {
    return view('librarian.dashboard');
})->middleware('auth');

// ============================================================
// TEACHER DASHBOARD
// ============================================================
Route::get('/teacher/dashboard', function () {
    return view('teacher.dashboard');
})->middleware('auth');

// ============================================================
// STUDENT DASHBOARD
// ============================================================
Route::get('/student/dashboard', function () {
    return view('student.dashboard');
})->middleware('auth');

// ============================================================
// BOOKS MANAGEMENT
// ============================================================
Route::get('/books', function () {
    return view('books.index');
})->middleware('auth');

Route::post('/books/store', function (Request $request) {
    DB::table('books')->insert([
        'title' => $request->title,
        'author' => $request->author,
        'genre' => $request->genre,
        'total_copies' => $request->total_copies,
        'available_copies' => $request->available_copies,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    return redirect('/books')->with('success', 'Book added successfully!');
})->middleware('auth');

Route::get('/books/delete/{id}', function ($id) {
    DB::table('borrowings')->where('book_id', $id)->delete();
    DB::table('books')->where('id', $id)->delete();
    return redirect('/books')->with('success', 'Book deleted successfully!');
})->middleware('auth');

// ============================================================
// STUDENTS MANAGEMENT
// ============================================================
Route::get('/students', function () {
    return view('students.index');
})->middleware('auth');

Route::post('/students/store', function (Request $request) {
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:6',
    ]);

    DB::table('users')->insert([
        'name' => $request->name,
        'email' => $request->email,
        'password' => bcrypt($request->password),
        'role' => 'student',
        'grade_level' => $request->grade_level,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    return redirect('/students')->with('success', 'Student added successfully!');
})->middleware('auth');

Route::get('/students/delete/{id}', function ($id) {
    DB::table('users')->where('id', $id)->where('role', 'student')->delete();
    return redirect('/students')->with('success', 'Student deleted successfully!');
})->middleware('auth');

// ============================================================
// BORROWINGS MANAGEMENT
// ============================================================
Route::get('/borrowings', function () {
    return view('borrowings.index');
})->middleware('auth');

Route::post('/borrowings/issue', function (Request $request) {
    $student = DB::table('users')->where('id', $request->user_id)->first();
    if ($student->fine_balance > 0) {
        return redirect('/borrowings')->with('error', 'Student has unpaid fines: Rs ' . $student->fine_balance);
    }
    
    $book = DB::table('books')->where('id', $request->book_id)->first();
    if ($book->available_copies < 1) {
        return redirect('/borrowings')->with('error', 'Book not available!');
    }
    
    DB::table('borrowings')->insert([
        'user_id' => $request->user_id,
        'book_id' => $request->book_id,
        'borrow_date' => date('Y-m-d'),
        'due_date' => $request->due_date,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    DB::table('books')->where('id', $request->book_id)->decrement('available_copies');
    return redirect('/borrowings')->with('success', 'Book issued successfully!');
})->middleware('auth');

Route::get('/borrowings/return/{id}', function ($id) {
    $borrowing = DB::table('borrowings')->where('id', $id)->first();
    $book = DB::table('books')->where('id', $borrowing->book_id)->first();
    
    $fine = 0;
    $today = date('Y-m-d');
    if ($borrowing->due_date < $today) {
        $daysOverdue = (strtotime($today) - strtotime($borrowing->due_date)) / (60 * 60 * 24);
        if ($daysOverdue <= 7) {
            $fine = $daysOverdue * 5;
        } else {
            $fine = (7 * 5) + (($daysOverdue - 7) * 10);
        }
    }
    
    DB::table('borrowings')->where('id', $id)->update([
        'return_date' => date('Y-m-d'),
        'fine_amount' => $fine,
        'updated_at' => now(),
    ]);
    
    DB::table('books')->where('id', $borrowing->book_id)->increment('available_copies');
    
    if ($fine > 0) {
        DB::table('users')->where('id', $borrowing->user_id)->increment('fine_balance', $fine);
        return redirect('/borrowings')->with('success', 'Book returned. Fine: Rs ' . $fine);
    }
    
    return redirect('/borrowings')->with('success', 'Book returned on time.');
})->middleware('auth');

// ============================================================
// FINES MANAGEMENT
// ============================================================
Route::get('/fines', function () {
    return view('fines.index');
})->middleware('auth');

Route::get('/fines/pay/{id}', function ($id) {
    DB::table('users')->where('id', $id)->update(['fine_balance' => 0]);
    return redirect('/fines')->with('success', 'All fines cleared for this student!');
})->middleware('auth');

// ============================================================
// ANALYTICS
// ============================================================
Route::get('/analytics', function () {
    return view('analytics.index');
})->middleware('auth');

// ============================================================
// BADGES
// ============================================================
Route::get('/badges', function () {
    return view('badges.index');
})->middleware('auth');

// ============================================================
// NOTIFICATIONS
// ============================================================
Route::get('/notifications', function () {
    return view('notifications.index');
})->middleware('auth')->name('notifications.index');

Route::get('/notifications/read/{id}', function ($id) {
    DB::table('notifications')->where('id', $id)->update(['is_read' => 1]);
    return redirect('/notifications');
})->middleware('auth');

Route::get('/notifications/mark-all-read', function () {
    DB::table('notifications')->where('user_id', Auth::id())->update(['is_read' => 1]);
    return redirect('/notifications');
})->middleware('auth');

// ============================================================
// SEARCH & RESERVATIONS
// ============================================================
Route::get('/search', function () {
    return view('search.index');
})->middleware('auth')->name('search.index');

Route::get('/reserve/{id}', function ($id) {
    $existing = DB::table('reservations')
        ->where('book_id', $id)
        ->where('user_id', auth()->id())
        ->where('status', 'pending')
        ->exists();

    if ($existing) {
        return redirect('/search')->with('error', 'You already reserved this book');
    }

    $queue = DB::table('reservations')->where('book_id', $id)->where('status', 'pending')->count();

    DB::table('reservations')->insert([
        'user_id' => auth()->id(),
        'book_id' => $id,
        'reservation_date' => date('Y-m-d'),
        'queue_position' => $queue + 1,
        'status' => 'pending',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return redirect('/search')->with('success', 'Book reserved! You are #' . ($queue + 1) . ' in line');
})->middleware('auth');

// ============================================================
// LIBRARIAN - BOOKS
// ============================================================
Route::get('/librarian/books', function () {
    return view('librarian.books');
})->middleware('auth');

Route::post('/librarian/books/store', function (Request $request) {
    DB::table('books')->insert([
        'title' => $request->title,
        'author' => $request->author,
        'genre' => $request->genre,
        'total_copies' => $request->total_copies,
        'available_copies' => $request->available_copies,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    return redirect('/librarian/books')->with('success', 'Book added!');
})->middleware('auth');

Route::get('/librarian/books/delete/{id}', function ($id) {
    DB::table('borrowings')->where('book_id', $id)->delete();
    DB::table('books')->where('id', $id)->delete();
    return redirect('/librarian/books')->with('success', 'Book deleted!');
})->middleware('auth');

// ============================================================
// LIBRARIAN ROUTES
Route::get('/librarian/dashboard', function () {
    return view('librarian.dashboard');
})->middleware('auth');

Route::get('/librarian/books', function () {
    return view('librarian.books');
})->middleware('auth');

Route::get('/librarian/students', function () {
    return view('librarian.students');
})->middleware('auth');

Route::get('/librarian/borrowings', function () {
    return view('librarian.borrowings');
})->middleware('auth');

Route::get('/librarian/fines', function () {
    return view('librarian.fines');
})->middleware('auth');

// ============================================================
// TEACHER ROUTES
// ============================================================
Route::get('/teacher/dashboard', function () {
    return view('teacher.dashboard');
})->middleware('auth');

Route::get('/teacher/students', function () {
    return view('teacher.students');
})->middleware('auth');

Route::get('/teacher/reports', function () {
    return view('teacher.reports');
})->middleware('auth');

Route::get('/student/dashboard', function () {
    return view('student.dashboard');
})->middleware('auth');

Route::get('/student/badges', function () {
    return view('student.badges');
})->middleware('auth');

Route::get('/search', function () {
    return view('search.index');
})->middleware('auth')->name('search.index');

