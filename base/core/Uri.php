<?php

namespace core;

class Uri {
    
    public static function get()
    {
        return Request::baseuri();
    }
}
