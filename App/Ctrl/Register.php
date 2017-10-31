<?php

namespace App\Ctrl;

use App\View, App\Errors, App\Users, App\Auth;
use Nonce\Nonce;

class Register extends Ctrl
{
    static $route = '/register';
    static $pageTitle = 'Register';
    protected static $errorsGroup = 'register';

    public function get()
    {
        self::check();

        $err = (new Errors)->setGroup('register')->import();

        View::file('register', array(
            'err' => $err,
            'login' => Login::url(),
        ));
    }

    public function post()
    {
        self::check();

        if ( !isset($_POST['email']) )
            $_POST['email'] = null;
        if ( !isset($_POST['name']) )
            $_POST['name'] = null;
        if ( !isset($_POST['pass']) )
            $_POST['pass'] = null;
        if ( !isset($_POST['pass_conf']) )
            $_POST['pass_conf'] = null;

        $_POST = array_map('trim', $_POST);

        $err = (new Errors)->setGroup('register');

        if ( !Nonce::verify( old('nonce'), 'register' ) ) {
            return redirect(View::getRouteUri(), array(
                'data' => array( 'email' => $_POST['email'], 'name' => $_POST['name'] ),
                'errors' => bad_auth($err),
            ));
        }

        switch ( true ) {
            case !trim($_POST['name']):
                $err->addError('Please enter your name.', 'error', 'name');
                break;

            case !trim($_POST['email']):
                $err->addError('Please enter an email address.', 'error', 'email');
                break;

            case !is_email($_POST['email']):
                $err->addError('Invalid email address.', 'error', 'email');
                break;

            case !$_POST['pass'] || strlen($_POST['pass']) < cfg('PASSWORD_MIN_CHAR', 6):
                $err->addError('Missing/invalid password.', 'error', 'pass');
                break;

            case !$_POST['pass_conf'] || strlen($_POST['pass_conf']) < cfg('PASSWORD_MIN_CHAR', 6):
                $err->addError('Missing/invalid password confirmation.', 'error', 'pass_conf');
                break;

            case $_POST['pass'] != $_POST['pass_conf']:
                $err->addError('Password mismatch.', 'error', 'pass_conf');
                break;

            default:
                if ( $id = Users::insert($_POST['name'], $_POST['email'], $_POST['pass_conf'], $err) ) {
                    if ( cfg('SEND_WELCOME_EMAIL') ) send_mail(
                        $_POST['email'],
                        sprintf("[%s] Welcome {$_POST['name']}!", cfg('SITE_NAME', 'My Site')),
                        "Thanks for signing up.\n\nLogin to your account:\n" . Login::url()
                    );

                    $err->renameGroup('login')->addError('Your account was successfully created! Login below.', 'success');

                    return redirect(Login::url(), array(
                        'data' => array( 'email' => $_POST['email'] ),
                        'errors' => $err,
                    ));
                } else {
                    // $err->addError('Error occured, could not sign you up. See below.', 'error');
                }
                break;
        }

        return redirect(View::getRouteUri(), array(
            'data' => array( 'email' => $_POST['email'], 'name' => $_POST['name'] ),
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