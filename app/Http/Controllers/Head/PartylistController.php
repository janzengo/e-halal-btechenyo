<?php

namespace App\Http\Controllers\Head;

use App\Http\Controllers\Controller;
use App\Models\Partylist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PartylistController extends Controller
{
    /**
     * Store a newly created partylist
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRequest([
            'name' => 'required|string|max:255',
            'color' => 'required|string|regex:/^#[0-9A-F]{6}$/i',
            'platform' => 'nullable|string|max:1000',
        ], [
            'name.required' => 'Partylist name is required',
            'color.required' => 'Please select a color for the partylist',
            'color.regex' => 'Color must be a valid hex color code',
            'platform.max' => 'Platform description cannot exceed 1000 characters',
        ]);

        // Auto-append "Partylist" if not already included
        $name = $validated['name'];
        if (! str_contains(strtolower($name), 'partylist')) {
            $name .= ' Partylist';
        }

        // Check uniqueness with the modified name
        if (Partylist::where('name', $name)->exists()) {
            return $this->redirectWithError('head.partylists.index', 'A partylist with this name already exists');
        }

        $partylist = Partylist::create([
            'name' => $name,
            'color' => $validated['color'],
            'platform' => $validated['platform'] ?? null,
        ]);

        // Logging is handled automatically by the Loggable trait

        return $this->redirectWithSuccess('head.partylists.index', 'Partylist created successfully!');
    }

    /**
     * Update the specified partylist
     */
    public function update(Request $request, Partylist $partylist): RedirectResponse
    {
        $validated = $this->validateRequest([
            'name' => 'required|string|max:255|unique:partylists,name,'.$partylist->id,
            'color' => 'required|string|regex:/^#[0-9A-F]{6}$/i',
            'platform' => 'nullable|string|max:1000',
        ], [
            'name.required' => 'Partylist name is required',
            'name.unique' => 'A partylist with this name already exists',
            'color.required' => 'Please select a color for the partylist',
            'color.regex' => 'Color must be a valid hex color code',
            'platform.max' => 'Platform description cannot exceed 1000 characters',
        ]);

        $oldValues = $partylist->getAttributes();

        $partylist->update([
            'name' => $validated['name'],
            'color' => $validated['color'],
            'platform' => $validated['platform'] ?? null,
        ]);

        // Logging is handled automatically by the Loggable trait

        return $this->redirectWithSuccess('head.partylists.index', 'Partylist updated successfully!');
    }

    /**
     * Remove the specified partylist
     */
    public function destroy(Partylist $partylist): RedirectResponse
    {
        // Check if partylist has candidates
        if ($partylist->candidates()->count() > 0) {
            return $this->redirectWithError(
                'head.partylists.index',
                'Cannot delete partylist that has candidates assigned to it. Please reassign or remove candidates first.'
            );
        }

        $partylistName = $partylist->name;
        $partylist->delete();

        // Logging is handled automatically by the Loggable trait

        return $this->redirectWithSuccess('head.partylists.index', 'Partylist deleted successfully!');
    }

    /**
     * Get candidates for a specific partylist
     */
    public function candidates(Partylist $partylist)
    {
        $candidates = $partylist->candidates()
            ->with('position:id,description')
            ->select(['id', 'firstname', 'lastname', 'position_id', 'photo', 'platform', 'created_at'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($candidate) {
                return [
                    'id' => $candidate->id,
                    'firstname' => $candidate->firstname,
                    'lastname' => $candidate->lastname,
                    'position' => [
                        'id' => $candidate->position->id,
                        'title' => $candidate->position->description,
                    ],
                    'photo' => $candidate->photo,
                    'platform' => $candidate->platform,
                    'created_at' => $candidate->created_at->toISOString(),
                ];
            });

        return response()->json([
            'candidates' => $candidates,
        ]);
    }
}
