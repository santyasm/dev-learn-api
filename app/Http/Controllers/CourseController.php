<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseRequest;
use App\Models\Course;
use App\Models\User;
use App\Services\GumletService;
use Illuminate\Http\Request;

class CourseController extends Controller
{

    public function store(StoreCourseRequest $request)
    {

        try {
            $data = $request->validated();

            $instructor = User::findOrFail($data['user_instructor_id']);

            if ($instructor->role !== "instructor") {
                return response()->json(["message" => "invalid instructor user"], 404);
            }

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
}
