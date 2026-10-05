<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class BadgeSeeder extends Seeder
{
    public function run()
    {
        DB::table('badges')->insert([
            [
                'name' => 'Bookworm Beginner',
                'description' => 'Borrowed 5 books',
                'criteria_type' => 'books_borrowed',
                'criteria_value' => 5,
                'points' => 10,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Avid Reader',
                'description' => 'Borrowed 20 books',
                'criteria_type' => 'books_borrowed',
                'criteria_value' => 20,
                'points' => 25,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Punctual Patron',
                'description' => '5 on-time returns',
                'criteria_type' => 'on_time_returns',
                'criteria_value' => 5,
                'points' => 15,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ]);
    }
}