<?php

namespace app\controllers;

use core\Controller;
use core\Request;
use core\Arr;

/**
 * Description of IndexController
 *
 * @author pass
 */
class IndexController extends Controller {

    public function __construct($params)
    {
        parent::__construct($params);
    }

    public function indexAction() {
        
        echo 'backend!';
    }

}
