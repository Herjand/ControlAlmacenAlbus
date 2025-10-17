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
$id_cliente = $_POST['id_cliente'] ?? '';
$empresa = trim($_POST['empresa'] ?? '');
$contacto = trim($_POST['contacto'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$email = trim($_POST['email'] ?? '');
$nit = trim($_POST['nit'] ?? '');
$direccion = trim($_POST['direccion'] ?? '');
$ciudad = trim($_POST['ciudad'] ?? '');

// Validaciones
if (empty($id_cliente) || empty($empresa) || empty($contacto)) {
    header("Location: ../clientes_admin.php?error=2");
    exit();
}

try {
    // Verificar si ya existe otro cliente con el mismo NIT
    if (!empty($nit)) {
        $sql_verificar_nit = "SELECT id_cliente FROM clientes WHERE nit = ? AND id_cliente != ?";
        $stmt_verificar_nit = $conn->prepare($sql_verificar_nit);
        $stmt_verificar_nit->bind_param("si", $nit, $id_cliente);
        $stmt_verificar_nit->execute();
        $result_verificar_nit = $stmt_verificar_nit->get_result();
        
        if ($result_verificar_nit->num_rows > 0) {
            header("Location: ../clientes_admin.php?error=4");
            exit();
        }
    }

    // Verificar si ya existe otro cliente con la misma empresa
    $sql_verificar_empresa = "SELECT id_cliente FROM clientes WHERE empresa = ? AND id_cliente != ?";
    $stmt_verificar_empresa = $conn->prepare($sql_verificar_empresa);
    $stmt_verificar_empresa->bind_param("si", $empresa, $id_cliente);
    $stmt_verificar_empresa->execute();
    $result_verificar_empresa = $stmt_verificar_empresa->get_result();
    
    if ($result_verificar_empresa->num_rows > 0) {
        header("Location: ../clientes_admin.php?error=5");
        exit();
    }

    // Obtener datos antiguos para el log
    $sql_old = "SELECT empresa, contacto, telefono, email, nit, direccion, ciudad FROM clientes WHERE id_cliente = ?";
    $stmt_old = $conn->prepare($sql_old);
    $stmt_old->bind_param("i", $id_cliente);
    $stmt_old->execute();
    $old_data = $stmt_old->get_result()->fetch_assoc();

    // Actualizar cliente
    $sql = "UPDATE clientes SET empresa = ?, contacto = ?, telefono = ?, email = ?, nit = ?, direccion = ?, ciudad = ? WHERE id_cliente = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssi", $empresa, $contacto, $telefono, $email, $nit, $direccion, $ciudad, $id_cliente);

    if ($stmt->execute()) {
        // Registrar en logs
        $cambios = [];
        if ($old_data['empresa'] != $empresa) {
            $cambios[] = "Empresa: {$old_data['empresa']} → {$empresa}";
        }
        if ($old_data['contacto'] != $contacto) {
            $cambios[] = "Contacto: {$old_data['contacto']} → {$contacto}";
        }
        if ($old_data['nit'] != $nit) {
            $cambios[] = "NIT: {$old_data['nit']} → {$nit}";
        }
        if ($old_data['ciudad'] != $ciudad) {
            $cambios[] = "Ciudad: {$old_data['ciudad']} → {$ciudad}";
        }
        
        $accion = "Editó cliente: " . $empresa . " (" . implode(", ", $cambios) . ")";
        $sql_log = "INSERT INTO logs (id_usuario, accion, modulo) VALUES (?, ?, 'Clientes')";
        $stmt_log = $conn->prepare($sql_log);
        $stmt_log->bind_param("is", $_SESSION['usuario_id'], $accion);
        $stmt_log->execute();
        
        header("Location: ../clientes_admin.php?success=2");
    } else {
        header("Location: ../clientes_admin.php?error=1");
    }
    
    $stmt->close();
} catch (Exception $e) {
    error_log("Error al editar cliente: " . $e->getMessage());
    header("Location: ../clientes_admin.php?error=1");
}

$conn->close();
exit();
?>