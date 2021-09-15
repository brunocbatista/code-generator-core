<?php
declare(strict_types=1);

namespace App\Domain\Auth;


use App\Domain\User\User;

interface AuthTokenService
{
    /**
     * @param User $userInfo
     * @return AuthToken
     */
    public function generate(User $userInfo): AuthToken;
}
