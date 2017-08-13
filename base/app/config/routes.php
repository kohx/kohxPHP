<?php

use core\Debug;

return [
    /*
     * The default route
     */
    '/' => [
        'controller' => 'home',
        'action' => 'index',
    ],
    /*
     * User edit
     */
    '/user/edit' => [
        'controller' => 'user',
        'action' => 'edit',
    ],
    /*
     * User detail :id
     */
    '/user/:id' => [
        'controller' => 'user',
        'action' => 'detail',
    ],
    /*
     * Item :action
     */
    '/item/:action' => [
        'controller' => 'item',
    ],
    /*
     * Home :action :id
     */
    '/home/:action/:id' => [
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
    ],
    /*
     * :controller index
     */
    '/:controller' => [
        'action' => 'index',
        ],
    /*
     * :controller :action :id
     */
//    '/:controller/:action/:id' => array(
//    ),
    // :controller :action
    '/:controller/:action/' => array(),
    // not found
//    '' => array(
//        'controller' => 404,
//        'action' => 'index',
//        'func' => function($params)
//        {
//            $segments = explode('/', trim($params['pathinfo'], '/'));
//
//            if (true)
//            {
//                return ['controller' => reset($segments), 'action' => end($segments)];
//            }
//        }),
];
