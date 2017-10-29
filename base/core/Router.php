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
    public static function inst( $routes = [] ) {

        if ( is_null( self::$instance ) ) {

            self::$instance = new static( $routes );
        }

        return static::$instance;
    }

    /**
     * construct
     * 
     * @param array $routes
     */
    public function __construct( $routes = [] ) {

        $this->pathinfo = Request::pathinfo();

        // When has toutes
        if ( $routes ) {
            // Set routes
            foreach ( $routes as $key => $value ) {
                $controller = Arr::get( $value, 'controller' );
                $action = Arr::get( $value, 'action' );
                $func = Arr::get( $value, 'func' );

                $this->set( $key, $controller, $action, $func );
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
    public function set( string $route, string $controller = null, string $action = null, callable $func = null ) {

        $this->routes[$route] = [
            'route' => $route,
            'controller' => $controller,
            'action' => $action,
            'func' => $func,
        ];

        return $this;
    }

    protected function compile() {

        // Iterate routes whith set patterns
        foreach ( $this->routes as $route => &$params ) {
            $segments = explode( '/', ltrim( $route, '/' ) );

            $pattern = '[^/]';
//            $pattern = '[1-9a-zA-Z_-]';
//            $pattern = '[1-9]';
//            $pattern = '[a-zA-Z_-]';

            foreach ( $segments as &$segment ) {
                // has param
                if ( strpos( $segment, ':' ) === 0 ) {
                    $name = substr( $segment, 1 );
                    $segment = "(?<{$name}>{$pattern}+)";
                }
            }
            $params['pattern'] = '#^/' . implode( '/', $segments ) . '$#';
        }

        // if the first letter is not "/" addition "/"
        if ( substr( $this->pathinfo, 0, 1 ) !== '/' ) {
            $this->pathinfo = '/' . $this->pathinfo;
        }

        // Iterate routes with get route params from this route
        $result = [];
        foreach ( $this->routes as &$params ) {
            // Declare matches
            $matches = [];
            if ( preg_match( Arr::get( $params, 'pattern' ), $this->pathinfo, $matches ) ) {
                foreach ( $matches as $key => $segment ) {
                    // When not number set to result
                    if ( ! is_numeric( $key ) ) {
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

        // When result has func
        if ( Arr::get( $result, 'func' ) ) {
            
            $temp = Arr::get( $result, 'func' )( $result );

            foreach ( $temp as $key => $segment ) {
                $result[$key] = $segment;
            }
        }

        return $result;
    }

    public function dispatch() {
        
        $route = $this->compile();
        
        $controller = Arr::get( $route, 'controller' );
        $action = Arr::get( $route, 'action' );
//        var_dump( $route );

        $params = array_filter( $route, function($value) {
            $filter = [ 'route' ];
            return in_array( $value, $filter );
        } );
//        var_dump( $params );

        // controller to upper snake
        $segments = explode( '_', $controller );

        foreach ( $segments as &$segment ) {
            $segment = ucfirst( strtolower( $segment ) );
        }

        // set controller name, action name and params
        $this->controller = implode('_', $segments) . self::CONTROLLEER_SUFFIX;
        $this->action = ucfirst(strtolower($action)) . self::ACTION_SUFFIX;
        $this->params = $params ?? [];

        // build controller file path and full namespace
        $controller_file = APP_PATH . self::CONTROLLER_DIR . DS . implode( DS, $segments ) . self::CONTROLLEER_SUFFIX . EXT;
        $controller_full = APP_NAMESPACE . NS . self::CONTROLLER_DIR . NS . $this->controller;
        
        try {
            
            // check controller file
            if ( ! (file_exists( $controller_file ) AND is_readable( $controller_file )) ) {
                throw new Exception( 'controller not found!' );
            }
           
            // require
            require_once $controller_file;

            // check class
            if ( ! class_exists( $controller_full ) ) {
                Debug::v('in');
                die;
                throw new Exception( 'controller not exist!' );
            }

            // make controller instance
            $this->controlle_instance = new $controller_full( $this->params );

            // chekc action
            if ( ! method_exists( $this->controlle_instance, $this->action ) ) {
                throw new Exception( 'action not exist!' );
            }

            // do action
            $this->controlle_instance->{$this->action}();
        }
        catch ( Exception $exc ) {
            
            echo $exc->getMessage() . '<br />';
            echo nl2br( $exc->getTraceAsString() );
        }
    }

    // seter
    public function setDefaultController( $controller ) {
        $this->default_controller = $controller;
    }

    public function setDefaultAction( $acton ) {
        $this->default_action = $acton;
    }

}
