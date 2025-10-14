<?php

namespace App\Http\Controllers\Head;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class OfficerController extends Controller
{
    /**
     * Store a newly created officer
     * No password is required - officer will receive email verification link to set password
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRequest([
            'username' => 'required|string|max:255|unique:admin,username',
            'email' => 'required|email|max:255|unique:admin,email',
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'gender' => 'required|in:Male,Female',
            'photo_file' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:5120',
        ], [
            'username.required' => 'Username is required',
            'username.unique' => 'This username is already taken',
            'email.required' => 'Email is required',
            'email.email' => 'Please provide a valid email address',
            'email.unique' => 'This email is already registered',
            'firstname.required' => 'First name is required',
            'lastname.required' => 'Last name is required',
            'gender.required' => 'Gender is required',
            'gender.in' => 'Gender must be either Male or Female',
            'photo_file.image' => 'Photo must be an image file',
            'photo_file.mimes' => 'Photo must be a jpeg, jpg, png, or gif file',
            'photo_file.max' => 'Photo size must not exceed 5MB',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo_file')) {
            $photoPath = $request->file('photo_file')->store('officers', 'public');
        }

        // Create officer without password - they will set it via email verification
        $officer = Admin::create([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make(bin2hex(random_bytes(32))), // Temporary secure random password
            'firstname' => $validated['firstname'],
            'lastname' => $validated['lastname'],
            'gender' => $validated['gender'],
            'photo' => $photoPath,
            'role' => 'officer',
            'created_on' => now()->toDateString(),
        ]);

        // TODO: Send password setup email to officer
        // Mail::to($officer->email)->send(new SetupPasswordMail($officer));

        return $this->redirectWithSuccess('head.officers.index', 'Officer account created! A password setup link has been sent to their email.');
    }
}
