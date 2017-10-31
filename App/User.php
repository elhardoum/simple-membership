<?php

namespace App;

class User
{
    private static $user;

    static function load($user)
    {
        if ( is_object($user) ) {
            self::$user = $user;
        } else if ( is_numeric($user) ) {
            self::$user = Users::getBy('id', $user);
        } else if ( is_email( $user ) ) {
            self::$user = Users::getBy('email', $user);
        } else {
            self::$user = null;
        }

        return self::instance();
    }

    static function instance($user=null)
    {
        static $instance = null;
        
        if ( null === $instance ) {
            $instance = new User;
        }

        return $instance;
    }

    static function update($data, Errors &$err)
    {
        if ( !isset(self::$user) || empty(self::$user->id) ) {
            $err->addError('Could not fetch user to update.', 'error');
            return false;
        }

        foreach ( $data as $k=>$v ) {
            if ( $v === self::$user->$k ) {
                unset($data[$k]);
            } else {
                switch ( $k ) {
                    case 'id':
                        unset($data[$k]);
                        $err->addError('You cannot update the user ID.', 'error');
                        break;

                    case 'email':
                        if ( !is_email( $v ) || Users::getIdBy('email', $v) ) {
                            unset($data[$k]);
                            $err->addError('This email is either invalid or already in use.', 'error', 'email');
                            return false;
                        }
                        break;

                    case 'name':
                        $data[$k] = trim($v) ? sanitize_text( substr($v, 0, 30) ) : 'John Smith';
                        break;

                    case 'password':
                        if ( !trim($v) || strlen($v) < cfg('PASSWORD_MIN_CHAR', 6) ) {
                            $err->addError('Invalid password.', 'error', 'pass');
                        } else {
                            $data[$k] = password_hash($v, Auth::PASSWORD_ALGO);
                        }
                        break;

                    case 'registered':
                        if ( !is_integer($v) ) {
                            unset($data[$k]);
                        }
                        break;

                    default:
                        unset($data[$k]);
                        break;
                }
            }
        }

        if ( !$data ) {
            $err->addError('There are no fields to update for this user.', 'warning', 'empty_fields');
            return;
        }

        $stmt = 'update `users` set ';
        $args = array();
        foreach ( $data as $k=>$v ) {
            $stmt .= "`{$k}` = :{$k}, ";
            $args[":{$k}"] = $v;
        }
        $stmt = preg_replace('/, $/si', '', $stmt);
        $stmt .= " where `id` = :id limit 1;";
        $args[':id'] = self::$user->id;

        $db = DB::i();
        $db = $db->prepare($stmt);
        $db->execute($args);

        return $db->rowCount();
    }

    static function get()
    {
        return self::$user;
    }
}