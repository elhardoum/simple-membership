<?php

namespace App;

use RandomLib\Factory as RandomLibFactory;
use SecurityLib\Strength;

class Auth
{
    const TOKEN_HASHER = 'sha512';
    const PASSWORD_ALGO = PASSWORD_DEFAULT;
    private static $current_user_id, $current_user, $current_token;

    static function randChar($length=16)
    {
        $factory = new RandomLibFactory;
        $generator = $factory->getGenerator(new Strength(Strength::MEDIUM));
        
        return $generator->generateString($length);
    }

    static function token($length=16, $salt=null)
    {
        $token = !empty($salt) ? $salt : Config::AUTH_SALT . self::randChar(22);
        $hash = hash(self::TOKEN_HASHER, $token);
        $hash = $length ? substr($hash, 0, $length) : $hash;

        return $hash;
    }

    static function setCurrentUser($user_id_data, $remeber=false)
    {
        if ( is_object($user_id_data) && !empty($user_id_data->id) ) {
            $user_id = $user_id_data->id;
            $user = $user_id_data;
        } else {
            $user_id = (int) $user_id_data;
            $user = null;
        }

        $tokens = self::getUserTokens($user_id);
        $token = self::token(36);
        $tokens[ $token ] = time() + ($remeber ? Config::WEEK_IN_SECONDS : Config::DAY_IN_SECONDS)*2;

        if ( update_user_meta( $user_id, 'tokens', $tokens ) ) {
            Cookie::set( AUTH_COOKIE, "{$user_id}:{$token}", $tokens[$token]-time(), null, true );

            self::$current_user = $user;
            self::$current_user_id = $user_id;
            self::$current_token = $token;

            return true;
        } else {
            return false;
        }
    }

    static function getCurrentUser()
    {
        if ( self::$current_user ) {
            return self::$current_user;
        } else if ( $cookie = Cookie::get(AUTH_COOKIE) ) {
            list($user_id, $token) = explode( ':', $cookie );

            if ( empty($user_id) || !intval($user_id) )
                return;

            $user_id = (int) $user_id;

            if ( empty($token) )
                return;

            $tokens = self::getUserTokens( $user_id );

            if ( $tokens && isset($tokens[$token]) && $tokens[$token] > time() ) {
                self::$current_user_id = $user_id;
                self::$current_user = Users::getBy( 'id', $user_id );
                self::$current_token = $token;

                if ( !self::$current_user ) {
                    self::$current_user = null;
                    self::$current_user_id = null;
                    self::$current_token = null;
                }
            }
        }

        return self::$current_user;
    }

    static function getUserTokens($user_id)
    {
        return self::validateTokens( (array) get_user_meta( $user_id, 'tokens' ) );
    }

    static function setUserTokens($user_id, $tokens)
    {
        if ( $tokens = self::validateTokens( $tokens ) ) {
            return update_user_meta( $user_id, 'tokens', $tokens );
        } else {
            return delete_user_meta( $user_id, 'tokens' );
        }
    }

    private static function validateTokens($tokens)
    {
        $tokens = (array) $tokens;
        array_walk($tokens, function(&$v, $k){
            $v = $v < time() ? null : $v;
        });
        $tokens = array_filter($tokens);
        asort($tokens);

        return $tokens;
    }

    static function logout()
    {
        if ( !self::$current_user ) {
            return true;
        }

        if ( self::$current_token ) {
            $tokens = self::getUserTokens( self::$current_user->id );

            if ( $tokens && isset($tokens[self::$current_token]) ) {
                unset($tokens[self::$current_token]);
                if ( self::setUserTokens( self::$current_user->id, $tokens ) ) {
                    Cookie::delete( AUTH_COOKIE );

                    self::$current_user = null;
                    self::$current_user_id = null;
                    self::$current_token = null;

                    return !self::loggedIn();
                }
            }
        }
    }

    static function logoutAll()
    {
        if ( !self::$current_user ) {
            return true;
        }

        if ( self::setUserTokens(self::$current_user->id, array()) ) {
            Cookie::delete( AUTH_COOKIE );

            self::$current_user = null;
            self::$current_user_id = null;
            self::$current_token = null;

            return !self::loggedIn();
        }
    }

    static function loggedIn()
    {
        return (bool) self::getCurrentUser();
    }

    static function protect($err=null)
    {
        if ( !Auth::loggedIn() ) {
            if ( !$err || ! $err instanceOf Errors ) {
                $err = (new Errors)->setGroup('login');
            } else {
                $err->renameGroup('login');
            }

            return View::redirect( Ctrl\Login::url(), array( 'errors' => $err->addError('You must login first', 'error') ) );
        }
    }

    static function getCurrentToken()
    {
        return self::$current_token;
    }
}