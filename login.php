<?php
session_start();
include 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = trim($_POST['correo']);
    $contrasena = trim($_POST['contrasena']);
    
    // Buscar usuario
    $sql = "SELECT * FROM usuarios WHERE correo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $usuario = $result->fetch_assoc();
        
        // Verificar contraseña (usando SHA2 en la consulta)
        $sql_verify = "SELECT * FROM usuarios WHERE correo = ? AND contrasena = SHA2(?, 256)";
        $stmt_verify = $conn->prepare($sql_verify);
        $stmt_verify->bind_param("ss", $correo, $contrasena);
        $stmt_verify->execute();
        $result_verify = $stmt_verify->get_result();
        
        if ($result_verify->num_rows == 1) {
            $_SESSION['usuario_id'] = $usuario['id_usuario'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_rol'] = $usuario['rol'];
            
            // Redirigir según el rol
            if ($usuario['rol'] == 'Administrador') {
                header("Location: admin/index_admin.php");
            } else {
                header("Location: operario/index_operario.php");
            }
            exit();
        } else {
            $error = "Contraseña incorrecta";
        }
    } else {
        $error = "Usuario no encontrado";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Albus S.R.L.</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="login-card p-4">
                    <div class="text-center mb-4">
                        <h2><i class="bi bi-box-seam"></i> Albus S.R.L.</h2>
                        <p class="text-muted">Sistema de Gestión de Almacén</p>
                    </div>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Correo electrónico</label>
                            <input type="email" class="form-control" name="correo" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password" class="form-control" name="contrasena" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Iniciar Sesión</button>
                    </form>
                    
                    <div class="mt-3 text-center">
                        <small class="text-muted">
                            <strong>Demo:</strong><br>
                            Admin: admin@albus.com / admin123<br>
                            Operario: operario1@albus.com / operario123
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>