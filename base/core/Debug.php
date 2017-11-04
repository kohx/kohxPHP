<?php

namespace core;

/**
 * Debug class
 *
 * @author kohei okuda
 */
class Debug {

    public static $instance;
    protected $_start_timer = [];
    protected $_end_timer = [];

    public static function v($value, $message = '')
    {
        $bt = debug_backtrace();
        $file = Arr::get($bt, '0.file');
        $line = Arr::get($bt, '0.line');
        
        if($message) {
            echo "[ {$message} ]<br/ >";
        }

        echo "{$file} - {$line}<br/ ><pre>";
        var_export($value);
        echo "</pre><br/ >";
    }

    /**
     * timer
     * 
     * 			Debug::timer()->start('name');
     * 			Debug::timer()->end('name');
     * 			Debug::timer()->show('name');
     * 
     * @return this
     */
    public static function timer()
    {
        // Make instance
        if (!isset(static::$instance))
        {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public function __construct()
    {
        
    }

    /*
     * Timer start
     */

    public function start($name = 'default')
    {
        $this->_start_timer[$name] = microtime();
    }

    public function end($name = 'default')
    {
        $this->_end_timer[$name] = microtime();
    }

    public function show($name = 'default')
    {
        $bt = debug_backtrace();
        $file = $bt[0]['file'];
        $line = $bt[0]['line'];
        $def = round($this->_end_timer[$name] - $this->_start_timer[$name], 5);

        echo "$file\n$line\n";
        echo '<pre>';
        print_r("timer[{$name}]: {$def}");
        echo '</pre>';
    }

}
