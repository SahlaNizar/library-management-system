<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Core Users if not existing
        $users = [
            [
                'name' => 'System Admin',
                'email' => 'admin@library.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'grade_level' => null,
                'fine_balance' => 0.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Head Librarian',
                'email' => 'librarian@library.com',
                'password' => Hash::make('password'),
                'role' => 'librarian',
                'grade_level' => null,
                'fine_balance' => 0.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sarah Johnson (Teacher)',
                'email' => 'teacher@library.com',
                'password' => Hash::make('password'),
                'role' => 'teacher',
                'grade_level' => 'Grade 10',
                'fine_balance' => 0.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Alex Smith (Student)',
                'email' => 'student@library.com',
                'password' => Hash::make('password'),
                'role' => 'student',
                'grade_level' => 'Grade 10',
                'fine_balance' => 0.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($users as $user) {
            DB::table('users')->updateOrInsert(
                ['email' => $user['email']],
                $user
            );
        }

        // 2. Seed Sample Books
        if (Schema::hasTable('books') && DB::table('books')->count() === 0) {
            $books = [
                [
                    'title' => 'To Kill a Mockingbird',
                    'author' => 'Harper Lee',
                    'isbn' => '9780061120084',
                    'publisher' => 'Harper Perennial',
                    'genre' => 'Fiction',
                    'grade_level' => 'Grade 10',
                    'total_copies' => 5,
                    'available_copies' => 4,
                    'location_shelf' => 'Shelf A-12',
                    'description' => 'A masterpiece of American literature tackling morality and empathy in the Deep South.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'title' => '1984',
                    'author' => 'George Orwell',
                    'isbn' => '9780451524935',
                    'publisher' => 'Signet Classic',
                    'genre' => 'Fiction',
                    'grade_level' => 'Grade 11',
                    'total_copies' => 4,
                    'available_copies' => 3,
                    'location_shelf' => 'Shelf A-15',
                    'description' => 'A chilling dystopian novel examining totalitarianism, surveillance, and individual freedom.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'title' => 'Principles of Mathematics',
                    'author' => 'Bertrand Russell',
                    'isbn' => '9780415487412',
                    'publisher' => 'Routledge',
                    'genre' => 'Mathematics',
                    'grade_level' => 'Grade 12',
                    'total_copies' => 3,
                    'available_copies' => 3,
                    'location_shelf' => 'Shelf M-04',
                    'description' => 'Comprehensive textbook on foundational mathematical logic and theory.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'title' => 'A Brief History of Time',
                    'author' => 'Stephen Hawking',
                    'isbn' => '9780553380163',
                    'publisher' => 'Bantam',
                    'genre' => 'Science',
                    'grade_level' => 'Grade 10',
                    'total_copies' => 6,
                    'available_copies' => 5,
                    'location_shelf' => 'Shelf S-02',
                    'description' => 'Exploring black holes, quantum physics, and the universe in accessible science writing.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];

            foreach ($books as $b) {
                DB::table('books')->insert($b);
            }
        }

        // 3. Seed Sample Badges
        if (Schema::hasTable('badges') && DB::table('badges')->count() === 0) {
            $badges = [
                ['name' => 'Bookworm', 'code' => 'bookworm', 'description' => 'Borrowed 5 books', 'icon' => 'fa-book-reader', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Bibliophile', 'code' => 'bibliophile', 'description' => 'Borrowed 20 books', 'icon' => 'fa-crown', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Speed Reader', 'code' => 'speed_reader', 'description' => 'Returned book on exact same day', 'icon' => 'fa-bolt', 'created_at' => now(), 'updated_at' => now()],
            ];

            foreach ($badges as $badge) {
                DB::table('badges')->insert($badge);
            }
        }
    }
}
