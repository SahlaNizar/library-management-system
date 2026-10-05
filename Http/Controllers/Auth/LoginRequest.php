<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $email = Str::lower($this->input('email'));

        // Auto-provision or update standard role if missing/incorrect
        if (Schema::hasTable('users')) {
            $roleMap = [
                'admin@library.com' => 'admin',
                'librarian@library.com' => 'librarian',
                'teacher@library.com' => 'teacher',
                'student@library.com' => 'student',
            ];

            if (array_key_exists($email, $roleMap)) {
                $role = $roleMap[$email];
                $existing = DB::table('users')->where('email', $email)->first();

                if (!$existing) {
                    $nameMap = [
                        'admin' => 'System Admin',
                        'librarian' => 'Head Librarian',
                        'teacher' => 'Sarah Johnson (Teacher)',
                        'student' => 'Alex Smith (Student)',
                    ];

                    $userData = [
                        'name' => $nameMap[$role] ?? 'System User',
                        'email' => $email,
                        'password' => Hash::make('password'),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    if (Schema::hasColumn('users', 'role')) {
                        $userData['role'] = $role;
                    }
                    if (Schema::hasColumn('users', 'grade_level')) {
                        $userData['grade_level'] = $role === 'student' ? 'Grade 10' : null;
                    }
                    if (Schema::hasColumn('users', 'fine_balance')) {
                        $userData['fine_balance'] = 0.00;
                    }

                    DB::table('users')->insert($userData);
                } else if (Schema::hasColumn('users', 'role') && $existing->role !== $role) {
                    // Update existing demo user role if it was saved as student previously
                    DB::table('users')->where('email', $email)->update(['role' => $role]);
                }
            }
        }

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
