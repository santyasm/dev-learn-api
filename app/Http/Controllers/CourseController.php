<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Course;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

class CourseController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/courses",
     * tags={"Courses"},
     * summary="Lista todos os cursos",
     * description="Retorna uma lista de todos os cursos, incluindo o instrutor e a contagem de matrículas. Inclui status de matrícula para o usuário logado.",
     * @OA\Response(
     * response=200,
     * description="Operação bem-sucedida",
     * @OA\JsonContent(
     * type="array",
     * @OA\Items(ref="#/components/schemas/Course")
     * )
     * ),
     * @OA\Response(
     * response=500,
     * description="Erro interno no servidor"
     * ),
     * security={
     * {"bearerAuth": {}}
     * }
     * )
     */
    public function index()
    {
        try {
            $query = Course::with('instructor')
                ->withCount('enrollments');

            if (Auth::check()) {
                $userId = Auth::id();
                $query->with(['enrollments' => function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                }]);
            }

            $courses = $query->get();

            return response()->json($courses);
        } catch (\Exception $ex) {
            return response()->json([
                'message' => 'An error occurred while listing the courses.',
                'error' => $ex->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     * path="/api/courses/{id}",
     * tags={"Courses"},
     * summary="Mostra um curso específico",
     * description="Retorna os detalhes de um curso, incluindo instrutor, vídeos e matrículas.",
     * @OA\Parameter(
     * name="id",
     * in="path",
     * required=true,
     * description="UUID do curso",
     * @OA\Schema(type="string", format="uuid")
     * ),
     * @OA\Response(
     * response=200,
     * description="Operação bem-sucedida",
     * @OA\JsonContent(ref="#/components/schemas/Course")
     * ),
     * @OA\Response(
     * response=404,
     * description="Curso não encontrado"
     * ),
     * security={
     * {"bearerAuth": {}}
     * }
     * )
     */
    public function show(string $id)
    {
        try {
            $course = Course::with("instructor", "videos", "enrollments")->findOrFail($id);

            return response()->json($course);
        } catch (\Exception $ex) {
            return response()->json([
                'message' => 'An error occurred while show the course',
                'error' => $ex->getMessage()
            ], 404);
        }
    }

    /**
     * @OA\Post(
     * path="/api/courses",
     * tags={"Courses"},
     * summary="Cria um novo curso",
     * description="Registra um novo curso. Requer autenticação e validação do instrutor.",
     * security={{"bearerAuth": {}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"title", "description", "user_instructor_id"},
     * @OA\Property(property="title", type="string"),
     * @OA\Property(property="description", type="string"),
     * @OA\Property(property="thumbnail", type="string", nullable=true),
     * @OA\Property(property="price", type="number", format="float"),
     * @OA\Property(property="level", type="string", enum={"beginner", "intermediate", "advanced"}),
     * @OA\Property(property="status", type="string", enum={"draft", "published", "archived"}),
     * @OA\Property(property="user_instructor_id", type="string", format="uuid", description="ID do instrutor"),
     * @OA\Property(property="category", type="string"),
     * @OA\Property(property="duration_in_seconds", type="integer")
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="Curso criado com sucesso",
     * @OA\JsonContent(ref="#/components/schemas/Course")
     * ),
     * @OA\Response(
     * response=500,
     * description="Erro durante o registro ou instrutor inválido"
     * )
     * )
     */
    public function store(StoreCourseRequest $request)
    {
        try {
            $data = $request->validated();

            $this->validateInstructor($data['user_instructor_id']);

            $course = Course::create(
                $data
            );

            return response()->json($course, 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred during course registration.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Put(
     * path="/api/courses/{id}",
     * tags={"Courses"},
     * summary="Atualiza um curso existente",
     * description="Atualiza os dados de um curso pelo ID.",
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * required=true,
     * description="UUID do curso",
     * @OA\Schema(type="string", format="uuid")
     * ),
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * @OA\Property(property="title", type="string"),
     * @OA\Property(property="description", type="string"),
     * @OA\Property(property="price", type="number", format="float"),
     * @OA\Property(property="user_instructor_id", type="string", format="uuid", description="ID do instrutor (opcional)"),
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Curso atualizado com sucesso",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Course updated successfully")
     * )
     * ),
     * @OA\Response(
     * response=400,
     * description="Erro na atualização ou validação"
     * ),
     * @OA\Response(
     * response=404,
     * description="Curso não encontrado"
     * )
     * )
     */
    public function update(UpdateCourseRequest $request, string $id)
    {
        $data = $request->validated();
        try {

            $course = Course::findOrFail($id);

            if (!empty($data['user_instructor_id'])) {
                $this->validateInstructor($data['user_instructor_id']);
            }

            $course->update($data);

            return response()->json(['message' => 'Course updated successfully'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred during course update.',
                'error' => $e->getMessage()
            ], 400);
        }
    }


    /**
     * @OA\Delete(
     * path="/api/courses/{id}",
     * tags={"Courses"},
     * summary="Deleta um curso",
     * description="Remove um curso pelo seu ID.",
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * required=true,
     * description="UUID do curso",
     * @OA\Schema(type="string", format="uuid")
     * ),
     * @OA\Response(
     * response=204,
     * description="Curso deletado com sucesso (Sem conteúdo)"
     * ),
     * @OA\Response(
     * response=400,
     * description="Erro na deleção (curso não encontrado ou outro erro)"
     * )
     * )
     */
    public function destroy(string $id)
    {
        try {

            $courseRemoved = Course::destroy($id);

            if (!$courseRemoved) {
                throw new Exception();
            }

            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred during course deletion.',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    // Assistants
    private function validateInstructor($id)
    {
        $instructor = User::findOrFail($id);
        if ($instructor->role !== "instructor") {
            throw new Exception("Invalid instructor user");
        }
        return $instructor;
    }
}
