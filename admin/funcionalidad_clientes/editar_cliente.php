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

// Validaciones
if (empty($id_cliente) || empty($empresa) || empty($contacto)) {
    header("Location: ../clientes_admin.php?error=2");
    exit();
}

// Actualizar cliente
$sql = "UPDATE clientes SET empresa = ?, contacto = ?, telefono = ?, email = ? WHERE id_cliente = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssi", $empresa, $contacto, $telefono, $email, $id_cliente);

if ($stmt->execute()) {
    header("Location: ../clientes_admin.php?success=2");
} else {
    header("Location: ../clientes_admin.php?error=1");
}

$stmt->close();
$conn->close();
exit();
?>