<?php

namespace App\Ctrl;

use App\View, App\Auth, App\Errors, App\User, App\Users;
use Nonce\Nonce;

class ProfileEditPassword extends Ctrl
{
    static $route = '/profile/edit/password';
    static $pageTitle = 'Edit Password';
    private static $user;
    
    public function get()
    {
        $err = (new Errors)->setGroup('profile')->import();
        Auth::protect($err);

        return View::file('profile-edit-password', array(
            'err' => $err,
        ));
    }

    public function post()
    {
        $err = (new Errors)->setGroup('profile');
        Auth::protect($err);

        $data = array_combine(array('nonce','old_pass','pass','pass_conf'), array_map('old', array('nonce','old_pass','pass','pass_conf')));

        if ( !Nonce::verify( $data['nonce'], 'edit-password' ) ) {
            bad_auth($err);
        } else {
            switch ( true ) {
                case !$data['old_pass']:
                    $err->addError('Please enter your old password', 'error', 'old_pass');
                    break;

                case !Users::checkPass( $data['old_pass'], self::user()->get()->password ):
                    $err->addError('Incorrect password.', 'error', 'old_pass');
                    break;

                case !$data['pass'] || strlen($data['pass']) < cfg('PASSWORD_MIN_CHAR', 6):
                    $err->addError('Missing/invalid password.', 'error', 'pass');
                    break;

                case !$data['pass_conf'] || strlen($data['pass_conf']) < cfg('PASSWORD_MIN_CHAR', 6):
                    $err->addError('Missing/invalid password confirmation.', 'error', 'pass_conf');
                    break;

                case $data['pass'] != $data['pass_conf']:
                    $err->addError('Password mismatch.', 'error', 'pass_conf');
                    break;

                case $data['old_pass'] === $data['pass_conf']:
                    $err->addError('Your new password is the same as the old one.', 'warning', 'same_password');
                    break;

                default:
                    if ( self::user()->update(array('password' => $data['pass_conf']), $err) ) {
                        if ( $tokens = Auth::getUserTokens( self::user()->get()->id ) ) {
                            if ( count($tokens) > 1||1 ) {
                                if ( $token = Auth::getCurrentToken() ) {
                                    $tokens = isset($tokens[$token]) ? array( $token => $tokens[$token] ) : array();
                                    Auth::setUserTokens( self::user()->get()->id, $tokens );
                                } else {
                                    Auth::setUserTokens( self::user()->get()->id, array() );
                                }
                            }
                        }
                        $err->addError('Password updated successfully!', 'success');
                    } else {
                        $err->addError('Something went wrong, please try again', 'error');
                    }
                    break;
            }
        }

        return redirect(View::getRouteUri(), array(
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