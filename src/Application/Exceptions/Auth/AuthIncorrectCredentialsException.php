<?php
declare(strict_types=1);

namespace App\Application\Exceptions\Auth;

use App\Application\Exceptions\ForbiddenException;

class AuthIncorrectCredentialsException extends ForbiddenException
{
    public $message = 'Email and/or Password is incorrect!';
}
