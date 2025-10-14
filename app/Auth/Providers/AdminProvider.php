<?php

namespace App\Auth\Providers;

use App\Models\Admin;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Hash;

class AdminProvider implements UserProvider
{
    /**
     * Retrieve a user by their unique identifier.
     */
    public function retrieveById($identifier): ?Authenticatable
    {
        return Admin::find($identifier);
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     */
    public function retrieveByToken($identifier, $token): ?Authenticatable
    {
        $admin = Admin::find($identifier);

        if (! $admin || ! $admin->getRememberToken()) {
            return null;
        }

        return hash_equals($admin->getRememberToken(), $token) ? $admin : null;
    }

    /**
     * Update the "remember me" token for the given user in storage.
     */
    public function updateRememberToken(Authenticatable $user, $token): void
    {
        $user->setRememberToken($token);
        $user->save();
    }

    /**
     * Retrieve a user by the given credentials.
     */
    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        if (! isset($credentials['username'])) {
            return null;
        }

        return Admin::where('username', $credentials['username'])->first();
    }

    /**
     * Validate a user against the given credentials.
     */
    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        if (! isset($credentials['password'])) {
            return false;
        }

        return Hash::check($credentials['password'], $user->getAuthPassword());
    }

    /**
     * Rehash the user's password if required and the user is using the Bcrypt algorithm.
     */
    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): void
    {
        if ($force || $this->needsRehash($user)) {
            $user->password = Hash::make($credentials['password']);
            $user->save();
        }
    }

    /**
     * Determine if the given user needs a password rehash.
     */
    protected function needsRehash(Authenticatable $user): bool
    {
        return Hash::needsRehash($user->getAuthPassword());
    }
}
