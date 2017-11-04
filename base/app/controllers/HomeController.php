<?php

namespace app\controllers;

use core\Request;
use core\View;

class HomeController
{

    public $params = [];

    public function __construct( $params = null ) {
        $this->params = $params;
    }

    public function indexAction() {
        
        View::setGlobal( 'global', 'ggggg' );
        View::setGlobal( 'global2', '<div>ggggg!</div>', false );

        $bind = '1234';
        $title = 'asdf';

        Request::Get();

        $view = View::fact();
        $view->file( 'index' )
                ->set( 'title', $title )
//                ->set('base_url', Request::baseurl())
                ->set( [
                    'name' => 'aaa',
                    'age' => 40
                ] )
                ->bind( 'bind', $bind )
                ->part( 'header', 'parts/header' );

        $bind = 'change';
        $title = 'change';
//        
        $body = $view->render();

        echo $body;
    }

    public function productAction() {
        
//        var_dump($this->params);
        echo 'product';

        $array = [
            [ 'id' => 0, 'age' => 10 ],
            [ 'id' => 1, 'age' => 20 ],
            [ 'id' => 2, 'age' => 30 ],
        ];
//        var_dump(Arr::pluck($array, 'age', 'id'));

//        $result = Arr::map( $array, function($v, $k) {
//                    return $v + 1;
//                }, 'age' );
//
//        var_dump( $result );

//        $array = [
//            111,
//            222,
//            333,
//            [55,66],
//        ];
//        
//        $result = Arr::set($array, '3.0', 'value!');
//        var_dump($result);
//        var_dump($array);
//        var_dump(Request::protocol());
//        var_dump(Request::baseurl());
//        var_dump(Request::baseurl(true));
//        var_dump(Request::baseurl('https'));
//        var_dump(Request::isAjax());
//        var_dump(Request::referrer());
//        var_dump(Request::referrer('default url'));
//        var_dump(Request::queryString());
//        var_dump(Request::get());
//        var_dump(Request::get('aaa'));
//        var_dump(Request::get('ccc', 'default'));
//        var_dump(Request::method());
//        var_dump(Request::method('get'));
//        var_dump(Request::userAgent());
//        var_dump(Request::isPhone());
//        var_dump(Request::device());
//        var_dump(Request::device(true));
//        var_dump(Request::acceptLang());
    }

}
