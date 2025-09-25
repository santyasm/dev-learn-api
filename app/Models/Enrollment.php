<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 * schema="Enrollment",
 * title="Enrollment",
 * description="Matrícula de um usuário em um curso",
 * @OA\Property(property="id", type="string", format="uuid", description="UUID da matrícula"),
 * @OA\Property(property="course_id", type="string", format="uuid", description="ID do curso matriculado"),
 * @OA\Property(property="user_id", type="string", format="uuid", description="ID do usuário matriculado"),
 * @OA\Property(property="progress", type="number", format="float", description="Progresso do curso (0.0 a 100.0)"),
 * @OA\Property(property="created_at", type="string", format="date-time", description="Data de criação"),
 * @OA\Property(property="updated_at", type="string", format="date-time", description="Data da última atualização")
 * )
 */
class Enrollment extends Model
{
    use HasUuids;

    protected $fillable = [
        'course_id',
        'user_id',
        'progress'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function completedVideos()
    {
        return $this->hasMany(VideoProgress::class, 'enrollment_id');
    }
}
