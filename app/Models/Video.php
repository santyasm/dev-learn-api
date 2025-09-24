<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 * schema="Video",
 * title="Video",
 * description="Detalhes de um vídeo de curso",
 * @OA\Property(property="id", type="string", format="uuid", description="UUID do vídeo"),
 * @OA\Property(property="title", type="string", description="Título do vídeo"),
 * @OA\Property(property="description", type="string", description="Descrição do vídeo"),
 * @OA\Property(property="course_id", type="string", format="uuid", description="ID do curso ao qual pertence"),
 * @OA\Property(property="gumlet_asset_id", type="string", description="ID do asset no Gumlet"),
 * @OA\Property(property="video_order", type="integer", description="Ordem do vídeo no curso"),
 * @OA\Property(property="duration_in_seconds", type="integer", description="Duração do vídeo em segundos"),
 * @OA\Property(property="created_at", type="string", format="date-time", description="Data de criação"),
 * @OA\Property(property="updated_at", type="string", format="date-time", description="Data da última atualização")
 * )
 */
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

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function videoProgresses()
    {
        return $this->hasMany(VideoProgress::class);
    }
}
