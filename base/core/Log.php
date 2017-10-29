<?php

namespace core;

use core\Log\LoggerInterface;
use core\Log\LoggerTrait;
use core\Log\LogLevel;

/**
 * Log
 *
 * @author kohx
 */
class Log implements LoggerInterface {
    
    public $datetime_format = 'Y-m-d h:i:s';

    use LoggerTrait;
    
    public function log( $level, $message, array $context = array() ): void
    {
        
        $date = new \DateTime();
        $datetime = $date->format($this->datetime_format);
        
        $trace = debug_backtrace();
        Debug::v($trace);
        die;
    }

}