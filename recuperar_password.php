<?php
session_start();
include 'connect.php';

$mensaje = '';
$error = '';
$token_valido = false;
$mostrar_formulario = false;

// Verificar si se proporcionó el token
if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = trim($_GET['token']);
    
    // Verificar si el token es válido y no ha expirado
    $sql = "SELECT id_usuario, nombre, token_expiracion 
            FROM usuarios 
            WHERE token_recuperacion = ? 
            AND token_expiracion > NOW()";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $usuario = $result->fetch_assoc();
            $token_valido = true;
            $id_usuario = $usuario['id_usuario'];
            $mostrar_formulario = true;
            
            // Procesar el formulario de nueva contraseña
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nueva_password'])) {
                $nueva_password = trim($_POST['nueva_password']);
                $confirmar_password = trim($_POST['confirmar_password']);
                
                // Validaciones
                if (empty($nueva_password)) {
                    $error = "La contraseña no puede estar vacía.";
                } elseif (strlen($nueva_password) < 6) {
                    $error = "La contraseña debe tener al menos 6 caracteres.";
                } elseif ($nueva_password !== $confirmar_password) {
                    $error = "Las contraseñas no coinciden.";
                } else {
                    // Hash de la nueva contraseña (usando SHA256 como en tu base de datos)
                    $password_hash = hash('sha256', $nueva_password);
                    
                    // Actualizar contraseña y limpiar token
                    $sql_update = "UPDATE usuarios 
                                  SET contrasena = ?, 
                                      token_recuperacion = NULL, 
                                      token_expiracion = NULL 
                                  WHERE id_usuario = ? 
                                  AND token_recuperacion = ?";
                    $stmt_update = $conn->prepare($sql_update);
                    
                    if ($stmt_update) {
                        $stmt_update->bind_param("sis", $password_hash, $id_usuario, $token);
                        
                        if ($stmt_update->execute()) {
                            if ($stmt_update->affected_rows > 0) {
                                $mensaje = "¡Contraseña actualizada exitosamente! Ahora puedes iniciar sesión con tu nueva contraseña.";
                                $mostrar_formulario = false;
                                $token_valido = false;
                            } else {
                                $error = "No se pudo actualizar la contraseña. El token puede haber sido ya usado.";
                                $mostrar_formulario = false;
                            }
                        } else {
                            $error = "Error al ejecutar la actualización.";
                        }
                        $stmt_update->close();
                    } else {
                        $error = "Error al preparar la consulta de actualización.";
                    }
                }
            }
        } else {
            $error = "El enlace de recuperación no es válido o ha expirado. Por favor, solicita uno nuevo.";
        }
        $stmt->close();
    } else {
        $error = "Error en la consulta de verificación del token.";
    }
} else {
    $error = "Token no proporcionado o inválido.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Contraseña | Albus S.R.L.</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px 0;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .login-body {
            padding: 2rem;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="login-card">
                    <div class="login-header">
                        <i class="bi bi-key" style="font-size: 3rem;"></i>
                        <h2 class="mt-3">Nueva Contraseña</h2>
                        <p class="mb-0 opacity-75">Crea una nueva contraseña para tu cuenta</p>
                    </div>
                    
                    <div class="login-body">
                        <?php if ($mensaje): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle-fill"></i>
                                <?php echo $mensaje; ?>
                            </div>
                            <div class="text-center mt-3">
                                <a href="login.php" class="btn btn-primary">
                                    <i class="bi bi-box-arrow-in-right"></i> Ir al Login
                                </a>
                            </div>
                            
                        <?php elseif ($mostrar_formulario): ?>
                            
                            <?php if ($error): ?>
                                <div class="alert alert-danger">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                    <?php echo $error; ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" id="passwordForm">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Nueva Contraseña</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-lock"></i>
                                        </span>
                                        <input type="password" class="form-control" name="nueva_password" 
                                               id="nueva_password" required minlength="6"
                                               placeholder="Mínimo 6 caracteres">
                                    </div>
                                    <div class="form-text">
                                        La contraseña debe tener al menos 6 caracteres.
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Confirmar Contraseña</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-lock-fill"></i>
                                        </span>
                                        <input type="password" class="form-control" name="confirmar_password" 
                                               id="confirmar_password" required minlength="6"
                                               placeholder="Repite tu contraseña">
                                    </div>
                                    <div id="passwordMatch" class="form-text"></div>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="bi bi-check-lg"></i> Actualizar Contraseña
                                    </button>
                                    <a href="login.php" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-left"></i> Cancelar
                                    </a>
                                </div>
                            </form>
                            
                        <?php else: ?>
                            
                            <?php if ($error): ?>
                                <div class="alert alert-danger">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                    <?php echo $error; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="text-center">
                                <p>No se puede procesar la solicitud de recuperación.</p>
                                <div class="d-grid gap-2">
                                    <a href="forgot_password.php" class="btn btn-primary">
                                        <i class="bi bi-arrow-repeat"></i> Solicitar Nuevo Enlace
                                    </a>
                                    <a href="login.php" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-left"></i> Volver al Login
                                    </a>
                                </div>
                            </div>
                            
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validación de coincidencia de contraseñas en tiempo real
        document.addEventListener('DOMContentLoaded', function() {
            const password = document.getElementById('nueva_password');
            const confirmPassword = document.getElementById('confirmar_password');
            const matchMessage = document.getElementById('passwordMatch');
            
            if (password && confirmPassword && matchMessage) {
                function validatePassword() {
                    if (password.value && confirmPassword.value) {
                        if (password.value === confirmPassword.value) {
                            matchMessage.innerHTML = '<span class="text-success"><i class="bi bi-check-circle"></i> Las contraseñas coinciden</span>';
                        } else {
                            matchMessage.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle"></i> Las contraseñas no coinciden</span>';
                        }
                    } else {
                        matchMessage.innerHTML = '';
                    }
                }
                
                password.addEventListener('input', validatePassword);
                confirmPassword.addEventListener('input', validatePassword);
            }
        });
    </script>
</body>
</html>