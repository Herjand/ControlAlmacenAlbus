<?php
session_start();
require_once '../../connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../entradas_admin.php?error=1");
    exit();
}

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'Administrador') {
    header("Location: ../../login.php");
    exit();
}

// Obtener datos del formulario
$id_producto = $_POST['id_producto'] ?? '';
$cantidad = $_POST['cantidad'] ?? '';
$motivo = $_POST['motivo'] ?? '';
$observaciones = $_POST['observaciones'] ?? '';
$usuario_responsable = $_SESSION['usuario_id'];

// Validaciones básicas
if (empty($id_producto) || empty($cantidad) || empty($motivo)) {
    header("Location: ../entradas_admin.php?error=2");
    exit();
}

if ($cantidad <= 0) {
    header("Location: ../entradas_admin.php?error=1");
    exit();
}

// 1. INSERT en la tabla entradas
$sql_entrada = "INSERT INTO entradas (id_producto, cantidad, usuario_responsable, motivo, observaciones) 
                VALUES (?, ?, ?, ?, ?)";
$stmt_entrada = $conn->prepare($sql_entrada);
$stmt_entrada->bind_param("iiiss", $id_producto, $cantidad, $usuario_responsable, $motivo, $observaciones);

// 2. UPDATE en productos
$sql_actualizar = "UPDATE productos SET stock = stock + ? WHERE id_producto = ?";
$stmt_actualizar = $conn->prepare($sql_actualizar);
$stmt_actualizar->bind_param("ii", $cantidad, $id_producto);

// Ejecutar ambas consultas
if ($stmt_entrada->execute() && $stmt_actualizar->execute()) {
    header("Location: ../entradas_admin.php?success=1");
} else {
    header("Location: ../entradas_admin.php?error=1");
}

// Cerrar statements
$stmt_entrada->close();
$stmt_actualizar->close();
$conn->close();
exit();
?>