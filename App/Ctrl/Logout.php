<?php

namespace App\Ctrl;

use App\View, App\Auth, App\Routes, App\Errors;
use Nonce\Nonce;

class Logout extends Ctrl
{
    static $route = '/logout';
    static $pageTitle = 'Logout';

    public function request()
    {
        if ( !isset($_REQUEST['nonce']) ) {
            return Routes::Err404();
        }

        $user = Auth::getCurrentUser();

        if ( !$user ) {
            return Home::redirectHere(array(
                'errors' => self::getErrors()->addError('You are already logged out.', 'error')
            ));
        }

        if ( Nonce::verify($_REQUEST['nonce'], 'logout') ) {
            $tokens = Auth::getUserTokens($user->id);

            if ( count($tokens) > 1 ) {
                if ( !isset($_REQUEST['conf']) ) {
                    return View::make('<label><form method="get"><input name="all" type="checkbox" /> Logout all other devices'
                        . ' (' . (count($tokens)-1) . ' connection'
                        . ((count($tokens)-1) != 1 ? 's' : '')
                        . ')?'
                        . '&nbsp;&nbsp;<input type="submit" name="conf" value="Logout" />'
                        . '<input type="hidden" name="nonce" value="' . esc_attr($_REQUEST['nonce']) . '" />'
                        . '<input type="hidden" name="redirect_to" value="' . esc_attr(old('redirect_to')) . '" />'
                        . '</form></label>');
                } else if ( isset($_REQUEST['all']) ) {
                    Auth::logoutAll();
                }
            }

            if ( Auth::logout() ) {
                if ( $to = old( 'redirect_to' ) ) {
                    return View::redirect($to, array( 'safe' => true ));
                } else {
                    return Home::redirectHere();
                }
            }
        }

        return View::Err( '<h1>Error occured..</h1><p style="color:#ddd">Something went wrong, we could not sign you out.</p>' );
    }
}