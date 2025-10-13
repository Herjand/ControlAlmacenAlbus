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

// Validaciones
if (empty($empresa) || empty($contacto)) {
    header("Location: ../clientes_admin.php?error=2");
    exit();
}

// Insertar cliente
$sql = "INSERT INTO clientes (empresa, contacto, telefono, email) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $empresa, $contacto, $telefono, $email);

if ($stmt->execute()) {
    header("Location: ../clientes_admin.php?success=1");
} else {
    header("Location: ../clientes_admin.php?error=1");
}

$stmt->close();
$conn->close();
exit();
?>