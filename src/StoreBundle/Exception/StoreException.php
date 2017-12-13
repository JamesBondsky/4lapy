<?php

namespace FourPaws\StoreBundle\Exception;

use \Throwable;

class StoreException extends \Exception
{
    public function __construct($message = "", int $code = 0, Throwable $previous = null)
    {
        if (is_array($message)) {
            $message = implode('. ', $message);
        }
        
        parent::__construct($message, $code, $previous);
    }
}
