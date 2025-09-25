<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 * schema="VideoProgress",
 * title="VideoProgress",
 * description="Registro de vídeo concluído por um usuário em uma matrícula",
 * @OA\Property(property="id", type="string", format="uuid", description="UUID do registro de progresso"),
 * @OA\Property(property="enrollment_id", type="string", format="uuid", description="ID da matrícula"),
 * @OA\Property(property="video_id", type="string", format="uuid", description="ID do vídeo concluído"),
 * @OA\Property(property="created_at", type="string", format="date-time", description="Data de marcação como concluído"),
 * @OA\Property(property="updated_at", type="string", format="date-time", description="Data da última atualização")
 * )
 */
class VideoProgress extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'video_progress';

    protected $fillable = [
        'enrollment_id',
        'video_id',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class, 'video_id');
    }
}
