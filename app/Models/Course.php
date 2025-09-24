<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 * schema="Course",
 * title="Course",
 * description="Detalhes de um Curso",
 * @OA\Property(
 * property="id",
 * type="string",
 * format="uuid",
 * description="UUID do curso"
 * ),
 * @OA\Property(
 * property="title",
 * type="string",
 * description="Título do curso"
 * ),
 * @OA\Property(
 * property="description",
 * type="string",
 * description="Descrição detalhada do curso"
 * ),
 * @OA\Property(
 * property="thumbnail",
 * type="string",
 * description="URL da imagem miniatura"
 * ),
 * @OA\Property(
 * property="price",
 * type="number",
 * format="float",
 * description="Preço do curso"
 * ),
 * @OA\Property(
 * property="level",
 * type="string",
 * enum={"beginner", "intermediate", "advanced"},
 * description="Nível de dificuldade"
 * ),
 * @OA\Property(
 * property="status",
 * type="string",
 * enum={"draft", "published", "archived"},
 * description="Status de publicação"
 * ),
 * @OA\Property(
 * property="user_instructor_id",
 * type="string",
 * format="uuid",
 * description="ID do instrutor (User)"
 * ),
 * @OA\Property(
 * property="category",
 * type="string",
 * description="Categoria do curso"
 * ),
 * @OA\Property(
 * property="duration_in_seconds",
 * type="integer",
 * description="Duração total em segundos"
 * ),
 * @OA\Property(
 * property="created_at",
 * type="string",
 * format="date-time",
 * description="Data de criação"
 * ),
 * @OA\Property(
 * property="updated_at",
 * type="string",
 * format="date-time",
 * description="Data da última atualização"
 * ),
 * @OA\Property(
 * property="enrollments_count",
 * type="integer",
 * description="Número de matrículas (somente no index)"
 * ),
 * @OA\Property(
 * property="is_enrolled",
 * type="boolean",
 * description="Indica se o usuário logado está matriculado (somente se autenticado)"
 * )
 * )
 */
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
