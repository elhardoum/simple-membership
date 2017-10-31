<?php

namespace App\Ctrl;

use App\View, App\Auth, App\Errors;

class Profile extends Ctrl
{
    static $route = '/profile';
    static $pageTitle = 'My Profile';
    
    public function get()
    {
        $err = (new Errors)->setGroup('profile');
        Auth::protect($err);

        return View::file('profile', array(
            'user' => Auth::getCurrentUser(),
            'err' => $err,
        ));
    }
}