<?php
require_once 'db_config.php';
require_once 'cors_config.php';
$metodo = $_SERVER['REQUEST_METHOD'];
//print_r($metodo); Esto  es para saber que metod esta accediendo el usuario

switch ($metodo) {

        //Consulta SELECT usuarios
    case 'GET':
        consultaSelectProductos($conexion);
        break;
        //INSERT
    case 'POST':
        insertarConsulta($conexion);
        break;
    default:
        echo "Metodo no permitido";
        break;
}
function consultaSelectProductos($conexion)
{
    $sql = "SELECT * FROM  productos";
    $resultado = $conexion->query($sql);
    if ($resultado) {
        $datos = array();
        while ($fila = $resultado->fetch_assoc()) {
            $datos[] = $fila;
        }
        echo json_encode($datos);
    }
}
function insertarConsulta($conexion)
{
    $dato = json_decode(file_get_contents('php://input'), true);
    $nombre = $dato['nombre'];
    $precio = $dato['precio'];
    $descripcion = $dato['descripcion'];
    $existencia = $dato['existencia'];
    $id_categoria = $dato['id_categoria'];
    $sql = "INSERT INTO aportaciones(nombre, precio, descripcion,existencia, id_categoria) VALUES('$nombre', '$precio','$descripcion','$existencia','$id_categoria')";
    $resultado = $conexion->query($sql);
    if ($resultado) {
        $dato['id'] = $conexion->insert_id;
        echo json_encode($dato);
    } else {
        echo json_encode(array('error' => 'Error al crear usuario'));
    }
    print_r($dato);
}
