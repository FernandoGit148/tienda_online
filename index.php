<?php

// Cargar variables de entorno
$env = parse_ini_file(__DIR__ . "/.env");

if ($env === false) {
    die("No se encuentra el archivo .env");
}

foreach ($env as $key => $value) {
    putenv("$key=$value");
}

// Cargar la conexión a la base de datos
require_once(__DIR__ . "/db.php");

// Cargar el controlador principal
require_once(__DIR__ . "/controllers/mainController.php");

?>