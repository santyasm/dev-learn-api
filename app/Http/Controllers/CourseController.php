<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Course;
use App\Models\User;
use Exception;

class CourseController extends Controller
{
    public function index()
    {
        try {
            $courses = Course::all();

            return response()->json($courses);
        } catch (\Exception $ex) {
            return response()->json([
                'message' => 'An error occurred while listing the course',
                'error' => $ex->getMessage()
            ], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $course = Course::findOrFail($id);

            return response()->json($course);
        } catch (\Exception $ex) {
            return response()->json([
                'message' => 'An error occurred while show the course',
                'error' => $ex->getMessage()
            ], 404);
        }
    }

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

    private function validateInstructor($id)
    {
        $instructor = User::findOrFail($id);
        if ($instructor->role !== "instructor") {
            throw new Exception("Invalid instructor user");
        }
        return $instructor;
    }
}
