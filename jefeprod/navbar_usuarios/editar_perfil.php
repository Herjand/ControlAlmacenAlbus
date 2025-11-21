<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login.php");
    exit();
}

include '../../connect.php';

// Obtener datos del usuario actual
$usuario_id = $_SESSION['usuario_id'];
$sql = "SELECT * FROM usuarios WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

// Verificar si se encontró el usuario
if ($result->num_rows === 0) {
    echo "Error: Usuario no encontrado";
    exit();
}

$usuario = $result->fetch_assoc();

// Procesar actualización
$success = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    
    if (!empty($nombre) && !empty($correo)) {
        // Verificar si el correo ya existe en otro usuario
        $check_sql = "SELECT id_usuario FROM usuarios WHERE correo = ? AND id_usuario != ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("si", $correo, $usuario_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error = "El correo electrónico ya está en uso por otro usuario.";
        } else {
            // Actualizar perfil
            $update_sql = "UPDATE usuarios SET nombre = ?, correo = ?, updated_at = NOW() WHERE id_usuario = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ssi", $nombre, $correo, $usuario_id);
            
            if ($update_stmt->execute()) {
                // Actualizar sesión
                $_SESSION['usuario_nombre'] = $nombre;
                $success = "Perfil actualizado correctamente.";
                
                // Recargar datos
                $stmt->execute();
                $result = $stmt->get_result();
                $usuario = $result->fetch_assoc();
            } else {
                $error = "Error al actualizar el perfil.";
            }
        }
    } else {
        $error = "Todos los campos son obligatorios.";
    }
}

include '../header_jefe_produccion.php';
?>

<div class="container-fluid">
    <h2><i class="bi bi-pencil-square"></i> Editar Perfil</h2>
    <p class="text-muted">Actualiza tu información personal.</p>

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

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <i class="bi bi-person-gear"></i> Editar Información
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nombre Completo:</label>
                                    <input type="text" class="form-control" name="nombre" 
                                           value="<?php echo htmlspecialchars($usuario['nombre'] ?? ''); ?>" 
                                           required maxlength="50">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Correo Electrónico:</label>
                                    <input type="email" class="form-control" name="correo" 
                                           value="<?php echo htmlspecialchars($usuario['correo'] ?? ''); ?>" 
                                           required maxlength="50">
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>Nota:</strong> Los cambios se reflejarán inmediatamente en tu sesión.
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> Guardar Cambios
                            </button>
                            <!-- CORREGIDO: Ruta relativa correcta -->
                            <a href="../navbar_usuarios/mi_perfil.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Volver al Perfil
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
$stmt->close();
$conn->close();
?>