<?php

$env = parse_ini_file(__DIR__ . "/.env");

if ($env === false) {
    die("No se encuentra el archivo .env");
}

foreach ($env as $key => $value) {
    putenv("$key=$value");
}

require_once(__DIR__ . "/db.php");

$db = DB::connect();

echo "¡Conexión correcta con tienda_online!";

$db->close();