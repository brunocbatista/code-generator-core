<?php
declare(strict_types=1);

namespace App\Application\Exceptions;

class InternalServerException extends ApplicationException
{
    public $code = 500;
    public $message = 'Internal server error.';
}
