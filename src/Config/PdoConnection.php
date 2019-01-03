<?php

namespace Scorecard\Config;


use PDO;

class PdoConnection
{
    private static $handle;

    private function __construct(){}

    public static function getConnection()
    {
        if(!isset(self::$handle)) {
            $host = DATABASE_HOST;
            $db_name = PRIMARY_DATABASE;
            $username = DATABASE_USER;
            $password = DATABASE_PASSWORD;

            $dsn = "mysql:host=$host;dbname=$db_name";

            $handle = new PDO($dsn, $username, $password);
            $handle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            self::$handle = $handle;
        }

        return self::$handle;
    }
}