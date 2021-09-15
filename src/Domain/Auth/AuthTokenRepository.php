<?php
declare(strict_types=1);

namespace App\Domain\Auth;

interface AuthTokenRepository
{
    /**
     * @param AuthToken $token
     * @return bool
     */
    public function save(AuthToken $token): bool;

    /**
     * @param int $userId
     * @return AuthToken
     */
    public function findTokenOfUserId(int $userId): AuthToken;

    /**
     * @param string $token
     * @return bool
     */
    public function verifyToken(string $token): bool;
}
