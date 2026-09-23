<?php

namespace App\Services;

/** A workflow rule was not met. The message is safe to show (Arabic, no internal data). */
class WorkflowException extends \RuntimeException
{
    public function __construct(string $message, public readonly string $reason = 'not_allowed')
    {
        parent::__construct($message);
    }
}
