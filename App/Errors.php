<?php

namespace App;

class Errors
{
    private $groups;
    private $group;
    const COOKIE_LIFESPAN = 30;

    private function setup()
    {
        $this->groups = (array) $this->groups;
        $this->setGroup('default');

        return $this;
    }

    public function setGroup($group)
    {
        if ( !isset($this->groups[$group]) ) {
            $this->groups[$group] = array();
        }

        $this->group = $group;

        return $this;
    }

    public function renameGroup($group, $keep_old=false)
    {
        $data = array();

        if ( $this->group ) {
            if ( isset($this->groups[$this->group]) ) {
                $data = $this->groups[$this->group];
            }

            if ( !$keep_old ) {
                unset($this->groups[$this->group]);
            }
        }

        $this->group = $group;
        $this->groups[$group] = $data;

        return $this;
    }

    public function addError($message, $type=null, $code=null)
    {
        $err = array_map('esc_attr', func_get_args());

        if ( isset($this->groups[$this->group]) && in_array($err, $this->groups[$this->group]) )
            return $this;

        $this->groups[$this->group][] = $err;

        return $this;
    }

    public function removeError($message=null, $type=null, $code=null)
    {
        $err = array_map('esc_attr', func_get_args());
        
        if ( empty($this->groups[$this->group]) )
            return $this;

        foreach ( $this->groups[$this->group] as $i=>$err ) {
            $unset = is_null($message) ? true : (isset($err[0]) && $err[0] == $message);

            if ( $unset && array_key_exists(1, func_get_args()) ) {
                $unset = is_null($type) ? true : (isset($err[1]) && $err[1] == $type);
            }

            if ( $unset && array_key_exists(2, func_get_args()) ) {
                $unset = is_null($code) ? true : (isset($err[2]) && $err[2] == $code);
            }

            if ( $unset ) {
                unset($this->groups[$this->group][$i]);
            }
        }

        return $this;
    }

    public function hasError($code=null, $type=null)
    {        
        if ( empty($this->groups[$this->group]) )
            return 0;

        $count = 0;
        foreach ( $this->groups[$this->group] as $i=>$err ) {
            $has = is_null($code) ? true : (isset($err[2]) && $err[2] == $code);

            if ( $has && array_key_exists(1, func_get_args()) ) {
                $has = is_null($type) ? true : (isset($err[1]) && $err[1] == $type);
            }

            if ( $has ) {
                $count++;
            }
        }

        return $count;
    }

    public function getErrors($message=null, $type=null, $code=null, $ignore_codes=null)
    {
        $err = array_map('esc_attr', array_slice(func_get_args(), 0, 3));
        $errors = array();

        if ( empty($this->groups[$this->group]) )
            return $errors;

        foreach ( $this->groups[$this->group] as $i=>$err ) {
            $found = is_null($message) ? true : (isset($err[0]) && $err[0] == $message);

            if ( $found && array_key_exists(1, func_get_args()) ) {
                $found = is_null($type) ? true : (isset($err[1]) && $err[1] == $type);
            }

            if ( $found && array_key_exists(2, func_get_args()) ) {
                $found = is_null($code) ? true : (isset($err[2]) && $err[2] == $code);

                if ( $ignore_codes && isset($err[2]) ) {
                    $found = !in_array($err[2], (array) $ignore_codes);
                }
            }

            if ( $found ) {
                $errors[] = $this->groups[$this->group][$i];
            }
        }

        return $errors;
    }

    public function export($thisGroup=null, $cookieKey='errors')
    {
        $groups = $this->groups;
        
        if ( !$groups )
            return $this;

        if ( $thisGroup ) {
            $groups = array(
                $this->group => $this->groups[$this->group]
            );
        }

        if ( $groups ) {
            Cookie::set($cookieKey, $groups, self::COOKIE_LIFESPAN);
        }

        return $this;
    }

    public function import($cookieKey='errors')
    {
        $errors = Cookie::get($cookieKey);
        Cookie::delete($cookieKey);

        if ( $errors && is_array($errors) ) {
            $this->groups = $errors;
        }

        return $this;
    }

    public function flush()
    {
        $this->groups = null;
        $this->group = null;

        return $this->setup();
    }

    public static function instance()
    {
        static $instance = null;
        
        if ( null === $instance ) {
            $instance = new Errors;
            $instance->setup();
        }

        return $instance;
    }
}