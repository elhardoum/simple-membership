<?php

use App\Config;

defined ( 'ROOT_DIR' ) || exit('Direct access not allowed.');

if ( DEBUG_MODE ) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

// app root
define ( 'APP_DIR', ROOT_DIR . '/App' );

// site URL
define ( 'SITE_URL', dirname("{$_SERVER['REQUEST_SCHEME']}://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}") );

// some important constants
$url_parts = parse_url(SITE_URL);
define ( 'DOMAIN', $url_parts['host'] );
define ( 'PROTOCOL', isset($url_parts['scheme']) ? "{$url_parts['scheme']}://" : 'http://' );
define ( 'COOKIE_PATH', rtrim(!empty($url_parts['path']) ? $url_parts['path'] : '/', '/') . '/' );

// setup app autoloader
spl_autoload_register(function($class) {
    if ( 0 === strpos($class, 'App\\') ) {
        $file = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, sprintf(
            APP_DIR . '/%s.php',
            str_replace(array( 'App\\', '.php' ), '', $class)
        ));

        if ( file_exists($file) ) {
            require ( $file );
        }
    }
});

define ( 'AUTH_COOKIE', substr(hash('sha256', SITE_URL . Config::SALT), 0, 44) );