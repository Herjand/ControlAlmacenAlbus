<?php
session_start();
include 'connect.php';

$mensaje = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = trim($_POST['correo']);
    
    // Verificar si el correo existe
    $sql = "SELECT * FROM usuarios WHERE correo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $usuario = $result->fetch_assoc();
        
        // Generar token único
        $token = bin2hex(random_bytes(32));
        $expiracion = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Guardar token en la base de datos
        $sql_token = "UPDATE usuarios SET token_recuperacion = ?, token_expiracion = ? WHERE id_usuario = ?";
        $stmt_token = $conn->prepare($sql_token);
        $stmt_token->bind_param("ssi", $token, $expiracion, $usuario['id_usuario']);
        
        if ($stmt_token->execute()) {
            // Construir la URL correcta para recuperar_password.php
            $protocolo = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $host = $_SERVER['HTTP_HOST'];
            $carpeta_actual = dirname($_SERVER['PHP_SELF']);
            
            // Asegurar que la ruta sea correcta
            if ($carpeta_actual == '/') {
                $carpeta_actual = '';
            }
            
            $enlace = "{$protocolo}://{$host}{$carpeta_actual}/recuperar_password.php?token=" . $token;
            
            // Configurar el correo electrónico
            $asunto = "Recuperación de Contraseña - Sistema Albus S.A.";
            
            $mensaje_correo = "
            Hola " . $usuario['nombre'] . ",

            Has solicitado restablecer tu contraseña en el Sistema de Gestión de Almacén Albus S.A.

            PARA RESTABLECER TU CONTRASEÑA, HAZ CLIC EN EL SIGUIENTE ENLACE:
            $enlace

            ⚠️ IMPORTANTE:
            - Este enlace expirará en 1 hora
            - Si no solicitaste este cambio, ignora este mensaje
            - El enlace es de un solo uso

            Si tienes problemas, contacta al administrador del sistema.

            Saludos cordiales,
            Equipo de Soporte Albus S.R.L.
            Sistema de Gestión de Almacén
            ";
            
            // Headers para el correo
            $headers = "From: sistema@albus.com\r\n";
            $headers .= "Reply-To: sistema@albus.com\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion();
            
            // INTENTAR ENVIAR EL CORREO
            if (mail($correo, $asunto, $mensaje_correo, $headers)) {
                $mensaje = "✅ Se han enviado las instrucciones para restablecer tu contraseña al correo: " . htmlspecialchars($correo);
                $mensaje .= "<br><small class='text-muted'>Si no ves el correo en tu bandeja de entrada, revisa la carpeta de spam o correo no deseado.</small>";
                
                // Registrar en logs
                $sql_log = "INSERT INTO logs (id_usuario, accion, modulo, detalles) VALUES (?, 'Solicitud recuperación contraseña', 'Autenticación', ?)";
                $stmt_log = $conn->prepare($sql_log);
                $detalles_log = "Solicitud enviada a: " . $correo;
                $stmt_log->bind_param("is", $usuario['id_usuario'], $detalles_log);
                $stmt_log->execute();
                $stmt_log->close();
                
            } else {
                // Si falla el envío del correo, mostrar el enlace directamente
                $error = "⚠️ No pudimos enviar el correo automáticamente. ";
                $mensaje = "Para restablecer tu contraseña, usa el siguiente enlace:";
                $mensaje .= "<br><br><div class='text-center'><a href='$enlace' class='btn btn-primary btn-lg'><i class='bi bi-key'></i> Restablecer Contraseña</a></div>";
                $mensaje .= "<br><small class='text-muted'>Copia este enlace si es necesario: $enlace</small>";
                
                // Registrar error en logs
                error_log("Error enviando correo de recuperación a: " . $correo);
            }
            
        } else {
            $error = "❌ Error al generar el token de recuperación. Por favor, intenta nuevamente.";
        }
        
        $stmt_token->close();
    } else {
        $error = "❌ El correo electrónico no está registrado en el sistema.";
    }
    
    $stmt->close();
}

// Cerrar conexión
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña | Albus S.R.L.</title>
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
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .alert-success {
            border-left: 4px solid #28a745;
        }
        .alert-danger {
            border-left: 4px solid #dc3545;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="login-card">
                    <div class="login-header">
                        <i class="bi bi-shield-lock" style="font-size: 3rem;"></i>
                        <h2 class="mt-3">Recuperar Contraseña</h2>
                        <p class="mb-0 opacity-75">Ingresa tu correo electrónico para recibir instrucciones</p>
                    </div>
                    
                    <div class="login-body">
                        <?php if ($mensaje): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle-fill"></i>
                                <?php echo $mensaje; ?>
                            </div>
                            <div class="text-center mt-3">
                                <a href="login.php" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Volver al Login
                                </a>
                            </div>
                        <?php else: ?>
                            
                            <?php if ($error): ?>
                                <div class="alert alert-danger">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                    <?php echo $error; ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST">
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Correo electrónico</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-envelope"></i>
                                        </span>
                                        <input type="email" class="form-control" name="correo" required 
                                               placeholder="tu@email.com" 
                                               value="<?php echo isset($_POST['correo']) ? htmlspecialchars($_POST['correo']) : ''; ?>">
                                    </div>
                                    <div class="form-text">
                                        Te enviaremos un enlace seguro para restablecer tu contraseña.
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="bi bi-send"></i> Enviar Instrucciones
                                    </button>
                                    <a href="login.php" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-left"></i> Volver al Login
                                    </a>
                                </div>
                            </form>
                            
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Información adicional -->
                <div class="text-center mt-4">
                    <small class="text-white opacity-75">
                        <i class="bi bi-info-circle"></i>
                        El enlace de recuperación expira en 1 hora por seguridad.
                    </small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>