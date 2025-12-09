<?php

declare(strict_types=1);

namespace App\Http\Controllers\Head;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Voter;
use App\Services\LoggingService;
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
     * Bulk delete voters
     */
    public function bulkDelete(Request $request): RedirectResponse
    {
        $validated = $this->validateRequest([
            'voter_ids' => 'required|array|min:1',
            'voter_ids.*' => 'required|integer|exists:voters,id',
        ], [
            'voter_ids.required' => 'No voters selected for deletion',
            'voter_ids.array' => 'Invalid voter selection format',
            'voter_ids.min' => 'At least one voter must be selected',
            'voter_ids.*.exists' => 'One or more selected voters do not exist',
        ]);

        $voterIds = $validated['voter_ids'];
        
        // Get voters to be deleted
        $voters = Voter::whereIn('id', $voterIds)->get();
        
        // Check if any voters have already voted
        $votedVoters = $voters->where('has_voted', true);
        if ($votedVoters->isNotEmpty()) {
            $votedStudentNumbers = $votedVoters->pluck('student_number')->join(', ');
            return $this->redirectWithError('head.voters.index', 
                "Cannot delete voters who have already voted: {$votedStudentNumbers}");
        }

        // Collect voter data for logging before deletion
        $deletedVoters = [];
        foreach ($voters as $voter) {
            $deletedVoters[] = [
                'id' => $voter->id,
                'student_number' => $voter->student_number,
                'course_id' => $voter->course_id,
                'course_name' => $voter->course?->description ?? 'Unknown',
            ];
        }

        // Perform bulk deletion using direct database query to avoid individual logging
        $deletedCount = Voter::whereIn('id', $voterIds)->delete();

        // Log the bulk deletion action
        LoggingService::logAdminAction(
            actionType: 'bulk_delete',
            description: "Bulk deleted {$deletedCount} voter(s)",
            modelType: 'App\Models\Voter',
            metadata: [
                'deleted_count' => $deletedCount,
                'deleted_voters' => $deletedVoters,
                'voter_ids' => $voterIds,
            ]
        );

        return $this->redirectWithSuccess('head.voters.index', 
            "Successfully deleted {$deletedCount} voter(s)!");
    }

    /**
     * Download Excel template for voter import with course dropdown validation
     */
    public function downloadTemplate()
    {
        try {
            $export = new \App\Exports\VotersTemplateExport();
            $filename = 'voters_import_template_' . date('Y-m-d_H-i-s') . '.xlsx';
            
            return \Maatwebsite\Excel\Facades\Excel::download($export, $filename);

        } catch (\Exception $e) {
            return $this->redirectWithError('head.voters.index', 'Error generating template: ' . $e->getMessage());
        }
    }

    /**
     * Import voters from CSV file
     */
    public function importCsv(Request $request): RedirectResponse
    {
        \Log::info('Import CSV called', [
            'has_file' => $request->hasFile('csv_file'),
            'files' => $request->allFiles(),
        ]);

        $validated = $this->validateRequest([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
        ], [
            'csv_file.required' => 'Please select a CSV file to upload',
            'csv_file.mimes' => 'File must be in CSV format',
            'csv_file.max' => 'File size must not exceed 10MB',
        ]);

        \Log::info('Validation passed', ['validated' => $validated]);

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
