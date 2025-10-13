<?php
// CONFIGURACIÓN DE BASE DE DATOS - EJEMPLO
// COPIAR ESTE ARCHIVO A connect.php Y CONFIGURAR CON TUS DATOS REALES

$servername = "localhost";
$username = "root";                    // Tu usuario MySQL
$password = "tu_password_aqui";        // Tu password MySQL
$dbname = "albus_gestion_almacen";     // Nombre de tu base de datos

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

echo "<!-- Conexión a BD exitosa -->";
?>