<?php
namespace core;

abstract class Controller {

    protected $params = [];
    protected $request = null;

    public function __construct($params)
    {
        $this->params = $params;
    }

    abstract public function indexAction();
    
    public function render()
    {
        echo 'rendered';
    }
}
