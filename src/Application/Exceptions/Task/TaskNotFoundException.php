<?php
declare(strict_types=1);

namespace App\Application\Exceptions\Task;

use App\Application\Exceptions\RecordNotFoundException;

class TaskNotFoundException extends RecordNotFoundException
{
    public $message = 'The task you requested does not exist.';
}
