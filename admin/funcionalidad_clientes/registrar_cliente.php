<?php
session_start();
require_once '../../connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../clientes_admin.php?error=1");
    exit();
}

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'Administrador') {
    header("Location: ../../login.php");
    exit();
}

// Obtener datos del formulario
$empresa = trim($_POST['empresa'] ?? '');
$contacto = trim($_POST['contacto'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$email = trim($_POST['email'] ?? '');
$nit = trim($_POST['nit'] ?? '');
$direccion = trim($_POST['direccion'] ?? '');
$ciudad = trim($_POST['ciudad'] ?? '');

// Validaciones
if (empty($empresa) || empty($contacto)) {
    header("Location: ../clientes_admin.php?error=2");
    exit();
}

try {
    // Verificar si ya existe un cliente con el mismo NIT o empresa
    if (!empty($nit)) {
        $sql_verificar = "SELECT id_cliente FROM clientes WHERE nit = ?";
        $stmt_verificar = $conn->prepare($sql_verificar);
        $stmt_verificar->bind_param("s", $nit);
        $stmt_verificar->execute();
        $result_verificar = $stmt_verificar->get_result();
        
        if ($result_verificar->num_rows > 0) {
            header("Location: ../clientes_admin.php?error=4");
            exit();
        }
    }

    // Verificar si ya existe un cliente con la misma empresa
    $sql_verificar_empresa = "SELECT id_cliente FROM clientes WHERE empresa = ?";
    $stmt_verificar_empresa = $conn->prepare($sql_verificar_empresa);
    $stmt_verificar_empresa->bind_param("s", $empresa);
    $stmt_verificar_empresa->execute();
    $result_verificar_empresa = $stmt_verificar_empresa->get_result();
    
    if ($result_verificar_empresa->num_rows > 0) {
        header("Location: ../clientes_admin.php?error=5");
        exit();
    }

    // Insertar cliente
    $sql = "INSERT INTO clientes (empresa, contacto, telefono, email, nit, direccion, ciudad) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $empresa, $contacto, $telefono, $email, $nit, $direccion, $ciudad);

    if ($stmt->execute()) {
        // Registrar en logs
        $accion = "Registró nuevo cliente: " . $empresa;
        $sql_log = "INSERT INTO logs (id_usuario, accion, modulo) VALUES (?, ?, 'Clientes')";
        $stmt_log = $conn->prepare($sql_log);
        $stmt_log->bind_param("is", $_SESSION['usuario_id'], $accion);
        $stmt_log->execute();
        
        header("Location: ../clientes_admin.php?success=1");
    } else {
        header("Location: ../clientes_admin.php?error=1");
    }
    
    $stmt->close();
} catch (Exception $e) {
    error_log("Error al registrar cliente: " . $e->getMessage());
    header("Location: ../clientes_admin.php?error=1");
}

$conn->close();
exit();
?>