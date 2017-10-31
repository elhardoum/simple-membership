<?php

namespace App\Ctrl;

use App\View, App\Errors;

abstract class Ctrl
{
    static $route, $pageTitle = 'Welcome!', $errorsGroup;
    protected static $errors;

    static function head()
    {
        echo '<link rel="stylesheet" type="text/css" href="', View::url('/assets/css/style.css', true), '">', PHP_EOL;
    }

    static function footer()
    {
    }

    static function url($after=null, $relative=null)
    {
        return View::url(static::$route . $after, $relative);
    }

    static function canonical()
    {
        return self::url();
    }

    static function redirectHere($args=null, $after=null)
    {
        $args = (array) $args;

        if ( isset($args['errors']) && $args['errors'] instanceOf Errors ) {
            if ( method_exists($args['errors'], 'renameGroup') ) {
                $args['errors']->renameGroup( self::getErrorsGroup() );
            }
        }

        View::redirect(View::url(static::$route . $after, true), $args);
    }

    private static function getErrorsGroup()
    {
        if ( !static::$errorsGroup ) {
            $class = basename( str_replace( '\\', '/', get_called_class() ) );
            $c=0;
            static::$errorsGroup = preg_replace_callback('/[A-Z]/s', function($m) use (&$c){
                $m = strtolower(array_shift($m));
                if ( $c ) {
                    return "-{$m}";
                } else {
                    $c++;
                    return $m;
                }
            }, $class);
        }

        return static::$errorsGroup;
    }
}