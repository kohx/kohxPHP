<?php

namespace app\controllers;

use core\Request;
use core\View;

class IndexController {

    public $params = [];

    public function __construct($params = null)
    {
        $this->params = $params;
    }

    public function indexAction()
    {
        View::setGlobal('global', 'ggggg');
        View::setGlobal('global2', '<div>ggggg!</div>', false);
        
        $bind = '1234';
        $title = 'asdf';
        
        Debug::v(Request::baseurl());
        die;

        $view = View::fact();
        $view->file('index')
                ->set('title', $title)
                ->set('base_url', Request::baseurl())
                ->set([
                    'name' => 'aaa',
                    'age' => 40
                ])
                ->bind('bind', $bind)
                ->part('header', 'parts/header');

        $bind = 'change';
        $title = 'change';
//        
        $body = $view->render();

        echo $body;
    }

}
