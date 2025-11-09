<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit();
}

include '../connect.php';
include 'header_operario.php';

// Consultar solo productos normales
$sql = "SELECT 
            nombre,
            descripcion,
            tamaño_peso,
            presentacion,
            cantidad_unidad,
            tipo_especifico
        FROM productos
        ORDER BY nombre ASC";

$result = $conn->query($sql);
?>

<div class="container-fluid">
    <h2><i class="fas fa-boxes"></i> Ver Productos</h2>
    <p class="text-muted">Consulta el catálogo de productos disponibles.</p>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-6">
                    <input type="text" name="buscar" class="form-control" placeholder="Buscar producto..." 
                           value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">Buscar</button>
                    <a href="productos_operario.php" class="btn btn-secondary">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Productos -->
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <i class="fas fa-list-ul"></i> Catálogo de Productos
        </div>
        <div class="card-body">
            <?php if ($result && $result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-secondary">
                            <tr>
                                <th>Nombre del Producto</th>
                                <th>Descripción</th>
                                <th>Especificaciones Técnicas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $productos = [];
                            while ($row = $result->fetch_assoc()) {
                                $productos[] = $row;
                            }
                            
                            // Aplicar filtros de búsqueda
                            if (isset($_GET['buscar']) && !empty($_GET['buscar'])) {
                                $buscar = strtolower($_GET['buscar']);
                                $productos = array_filter($productos, function($producto) use ($buscar) {
                                    return strpos(strtolower($producto['nombre']), $buscar) !== false || 
                                           strpos(strtolower($producto['descripcion']), $buscar) !== false;
                                });
                            }
                            
                            foreach ($productos as $row): 
                                // Construir especificaciones
                                $especificaciones = [];
                                if ($row['tamaño_peso']) {
                                    $especificaciones[] = '<strong>Tamaño/Peso:</strong> ' . htmlspecialchars($row['tamaño_peso']);
                                }
                                if ($row['presentacion']) {
                                    $especificaciones[] = '<strong>Presentación:</strong> ' . htmlspecialchars($row['presentacion']);
                                }
                                if ($row['cantidad_unidad']) {
                                    $especificaciones[] = '<strong>Cantidad:</strong> ' . htmlspecialchars($row['cantidad_unidad']);
                                }
                                if ($row['tipo_especifico']) {
                                    $especificaciones[] = '<strong>Tipo:</strong> ' . htmlspecialchars($row['tipo_especifico']);
                                }
                            ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['nombre']); ?></strong>
                                    </td>
                                    <td>
                                        <?php if ($row['descripcion']): ?>
                                            <?php echo htmlspecialchars($row['descripcion']); ?>
                                        <?php else: ?>
                                            <small class="text-muted">- Sin descripción -</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($especificaciones)): ?>
                                            <div class="small">
                                                <?php foreach ($especificaciones as $espec): ?>
                                                    <div><?php echo $espec; ?></div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <small class="text-muted">- Sin especificaciones -</small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle"></i> No hay productos registrados en el sistema.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
include '../footer.php';
$conn->close();
?>