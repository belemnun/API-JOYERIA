<?php
require_once '../db_config.php';
require_once '../cors_config.php';
$metodo = $_SERVER['REQUEST_METHOD'];
//print_r($metodo); Esto  es para saber que metod está accediendo el usuario

switch ($metodo) {

    //Consulta SELECT usuarios
    case 'GET':
        // Verificar si se está solicitando un usuario específico por nombre
        if (isset($_GET['nombre'])) {
            consultarUsuarioPorNombre($conexion, $_GET['nombre']);
        } else {
            consultaSelectUsuarios($conexion);
        }
        break;
    //INSERT
    case 'POST':
        insertarUsuarios($conexion);
        break;
    case 'PUT':
        actualizarUsuariosPorNombre($conexion);
        break;
    case 'DELETE':
        eliminarUsuarioPorNombre($conexion);
        break;
    default:
        echo "Método no permitido";
        break;
}

function consultaSelectUsuarios($conexion)
{
    $sql = "SELECT * FROM usuarios";
    $resultado = $conexion->query($sql);
    if ($resultado) {
        $datos = array();
        while ($fila = $resultado->fetch_assoc()) {
            $datos[] = $fila;
        }
        echo json_encode($datos);
    }
}

function consultarUsuarioPorNombre($conexion, $nombre)
{
    // Preparar la consulta SQL utilizando una sentencia preparada
    $sql = "SELECT * FROM usuarios WHERE nombre = ?";
    $stmt = $conexion->prepare($sql);

    // Vincular el parámetro
    $stmt->bind_param("s", $nombre);

    // Ejecutar la consulta preparada
    $stmt->execute();

    // Obtener el resultado
    $resultado = $stmt->get_result();

    if ($resultado && $resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();
        echo json_encode($usuario);
    } else {
        echo json_encode(array('error' => 'Usuario no encontrado'));
    }

    // Cerrar la sentencia preparada
    $stmt->close();
}

function insertarUsuarios($conexion)
{
    $dato = json_decode(file_get_contents('php://input'), true);
    $nombre = $dato['nombre'];
    $apellido = $dato['apellido'];
    $email = $dato['email'];
    $password = $dato['password'];

    // Preparar la consulta SQL utilizando una sentencia preparada
    $sql = "INSERT INTO usuarios (nombre, apellido, email, password) VALUES (?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);

    // Vincular los parámetros
    $stmt->bind_param("ssss", $nombre, $apellido, $email, $password);

    // Ejecutar la consulta preparada
    $resultado = $stmt->execute();

    if ($resultado) {
        $idInsercion = $conexion->insert_id;
        $response = [
            'status' => 'success',
            'message' => 'Datos insertados correctamente',
            'data' => [
                'id_usuario' => $idInsercion,
                'nombre' => $nombre,
                'apellido' => $apellido,
                'email' => $email,
                'password' => $password
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

function actualizarUsuariosPorNombre($conexion)
{
    // Obtener los datos de la solicitud HTTP
    $dato = json_decode(file_get_contents('php://input'), true);
    $id_usuario = $dato['id_usuario'];
    $nombre = $dato['nombre'];
    $apellido = $dato['apellido'];
    $email = $dato['email'];
    $password = $dato['password'];

    // Preparar la consulta SQL para actualizar el usuario utilizando una sentencia preparada
    $sql = "UPDATE usuarios SET nombre = ?, apellido = ?, email = ?, password = ? WHERE nombre = ?";
    $stmt = $conexion->prepare($sql);

    // Vincular los parámetros
    $stmt->bind_param("sssss", $nombre, $apellido, $email, $password, $id_usuario);

    // Ejecutar la consulta preparada
    $resultado = $stmt->execute();

    if ($resultado) {
        // Construir la respuesta en caso de éxito
        $response = [
            'status' => 'success',
            'message' => 'Datos actualizados correctamente',
            'data' => [
                'nombre' => $nombre,
                'apellido' => $apellido,
                'email' => $email,
                'password' => $password
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

function eliminarUsuarioPorNombre($conexion)
{
    // Obtener el nombre del usuario a eliminar
    $nombre = $_GET['nombre'];

    // Preparar la consulta SQL utilizando una sentencia preparada
    $sql = "DELETE FROM usuarios WHERE nombre = ?";
    $stmt = $conexion->prepare($sql);

    // Vincular el parámetro
    $stmt->bind_param("s", $nombre);

    // Ejecutar la consulta preparada
    $resultado = $stmt->execute();

    if ($resultado) {
        $response = [
            'status' => 'success',
            'message' => 'Usuario eliminado correctamente'
        ];
    } else {
        $response = [
            'status' => 'error',
            'message' => 'Error al eliminar usuario: ' . $conexion->error
        ];
    }

    // Cerrar la consulta
    $stmt->close();

    // Devolver la respuesta como JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}
?>
