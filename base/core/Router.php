<?php

namespace core;

use \Exception;

/**
 * Route class
 *
 * @author kohei okuda
 */
class Router
{

    const CONTROLLEER_SUFFIX = 'Controller';
    const ACTION_SUFFIX = 'Action';
    const CONTROLLER_DIR = 'controllers';

    protected $default_controller = 'Index';
    protected $default_action = 'Index';
    // instance
    protected static $instance = null;
    // routes
    protected $routes = [];
    //
    protected $pathinfo = null;
    protected $controller = null;
    protected $action = null;
    protected $params = [];
    protected $controlle_instance = null;

    /**
     * inst
     * 
     * @param array $routes
     * @return Router
     */
    public static function inst($routes = []) {

        if (is_null(self::$instance)) {

            self::$instance = new static($routes);
        }

        return static::$instance;
    }

    /**
     * construct
     * 
     * @param array $routes
     */
    public function __construct($routes = []) {

        $this->pathinfo = Request::pathinfo();

        // When has toutes
        if ($routes) {
            // Set routes
            foreach ($routes as $key => $value) {
                $directory = Arr::get($value, 'directory');
                $controller = Arr::get($value, 'controller');
                $action = Arr::get($value, 'action');
                $func = Arr::get($value, 'func');

                $this->set($key, $directory, $controller, $action, $func);
            }
        }
    }

    /**
     * Set
     * 
     * @param string $route
     * @param string $controller
     * @param string $action
     */
    public function set(string $route, string $directory = null, string $controller = null, string $action = null, callable $func = null) {

        $this->routes[$route] = [
            'route' => $route,
            'directory' => $directory,
            'controller' => $controller,
            'action' => $action,
            'func' => $func,
        ];

        return $this;
    }

    protected function compile() {

        // Iterate routes whith set patterns
        foreach ($this->routes as $route => &$params) {
            $segments = explode('/', ltrim($route, '/'));

            $pattern = '[^/]';
//            $pattern = '[1-9a-zA-Z_-]';
//            $pattern = '[1-9]';
//            $pattern = '[a-zA-Z_-]';

            foreach ($segments as &$segment) {
                // has param
                if (strpos($segment, ':') === 0) {
                    $name = substr($segment, 1);
                    $segment = "(?<{$name}>{$pattern}+)";
                }
            }
            $params['pattern'] = '#^/' . implode('/', $segments) . '$#';
        }

        // if the first letter is not "/" addition "/"
        if (substr($this->pathinfo, 0, 1) !== '/') {
            $this->pathinfo = '/' . $this->pathinfo;
        }

        // Iterate routes with get route params from this route
        $result = [];

        // ルートを検索
        foreach ($this->routes as &$params) {

            $pattern = Arr::get($params, 'pattern');
            $matches = [];
            if (preg_match($pattern, $this->pathinfo, $matches)) {
                // プレグマッチの結果をチェック
                foreach ($matches as $key => $segment) {

                    // サブパターンだけを取得するので結果がナンバー以外をパーラムとして取得
                    if ( ! is_numeric($key)) {
                        $params[$key] = $segment;
                    }
                }

                $result = $params;
                break;
            }
            else {
                $result = $params;
            }

            $result['pathinfo'] = $this->pathinfo;
        }

        // resultがfuncを持っている場合
        if (Arr::get($result, 'func')) {

            $temp = Arr::get($result, 'func')($result);

            foreach ($temp as $key => $segment) {

                $result[$key] = $segment;
            }
        }

        return $result;
    }

    // $this->pathinfo で振り分け
    public function dispatch() {

        // compiled route
        $route = $this->compile();

        // set directory name
        $directory = Arr::get($route, 'directory');
        $this->directory = $directory ? strtolower(trim($directory, '/')) : '';

        // set controller name
        $controller = Arr::get($route, 'controller');
        $this->controller = $controller ? strtolower($controller) . self::CONTROLLEER_SUFFIX : $this->default_controller . self::CONTROLLEER_SUFFIX;
        
        // set action name
        $action = Arr::get($route, 'action');
        $this->action = $action ? strtolower($action) . self::ACTION_SUFFIX : $this->default_action . self::ACTION_SUFFIX;

        // select ather then 'route', 'func', 'pattern'
        $this->params = Arr::filterKeys($route, ['route', 'func', 'pattern']);

        // build controller file path
        $directory_ds = $this->directory ? str_replace('/', DS, $this->directory) : '';
        $controller_file = APP_PATH . self::CONTROLLER_DIR . DS . $directory_ds . DS . $this->controller . EXT;

        // ネームスペースをビルド
        $directory_ns = $this->directory ? str_replace('/', NS, $this->directory) : '';
        $controller_namespace = APP_NAMESPACE . NS . self::CONTROLLER_DIR . NS . $directory_ns . NS . $this->controller;

        try {

            // check controller file
            if ( ! (file_exists($controller_file) AND is_readable($controller_file))) {
                throw new Exception($controller_file . ': controller not found!');
            }

            // require
            require_once $controller_file;

            // check class
            if ( ! class_exists($controller_namespace)) {

                throw new Exception('controller not exist!');
            }

            // make controller instance
            $this->controlle_instance = new $controller_namespace($this->params);

            // chekc action
            if ( ! method_exists($this->controlle_instance, $this->action)) {
                throw new Exception('action not exist!');
            }

            // do action
            $this->controlle_instance->{$this->action}();
        }
        catch (Exception $exc) {

            echo $exc->getMessage() . '<br />';
            echo nl2br($exc->getTraceAsString());
        }
    }

    // seter
    public function setDefaultController($controller) {
        $this->default_controller = $controller;
    }

    public function setDefaultAction($acton) {
        $this->default_action = $acton;
    }

}
