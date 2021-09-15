<?php
declare(strict_types=1);

namespace App\Application\Exceptions\Auth;

use App\Application\Exceptions\RecordNotFoundException;

class AuthTokenNotFoundException extends RecordNotFoundException
{
    public $message = 'The token you requested does not exist.';
}
