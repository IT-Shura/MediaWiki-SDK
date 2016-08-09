<?php

namespace MediaWiki\Api\Exceptions;

class AccessDeniedException extends ApiException
{
    public function __construct($message, $code)
    {
        $this->message = $message;
        $this->code = $code;
    }
}
