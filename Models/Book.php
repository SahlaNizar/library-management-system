<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = [
        'title',
        'author',
        'isbn',
        'publisher',
        'published_year',
        'genre',
        'grade_level',
        'total_copies',
        'available_copies',
        'description',
        'cover_image',
        'is_active',
    ];

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }
}