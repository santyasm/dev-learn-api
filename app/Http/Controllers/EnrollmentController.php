<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEnrollmentRequest;
use App\Http\Requests\UpdateEnrollmentRequest;
use App\Models\Enrollment;
use Illuminate\Support\Facades\Auth;

class EnrollmentController extends Controller
{
    public function index()
    {
        try {
            if (!Auth::user()->isAdmin()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $enrollments = Enrollment::all();

            return response()->json($enrollments);
        } catch (\Exception $ex) {
            return response()->json([
                'message' => 'An error occurred while listing the enrollment',
                'error' => $ex->getMessage()
            ], 500);
        }
    }

    public function store(StoreEnrollmentRequest $request)
    {
        $data = $request->validated();

        $loggedInUser = Auth::user();

        // Only allow the request if the user is an admin OR
        // if the user_id in the request matches the logged-in user's ID.
        // This is crucial to prevent a regular user from enrolling other users.
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

    public function show(string $id)
    {
        try {
            $enrollment = Enrollment::with([
                'course' => function ($query) {
                    $query->with('instructor', 'videos');
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
        } catch (\Exception $ex) {
            return response()->json([
                'message' => 'An error occurred during listing enrollment',
                'error' => $ex->getMessage()
            ], 500);
        }
    }


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
