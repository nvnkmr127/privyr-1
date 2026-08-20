<?php

namespace Webkul\Lead\Exceptions;

use Exception;

class LeadIngestionException extends Exception
{
    public static function duplicate(string $message = 'Duplicate lead detected'): self
    {
        return new self($message, 409);
    }

    public static function validation(string $message = 'Invalid lead data'): self
    {
        return new self($message, 422);
    }
    
    public static function rejection(string $message = 'Lead rejected'): self
    {
        return new self($message, 406);
    }
}
