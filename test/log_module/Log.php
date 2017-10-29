<?php

namespace core;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

require './Psr/Log/LoggerInterface.php';

class Log implements LoggerInterface {

    private static $logdir;
    private static $display_on;

    public function __construct()
    {
        var_dump(LogLevel::ALERT);
        die;
        ;
    }

    public function alert($message, array $context = array()): void
    {
        
    }

    public function critical($message, array $context = array()): void
    {
        
    }

    public function debug($message, array $context = array()): void
    {
        
    }

    public function emergency($message, array $context = array()): void
    {
        
    }

    public function error($message, array $context = array()): void
    {
        
    }

    public function info($message, array $context = array()): void
    {
        
    }

    public function log($level, $message, array $context = array()): void
    {
        
    }

    public function notice($message, array $context = array()): void
    {
        
    }

    public function warning($message, array $context = array()): void
    {
        
    }

}
