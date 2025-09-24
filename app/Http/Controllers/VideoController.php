<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportVideosRequest;
use App\Http\Requests\StoreVideoRequest;
use App\Http\Requests\UpdateVideoRequest;
use App\Models\Course;
use App\Models\Video;
use App\Services\GumletService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use OpenApi\Annotations as OA;

class VideoController extends Controller
{
    protected GumletService $gumlet;

    public function __construct(GumletService $gumlet)
    {
        $this->gumlet = $gumlet;
    }


    /**
     * @OA\Get(
     * path="/api/videos",
     * tags={"Videos"},
     * summary="Lista todos os vídeos",
     * description="Retorna uma lista de todos os vídeos.",
     * security={{"bearerAuth": {}}},
     * @OA\Response(
     * response=200,
     * description="Lista de vídeos retornada com sucesso",
     * @OA\JsonContent(
     * type="array",
     * @OA\Items(ref="#/components/schemas/Video")
     * )
     * ),
     * @OA\Response(response=500, description="Erro interno no servidor")
     * )
     */
    public function index()
    {
        try {
            $videos = Video::all();


            return response()->json($videos);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'An error occurred while the video listing.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     * path="/api/videos/{video}",
     * tags={"Videos"},
     * summary="Busca um vídeo específico",
     * description="Retorna os detalhes de um vídeo pelo ID.",
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(
     * name="video",
     * in="path",
     * required=true,
     * description="UUID do vídeo",
     * @OA\Schema(type="string", format="uuid")
     * ),
     * @OA\Response(
     * response=200,
     * description="Detalhes do vídeo retornados com sucesso",
     * @OA\JsonContent(ref="#/components/schemas/Video")
     * ),
     * @OA\Response(response=404, description="Vídeo não encontrado")
     * )
     */
    public function show(string $id)
    {
        try {
            $video = Video::findOrFail($id);
            return response()->json($video);
        } catch (\Exception $ex) {
            return response()->json([
                'message' => 'Video not found',
                'error' => $ex->getMessage()
            ], 404);
        }
    }

    /**
     * @OA\Post(
     * path="/api/videos",
     * tags={"Videos"},
     * summary="Cria um novo vídeo (APENAS ADMIN)",
     * description="Armazena um novo vídeo, associa a um curso e atualiza a duração total do curso. Requer role 'admin'.",
     * security={{"bearerAuth": {}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"title", "description", "course_id", "gumlet_asset_id", "video_order"},
     * @OA\Property(property="title", type="string"),
     * @OA\Property(property="description", type="string"),
     * @OA\Property(property="course_id", type="string", format="uuid", description="ID do Curso"),
     * @OA\Property(property="gumlet_asset_id", type="string", description="ID do Asset no Gumlet"),
     * @OA\Property(property="video_order", type="integer", description="Ordem do vídeo no curso")
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="Vídeo criado com sucesso",
     * @OA\JsonContent(ref="#/components/schemas/Video")
     * ),
     * @OA\Response(response=403, description="Proibido: Apenas admin pode realizar esta ação"),
     * @OA\Response(response=500, description="Erro interno no servidor ou Asset não encontrado")
     * )
     */
    public function store(StoreVideoRequest $request)
    {
        $data = $request->validated();
        DB::beginTransaction();
        try {
            $asset = $this->gumlet->getAsset($data['gumlet_asset_id']);
            if (!$asset) {
                throw new Exception("Asset not found on Gumlet.");
            }

            $durationSeconds = (int) $asset['input']['duration'];

            $course = Course::findOrFail($data['course_id']);
            $course->duration_in_seconds += $durationSeconds;
            $course->save();

            $newVideo = Video::create(array_merge(
                $data,
                ['duration_in_seconds' => $durationSeconds]
            ));

            DB::commit();

            return response()->json($newVideo, 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'An error occurred while creating the video.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Put(
     * path="/api/videos/{id}",
     * tags={"Videos"},
     * summary="Atualiza um vídeo existente (APENAS ADMIN)",
     * description="Atualiza os dados de um vídeo pelo ID. Requer role 'admin'.",
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * required=true,
     * description="UUID do vídeo",
     * @OA\Schema(type="string", format="uuid")
     * ),
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * @OA\Property(property="title", type="string", nullable=true),
     * @OA\Property(property="description", type="string", nullable=true),
     * @OA\Property(property="video_order", type="integer", nullable=true)
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Vídeo atualizado com sucesso",
     * @OA\JsonContent(@OA\Property(property="message", type="string", example="Video updated successfully"))
     * ),
     * @OA\Response(response=403, description="Proibido: Apenas admin pode realizar esta ação"),
     * @OA\Response(response=404, description="Vídeo não encontrado")
     * )
     */
    public function update(UpdateVideoRequest $request, string $id)
    {
        $data = $request->validated();
        try {
            $video = Video::findOrFail($id);

            $video->update($data);

            return response()->json(["message" => "Video updated successfully"]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'An error occurred while updating the videos.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Post(
     * path="/api/videos/import",
     * tags={"Videos"},
     * summary="Importa vídeos de uma playlist do Gumlet (APENAS ADMIN)",
     * description="Deleta vídeos existentes do curso e importa uma nova lista de assets de uma playlist Gumlet. Requer role 'admin'.",
     * security={{"bearerAuth": {}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"playlist_id", "course_id"},
     * @OA\Property(property="playlist_id", type="string", description="ID da playlist no Gumlet"),
     * @OA\Property(property="course_id", type="string", format="uuid", description="ID do curso para associar os vídeos")
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Vídeos importados com sucesso",
     * @OA\JsonContent(@OA\Property(property="message", type="string", example="Videos from playlist successfully imported."))
     * ),
     * @OA\Response(response=403, description="Proibido: Apenas admin pode realizar esta ação"),
     * @OA\Response(response=500, description="Erro de importação ou Playlist não encontrada")
     * )
     */
    public function importVideosFromGumletPlaylist(ImportVideosRequest $request)
    {
        $data = $request->validated();
        $totalDuration = 0;
        $videosToImport = [];

        DB::beginTransaction();
        try {
            Video::where('course_id', $data['course_id'])->delete();

            $playlistAssets = $this->gumlet->getPlaylistAssets($data["playlist_id"]);

            if (empty($playlistAssets['asset_list'])) {
                throw new Exception("Playlist not found or empty on Gumlet.");
            }

            $order = 1;

            foreach ($playlistAssets['asset_list'] as $videoAsset) {
                $totalDuration += (int) ($videoAsset['duration'] ?? 0);

                $videosToImport[] = [
                    'id' => (string) Str::uuid(),
                    'title'         => $videoAsset['title'] ?? 'Sem Título',
                    'description'   => $videoAsset['description'] ?? 'Sem descrição',
                    'gumlet_asset_id'     => $videoAsset['id'] ?? null,
                    'course_id'     => $data["course_id"],
                    'video_order' => $order++,
                    'duration_in_seconds' => (int) ($videoAsset['duration'] ?? 0),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            Video::insert($videosToImport);

            // Incrementa a duração total do curso
            $course = Course::findOrFail($data['course_id']);
            $course->duration_in_seconds += $totalDuration;
            $course->save();

            DB::commit();

            return response()->json([
                'message' => 'Videos from playlist successfully imported.',
                'data'    => $playlistAssets
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'An error occurred while importing the videos.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     * path="/api/videos/{id}",
     * tags={"Videos"},
     * summary="Deleta um vídeo (APENAS ADMIN)",
     * description="Remove um vídeo pelo seu ID. Requer role 'admin'.",
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * required=true,
     * description="UUID do vídeo",
     * @OA\Schema(type="string", format="uuid")
     * ),
     * @OA\Response(response=204, description="Vídeo deletado com sucesso (No Content)"),
     * @OA\Response(response=400, description="Erro na deleção"),
     * @OA\Response(response=403, description="Proibido: Apenas admin pode realizar esta ação")
     * )
     */
    public function destroy(string $id)
    {
        try {
            $videoRemoved = Video::destroy($id);

            if (!$videoRemoved) {
                return response()->json(['message' => 'Video not found or could not be deleted.'], 404);
            }

            return response()->json(null, 204);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred during video deletion.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
