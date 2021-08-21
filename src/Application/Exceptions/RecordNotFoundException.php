<?php
declare(strict_types=1);

namespace App\Application\Exceptions;

class RecordNotFoundException extends ApplicationException
{
    public $code = 404;
    public $message = 'The record could not be found.';
}
