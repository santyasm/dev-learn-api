<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Video;
use App\Models\VideoProgress;
use Illuminate\Support\Facades\Auth;

class VideoProgressController extends Controller
{
    /**
     * @OA\Post(
     * path="/api/videos/{enrollment}/{video}/complete",
     * operationId="markVideoComplete",
     * tags={"Video Progress"},
     * summary="Marca um vídeo como concluído",
     * description="Registra ou garante que um vídeo específico de uma matrícula está marcado como concluído. Apenas o dono da matrícula pode executar esta ação.",
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(
     * name="enrollment",
     * in="path",
     * required=true,
     * description="UUID da matrícula",
     * @OA\Schema(type="string", format="uuid")
     * ),
     * @OA\Parameter(
     * name="video",
     * in="path",
     * required=true,
     * description="UUID do vídeo a ser marcado",
     * @OA\Schema(type="string", format="uuid")
     * ),
     * @OA\Response(
     * response=201,
     * description="Vídeo marcado como concluído com sucesso",
     * @OA\JsonContent(ref="#/components/schemas/VideoProgress")
     * ),
     * @OA\Response(response=403, description="Não autorizado (a matrícula não pertence ao usuário logado)"),
     * @OA\Response(response=404, description="Matrícula ou Vídeo não encontrados no contexto do curso")
     * )
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

    /**
     * @OA\Delete(
     * path="/api/videos/{enrollment}/{video}/complete",
     * operationId="unmarkVideoComplete",
     * tags={"Video Progress"},
     * summary="Remove a marcação de conclusão de um vídeo",
     * description="Remove o registro de conclusão de um vídeo específico. Apenas o dono da matrícula pode executar esta ação.",
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(
     * name="enrollment",
     * in="path",
     * required=true,
     * description="UUID da matrícula",
     * @OA\Schema(type="string", format="uuid")
     * ),
     * @OA\Parameter(
     * name="video",
     * in="path",
     * required=true,
     * description="UUID do vídeo a ser desmarcado",
     * @OA\Schema(type="string", format="uuid")
     * ),
     * @OA\Response(
     * response=200,
     * description="Conclusão removida com sucesso"
     * ),
     * @OA\Response(response=403, description="Não autorizado (a matrícula não pertence ao usuário logado)"),
     * @OA\Response(response=404, description="Matrícula ou registro de conclusão não encontrados")
     * )
     */
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
     * @OA\Get(
     * path="/api/enrollments/{enrollment}/completed-videos",
     * operationId="listCompletedVideos",
     * tags={"Video Progress"},
     * summary="Lista os vídeos concluídos de uma matrícula",
     * description="Retorna uma lista de IDs dos vídeos concluídos dentro de uma matrícula específica. Apenas o dono da matrícula pode acessá-la.",
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(
     * name="enrollment",
     * in="path",
     * required=true,
     * description="UUID da matrícula",
     * @OA\Schema(type="string", format="uuid")
     * ),
     * @OA\Response(
     * response=200,
     * description="Lista de IDs retornada com sucesso",
     * @OA\JsonContent(
     * @OA\Property(property="data", type="array", @OA\Items(type="string", format="uuid", description="ID do Vídeo Concluído"))
     * )
     * ),
     * @OA\Response(response=403, description="Não autorizado (a matrícula não pertence ao usuário logado)"),
     * @OA\Response(response=404, description="Matrícula não encontrada")
     * )
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
