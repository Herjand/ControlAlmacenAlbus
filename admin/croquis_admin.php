<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'Administrador') {
    header("Location: ../login.php");
    exit();
}

include 'header_admin.php';

// Procesar subida de imagen
$mensaje = '';
$imagen_actual = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['imagen_croquis'])) {
    $directorio_upload = '../uploads/croquis/';
    
    // Crear directorio si no existe
    if (!is_dir($directorio_upload)) {
        mkdir($directorio_upload, 0755, true);
    }
    
    $archivo = $_FILES['imagen_croquis'];
    $nombre_archivo = 'croquis_almacen.' . pathinfo($archivo['name'], PATHINFO_EXTENSION);
    $ruta_completa = $directorio_upload . $nombre_archivo;
    
    // Validar tipo de archivo
    $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    
    if (in_array($extension, $extensiones_permitidas)) {
        if (move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
            $mensaje = '<div class="alert alert-success">✅ Croquis actualizado correctamente</div>';
            $imagen_actual = $ruta_completa;
        } else {
            $mensaje = '<div class="alert alert-danger">❌ Error al subir la imagen</div>';
        }
    } else {
        $mensaje = '<div class="alert alert-danger">❌ Formato no permitido. Use JPG, PNG o GIF</div>';
    }
}

// Buscar imagen existente
$directorio_upload = '../uploads/croquis/';
$archivos = glob($directorio_upload . 'croquis_almacen.*');
if (!empty($archivos)) {
    $imagen_actual = $archivos[0];
}
?>

<div class="container-fluid">
    <h2><i class="bi bi-map"></i> Croquis del Almacén</h2>
    
    <div class="row">
        <div class="col-md-8">
            <!-- Visualización del Croquis -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-eye"></i> Croquis Actual del Almacén</h5>
                </div>
                <div class="card-body">
                    <?php echo $mensaje; ?>
                    
                    <?php if ($imagen_actual && file_exists($imagen_actual)): ?>
                        <div class="text-center">
                            <img src="<?php echo $imagen_actual . '?t=' . time(); ?>" 
                                 alt="Croquis del Almacén" 
                                 class="img-fluid rounded border shadow-sm"
                                 style="max-height: 600px;">
                            <p class="text-muted mt-2">
                                <small>Última actualización: <?php echo date('d/m/Y H:i', filemtime($imagen_actual)); ?></small>
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-image text-muted" style="font-size: 4rem;"></i>
                            <h5 class="text-muted mt-3">No hay croquis cargado</h5>
                            <p class="text-muted">Suba una imagen del croquis del almacén usando el formulario.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Información del Almacén -->
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Distribución del Almacén</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-