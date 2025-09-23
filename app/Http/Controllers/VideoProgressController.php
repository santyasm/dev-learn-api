<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Video;
use App\Models\VideoProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VideoProgressController extends Controller
{
    /**
     * Marcar vídeo como concluído
     */
    public function store($enrollmentId, $videoId)
    {
        $user = Auth::user();

        $enrollment = Enrollment::where('id', $enrollmentId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $video = Video::where('id', $videoId)
            ->where('course_id', $enrollment->course_id)
            ->firstOrFail();

        $completion = VideoProgress::firstOrCreate([
            'enrollment_id' => $enrollment->id,
            'video_id' => $video->id,
        ]);

        return response()->json([
            'message' => 'Vídeo marcado como concluído com sucesso',
            'data' => $completion,
        ], 201);
    }

    public function destroy($enrollmentId, $videoId)
    {
        $user = Auth::user();

        $enrollment = Enrollment::where('id', $enrollmentId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $completion = VideoProgress::where('enrollment_id', $enrollment->id)
            ->where('video_id', $videoId)
            ->first();

        if (!$completion) {
            return response()->json([
                'message' => 'Conclusão não encontrada'
            ], 404);
        }

        $completion->delete();

        return response()->json([
            'message' => 'Conclusão removida com sucesso',
        ], 200);
    }

    /**
     * Listar vídeos concluídos do user
     */
    public function index($enrollmentId)
    {
        $user = Auth::user();

        $enrollment = Enrollment::where('id', $enrollmentId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $completedVideos = VideoProgress::where('enrollment_id', $enrollment->id)
            ->pluck('video_id');

        return response()->json([
            'data' => $completedVideos
        ], 200);
    }
}
