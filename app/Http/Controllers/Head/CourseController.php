<?php

declare(strict_types=1);

namespace App\Http\Controllers\Head;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * Store a newly created course
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRequest([
            'code' => 'required|string|max:10|unique:courses,code',
            'description' => 'required|string|max:255|unique:courses,description',
        ], [
            'code.required' => 'Course code is required',
            'code.unique' => 'A course with this code already exists',
            'code.max' => 'Course code cannot exceed 10 characters',
            'description.required' => 'Course description is required',
            'description.unique' => 'A course with this description already exists',
            'description.max' => 'Course description cannot exceed 255 characters',
        ]);

        $course = Course::create([
            'code' => $validated['code'],
            'description' => $validated['description'],
        ]);

        // Logging is handled automatically by the Loggable trait

        return $this->redirectWithSuccess('head.courses.index', 'Course created successfully!');
    }

    /**
     * Update the specified course
     */
    public function update(Request $request, Course $course): RedirectResponse
    {
        $validated = $this->validateRequest([
            'code' => 'required|string|max:10|unique:courses,code,'.$course->id,
            'description' => 'required|string|max:255|unique:courses,description,'.$course->id,
        ], [
            'code.required' => 'Course code is required',
            'code.unique' => 'A course with this code already exists',
            'code.max' => 'Course code cannot exceed 10 characters',
            'description.required' => 'Course description is required',
            'description.unique' => 'A course with this description already exists',
            'description.max' => 'Course description cannot exceed 255 characters',
        ]);

        // Check if any changes were made
        $hasChanges =
            $course->code !== $validated['code'] ||
            $course->description !== $validated['description'];

        if (! $hasChanges) {
            return $this->redirectWithError('head.courses.index', 'No changes were made to the course');
        }

        $course->update([
            'code' => $validated['code'],
            'description' => $validated['description'],
        ]);

        // Logging is handled automatically by the Loggable trait

        return $this->redirectWithSuccess('head.courses.index', 'Course updated successfully!');
    }

    /**
     * Remove the specified course
     */
    public function destroy(Course $course): RedirectResponse
    {
        // Check if course has voters
        if ($course->voters()->count() > 0) {
            return $this->redirectWithError(
                'head.courses.index',
                'Cannot delete course that has voters assigned to it. Please reassign or remove voters first.'
            );
        }

        $courseName = $course->description;
        $course->delete();

        // Logging is handled automatically by the Loggable trait

        return $this->redirectWithSuccess('head.courses.index', 'Course deleted successfully!');
    }

    /**
     * Get voters for a specific course
     */
    public function voters(Course $course)
    {
        $voters = $course->voters()
            ->orderBy('lastname')
            ->get()
            ->map(function ($voter) {
                return [
                    'id' => $voter->id,
                    'student_id' => $voter->student_id,
                    'firstname' => $voter->firstname,
                    'lastname' => $voter->lastname,
                    'year_level' => $voter->year_level,
                    'has_voted' => $voter->has_voted,
                    'created_at' => $voter->created_at->toISOString(),
                ];
            });

        return response()->json([
            'voters' => $voters,
        ]);
    }
}
