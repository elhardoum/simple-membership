<?php

namespace App\Ctrl;

use App\View, App\Auth, App\Errors, App\User;
use Nonce\Nonce;

class ProfileEdit extends Ctrl
{
    static $route = '/profile/edit';
    static $pageTitle = 'Edit Profile';
    private static $user;
    
    public function get()
    {
        $err = (new Errors)->setGroup('profile')->import();
        Auth::protect($err, self::url(null, true));

        if ( null === old('name') ) {
            set_old( 'name', self::user()->get()->name );
        }

        if ( null === old('email') ) {
            set_old( 'email', self::user()->get()->email );
        }

        return View::file('profile-edit', array(
            'err' => $err,
        ));
    }

    public function post()
    {
        $err = (new Errors)->setGroup('profile');
        Auth::protect($err, self::url(null, true));

        $data = array_combine(array('name','email','nonce'), array_map('old', array('name','email','nonce')));

        if ( !Nonce::verify( $data['nonce'], 'edit-profile' ) ) {
            bad_auth($err);
        } else if ( self::user()->update($data, $err) ) {
            $err->addError('Profile updated successfully!', 'success');
        } else {
            $err->removeError(null,null,'empty_fields');
        }

        return self::redirectHere(array(
            'data' => $data,
            'errors' => $err,
        ));
    }

    private static function user()
    {
        if ( !self::$user ) {
            self::$user = User::load(Auth::getCurrentUser());
        }

        return self::$user;
    }
}