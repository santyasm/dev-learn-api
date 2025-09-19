<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'title',
        'description',
        'thumbnail',
        'price',
        'level',
        'price',
        'status',
        'user_instructor_id',
        'category',
        "duration_in_seconds"
    ];

    public function instructor()
    {
        return $this->belongsTo(User::class, 'user_instructor_id');
    }

    public function videos()
    {
        return $this->hasMany(Video::class, 'course_id');
    }
}
