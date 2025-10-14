<?php

declare(strict_types=1);

namespace App\Http\Controllers\Head;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Voter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class VoterController extends Controller
{
    /**
     * Store a newly created voter
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRequest([
            'student_number' => 'required|string|digits:9|unique:voters,student_number',
            'course_id' => 'required|integer|exists:courses,id',
        ], [
            'student_number.required' => 'Student number is required',
            'student_number.digits' => 'Student number must be exactly 9 digits',
            'student_number.unique' => 'This student number is already registered',
            'course_id.required' => 'Course is required',
            'course_id.exists' => 'Selected course does not exist',
        ]);

        Voter::create([
            'student_number' => $validated['student_number'],
            'course_id' => $validated['course_id'],
            'has_voted' => false,
        ]);

        return $this->redirectWithSuccess('head.voters.index', 'Voter registered successfully!');
    }

    /**
     * Update the specified voter
     */
    public function update(Request $request, Voter $voter): RedirectResponse
    {
        // Prevent updating if voter has already voted
        if ($voter->has_voted) {
            return $this->redirectWithError('head.voters.index', 'Cannot update voter who has already voted');
        }

        $validated = $this->validateRequest([
            'student_number' => 'required|string|digits:9|unique:voters,student_number,'.$voter->id,
            'course_id' => 'required|integer|exists:courses,id',
        ], [
            'student_number.required' => 'Student number is required',
            'student_number.digits' => 'Student number must be exactly 9 digits',
            'student_number.unique' => 'This student number is already registered',
            'course_id.required' => 'Course is required',
            'course_id.exists' => 'Selected course does not exist',
        ]);

        // Check if any changes were made
        $hasChanges =
            $voter->student_number !== $validated['student_number'] ||
            $voter->course_id !== $validated['course_id'];

        if (! $hasChanges) {
            return $this->redirectWithError('head.voters.index', 'No changes were made to the voter');
        }

        $voter->update([
            'student_number' => $validated['student_number'],
            'course_id' => $validated['course_id'],
        ]);

        return $this->redirectWithSuccess('head.voters.index', 'Voter updated successfully!');
    }

    /**
     * Remove the specified voter
     */
    public function destroy(Voter $voter): RedirectResponse
    {
        // Prevent deleting if voter has already voted
        if ($voter->has_voted) {
            return $this->redirectWithError('head.voters.index', 'Cannot delete voter who has already voted');
        }

        $voter->delete();

        return $this->redirectWithSuccess('head.voters.index', 'Voter deleted successfully!');
    }

    /**
     * Download template CSV for voter import
     */
    public function downloadTemplate()
    {
        $courses = Course::orderBy('code')->get();

        $csv = "Student Number,Course\n";
        $csv .= "# Instructions:\n";
        $csv .= "# - Student Number must be exactly 9 digits (e.g., 202320023)\n";
        $csv .= "# - This will become the student's email: [student_number]@btech.ph.education\n";
        $csv .= "# - Course must match exactly one of the courses listed below\n";
        $csv .= "# \n";
        $csv .= "# Available Courses:\n";

        foreach ($courses as $course) {
            $csv .= "# - {$course->code} ({$course->description})\n";
        }

        $csv .= "# \n";
        $csv .= "# Example rows (delete these before importing):\n";
        $csv .= "202320023,{$courses->first()->code}\n";
        $csv .= "202320024,{$courses->first()->code}\n";

        $filename = 'voters_import_template_'.date('Y-m-d').'.csv';

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Import voters from CSV file
     */
    public function importCsv(Request $request): RedirectResponse
    {
        $validated = $this->validateRequest([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
        ], [
            'csv_file.required' => 'Please select a CSV file to upload',
            'csv_file.mimes' => 'File must be in CSV format',
            'csv_file.max' => 'File size must not exceed 10MB',
        ]);

        try {
            $file = $request->file('csv_file');
            $handle = fopen($file->getRealPath(), 'r');

            if ($handle === false) {
                throw new \Exception('Unable to open the uploaded file');
            }

            // Get course mapping (course_code => course_id)
            $courseMapping = Course::pluck('id', 'code')->toArray();

            // Skip header row
            $headers = fgetcsv($handle);

            $success = 0;
            $failed = 0;
            $errors = [];
            $rowNumber = 2;

            while (($row = fgetcsv($handle)) !== false) {
                // Skip empty rows and comment rows
                if (empty(array_filter($row)) || (isset($row[0]) && str_starts_with(trim($row[0]), '#'))) {
                    continue;
                }

                if (count($row) < 2) {
                    $errors[] = "Row {$rowNumber}: Missing required columns";
                    $failed++;
                    $rowNumber++;

                    continue;
                }

                $studentNumber = trim($row[0]);
                $courseCode = trim($row[1]);

                // Validate student number format
                if (! preg_match('/^\d{9}$/', $studentNumber)) {
                    $errors[] = "Row {$rowNumber}: Invalid student number '{$studentNumber}'. Must be exactly 9 digits.";
                    $failed++;
                    $rowNumber++;

                    continue;
                }

                // Check if course exists
                if (! isset($courseMapping[$courseCode])) {
                    $errors[] = "Row {$rowNumber}: Invalid course code '{$courseCode}'";
                    $failed++;
                    $rowNumber++;

                    continue;
                }

                // Check if student number already exists
                if (Voter::where('student_number', $studentNumber)->exists()) {
                    $errors[] = "Row {$rowNumber}: Student number '{$studentNumber}' already registered";
                    $failed++;
                    $rowNumber++;

                    continue;
                }

                try {
                    Voter::create([
                        'student_number' => $studentNumber,
                        'course_id' => $courseMapping[$courseCode],
                        'has_voted' => false,
                    ]);
                    $success++;
                } catch (\Exception $e) {
                    $errors[] = "Row {$rowNumber}: {$e->getMessage()}";
                    $failed++;
                }

                $rowNumber++;
            }

            fclose($handle);

            if ($failed > 0 && $success === 0) {
                return $this->redirectWithError('head.voters.index', "Import failed. {$failed} error(s): ".implode('; ', array_slice($errors, 0, 3)));
            }

            if ($failed > 0) {
                return $this->redirectWithSuccess('head.voters.index', "Import completed with {$success} success(es) and {$failed} error(s). Check details above.");
            }

            return $this->redirectWithSuccess('head.voters.index', "Successfully imported {$success} voter(s)!");
        } catch (\Exception $e) {
            return $this->redirectWithError('head.voters.index', 'Import error: '.$e->getMessage());
        }
    }
}
