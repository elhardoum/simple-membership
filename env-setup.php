<?php

// prevent direct access
defined ( 'ROOT_DIR' ) || exit('Direct access not allowed.');

// time constants
defined ( 'MINUTE_IN_SECONDS' )     || define ( 'MINUTE_IN_SECONDS', 60);
defined ( 'HOUR_IN_SECONDS' )       || define ( 'HOUR_IN_SECONDS', 60 * MINUTE_IN_SECONDS);
defined ( 'DAY_IN_SECONDS' )        || define ( 'DAY_IN_SECONDS', 24 * HOUR_IN_SECONDS);
defined ( 'WEEK_IN_SECONDS' )       || define ( 'WEEK_IN_SECONDS', 7 * DAY_IN_SECONDS);
defined ( 'MONTH_IN_SECONDS' )      || define ( 'MONTH_IN_SECONDS', 30 * DAY_IN_SECONDS);
defined ( 'YEAR_IN_SECONDS' )       || define ( 'YEAR_IN_SECONDS', 365 * DAY_IN_SECONDS);

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

define ( 'AUTH_COOKIE', substr(hash('sha256', SITE_URL . App\Config::SALT), 0, 44) );