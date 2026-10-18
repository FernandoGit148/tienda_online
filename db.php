<?php

class DB {

    public static function connect() {
        $db = new mysqli(
            getenv('DB_HOST'),
            getenv('DB_USER'),
            getenv('DB_PASS'),
            getenv('DB_NAME')
        );

        if ($db->connect_error) {
            die("Error de conexión: " . $db->connect_error);
        }

        $db->set_charset("utf8mb4");

        return $db;
    }
}