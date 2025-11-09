<?php
session_start();

// Registrar log de cierre de sesión
if (isset($_SESSION['usuario_id'])) {
    include '../../connect.php';
    
    $usuario_id = $_SESSION['usuario_id'];
    $accion = "Cierre de sesión";
    $modulo = "Autenticación";
    $detalles = "Usuario: " . $_SESSION['usuario_nombre'];
    
    $sql = "INSERT INTO logs (id_usuario, accion, modulo, detalles) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $usuario_id, $accion, $modulo, $detalles);
    $stmt->execute();
    $conn->close();
}

// Destruir todas las variables de sesión
$_SESSION = array();

// Destruir la sesión
session_destroy();

// Redireccionar según el parámetro
if (isset($_GET['redirect']) && $_GET['redirect'] === 'login') {
    header("Location: ../../login.php");
} else {
    header("Location: ../../login.php?logout=1");
}
exit();