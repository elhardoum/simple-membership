<?php

namespace App;

use PDO;

class DB
{
    static function i($close=null)
    {
        static $db = null;

        if ( $close ) {
            $db = null;
        }

        else if ( null === $db ) {
            $db = new PDO(
                sprintf('mysql:host=%s;dbname=%s', Config::DB_HOST, Config::DB_NAME),
                Config::DB_USER,
                Config::DB_PASS
            );
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        return $db;
    }
}