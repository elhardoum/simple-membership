<?php

// prevent direct access
defined ( 'ROOT_DIR' ) || exit('Direct access not allowed.');

// debugging (@see ./index.php)
if ( DEBUG_MODE ) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

// time saver
function maybe_define($const, $value=null) {
    return defined ( $const ) || define ( $const, $value );
}

// time constants
maybe_define( 'MINUTE_IN_SECONDS', 60 );
maybe_define( 'HOUR_IN_SECONDS', 60 * MINUTE_IN_SECONDS );
maybe_define( 'DAY_IN_SECONDS', 24 * HOUR_IN_SECONDS );
maybe_define( 'WEEK_IN_SECONDS', 7 * DAY_IN_SECONDS );
maybe_define( 'MONTH_IN_SECONDS', 30 * DAY_IN_SECONDS );
maybe_define( 'YEAR_IN_SECONDS', 365 * DAY_IN_SECONDS );

// app root
maybe_define( 'APP_DIR', ROOT_DIR . '/App' );

// site URL
maybe_define( 'SITE_URL', dirname("{$_SERVER['REQUEST_SCHEME']}://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}") );

// some important constants: cookies.
$url_parts = parse_url(SITE_URL);
maybe_define( 'DOMAIN', $url_parts['host'] );
maybe_define( 'PROTOCOL', isset($url_parts['scheme']) ? "{$url_parts['scheme']}://" : 'http://' );
maybe_define( 'COOKIE_PATH', rtrim(!empty($url_parts['path']) ? $url_parts['path'] : '/', '/') . '/' );

// auth cookie name
maybe_define( 'AUTH_COOKIE', substr(hash('sha256', SITE_URL . App\Config::SALT), 0, 44) );

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