<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login.php");
    exit();
}

include '../../connect.php';

// Procesar cambio de contraseña
$success = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password_actual = $_POST['password_actual'];
    $nuevo_password = $_POST['nuevo_password'];
    $confirmar_password = $_POST['confirmar_password'];
    
    // Verificar campos
    if (empty($password_actual) || empty($nuevo_password) || empty($confirmar_password)) {
        $error = "Todos los campos son obligatorios.";
    } elseif ($nuevo_password !== $confirmar_password) {
        $error = "Las nuevas contraseñas no coinciden.";
    } elseif (strlen($nuevo_password) < 6) {
        $error = "La nueva contraseña debe tener al menos 6 caracteres.";
    } else {
        // Verificar contraseña actual
        $usuario_id = $_SESSION['usuario_id'];
        $sql = "SELECT contrasena FROM usuarios WHERE id_usuario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $datos_usuario = $result->fetch_assoc();
        
        if (hash('sha256', $password_actual) === $datos_usuario['contrasena']) {
            // Actualizar contraseña
            $nuevo_password_hash = hash('sha256', $nuevo_password);
            $update_sql = "UPDATE usuarios SET contrasena = ?, updated_at = NOW() WHERE id_usuario = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("si", $nuevo_password_hash, $usuario_id);
            
            if ($update_stmt->execute()) {
                $success = "Contraseña actualizada correctamente.";
            } else {
                $error = "Error al actualizar la contraseña.";
            }
        } else {
            $error = "La contraseña actual es incorrecta.";
        }
    }
}

// CORREGIDO: Sin el punto extra
include '../header_jefe_produccion.php';
?>

<div class="container-fluid">
    <h2><i class="bi bi-shield-lock"></i> Cambiar Contraseña</h2>
    <p class="text-muted">Actualiza tu contraseña de acceso al sistema.</p>

    <!-- Mensajes -->
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> <?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-key"></i> Cambio de Contraseña
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Contraseña Actual:</label>
                            <input type="password" class="form-control" name="password_actual" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Nueva Contraseña:</label>
                            <input type="password" class="form-control" name="nuevo_password" required minlength="6">
                            <small class="text-muted">Mínimo 6 caracteres</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Confirmar Nueva Contraseña:</label>
                            <input type="password" class="form-control" name="confirmar_password" required minlength="6">
                        </div>

                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>Importante:</strong> Asegúrate de recordar tu nueva contraseña.
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> Cambiar Contraseña
                            </button>
                            <!-- CORREGIDO: Ruta relativa correcta -->
                            <a href="mi_perfil.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
include '../../footer.php';
$conn->close();
?>