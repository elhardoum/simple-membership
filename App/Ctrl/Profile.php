<?php

namespace App\Ctrl;

use App\View, App\Auth, App\Errors;

class Profile extends Ctrl
{
    static $route = '/profile';
    static $pageTitle = 'My Profile';
    
    public function get()
    {
        $err = self::getErrors();
        Auth::protect($err, self::url(null, true));

        return View::file('profile', array(
            'user' => Auth::getCurrentUser(),
            'err' => $err,
        ));
    }
}