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
            // Enviar correo electrónico
            $asunto = "Recuperación de Contraseña - Albus S.R.L.";
            $enlace = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=" . $token;
            
            $mensaje_correo = "
            Hola " . $usuario['nombre'] . ",

            Has solicitado restablecer tu contraseña en el Sistema de Gestión de Almacén Albus S.R.L.

            Para crear una nueva contraseña, haz clic en el siguiente enlace:
            $enlace

            Este enlace expirará en 1 hora.

            Si no solicitaste este cambio, ignora este mensaje.

            Saludos,
            Equipo Albus S.R.L.
            ";
            
            $headers = "From: sistema@albus.com\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
            
            // Para testing, muestra el enlace directamente
            $mensaje = "Se han enviado instrucciones para restablecer tu contraseña al correo: " . htmlspecialchars($correo);
            $mensaje .= "<br><br><strong>Enlace para testing:</strong> <a href='$enlace' class='btn btn-sm btn-outline-primary'>Haz clic aquí para restablecer contraseña</a>";
            
            // Para producción, descomenta esta línea:
            // if (mail($correo, $asunto, $mensaje_correo, $headers)) {
            //     $mensaje = "Se han enviado instrucciones para restablecer tu contraseña al correo: " . htmlspecialchars($correo);
            // } else {
            //     $error = "Error al enviar el correo. Por favor, contacta al administrador.";
            // }
        } else {
            $error = "Error al generar el token de recuperación";
        }
    } else {
        $error = "El correo electrónico no está registrado en el sistema.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña | Albus S.R.L.</title>
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
            <div class="col-md-5">
                <div class="login-card p-4">
                    <div class="text-center mb-4">
                        <h2>Recuperar Contraseña</h2>
                        <p class="text-muted">Ingresa tu correo electrónico para recibir instrucciones</p>
                    </div>
                    
                    <?php if ($mensaje): ?>
                        <div class="alert alert-info">
                            <?php echo $mensaje; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Correo electrónico</label>
                            <input type="email" class="form-control" name="correo" required 
                                   placeholder="tu@email.com" value="<?php echo isset($_POST['correo']) ? htmlspecialchars($_POST['correo']) : ''; ?>">
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                Enviar Instrucciones
                            </button>
                            <a href="login.php" class="btn btn-secondary">
                                Volver al Login
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>