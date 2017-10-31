<?php

namespace App;

use FastRoute;
use FastRoute\RouteCollector;
use FastRoute\Dispatcher;
use ReflectionClass;

class Routes
{
    static function routes(RouteCollector $r)
    {
        $ctrls = glob(APP_DIR . '/Ctrl/*.php');

        if ( $ctrls ) {
            foreach ( $ctrls as $ctrl ) {
                $ctrl = "\App\Ctrl\\" . preg_replace( '/\.php$/i', '', basename($ctrl) );
                $ctrl = new ReflectionClass($ctrl);
                if ( $ctrl->isAbstract() ) { continue; }
                $ctrl = $ctrl->newInstanceWithoutConstructor();

                if ( !isset($ctrl::$route) || empty($ctrl::$route) ) {
                    continue;
                }

                if ( method_exists($ctrl, 'request') ) {
                    $r->addRoute(['GET','POST'], $ctrl::$route, array( $ctrl, 'request' ));
                } else {
                    if ( method_exists($ctrl, 'get') ) {
                        $r->addRoute(['GET'], $ctrl::$route, array( $ctrl, 'get' ));
                    }

                    if ( method_exists($ctrl, 'post') ) {
                        $r->addRoute(['POST'], $ctrl::$route, array( $ctrl, 'post' ));
                    }
                }
            }
        }
    }

    static function dispatch()
    {
        $dispatcher = FastRoute\simpleDispatcher(array('App\Routes', 'routes'));

        // Fetch method and URI from somewhere
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];

        // Strip query string (?foo=bar) and decode URI
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);
        View::setRouteUri($uri);
        $uri = preg_replace( '/^' . str_replace('/', '\/', preg_replace( '/\/$/i', '/', COOKIE_PATH )) . '?/si', '/', $uri );

        $routeInfo = $dispatcher->dispatch($httpMethod, $uri);
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                return self::Err404();
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                return self::Err405();
                break;
            case Dispatcher::FOUND:
                http_response_code(200);
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                View::setController($handler[0]);
                return call_user_func($handler, $vars);
                break;
        }
    }

    static function Err404()
    {
        return View::Err('<h1>Error 404</h1><p>Page Not Found!</p>', 404, 'Page Not Found!');
    }

    static function Err405()
    {
        return View::Err('<h1>Error 405</h1><p>Method Not Allowed!</p>', 405, 'Method Not Allowed!');
    }
}