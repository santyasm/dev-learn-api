<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEnrollmentRequest;
use App\Http\Requests\UpdateEnrollmentRequest;
use App\Models\Enrollment;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

class EnrollmentController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/enrollments",
     * tags={"Enrollments"},
     * summary="Lista todas as matrículas (APENAS ADMIN)",
     * description="Retorna uma lista de todas as matrículas no sistema. Requer autenticação e role 'admin'.",
     * security={{"bearerAuth": {}}},
     * @OA\Response(
     * response=200,
     * description="Lista de matrículas retornada com sucesso",
     * @OA\JsonContent(
     * type="array",
     * @OA\Items(ref="#/components/schemas/Enrollment")
     * )
     * ),
     * @OA\Response(response=403, description="Proibido: Apenas admin pode realizar esta ação"),
     * @OA\Response(response=500, description="Erro interno no servidor")
     * )
     */
    public function index()
    {
        try {
            $enrollments = Enrollment::all();

            return response()->json($enrollments);
        } catch (\Exception $ex) {
            return response()->json([
                'message' => 'An error occurred while listing the enrollment',
                'error' => $ex->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     * path="/api/enrollments",
     * tags={"Enrollments"},
     * summary="Cria uma nova matrícula",
     * description="Cria uma matrícula. O user_id deve ser o ID do usuário logado, a menos que o usuário logado seja 'admin'.",
     * security={{"bearerAuth": {}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"course_id", "user_id"},
     * @OA\Property(property="course_id", type="string", format="uuid", description="ID do Curso"),
     * @OA\Property(property="user_id", type="string", format="uuid", description="ID do Usuário")
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="Matrícula criada com sucesso",
     * @OA\JsonContent(ref="#/components/schemas/Enrollment")
     * ),
     * @OA\Response(response=403, description="Não Autorizado: Tentativa de matricular outro usuário sem ser admin"),
     * @OA\Response(response=500, description="Erro no registro da matrícula")
     * )
     */
    public function store(StoreEnrollmentRequest $request)
    {
        $data = $request->validated();

        $loggedInUser = Auth::user();

        // Only allow the request if the user is an admin OR
        // if the user_id in the request matches the logged-in user's ID.
        if (!$loggedInUser->isAdmin() && $data['user_id'] !== $loggedInUser->id) {
            return response()->json([
                'message' => 'Unauthorized: You can only create an enrollment for your own account.'
            ], 403);
        }

        try {
            $enrollment = Enrollment::create($data);

            return response()->json([
                'message' => 'Enrollment created successfully.',
                'data'    => $enrollment
            ], 201);
        } catch (\Exception $ex) {
            return response()->json([
                'message' => 'An error occurred during enrollment registration.',
                'error' => $ex->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Put(
     * path="/api/enrollments/{id}",
     * tags={"Enrollments"},
     * summary="Atualiza uma matrícula",
     * description="Atualiza os dados de uma matrícula pelo ID. Normalmente usado apenas para atualizar o progresso (progress).",
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * required=true,
     * description="UUID da matrícula",
     * @OA\Schema(type="string", format="uuid")
     * ),
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * @OA\Property(property="progress", type="number", format="float", nullable=true, description="Progresso do curso (0.0 a 100.0)"),
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="Matrícula atualizada com sucesso",
     * @OA\JsonContent(ref="#/components/schemas/Enrollment")
     * ),
     * @OA\Response(response=404, description="Matrícula não encontrada")
     * )
     */
    public function update(UpdateEnrollmentRequest $request, string $id)
    {
        $data = $request->validated();
        try {
            $enrollment = Enrollment::findOrFail($id);

            $enrollment->update($data);

            return response()->json([
                'message' => 'Enrollment updated successfully.',
                'data'    => $enrollment
            ], 201);
        } catch (\Exception $ex) {
            return response()->json([
                'message' => 'An error occurred during enrollment update.',
                'error' => $ex->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     * path="/api/enrollments/{id}",
     * tags={"Enrollments"},
     * summary="Mostra detalhes de uma matrícula",
     * description="Retorna os detalhes de uma matrícula específica, incluindo o curso e vídeos. Apenas o usuário dono da matrícula pode acessá-la.",
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * required=true,
     * description="UUID da matrícula",
     * @OA\Schema(type="string", format="uuid")
     * ),
     * @OA\Response(
     * response=200,
     * description="Detalhes da matrícula retornados com sucesso",
     * @OA\JsonContent(
     * @OA\Property(property="data", ref="#/components/schemas/Enrollment")
     * )
     * ),
     * @OA\Response(response=403, description="Não Autorizado: Matrícula pertence a outro usuário."),
     * @OA\Response(response=404, description="Matrícula não encontrada")
     * )
     */
    public function show(string $id)
    {
        try {
            $enrollment = Enrollment::with([
                'course' => function ($query) {
                    $query->with('instructor', 'videos')
                        ->withCount('enrollments');
                },
                'completedVideos'
            ])->findOrFail($id);

            if (Auth::id() !== $enrollment->user_id) {
                return response()->json(['message' => 'Unauthorized to view this enrollment.'], 403);
            }

            $completedVideoIds = $enrollment->completedVideos->pluck('video_id')->toArray();

            $videosWithCompletion = $enrollment->course->videos->map(function ($video) use ($completedVideoIds) {
                $video->completed = in_array($video->id, $completedVideoIds);
                return $video;
            });

            $enrollment->course->setRelation('videos', $videosWithCompletion);

            return response()->json([
                'data' => $enrollment
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $mnfe) {
            return response()->json([
                'message' => 'Enrollment not found.',
                'error' => $mnfe->getMessage()
            ], 404);
        } catch (\Exception $ex) {
            return response()->json([
                'message' => 'An error occurred during listing enrollment',
                'error' => $ex->getMessage()
            ], 500);
        }
    }


    /**
     * @OA\Get(
     * path="/api/user/enrollments",
     * tags={"Enrollments"},
     * summary="Lista as matrículas do usuário logado",
     * description="Retorna todas as matrículas associadas ao usuário autenticado.",
     * security={{"bearerAuth": {}}},
     * @OA\Response(
     * response=200,
     * description="Lista de matrículas do usuário retornada com sucesso",
     * @OA\JsonContent(
     * @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Enrollment"))
     * )
     * ),
     * @OA\Response(response=500, description="Erro interno no servidor")
     * )
     */
    public function getMyEnrollments()
    {
        try {
            $enrollments = Enrollment::where("user_id", Auth::user()->id)->get();

            return response()->json([
                'data'    => $enrollments
            ], 200);
        } catch (\Exception $ex) {
            return response()->json([
                'message' => 'An error occurred during listing enrollment',
                'error' => $ex->getMessage()
            ], 500);
        }
    }
}
