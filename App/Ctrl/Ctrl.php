<?php

namespace App\Ctrl;

use App\View, App\Errors;

abstract class Ctrl
{
    static $route, $pageTitle = 'Welcome!';
    protected static $errors, $errorsGroup = 'default';

    public static function head()
    {
        echo '  <link rel="stylesheet" type="text/css" href="', SITE_URL, '/assets/css/style.css">', PHP_EOL;
    }

    public static function footer()
    {
    }

    static function url($after=null)
    {
        return View::url(static::$route . $after);
    }
}