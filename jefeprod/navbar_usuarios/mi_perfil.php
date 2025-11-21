<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login.php");
    exit();
}

include '../../connect.php';

// Obtener datos del usuario actual
$usuario_id = $_SESSION['usuario_id'];
$sql = "SELECT nombre, correo, rol, created_at, updated_at FROM usuarios WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

// Verificar si se encontró el usuario
if ($result->num_rows === 0) {
    echo "Error: Usuario no encontrado";
    exit();
}

// Usar nombre de variable diferente para evitar conflictos
$datos_usuario = $result->fetch_assoc();

// Incluir header
include '../header_jefe_produccion.php';
?>

<div class="container-fluid">
    <h2><i class="bi bi-person"></i> Mi Perfil</h2>
    <p class="text-muted">Información de tu cuenta de usuario.</p>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-person-badge"></i> Información Personal
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nombre:</label>
                                <p class="form-control-plaintext"><?php echo htmlspecialchars($datos_usuario['nombre'] ?? 'No disponible'); ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Correo Electrónico:</label>
                                <p class="form-control-plaintext"><?php echo htmlspecialchars($datos_usuario['correo'] ?? 'No disponible'); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Rol:</label>
                                <p class="form-control-plaintext">
                                    <span class="badge bg-warning">
                                        <?php echo htmlspecialchars($datos_usuario['rol'] ?? 'No disponible'); ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Fecha de Registro:</label>
                                <p class="form-control-plaintext"><?php echo isset($datos_usuario['created_at']) ? date('d/m/Y H:i', strtotime($datos_usuario['created_at'])) : 'No disponible'; ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Última Actualización:</label>
                                <p class="form-control-plaintext"><?php echo isset($datos_usuario['updated_at']) ? date('d/m/Y H:i', strtotime($datos_usuario['updated_at'])) : 'No disponible'; ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Estado:</label>
                                <p class="form-control-plaintext">
                                    <span class="badge bg-success">Activo</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <i class="bi bi-graph-up"></i> Estadísticas
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="bi bi-person-gear" style="font-size: 80px; color: #e67e22;"></i>
                    </div>
                    <h5><?php echo htmlspecialchars($datos_usuario['nombre'] ?? 'Usuario'); ?></h5>
                    <p class="text-muted"><?php echo htmlspecialchars($datos_usuario['rol'] ?? 'Rol no definido'); ?></p>
                    
                    <div class="mt-4">
                        <small class="text-muted">
                            Miembro desde <?php echo isset($datos_usuario['created_at']) ? date('M Y', strtotime($datos_usuario['created_at'])) : 'Fecha no disponible'; ?>
                        </small>
                    </div>
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