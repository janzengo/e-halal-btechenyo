<?php

declare(strict_types=1);

namespace App\Auth\Providers;

use App\Models\Voter;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;

class VoterProvider implements UserProvider
{
    /**
     * Retrieve a user by their unique identifier.
     */
    public function retrieveById($identifier): ?Authenticatable
    {
        return Voter::find($identifier);
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     */
    public function retrieveByToken($identifier, $token): ?Authenticatable
    {
        // Voters don't use remember tokens in this system
        return null;
    }

    /**
     * Update the "remember me" token for the given user in storage.
     */
    public function updateRememberToken(Authenticatable $user, $token): void
    {
        // Voters don't use remember tokens in this system
    }

    /**
     * Retrieve a user by the given credentials.
     */
    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        if (empty($credentials['student_number'])) {
            return null;
        }

        return Voter::where('student_number', $credentials['student_number'])->first();
    }

    /**
     * Validate a user against the given credentials.
     */
    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        // For voters, we only validate that the student number exists
        // OTP validation is handled separately
        return $user instanceof Voter &&
               $user->student_number === $credentials['student_number'];
    }

    /**
     * Rehash the password if required and supported.
     */
    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): void
    {
        // Voters don't use passwords in this system
    }
}
