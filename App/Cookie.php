<?php

namespace App;

class Cookie
{
    public $name, $value;

    static function set($name=null, $value=null, $expires=null, $path=null, $httponly=null)
    {
        $value = maybe_serialize($value);
        $expires = $expires ? (time() + $expires) : '';
        $path = $path && trim($path) ? $path : COOKIE_PATH;
        setcookie(
            $name,
            $value,
            (int) $expires,
            $path,
            DOMAIN,
            (!$httponly ? 'https://' == PROTOCOL : null),
            $httponly ? true : null
        );
        $_COOKIE[$name] = $value;
    }

    static function get($name=null)
    {
        $cookie = isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;  
        if ( $cookie ) {
            $cookie = maybe_unserialize($cookie);
        }
        return $cookie;
    }

    static function delete($name, $path=null, $httponly=false)
    {
        $path = $path && trim($path) ? $path : COOKIE_PATH;
        setcookie(
            $name,
            ' ',
            time()-YEAR_IN_SECONDS,
            $path,
            DOMAIN,
            (!$httponly ? 'https://' == PROTOCOL : null),
            $httponly ? true : null
        );
        unset($_COOKIE[$name]);
    }
}