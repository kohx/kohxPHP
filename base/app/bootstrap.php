<?php

require_once CORE_PATH . 'Autoload.php';
spl_autoload_register(array('AutoLoader', 'loadClass'));

use core\Router;
use core\Config;
use core\Arr;

/*
 * routing
 */
$routes = Config::inst()->read('routes')->get();

$router = Router::inst($routes);

// Base url
//$router->set('/', 'home');
//// User edit
//$router->set('/user/edit', 'user', 'edit');
//
//// User :id
//$router->set('/user/:id', 'user', 'show');
//
//// Item :action
//$router->set('/item/:action', 'item');
//
//// Home :action :id
//$router->set('/home/:action/:id', 'home', null, function($params)
//{
//    $route = [
//        'controller' => $params['controller'],
//        'action' => $params['action'],
//        'id' => $params['id'],
//    ];
//    return $route;
//});
//
//// :controller index
//$router->set('/:controller');
//
//// :controller :action :id
//$router->set('/:controller/:action/:id');
//
//// :controller :action
//$router->set('/:controller/:action');
//
//// not found
//$router->set('/error', 404);


$response = $router->dispatch();

