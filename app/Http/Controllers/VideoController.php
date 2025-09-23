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

class VideoController extends Controller
{
    protected GumletService $gumlet;

    public function __construct(GumletService $gumlet)
    {
        $this->gumlet = $gumlet;
    }

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
     * Armazena um novo vídeo e incrementa a duração total do curso.
     *
     * @param  \App\Http\Requests\StoreVideoRequest  $request
     * @return \Illuminate\Http\JsonResponse
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
     * Importa vídeos de uma playlist do Gumlet e os salva no banco de dados.
     *
     * @param  \App\Http\Requests\ImportVideosRequest  $request
     * @return \Illuminate\Http\JsonResponse
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
                    'video_order' => $order++, // Incrementa a ordem a cada iteração
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
}
