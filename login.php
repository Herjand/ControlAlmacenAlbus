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
            
            // Redirigir según el rol - CORREGIDO
            if ($usuario['rol'] == 'Administrador') {
                header("Location: admin/index_admin.php");
            } elseif ($usuario['rol'] == 'Jefe de Producción') {
                header("Location: jefeprod/index_jefe_produccion.php");
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
    <title>Login | Albus S.A.</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
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
        .forgot-password {
            font-size: 0.9em;
            text-decoration: none;
        }
        .forgot-password:hover {
            text-decoration: underline;
        }
        .register-link {
            font-size: 0.9em;
            text-decoration: none;
        }
        .register-link:hover {
            text-decoration: underline;
        }
        .role-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            font-size: 0.85em;
        }
        .role-info h6 {
            color: #495057;
            margin-bottom: 10px;
        }
        .role-badge {
            font-size: 0.75em;
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="login-card p-4">
                    <!-- Botón Volver al Index -->
                    <div class="mb-3">
                        <a href="index.php" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> Volver al Inicio
                        </a>
                    </div>
                    
                    <div class="text-center mb-4">
                        <h2><i class="bi bi-box-seam"></i> Albus S.A.</h2>
                        <p class="text-muted">Sistema de Gestión de Almacén</p>
                    </div>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Correo electrónico</label>
                            <input type="email" class="form-control" name="correo" required 
                                   placeholder="usuario@albus.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password" class="form-control" name="contrasena" required 
                                   placeholder="Ingresa tu contraseña">
                        </div>
                        
                        <!-- Enlace ¿Olvidaste tu contraseña? -->
                        <div class="mb-3 text-center">
                            <a href="recuperar_password.php" class="forgot-password text-muted">
                                ¿Olvidaste tu contraseña?
                            </a>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                        </button>
                        
                        <!-- Enlace ¿No tienes cuenta? Regístrate -->
                        <div class="mt-3 text-center">
                            <a href="registro.php" class="register-link text-primary">
                                ¿No tienes cuenta? Regístrate
                            </a>
                        </div>
                    </form>

                    <!-- Información de usuarios de prueba -->
                    <div class="role-info mt-4">
                        <h6><i class="bi bi-info-circle"></i> Usuarios de Prueba:</h6>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge bg-primary role-badge">Admin</span>
                            <small>admin@albus.com / admin123</small>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mt-1">
                            <span class="badge bg-warning role-badge">Jefe Producción</span>
                            <small>jefeprod@albus.com / jefeprod123</small>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mt-1">
                            <span class="badge bg-success role-badge">Operario</span>
                            <small>operario1@albus.com / operario123</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>