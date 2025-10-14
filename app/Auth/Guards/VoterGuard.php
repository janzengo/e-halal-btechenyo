<?php

declare(strict_types=1);

namespace App\Auth\Guards;

use App\Models\Voter;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;

class VoterGuard implements Guard
{
    use GuardHelpers;

    /**
     * The request instance.
     */
    protected Request $request;

    /**
     * Create a new authentication guard.
     */
    public function __construct(UserProvider $provider, Request $request)
    {
        $this->provider = $provider;
        $this->request = $request;
    }

    /**
     * Get the currently authenticated user.
     */
    public function user(): ?Voter
    {
        // If we've already retrieved the user for the current request we can just
        // return it back immediately. We do not want to fetch the user data on
        // every call to this method because that would be tremendously slow.
        if (! is_null($this->user)) {
            return $this->user;
        }

        // Check if voter is authenticated via session
        $studentNumber = $this->request->session()->get('voter.student_number');

        if ($studentNumber) {
            $this->user = Voter::where('student_number', $studentNumber)->first();
        }

        return $this->user;
    }

    /**
     * Validate a user's credentials.
     */
    public function validate(array $credentials = []): bool
    {
        if (empty($credentials['student_number'])) {
            return false;
        }

        $voter = $this->provider->retrieveByCredentials($credentials);

        return $voter !== null;
    }

    /**
     * Attempt to authenticate a user using the given credentials.
     */
    public function attempt(array $credentials = [], bool $remember = false): bool
    {
        $voter = $this->provider->retrieveByCredentials($credentials);

        if ($voter) {
            $this->login($voter, $remember);

            return true;
        }

        return false;
    }

    /**
     * Log a user into the application.
     */
    public function login(Voter $voter, bool $remember = false): void
    {
        $this->updateSession($voter->student_number);

        // If we have an event dispatcher instance set we will fire an event so that
        // any listeners will hook into the authentication events and run actions
        // based on the login and logout events fired from the guard instances.
        $this->fireLoginEvent($voter, $remember);

        $this->setUser($voter);
    }

    /**
     * Update the session with the given ID.
     */
    protected function updateSession(string $studentNumber): void
    {
        $this->request->session()->put('voter.student_number', $studentNumber);
        $this->request->session()->migrate(true);
    }

    /**
     * Log the user out of the application.
     */
    public function logout(): void
    {
        $voter = $this->user();

        // If we have an event dispatcher instance, we can fire off the logout
        // event so any further processing can be done. This allows the
        // developers to listen for logout events and take action.
        $this->fireLogoutEvent($voter);

        $this->clearUserDataFromStorage();

        if (! is_null($voter)) {
            $this->cycleRememberToken($voter);
        }

        // Once we have fired the logout event we will clear the users out of memory
        // so they are no longer available as the user is no longer considered as
        // being signed into this application and should not be available here.
        $this->user = null;
    }

    /**
     * Clear the user data from the session and cookies.
     */
    protected function clearUserDataFromStorage(): void
    {
        $this->request->session()->forget('voter.student_number');
    }

    /**
     * Fire the login event if the dispatcher is set.
     */
    protected function fireLoginEvent(Voter $voter, bool $remember = false): void
    {
        // Implementation for firing login event if needed
    }

    /**
     * Fire the logout event if the dispatcher is set.
     */
    protected function fireLogoutEvent(?Voter $voter): void
    {
        // Implementation for firing logout event if needed
    }

    /**
     * Cycle the "remember me" token of the user.
     */
    protected function cycleRememberToken(Voter $voter): void
    {
        // Voters don't use remember tokens in this system
    }
}
