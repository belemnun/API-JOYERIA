<?php
// db_config.php
$host = 'srv1289.hstgr.io';
$usuario = 'u463138286_bely';
$password = 'Belem_1234';
$database = 'u463138286_joyeriabel';





$conexion = new mysqli($host, $usuario, $password, $database);

if ($conexion->connect_error) {
    die("Conexion no establecida: " . $conexion->connect_error);
}
