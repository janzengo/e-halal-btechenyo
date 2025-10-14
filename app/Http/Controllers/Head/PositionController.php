<?php

namespace App\Http\Controllers\Head;

use App\Http\Controllers\Controller;
use App\Models\Position;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PositionController extends Controller
{
    /**
     * Display a listing of positions
     */
    public function index(): Response
    {
        $positions = Position::withCount('candidates')
            ->orderBy('priority')
            ->get()
            ->map(function ($position) {
                return [
                    'id' => $position->id,
                    'title' => $position->description,
                    'max_winners' => $position->max_vote,
                    'priority' => $position->priority,
                    'candidates_count' => $position->candidates_count,
                    'created_at' => $position->created_at?->toISOString(),
                    'updated_at' => $position->updated_at?->toISOString(),
                ];
            });

        return Inertia::render('head/positions/index', [
            'positions' => $positions,
        ]);
    }

    /**
     * Store a newly created position
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRequest([
            'title' => 'required|string|max:255|unique:positions,description',
            'max_winners' => 'required|integer|min:1|max:10',
        ], [
            'title.required' => 'Position title is required',
            'title.unique' => 'A position with this title already exists',
            'max_winners.required' => 'Maximum winners is required',
            'max_winners.min' => 'Maximum winners must be at least 1',
            'max_winners.max' => 'Maximum winners cannot exceed 10',
        ]);

        // Auto-assign priority based on creation order
        $nextPriority = Position::max('priority') + 1;

        $position = Position::create([
            'description' => $validated['title'],
            'max_vote' => $validated['max_winners'],
            'priority' => $nextPriority,
        ]);

        // Logging is handled automatically by the Loggable trait

        return $this->redirectWithSuccess('head.positions.index', 'Position created successfully!');
    }

    /**
     * Update the specified position
     */
    public function update(Request $request, Position $position): RedirectResponse
    {
        $validated = $this->validateRequest([
            'title' => 'required|string|max:255|unique:positions,description,'.$position->id,
            'max_winners' => 'required|integer|min:1|max:10',
        ], [
            'title.required' => 'Position title is required',
            'title.unique' => 'A position with this title already exists',
            'max_winners.required' => 'Maximum winners is required',
            'max_winners.min' => 'Maximum winners must be at least 1',
            'max_winners.max' => 'Maximum winners cannot exceed 10',
        ]);

        $oldValues = $position->getAttributes();

        $position->update([
            'description' => $validated['title'],
            'max_vote' => $validated['max_winners'],
            // Priority is not updated - it remains the same
        ]);

        // Logging is handled automatically by the Loggable trait

        return $this->redirectWithSuccess('head.positions.index', 'Position updated successfully!');
    }

    /**
     * Remove the specified position
     */
    public function destroy(Position $position): RedirectResponse
    {
        // Check if position has candidates
        if ($position->candidates()->count() > 0) {
            return $this->redirectWithError(
                'head.positions.index',
                'Cannot delete position that has candidates assigned to it. Please reassign or remove candidates first.'
            );
        }

        $positionName = $position->description;
        $position->delete();

        // Logging is handled automatically by the Loggable trait

        return $this->redirectWithSuccess('head.positions.index', 'Position deleted successfully!');
    }

    /**
     * Get candidates for a specific position
     */
    public function candidates(Position $position): JsonResponse
    {
        $candidates = $position->candidates()
            ->with('partylist')
            ->orderBy('lastname')
            ->get()
            ->map(function ($candidate) {
                return [
                    'id' => $candidate->id,
                    'firstname' => $candidate->firstname,
                    'lastname' => $candidate->lastname,
                    'partylist' => $candidate->partylist ? [
                        'id' => $candidate->partylist->id,
                        'name' => $candidate->partylist->name,
                        'color' => $candidate->partylist->color,
                    ] : null,
                    'photo' => $candidate->photo,
                    'platform' => $candidate->platform,
                    'created_at' => $candidate->created_at?->toISOString(),
                ];
            });

        return response()->json($candidates);
    }

    /**
     * Reorder positions
     */
    public function reorder(Request $request): JsonResponse
    {
        $validated = $this->validateRequest([
            'positions' => 'required|array',
            'positions.*.id' => 'required|exists:positions,id',
            'positions.*.priority' => 'required|integer|min:1',
        ]);

        try {
            foreach ($validated['positions'] as $positionData) {
                Position::where('id', $positionData['id'])
                    ->update(['priority' => $positionData['priority']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Position order updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update position order',
            ], 500);
        }
    }
}
