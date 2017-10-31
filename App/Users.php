<?php

namespace App;

use PDO;

class Users
{
    static function insert($name, $email, $password, Errors &$err)
    {
        if ( self::emailExists($email) ) {
            $err->addError( 'This email is already in use.', 'error', 'email' );
            return false;
        }

        $data = array();
        $data['name'] = !empty($name) ? sanitize_text( substr($name, 0, 30) ) : 'John Smith';
        $data['password'] = password_hash($password, Auth::PASSWORD_ALGO);
        $data['email'] = $email;
        $data['registered'] = time();

        $stmt = sprintf('insert into `users` (%s)', implode(', ', array_map(function($d){
            return "`{$d}`";
        }, array_keys($data))));

        $stmt .= sprintf(' values (%s)', implode(', ', array_map(function($d){
            return ":{$d}";
        }, array_keys($data))));

        $db = DB::i();
        $db = $db->prepare($stmt);
        $db->execute($data);

        return (int) DB::i()->lastInsertId();
    }

    static function getBy($field, $data)
    {
        $db = DB::i();

        switch ( $field ) {
            case 'id':
                $d = $db->prepare("select * from `users` where `id` = :data LIMIT 1");
                $d->execute(array(':data' => intval($data)));
                break;

            case 'email':
                $d = $db->prepare("select * from `users` where `email` = :data LIMIT 1");
                $d->execute(array(':data' => sanitize_text($data)));
                break;
        }

        $user = $d->fetch(PDO::FETCH_OBJ);

        if ( $user ) {
            $user->id = (int) $user->id;
            $user->registered = (int) $user->registered;
            $user->email = esc_attr($user->email);
            $user->name = esc_attr($user->name);
        }

        return $user;
    }

    static function emailExists($email)
    {
        return (bool) self::getIdBy('email', $email);
    }

    static function getIdBy($field, $data)
    {
        $db = DB::i();

        switch ( $field ) {
            case 'id':
                $d = $db->prepare("select `id` from `users` where `id` = :data LIMIT 1");
                $d->execute(array(':data' => intval($data)));
                break;

            case 'email':
                $d = $db->prepare("select `id` from `users` where `email` = :data LIMIT 1");
                $d->execute(array(':data' => sanitize_text($data)));
                break;

            default:
                return;
                break;
        }

        return (int) $d->fetchColumn();
    }

    static function countAll()
    {
        $db = DB::i();

        $d = $db->prepare('select count(*) from `users`');
        $d->execute();

        return (int) $d->fetchColumn();
    }

    static function checkPass($raw, $hash)
    {
        return call_user_func_array('password_verify', func_get_args());
    }
}