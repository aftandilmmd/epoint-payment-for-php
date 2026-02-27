<?php

namespace Aftandilmmd\EpointPayment\Exceptions;

use Exception;

class EpointException extends Exception
{
    public function __construct(
        string $message = '',
        int $code = 0,
        public readonly ?array $errors = null,
    ) {
        parent::__construct($message, $code);
    }
}
