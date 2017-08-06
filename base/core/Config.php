<?php

namespace core;

/**
 * Config class
 *
 * @author kohei okuda
 */
class Config {

    // instance
    protected static $instance = null;
    protected $config_dir = 'config';
    protected $values = [];
    protected $file = [];

    public static function inst($file = null, $dir = null)
    {
        if (is_null(self::$instance))
        {
            self::$instance = new static($dir);
        }

        if ($file)
        {
            self::$instance->file = $file;
            self::$instance->read($file);
        }

        return static::$instance;
    }

    /**
     * Constructers
     * 
     * @param Config
     */
    public function __construct($dir)
    {
        if ($dir)
        {
            $this->config_dir = $dir;
        }
    }

    public function read($file)
    {
        if (!Arr::get($this->values, $file))
        {
            $config_file = APP_PATH . $this->config_dir . DS . $file . EXT;

            if (!(file_exists($config_file) AND is_readable($config_file)))
            {
                throw new \Exception('config file not found!');
            }

            $this->values[$file] = require $config_file;
        }

        $this->file = $this->values[$file];

        return $this;
    }

    public function get($value = null)
    {
        if (!$this->file)
        {
            throw new \Exception('config file not readed!');
        }

        if ($value)
        {
            return Arr::get($this->file, $value);
        }
        
        return $this->file;
    }

}
