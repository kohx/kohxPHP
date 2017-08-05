<?php

namespace core;

class View {

    protected static $global_values = [];
    protected static $global_safes = [];
    protected $values = [];
    protected $safes = [];
    protected $parts = [];
    protected $view_dir = 'views';
    protected $view_ext = '.php';
    protected $file_path = '';

    /**
     * Constructor
     * 
     * @param string $file
     * @param mix $values
     */
    public function __construct($file = null, $values = [])
    {
        $this->file_path = $file;
        $this->values = $values;
    }

    /**
     * Make instance
     * 
     * @param type $file
     * @param type $values
     * @return \static
     */
    public static function fact($file = null, $values = [])
    {
        return new static($file, $values);
    }

    public function file($file_path)
    {
        $this->file_path = $file_path;

        return $this;
    }

    public function set($key, $value = null, $safe = true)
    {
        if (is_array($key))
        {
            foreach ($key as $k => $v)
            {
                $this->safes[$k] = $safe;
                $this->values[$k] = $v;
            }
        }
        else
        {
            $this->safes[$key] = $safe;
            $this->values[$key] = $value;
        }

        return $this;
    }

    public function bind($key, &$value = null, $safe = true)
    {
        if (is_array($key))
        {
            foreach ($key as $k => &$v)
            {
                $this->safes[$k] = $safe;
                $this->values[$k] = &$v;
            }
        }
        else
        {
            $this->safes[$key] = $safe;
            $this->values[$key] = &$value;
        }

        return $this;
    }

    public static function setGlobal($key, $value = null, $safe = true)
    {
        if (is_array($key))
        {
            foreach ($key as $k => $v)
            {
                self::$global_safes[$k] = $safe;
                self::$global_values[$k] = $v;
            }
        }
        else
        {
            self::$global_safes[$key] = $safe;
            self::$global_values[$key] = $value;
        }
    }

    public static function bindGlobal($key, &$value = null, $safe = true)
    {
        if (is_array($key))
        {
            foreach ($key as $k => &$v)
            {
                $this->safes[$k] = $safe;
                self::$global_values[$k] = &$v;
            }
        }
        else
        {
            $this->safes[$key] = $safe;
            self::$global_values[$key] = &$value;
        }
    }

    public function part($key, $file_path = null)
    {
        if (is_array($key))
        {
            foreach ($key as $k => $v)
            {
                $this->parts[$k] = $v;
            }
        }
        else
        {
            $this->parts[$key] = $file_path;
        }
    }

    /**
     * Build body
     * 
     * @return string html
     */
    public function render()
    {
        // part output and set to values
        if ($this->parts)
        {
            foreach ($this->parts as $key => $file_path)
            {
                $this->safes[$key] = false;
                $this->values[$key] = $this->output($file_path);
            }
        }

        // build view
        $view = $this->output($this->file_path);

        return $view;
    }

    protected function output($file_path)
    {
        try
        {
            if (!$file_path)
            {
                throw new \Exception('view file not set!');
            }

            $view_file = APP_PATH . $this->view_dir . DS . str_replace('/', DS, $file_path) . $this->view_ext;

            if (!(file_exists($view_file) AND is_readable($view_file)))
            {
                throw new \Exception('view file not found!');
            }

            // extract values
            $global_values = Arr::map(self::$global_values, function($v, $k)
                    {
                        return Arr::get(self::$global_safes, $k) ? htmlspecialchars($v, ENT_QUOTES) : $v;
                    });

            $values = Arr::map($this->values, function($v, $k)
                    {
                        return Arr::get($this->safes, $k) ? htmlspecialchars($v, ENT_QUOTES) : $v;
                    });

            extract($global_values, EXTR_REFS);
            extract($values, EXTR_REFS);

            // Start the output buffer
            ob_start();

            include $view_file;

            // Get the captured output and close the buffer
            $view = ob_get_clean();

            return $view;
        }
        catch (Exception $exc)
        {
            // Delete the output buffer
            ob_end_clean();

            echo $exc->getMessage() . '<br />';
            echo nl2br($exc->getTraceAsString());

            throw $exc;
        }
    }

}
