<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Video extends Model
{
    use HasUuids;

    protected $fillable = [
        'title',
        'description',
        'course_id',
        'gumlet_asset_id',
        'video_order',
        "duration_in_seconds"
    ];

    public function course() {
        return $this->belongsTo(Course::class, 'course_id');
    }
}
