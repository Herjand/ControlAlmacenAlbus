<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login.php");
    exit();
}

include '../header_operario.php';
?>

<div class="container-fluid">
    <h2><i class="bi bi-arrow-left-right"></i> Cambiar de Usuario</h2>
    <p class="text-muted">Cerrar sesión actual e iniciar con otro usuario.</p>

    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <i class="bi bi-person-check"></i> Confirmar Cambio de Usuario
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="bi bi-arrow-left-right" style="font-size: 64px; color: #fd7e14;"></i>
                    </div>
                    
                    <h4>¿Cambiar de Usuario?</h4>
                    <p class="text-muted">
                        Estás a punto de cerrar la sesión actual de 
                        <strong><?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></strong> 
                        y serás redirigido al inicio de sesión.
                    </p>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Nota:</strong> Todos los cambios no guardados se perderán.
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <a href="procesar_logout.php?redirect=login" class="btn btn-warning btn-lg">
                            <i class="bi bi-arrow-left-right"></i> Sí, Cambiar de Usuario
                        </a>
                        <a href="/ControlAlmacenAlbus/admin/navbar_usuarios/mi_perfil.php" class="btn btn-secondary btn-lg">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../footer.php'; ?>