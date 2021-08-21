<?php
declare(strict_types=1);

namespace App\Application\Exceptions;

class UnprocessableEntityException extends ApplicationException
{
    public $code = 422;
    public $message = 'There are attributes that are incorrect or not passed';
}
