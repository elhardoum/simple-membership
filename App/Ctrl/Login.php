<?php

namespace App\Ctrl;

use App\View, App\Cookie, App\Errors, App\Users, App\Auth;
use Nonce\Nonce;

class Login extends Ctrl
{
    static $route = '/login';
    static $pageTitle = 'Login';
    protected static $errorsGroup = 'login';

    public function get()
    {
        self::check();

        $err = (new Errors)->setGroup('login')->import();

        View::file('login', array(
            'err' => $err,
        ));
    }

    public function post()
    {
        self::check();

        if ( !isset($_POST['email']) )
            $_POST['email'] = null;
        if ( !isset($_POST['pass']) )
            $_POST['pass'] = null;
        if ( !isset($_POST['remember']) )
            $_POST['remember'] = null;

        $err = (new Errors)->setGroup('login');

        if ( !Nonce::verify( old('nonce'), 'login' ) ) {
            return redirect(View::getRouteUri(), array(
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

        return redirect(View::getRouteUri(), array(
            'data' => array( 'email' => $_POST['email'], 'remember' => $_POST['remember'] ),
            'errors' => $err,
        ));
    }

    static function check()
    {
        if ( Auth::loggedIn() ) {
            return View::redirect ( Profile::url() );
        }
    }
}