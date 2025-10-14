<?php

declare(strict_types=1);

namespace App\Http\Controllers\Head;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CandidateController extends Controller
{
    /**
     * Store a newly created candidate
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRequest([
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'position_id' => 'required|exists:positions,id',
            'partylist_id' => 'required|exists:partylists,id',
            'platform' => 'required|string',
            'photo' => 'nullable|string',
        ], [
            'firstname.required' => 'First name is required',
            'lastname.required' => 'Last name is required',
            'position_id.required' => 'Please select a position',
            'position_id.exists' => 'Selected position does not exist',
            'partylist_id.required' => 'Please select a partylist',
            'partylist_id.exists' => 'Selected partylist does not exist',
            'platform.required' => 'Platform is required',
        ]);

        // Handle photo upload if provided
        $photoPath = null;
        if ($request->hasFile('photo_file')) {
            $photoPath = $request->file('photo_file')->store('candidates', 'public');
        } elseif (! empty($validated['photo'])) {
            $photoPath = $validated['photo'];
        }

        $candidate = Candidate::create([
            'firstname' => $validated['firstname'],
            'lastname' => $validated['lastname'],
            'position_id' => $validated['position_id'],
            'partylist_id' => $validated['partylist_id'],
            'platform' => $validated['platform'],
            'photo' => $photoPath,
            'votes' => 0,
        ]);

        // Logging is handled automatically by the Loggable trait

        return $this->redirectWithSuccess('head.candidates.index', 'Candidate created successfully!');
    }

    /**
     * Update the specified candidate
     */
    public function update(Request $request, Candidate $candidate): RedirectResponse
    {
        $validated = $this->validateRequest([
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'position_id' => 'required|exists:positions,id',
            'partylist_id' => 'required|exists:partylists,id',
            'platform' => 'required|string',
            'photo' => 'nullable|string',
        ], [
            'firstname.required' => 'First name is required',
            'lastname.required' => 'Last name is required',
            'position_id.required' => 'Please select a position',
            'position_id.exists' => 'Selected position does not exist',
            'partylist_id.required' => 'Please select a partylist',
            'partylist_id.exists' => 'Selected partylist does not exist',
            'platform.required' => 'Platform is required',
        ]);

        // Handle photo upload if provided
        $photoPath = $candidate->photo; // Keep existing photo by default
        if ($request->hasFile('photo_file')) {
            $photoPath = $request->file('photo_file')->store('candidates', 'public');
        } elseif (! empty($validated['photo'])) {
            $photoPath = $validated['photo'];
        }

        // Check if any changes were made
        $hasChanges =
            $candidate->firstname !== $validated['firstname'] ||
            $candidate->lastname !== $validated['lastname'] ||
            $candidate->position_id != $validated['position_id'] ||
            $candidate->partylist_id != $validated['partylist_id'] ||
            $candidate->platform !== $validated['platform'] ||
            $candidate->photo !== $photoPath;

        if (! $hasChanges) {
            return $this->redirectWithError('head.candidates.index', 'No changes were made to the candidate');
        }

        $candidate->update([
            'firstname' => $validated['firstname'],
            'lastname' => $validated['lastname'],
            'position_id' => $validated['position_id'],
            'partylist_id' => $validated['partylist_id'],
            'platform' => $validated['platform'],
            'photo' => $photoPath,
        ]);

        // Logging is handled automatically by the Loggable trait

        return $this->redirectWithSuccess('head.candidates.index', 'Candidate updated successfully!');
    }

    /**
     * Remove the specified candidate
     */
    public function destroy(Candidate $candidate): RedirectResponse
    {
        // Check if candidate has votes
        if ($candidate->votes > 0) {
            return $this->redirectWithError('head.candidates.index', 'Cannot delete candidate with existing votes');
        }

        $candidate->delete();

        // Logging is handled automatically by the Loggable trait

        return $this->redirectWithSuccess('head.candidates.index', 'Candidate deleted successfully!');
    }
}
