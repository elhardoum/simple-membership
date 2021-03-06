<?php

namespace App;

use App\Ctrl\Err;

class View
{
    private static $content, $title, $status=200, $routeUri, $controller;

    public static function make($content, $args=null)
    {
        extract((array) $args);
        self::$content = $content;

        return self::render();
    }

    public static function file($file, $args=null, $ret=null)
    {
        ob_start();
        extract((array) $args);
        parse_redirect_data();

        if ( preg_match( '/\.(html|php)$/', $file ) ) {
            include APP_DIR. "/view/$file";
        } else if ( file_exists(APP_DIR. "/view/$file.php") ) {
            include APP_DIR. "/view/$file.php";
        } else if ( file_exists(APP_DIR. "/view/$file.html") ) {
            include APP_DIR. "/view/$file.html";
        }

        self::$content = ob_get_clean();

        return $ret ? true : self::render();
    }

    public static function setTitle($title)
    {
        self::$title = $title;
        return self::instance();
    }

    public static function setStatus($status)
    {
        self::$status = $status;
        return self::instance();
    }

    public static function render()
    {
        self::prepare();

        $head = $footer = null;

        if ( self::$controller && is_object(self::$controller) ) {
            if ( method_exists(self::$controller, 'head') ) {
                ob_start();
                call_user_func(array( self::$controller, 'head' ));
                $head = ob_get_clean();
            }
        }

        if ( self::$controller && is_object(self::$controller) ) {
            if ( method_exists(self::$controller, 'footer') ) {
                ob_start();
                call_user_func(array( self::$controller, 'footer' ));
                $footer = ob_get_clean();
            }
        }

        self::status()->file('template', array(
            'title' => self::$title,
            'content' => self::$content,
            'head' => $head,
            'footer' => $footer,
        ), true);

        echo ( self::$content ), PHP_EOL;
        DB::i(true);
    }

    public static function instance()
    {
        static $instance = null;
        
        if ( null === $instance ) {
            $instance = new View;
        }

        return $instance;
    }

    private static function status()
    {
        if ( !isset(self::$status) ) {
            http_response_code(200);            
        } else if ( (int) self::$status ) {
            http_response_code((int) self::$status);
        }

        return self::instance();
    }

    static function url($after=null, $relative=null)
    {
        return ($relative ? rtrim(cfg('SITE_PATH', COOKIE_PATH), '/') : SITE_URL) . (
            $after ? '/' . preg_replace( '/^\//i', '', $after ) : ''
        );
    }

    static function setRouteUri($uri)
    {
        self::$routeUri = $uri;

        return self::instance();
    }

    static function getRouteUri()
    {
        return self::$routeUri;
    }

    static function setController($ctrl)
    {
        self::$controller = $ctrl;

        return self::instance();
    }

    static function getController()
    {
        return self::$controller;
    }

    static function redirect($to, $args=null)
    {
        $args = (array) $args;
        $safe = isset($args['safe']) ? (bool) $args['safe'] : true;
        $status = !empty($args['permanent']) ? 301 : 302;
        if ( empty($args['no_esc']) ) {
            $to = esc_url($to);
        }
        $errors_code = isset($args['errors_code']) ? esc_attr($args['errors_code']) : 'errors';
        if ( $safe ) {
            $to = validate_redirect($to);
        }

        $errors = isset($args['errors']) && $args['errors'] instanceof Errors ? $args['errors'] : null;

        if ( $errors ) {
            if ( method_exists($errors, 'export') ) {
                $errors->export();
            }
        } else if ( $errors_code && !isset($args['no_errors_export']) ) {
            Errors::instance()->export($errors_code);
        }

        $data = isset($args['data']) && $args['data'] ? (array) $args['data'] : [];
        $cpath = isset($args['cookie_path']) ? $args['cookie_path'] : null;

        if ( $data ) {
            foreach ( $data as $k=>$v ) {
                Cookie::set("_rdr_{$k}", $v, Errors::COOKIE_LIFESPAN, $cpath, true);
            }
        }

        header('Location: ' . $to, true, $status);
        exit;
    }

    private static function prepare()
    {
        if ( self::$controller && is_object(self::$controller) ) {
            if ( self::$controller::$pageTitle ) {
                self::$title = self::$controller::$pageTitle;
            }
        }
    }

    static function Err($html, $status=null, $title=null)
    {
        return new Err($html, $status, $title);
    }
}