<?php

namespace App\Ctrl;

use App\View, App\Cookie, App\Errors, App\Users, App\Auth;
use Nonce\Nonce;

class Login extends Ctrl
{
    static $route = '/login';
    static $pageTitle = 'Login';

    public function get()
    {
        self::check();

        View::file('login', array(
            'err' => self::getErrors(),
        ));
    }

    public function post()
    {
        self::check();

        $_POST = array_combine(
            array( 'email', 'pass', 'remember', 'redirect_to', 'nonce' ),
            array_map('old', array( 'email', 'pass', 'remember', 'redirect_to', 'nonce' ))
        );

        $err = self::getErrors();

        if ( !Nonce::verify( $_POST['nonce'], 'login' ) ) {
            return self::redirectHere(array(
                'data' => array( 'email' => $_POST['email'], 'remember' => $_POST['remember'] ),
                'errors' => bad_auth($err),
            ));
        }

        switch ( true ) {
            case !trim($_POST['email']):
                $err->addError('Please enter an email address.', 'error', 'email');
                break;

            case !is_email($_POST['email']):
                $err->addError('Invalid email address.', 'error', 'email');
                break;

            case !$_POST['pass']:
                $err->addError('Please enter a password.', 'error', 'pass');
                break;

            default:
                if ( $user = Users::getBy('email', $_POST['email']) ) {
                    if ( Users::checkPass( $_POST['pass'], $user->password ) ) {
                        if ( Auth::setCurrentUser( $user, (bool) $_POST['remember'] ) ) {
                            self::check();
                        } else {
                            $err->addError( 'Error occured while signing you in.', 'error' );
                        }
                    } else {
                        $err->addError( 'Incorrect password, please try again.', 'error', 'pass' );
                    }
                } else {
                    $err->addError( 'Invalid login credentials, please try again.', 'error' );
                }
                break;
        }

        return self::redirectHere(array(
            'data' => $_POST,
            'errors' => $err,
        ));
    }

    static function check()
    {
        if ( Auth::loggedIn() ) {
            if ( $to = old( 'redirect_to' ) ) {
                return View::redirect($to, array( 'safe' => true ));
            } else {
                return Profile::redirectHere();
            }
        }
    }
}