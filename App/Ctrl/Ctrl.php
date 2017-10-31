<?php

namespace App\Ctrl;

use App\View, App\Errors;

abstract class Ctrl
{
    static $route, $pageTitle = 'Welcome!';
    protected static $errors;

    static function head()
    {
        echo '<link rel="stylesheet" type="text/css" href="', View::url('/assets/css/style.css', true), '">', PHP_EOL;
    }

    static function footer()
    {
    }

    static function url($after=null)
    {
        return View::url(static::$route . $after);
    }

    static function canonical()
    {
        return self::url();
    }
}