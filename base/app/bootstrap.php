<?php

require_once CORE_PATH . 'Autoload.php';
spl_autoload_register(array('AutoLoader', 'loadClass'));

use core\Router;

/*
 * rooting
 */
$routes = [
    // The default route
    '/' => array(
        'controller' => 'home',
        'action' => 'index',
    ),
    // User edit
    '/user/edit' => array(
        'controller' => 'user',
        'action' => 'edit',
    ),
    // User :id
    '/user/:id' => array(
        'controller' => 'user',
        'controller' => 'show',
    ),
    // Item :action
    '/item/:action' => array(
        'controller' => 'item',
    ),
    // Home :action :id
    '/home/:action/:id' => array(
        'controller' => 'home',
        'func' => function($params)
        {
            $route = [
                'controller' => $params['controller'],
                'action' => $params['action'],
                'id' => $params['id'],
            ];
            return $route;
        }
    ),
    // :controller index
    '/:controller' => array(
        'action' => 'index',
    ),
    // :controller :action :id
    '/:controller/:action/:id' => array(),
    // :controller :action
    '/:controller/:action/' => array(),
    // not found
    '' => array(
        'controller' => 404,
        'action' => 'index',
        'func' => function($params)
        {
            $segments = explode('/', trim($params['url'], '/'));

            if (true)
            {
                return ['controller' => reset($segments), 'action' => end($segments)];
            }
        }),
];

$router = Router::inst($routes);

// Base url
$router->set('/', 'home');

// User edit
$router->set('/user/edit', 'user', 'edit');

// User :id
$router->set('/user/:id', 'user', 'show');

// Item :action
$router->set('/item/:action', 'item');

// Home :action :id
$router->set('/home/:action/:id', 'home', null, function($params)
{
    $route = [
        'controller' => $params['controller'],
        'action' => $params['action'],
        'id' => $params['id'],
    ];
    return $route;
});

// :controller index
$router->set('/:controller');

// :controller :action :id
$router->set('/:controller/:action/:id');

// :controller :action
$router->set('/:controller/:action');

// not found
$router->set('/error', 404);


$response = $router->dispatch();

