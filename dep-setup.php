<?php

/**
  * Dependencies setup
  */

defined ( 'ROOT_DIR' ) || exit('Direct access not allowed.');

if ( class_exists('Nonce\Config') ) {
    Nonce\Config::$SALT = cfg('SALT');
    Nonce\Config::$COOKIE_PATH = cfg('COOKIE_PATH');
    Nonce\Config::$COOKIE_DOMAIN = cfg('DOMAIN');
}