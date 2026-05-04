<?php

namespace App\Http\Controllers\Head\Settings;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    /**
     * Update only the profile avatar photo.
     */
    public function updateAvatar(Request $request): JsonResponse
    {
        /** @var Admin $user */
        $user = $request->user('admin');

        $validated = $request->validate([
            'photo_file' => ['required', 'image', 'mimes:jpeg,jpg,png,gif', 'max:5120'],
        ]);

        // Delete old photo if exists
        if ($user->photo && \Storage::disk('public')->exists($user->photo)) {
            \Storage::disk('public')->delete($user->photo);
        }
        
        // Store new photo
        $photoPath = $request->file('photo_file')->store('heads', 'public');
        
        $user->update(['photo' => $photoPath]);

        return response()->json([
            'success' => true,
            'message' => 'Profile photo updated successfully.',
            'photo_url' => $photoPath ? '/storage/' . $photoPath : null,
        ]);
    }
    /**
     * Update the head admin's profile details and optional photo.
     */
    public function update(Request $request): RedirectResponse
    {
        /** @var Admin $user */
        $user = $request->user('admin');

        $validated = $request->validate([
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('admin', 'email')->ignore($user->id),
            ],
            'photo_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif', 'max:5120'],
        ]);

        // Handle photo upload
        $photoPath = $user->photo; // keep current if not replaced
        if ($request->hasFile('photo_file')) {
            // Delete old photo if exists
            if ($user->photo && \Storage::disk('public')->exists($user->photo)) {
                \Storage::disk('public')->delete($user->photo);
            }
            
            $photoPath = $request->file('photo_file')->store('heads', 'public');
        }

        $user->update([
            'firstname' => $validated['firstname'],
            'lastname' => $validated['lastname'],
            'email' => $validated['email'],
            'photo' => $photoPath,
        ]);

        return redirect()->route('head.settings.profile')->with('success', 'Profile updated successfully.');
    }
}
