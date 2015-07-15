<?php

namespace Main\Exceptions;

class ParseErrorException extends \Exception
{
    public function __construct()
    {
        $this->message = 'ERROR when parsing sending data';
    }
}