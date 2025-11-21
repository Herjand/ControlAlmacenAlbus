<?php
session_start();

echo "<h1>DEBUG DETALLADO</h1>";

// 1. Verificar sesión
echo "<h2>1. SESIÓN</h2>";
echo "<pre>";
var_dump($_SESSION);
echo "</pre>";

// 2. Verificar include de connect.php
echo "<h2>2. INCLUDE CONNECT.PHP</h2>";
$connect_path = '../../connect.php';
echo "Ruta: " . $connect_path . "<br>";
echo "¿Existe?: " . (file_exists($connect_path) ? '✅ SI' : '❌ NO') . "<br>";

if (file_exists($connect_path)) {
    include $connect_path;
    echo "✅ Include exitoso<br>";
    
    // 3. Verificar conexión
    echo "<h2>3. CONEXIÓN BD</h2>";
    if (isset($conn)) {
        echo "✅ Variable \$conn existe<br>";
        echo "Tipo: " . gettype($conn) . "<br>";
        
        if ($conn->connect_error) {
            echo "❌ Error conexión: " . $conn->connect_error;
        } else {
            echo "✅ Conexión OK<br>";
            
            // 4. Consulta directa
            echo "<h2>4. CONSULTA USUARIO</h2>";
            $usuario_id = $_SESSION['usuario_id'] ?? 0;
            echo "ID de usuario: " . $usuario_id . "<br>";
            
            $sql = "SELECT * FROM usuarios WHERE id_usuario = " . intval($usuario_id);
            echo "SQL: " . $sql . "<br>";
            
            $result = $conn->query($sql);
            
            if ($result === false) {
                echo "❌ Error en query: " . $conn->error;
            } else {
                echo "✅ Query ejecutado<br>";
                echo "Número de filas: " . $result->num_rows . "<br>";
                
                if ($result->num_rows > 0) {
                    $usuario = $result->fetch_assoc();
                    echo "<h3>DATOS OBTENIDOS:</h3>";
                    echo "Tipo de \$usuario: " . gettype($usuario) . "<br>";
                    echo "¿Es array?: " . (is_array($usuario) ? '✅ SI' : '❌ NO') . "<br>";
                    
                    if (is_array($usuario)) {
                        echo "<pre>";
                        print_r($usuario);
                        echo "</pre>";
                    } else {
                        echo "Valor de \$usuario: " . $usuario . "<br>";
                    }
                } else {
                    echo "❌ No se encontró usuario<br>";
                }
            }
        }
    } else {
        echo "❌ Variable \$conn NO existe después del include";
    }
} else {
    echo "❌ Archivo connect.php no encontrado en: " . realpath('../../');
}
?>