<?php
declare(strict_types=1);

namespace App\Application\Exceptions;

class ForbiddenException extends ApplicationException
{
    public $code = 403;
    public $message = 'You do not have permission.';
}
