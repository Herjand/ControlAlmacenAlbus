<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'Administrador') {
    header("Location: ../login.php");
    exit();
}

include 'header_admin.php';
?>

<div class="container-fluid">
    <h2><i class="bi bi-map"></i> Croquis del Almacén</h2>
    
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-eye"></i> Croquis del Almacén</h5>
                </div>
                <div class="card-body text-center">
                    <img src="../imagenes/croquis.png" 
                         alt="Croquis del Almacén" 
                         class="img-fluid rounded border shadow-sm"
                         style="max-height: 80vh;">
                    <p class="text-muted mt-2">
                        <small>Distribución física del almacén</small>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
include '../footer.php';
?>