<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
class AdminAnalyticsController extends Controller
{
    public function index()
    {
        if (!Schema::hasTable('books') || !Schema::hasTable('users') || !Schema::hasTable('borrowings')) {
            return view('admin.analytics', [
                'mostBorrowedBooks' => collect(),
                'activeClasses' => collect(),
                'monthlyTrends' => collect(),
                'totalBooks' => 0,
                'totalStudents' => 0,
                'totalBorrowings' => 0,
                'totalFines' => 0,
                'activeBorrowings' => 0,
                'overdueBorrowings' => 0,
                'gradeLabels' => json_encode([]),
                'genreLabels' => json_encode([]),
                'chartDatasets' => json_encode([]),
                'months' => collect(),
                'borrowCounts' => collect()
            ]);
