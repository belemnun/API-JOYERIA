<?php
require_once '../db_config.php';
require_once '../cors_config.php';
$metodo = $_SERVER['REQUEST_METHOD'];

switch ($metodo) {
    case 'GET':
        if (isset($_GET['nombre'])) {
            consultarCategoriaPorNombre($conexion, $_GET['nombre']);
        } else {
            consultaSelectCategorias($conexion);
        }
        break;
    case 'POST':
        insertarCategorias($conexion);
        break;
    case 'DELETE':
        eliminarCategoriaPorNombre($conexion);
        break;
    case 'PUT':
        actualizarCategoriaPorNombre($conexion);
        break;
    default:
        echo "Metodo no permitido";
        break;
}

function consultaSelectCategorias($conexion)
{
    $sql = "SELECT * FROM categorias";
    $resultado = $conexion->query($sql);
    if ($resultado) {
        $datos = array();
        while ($fila = $resultado->fetch_assoc()) {
            $datos[] = $fila;
        }
        echo json_encode($datos);
    }
}

function consultarCategoriaPorNombre($conexion, $nombre)
{
    // Preparar la consulta SQL utilizando una sentencia preparada
    $sql = "SELECT * FROM categorias WHERE nombre = ?";
    $stmt = $conexion->prepare($sql);
    
    // Vincular el parámetro
    $stmt->bind_param("s", $nombre);
    
    // Ejecutar la consulta preparada
    $stmt->execute();
    
    // Obtener el resultado
    $resultado = $stmt->get_result();
    
    if ($resultado && $resultado->num_rows > 0) {
        $categoria = $resultado->fetch_assoc();
        echo json_encode($categoria);
    } else {
        echo json_encode(array('error' => 'Categoría no encontrada'));
    }

    // Cerrar la sentencia preparada
    $stmt->close();
}

function insertarCategorias($conexion)
{
    $dato = json_decode(file_get_contents('php://input'), true);
    $nombre = $dato['nombre'];
    
    // Preparar la consulta SQL utilizando una sentencia preparada
    $sql = "INSERT INTO categorias(nombre) VALUES(?)";
    $stmt = $conexion->prepare($sql);
    
    // Vincular el parámetro
    $stmt->bind_param("s", $nombre);
    
    // Ejecutar la consulta preparada
    $resultado = $stmt->execute();
    
    if ($resultado) {
        $idInsercion = $conexion->insert_id;
        $response = [
            'status' => 'success',
            'message' => 'Datos insertados correctamente',
            'data' => [
                'id_categoria' => $idInsercion,
                'nombre' => $nombre
            ]
        ];
    } else {
        $response = [
            'status' => 'error',
            'message' => 'Error al insertar datos: ' . $conexion->error
        ];
    }

    // Cerrar la consulta
    $stmt->close();

    // Devolver la respuesta como JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}

function eliminarCategoriaPorNombre($conexion)
{
    // Obtener el nombre de la categoría a eliminar
    $nombre = $_GET['nombre'];

    // Preparar la consulta SQL utilizando una sentencia preparada
    $sql = "DELETE FROM categorias WHERE nombre = ?";
    $stmt = $conexion->prepare($sql);
    
    // Vincular el parámetro
    $stmt->bind_param("s", $nombre);
    
    // Ejecutar la consulta preparada
    $resultado = $stmt->execute();
    
    if ($resultado) {
        $response = [
            'status' => 'success',
            'message' => 'Categoría eliminada correctamente'
        ];
    } else {
        $response = [
            'status' => 'error',
            'message' => 'Error al eliminar categoría: ' . $conexion->error
        ];
    }

    // Cerrar la consulta
    $stmt->close();

    // Devolver la respuesta como JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}

function actualizarCategoriaPorNombre($conexion)
{
    // Obtener los datos de la solicitud HTTP
    $dato = json_decode(file_get_contents('php://input'), true);
    $nombre= $dato['nombre'];
    $id_categoria= $dato['id_categoria'];

    // Preparar la consulta SQL para actualizar la categoría utilizando una sentencia preparada
    $sql = "UPDATE categorias SET nombre = ? WHERE nombre = ?";
    $stmt = $conexion->prepare($sql);
    
    // Vincular los parámetros
    $stmt->bind_param("ss", $nombre, $id_categoria);
    
    // Ejecutar la consulta preparada
    $resultado = $stmt->execute();
    
    if ($resultado) {
        // Construir la respuesta en caso de éxito
        $response = [
            'status' => 'success',
            'message' => 'Datos actualizados correctamente',
            'data' => [
                'nombre_actual' => $nombre_actual,
                'nombre_nuevo' => $nombre_nuevo
            ]
        ];
    } else {
        // Construir la respuesta en caso de error
        $response = [
            'status' => 'error',
            'message' => 'Error al actualizar datos: ' . $conexion->error
        ];
    }

    // Cerrar la consulta
    $stmt->close();

    // Devolver la respuesta como JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}
?>
