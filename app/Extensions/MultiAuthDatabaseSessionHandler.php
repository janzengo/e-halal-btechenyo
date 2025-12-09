<?php

declare(strict_types=1);

namespace App\Extensions;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Session\DatabaseSessionHandler;

class MultiAuthDatabaseSessionHandler extends DatabaseSessionHandler
{
    /**
     * The guards to check for authenticated users.
     */
    protected array $guards = ['admin', 'voter'];

    /**
     * Create a new database session handler instance.
     */
    public function __construct(
        ConnectionInterface $connection,
        string $table,
        int $minutes,
        Container $container,
        protected bool $useTransactions = false
    ) {
        parent::__construct($connection, $table, $minutes, $container);
    }

    /**
     * Get the default payload for the session.
     */
    protected function getDefaultPayload($data): array
    {
        $payload = [
            'payload' => base64_encode($data),
            'last_activity' => $this->currentTime(),
        ];

        if (!$this->container) {
            return $payload;
        }

        return tap($payload, function (&$payload) {
            $this->addUserInformation($payload)
                 ->addRequestInformation($payload);
        });
    }

    /**
     * Add the user information to the session payload.
     */
    protected function addUserInformation(&$payload): static
    {
        $user = null;
        $guard = null;

        // Check each guard for an authenticated user
        foreach ($this->guards as $guardName) {
            try {
                $authGuard = $this->container->make('auth')->guard($guardName);
                if ($authGuard->check()) {
                    $user = $authGuard->user();
                    $guard = $guardName;
                    break;
                }
            } catch (\Exception $e) {
                // Guard doesn't exist or error, continue to next
                continue;
            }
        }

        if ($user) {
            $payload['authenticatable_id'] = $user->getAuthIdentifier();
            $payload['authenticatable_type'] = get_class($user);
        } else {
            $payload['authenticatable_id'] = null;
            $payload['authenticatable_type'] = null;
        }

        return $this;
    }

    /**
     * Add the request information to the session payload.
     */
    protected function addRequestInformation(&$payload): static
    {
        if ($this->container->bound('request')) {
            $request = $this->container->make('request');
            $payload['ip_address'] = $request->ip();
            $payload['user_agent'] = substr((string) $request->header('User-Agent'), 0, 500);
        }

        return $this;
    }
}
