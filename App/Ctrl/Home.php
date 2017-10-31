<?php

namespace App\Ctrl;

use App\View, App\Errors, App\Auth, App\Config;

class Home extends Ctrl
{
    static $route = '/';
    static $pageTitle = Config::SITE_NAME;
    
    public function get()
    {
        View::file('home', array(
            'err' => (new Errors)->setGroup('home')->import(),
            'loggedIn' => Auth::loggedIn(),
            'login' => Login::url(),
            'register' => Register::url(),
            'profile' => Profile::url(),
        ));
    }
}