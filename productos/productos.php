<?php
require_once '../db_config.php';
require_once '../cors_config.php';
$metodo = $_SERVER['REQUEST_METHOD'];

switch ($metodo) {
    case 'GET':
        if (isset($_GET['nombre'])) {
            consultarProductoPorNombre($conexion, $_GET['nombre']);
        } else {
            consultaSelectProductos($conexion);
        }
        break;
    case 'POST':
        insertarProductos($conexion);
        break;
    case 'DELETE':
        eliminarProductoPorNombre($conexion);
        break;
    case 'PUT':
        actualizarProductoPorNombre($conexion);
        break;
    default:
        echo "Metodo no permitido";
        break;
}

function consultaSelectProductos($conexion)
{
    $sql = "SELECT * FROM productos";
    $resultado = $conexion->query($sql);
    if ($resultado) {
        $datos = array();
        while ($fila = $resultado->fetch_assoc()) {
            $datos[] = $fila;
        }
        echo json_encode($datos);
    }
}

function consultarProductoPorNombre($conexion, $nombre)
{
    // Preparar la consulta SQL utilizando una sentencia preparada
    $sql = "SELECT * FROM productos WHERE nombre = ?";
    $stmt = $conexion->prepare($sql);
    
    // Vincular el parámetro
    $stmt->bind_param("s", $nombre);
    
    // Ejecutar la consulta preparada
    $stmt->execute();
    
    // Obtener el resultado
    $resultado = $stmt->get_result();
    
    if ($resultado && $resultado->num_rows > 0) {
        $producto = $resultado->fetch_assoc();
        echo json_encode($producto);
    } else {
        echo json_encode(array('error' => 'Producto no encontrado'));
    }

    // Cerrar la sentencia preparada
    $stmt->close();
}

function insertarProductos($conexion)
{
    $dato = json_decode(file_get_contents('php://input'), true);
    $nombre = $dato['nombre'];
    $precio = $dato['precio'];
    $descripcion = $dato['descripcion'];
    $existencia = $dato['existencia'];
    $id_categoria = $dato['id_categoria'];
    $imagen = $dato['imagen'];

    // Preparar la consulta SQL utilizando una sentencia preparada
    $sql = "INSERT INTO productos (nombre, precio, descripcion, existencia, id_categoria, imagen) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    
    // Vincular los parámetros
    $stmt->bind_param("sdsdis", $nombre, $precio, $descripcion, $existencia, $id_categoria, $imagen);
    
    // Ejecutar la consulta preparada
    $resultado = $stmt->execute();
    
    if ($resultado) {
        $idInsercion = $conexion->insert_id;
        $response = [
            'status' => 'success',
            'message' => 'Datos insertados correctamente',
            'data' => [
                'id_producto' => $idInsercion,
                'nombre' => $nombre,
                'precio' => $precio,
                'descripcion' => $descripcion,
                'existencia' => $existencia,
                'id_categoria' => $id_categoria,
                'imagen' => $imagen,
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

function eliminarProductoPorNombre($conexion)
{
    // Obtener el nombre del producto a eliminar
    $nombre = $_GET['nombre'];

    // Preparar la consulta SQL utilizando una sentencia preparada
    $sql = "DELETE FROM productos WHERE nombre = ?";
    $stmt = $conexion->prepare($sql);
    
    // Vincular el parámetro
    $stmt->bind_param("s", $nombre);
    
    // Ejecutar la consulta preparada
    $resultado = $stmt->execute();
    
    if ($resultado) {
        $response = [
            'status' => 'success',
            'message' => 'Producto eliminado correctamente'
        ];
    } else {
        $response = [
            'status' => 'error',
            'message' => 'Error al eliminar producto: ' . $conexion->error
        ];
    }

    // Cerrar la consulta
    $stmt->close();

    // Devolver la respuesta como JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}

function actualizarProductoPorNombre($conexion)
{
    // Obtener los datos de la solicitud HTTP
    
    $dato = json_decode(file_get_contents('php://input'), true);
    $id_producto = $dato['id_producto'];
    $nombre = $dato['nombre'];
    $precio = $dato['precio'];
    $descripcion = $dato['descripcion'];
    $existencia = $dato['existencia'];
    $id_categoria = $dato['id_categoria'];
    $imagen = $dato['imagen'];
    

    // Preparar la consulta SQL para actualizar el producto utilizando una sentencia preparada
    $sql = "UPDATE productos SET nombre = ?, precio = ?, descripcion = ?, existencia = ?, id_categoria = ?, imagen = ? WHERE nombre = ?";
    $stmt = $conexion->prepare($sql);

    // Vincular los parámetros
    $stmt->bind_param("sdssisi", $nombre, $precio, $descripcion, $existencia, $id_categoria, $imagen, $id_producto);

    // Ejecutar la consulta preparada
    $resultado = $stmt->execute();

    if ($resultado) {
        // Construir la respuesta en caso de éxito
        $response = [
            'status' => 'success',
            'message' => 'Datos actualizados correctamente',
            'data' => [
                'nombre' => $nombre,
                'precio' => $precio,
                'descripcion' => $descripcion,
                'existencia' => $existencia,
                'id_categoria' => $id_categoria,
                'imagen' => $imagen,
            ]
        ];
    } else {
        // Construir la respuesta en caso de error
        $response = [
            'status' => 'error',
            'message' => 'Error al actualizar datos: ' . $stmt->error
        ];
    }

    // Cerrar la consulta
    $stmt->close();

    // Devolver la respuesta como JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}
?>
