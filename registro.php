<?php
session_start();
include 'connect.php';

$mensaje = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $rol = trim($_POST['rol']);
    $contrasena = trim($_POST['contrasena']);
    $confirmar_contrasena = trim($_POST['confirmar_contrasena']);
    
    // Validaciones
    if (empty($nombre) || empty($correo) || empty($rol) || empty($contrasena)) {
        $error = "Todos los campos son obligatorios";
    } elseif ($contrasena !== $confirmar_contrasena) {
        $error = "Las contraseñas no coinciden";
    } elseif (strlen($contrasena) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres";
    } else {
        // Verificar si el correo ya existe
        $sql_check = "SELECT id_usuario FROM usuarios WHERE correo = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("s", $correo);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        
        if ($result_check->num_rows > 0) {
            $error = "El correo electrónico ya está registrado";
        } else {
            // Insertar nuevo usuario
            $sql_insert = "INSERT INTO usuarios (nombre, correo, contrasena, rol) VALUES (?, ?, SHA2(?, 256), ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("ssss", $nombre, $correo, $contrasena, $rol);
            
            if ($stmt_insert->execute()) {
                $mensaje = "¡Cuenta creada exitosamente! Ya puedes iniciar sesión.";
            } else {
                $error = "Error al crear la cuenta. Por favor, intenta nuevamente.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro | Albus S.R.L.</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px 0;
        }
        .register-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            max-width: 500px;
            width: 100%;
            margin: 0 auto;
        }
        .form-text small {
            font-size: 0.8rem;
        }
        /* Asegurar que el contenido no se desborde en móviles */
        @media (max-width: 576px) {
            .register-card {
                margin: 10px;
                padding: 20px !important;
            }
            body {
                padding: 10px;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="register-card p-4">
                    <div class="text-center mb-4">
                        <h2>Crear Cuenta</h2>
                        <p class="text-muted">Regístrate en el sistema</p>
                    </div>
                    
                    <?php if ($mensaje): ?>
                        <div class="alert alert-success">
                            <?php echo $mensaje; ?>
                            <div class="mt-2">
                                <a href="login.php" class="btn btn-success btn-sm">Iniciar Sesión</a>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Nombre completo</label>
                            <input type="text" class="form-control" name="nombre" required 
                                   value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>"
                                   placeholder="Ingresa tu nombre completo">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Correo electrónico</label>
                            <input type="email" class="form-control" name="correo" required 
                                   value="<?php echo isset($_POST['correo']) ? htmlspecialchars($_POST['correo']) : ''; ?>"
                                   placeholder="tu@email.com">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Tipo de usuario</label>
                            <select class="form-select" name="rol" required>
                                <option value="">Selecciona un rol</option>
                                <option value="Operario" <?php echo (isset($_POST['rol']) && $_POST['rol'] == 'Operario') ? 'selected' : ''; ?>>Operario</option>
                                <option value="Jefe de Producción" <?php echo (isset($_POST['rol']) && $_POST['rol'] == 'Jefe de Producción') ? 'selected' : ''; ?>>Jefe de Producción</option>
                                <option value="Administrador" <?php echo (isset($_POST['rol']) && $_POST['rol'] == 'Administrador') ? 'selected' : ''; ?>>Administrador</option>
                            </select>
                            <div class="form-text">
                                <small>
                                    <strong>Descripción de roles:</strong><br>
                                    • <strong>Operario:</strong> Gestiona inventario y pedidos<br>
                                    • <strong>Jefe de Producción:</strong> Supervisa producción<br>
                                    • <strong>Administrador:</strong> Acceso completo
                                </small>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password" class="form-control" name="contrasena" required 
                                   minlength="6" placeholder="Mínimo 6 caracteres">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Confirmar contraseña</label>
                            <input type="password" class="form-control" name="confirmar_contrasena" required
                                   placeholder="Repite la contraseña">
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Registrarse</button>
                            <a href="index.php" class="btn btn-outline-secondary">Volver</a>
                        </div>
                        
                        <div class="text-center mt-3">
                            <small class="text-muted">
                                ¿Ya tienes cuenta? <a href="login.php" class="text-decoration-none">Inicia sesión aquí</a>
                            </small>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>