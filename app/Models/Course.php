<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Course extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'title',
        'description',
        'thumbnail',
        'price',
        'level',
        'status',
        'user_instructor_id',
        'category',
        "duration_in_seconds"
    ];

    protected $appends = ['is_enrolled', 'logged_in_enrollment'];

    public function getLoggedInEnrollmentAttribute()
    {
        if (!Auth::check()) {
            return null;
        }

        return $this->enrollments->firstWhere('user_id', Auth::id());
    }

    public function getIsEnrolledAttribute(): bool
    {
        return (bool) $this->logged_in_enrollment;
    }



    public function instructor()
    {
        return $this->belongsTo(User::class, 'user_instructor_id');
    }

    public function videos()
    {
        return $this->hasMany(Video::class, 'course_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'course_id');
    }
}
