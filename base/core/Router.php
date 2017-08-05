<?php

namespace core;

/**
 * Route class
 *
 * @author kohei okuda
 */
class Router {

    const CONTROLLEER_SUFFIX = 'Controller';
    const ACTION_SUFFIX = 'Action';
    const CONTROLLER_DIR = 'controllers';

    protected $default_controller = 'Index';
    protected $default_action = 'Index';
    // instance
    protected static $instance = null;
    // routes
    protected $route_strings = [];
    // compiled routes
    protected $routes = [];
    //
    protected $route = null;
    protected $controller = null;
    protected $action = null;
    protected $params = [];
    protected $controlle_instance = null;

    public static function inst($routes = [])
    {
        if (is_null(self::$instance))
        {
            self::$instance = new static($routes);
        }

        return static::$instance;
    }

    public function __construct($routes = [])
    {
        $this->route = Request::route();
        
        // When has toutes
        if ($routes)
        {
            // Set routes
            foreach ($routes as $route => $route)
            {
                Debug::v($route);
                $controller = Arr::get($route, 'controller');
                $action = Arr::get($route, 'controller');
                $func = Arr::get($route, 'controller');

//                $this->set($url, $controller, $action, $func);
            }
        }
    }

    /**
     * Set
     * 
     * @param string $url
     * @param string $controller
     * @param string $action
     */
    public function set($url, $controller = null, $action = null, callable $func = null)
    {
        if (strlen($url) > 1 AND substr($url, -1) === '/')
        {
            $url = rtrim($url, '/');
        }

        // Create route strings array and set url
        $this->_route_strings[$url]['url'] = $url;

        // has controller
        if (!is_null($controller))
        {
            $this->_route_strings[$url]['controller'] = $controller;
        }

        // has action
        if (!is_null($action))
        {
            $this->_route_strings[$url]['action'] = $action;
        }

        // has func
        if (!is_null($func))
        {
            $this->_route_strings[$url]['func'] = $func;
        }

        return $this;
    }

    public function dispatch()
    {
        // routing
        if ('' === $this->route)
        {
            $controller = $this->default_controller;
            $action = $this->default_action;
        }
        elseif (strpos($this->route, '/') === false)
        {
            $controller = $this->route;
            $action = $this->default_action;
        }
        else
        {
            $segments = explode('/', $this->route);
            $controller = $segments[0];
            $action = $segments[1];
            $params = array_slice($segments, 2);
        }

        // controller to upper snake
        $segments = explode('_', $controller);

        foreach ($segments as &$segment)
        {
            $segment = ucfirst(strtolower($segment));
        }

        // set controller name, action name and params
        $this->controller = implode('_', $segments) . self::CONTROLLEER_SUFFIX;
        $this->action = ucfirst(strtolower($action)) . self::ACTION_SUFFIX;
        $this->params = $params ?? [];

        // build controller file path and full namespace
        $controller_file = APP_PATH . self::CONTROLLER_DIR . DS . implode(DS, $segments) . self::CONTROLLEER_SUFFIX . EXT;
        $controller_full = APP_NAMESPACE . NS . self::CONTROLLER_DIR . NS . $this->controller;

        try
        {
            // check controller file
            if (!(file_exists($controller_file) AND is_readable($controller_file)))
            {
                throw new \Exception('controller not found!');
            }
            // require
            require_once $controller_file;

            // check class
            if (!class_exists($controller_full))
            {
                throw new \Exception('controller not exist!');
            }

            // make controller instance
            $this->controlle_instance = new $controller_full($this->params);

            // chekc action
            if (!method_exists($this->controlle_instance, $this->action))
            {
                throw new \Exception('action not exist!');
            }

            // do action
            $this->controlle_instance->{$this->action}();
        }
        catch (\Exception $exc)
        {
            echo $exc->getMessage() . '<br />';
            echo nl2br($exc->getTraceAsString());
        }
    }

    // seter
    public function setDefaultController($controller)
    {
        $this->default_controller = $controller;
    }

    public function setDefaultAction($acton)
    {
        $this->default_action = $acton;
    }

}
