<?php include 'connect.php';
include 'header.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control | Albus S.R.L.</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a2d9d6a6c2.js" crossorigin="anonymous"></script>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f8f9fa;
        }
        .sidebar {
            height: 100vh;
            background-color: #1e2a38;
            padding-top: 20px;
            position: fixed;
            width: 230px;
        }
        .sidebar a {
            color: #fff;
            display: block;
            padding: 12px 20px;
            text-decoration: none;
            border-left: 4px solid transparent;
            transition: 0.3s;
        }
        .sidebar a:hover, .sidebar a.active {
            background-color: #0d6efd;
            border-left: 4px solid #00c3ff;
        }
        .content {
            margin-left: 120px;
            padding: 20px;
            padding-top: 40px;
        }
        .card-summary {
            color: white;
            border: none;
        }
        .card-purple { background-color: #6f42c1; }
        .card-green { background-color: #198754; }
        .card-orange { background-color: #fd7e14; }
        .card-blue { background-color: #0dcaf0; }
        footer {
            text-align: center;
            margin-top: 40px;
            color: #777;
        }
    </style>
</head>
<body>

<!-- SIDEBAR 
<div class="sidebar">
    <a href="index.php" class="active"><i class="bi bi-house-door"></i> Inicio</a>
    <a href="productos.php"><i class="bi bi-box"></i> Productos</a>
    <a href="pedidos.php"><i class="bi bi-cart-check"></i> Pedidos</a>
    <a href="entradas.php"><i class="bi bi-arrow-down-square"></i> Entradas</a>
    <a href="salidas.php"><i class="bi bi-arrow-up-square"></i> Salidas</a>
    <a href="despachos.php"><i class="bi bi-truck"></i> Despachos</a>
    <a href="logs.php"><i class="bi bi-activity"></i> Logs</a>
    <hr class="text-secondary">
    <a href="acerca.php"><i class="bi bi-info-circle"></i> Acerca de nosotros</a>
    <a href="uso_loteadora.php"><i class="bi bi-gear"></i> Uso correcto de loteadora</a>
    <a href="croquis.php"><i class="bi bi-map"></i> Croquis del almacén</a>
</div>
    -->

<!-- CONTENIDO -->
<div class="content">
    <h2 class="mb-4">Panel de Control <small class="text-muted">Versión 1.0</small></h2>

    <!-- TARJETAS DE RESUMEN -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card card-summary card-purple">
                <div class="card-body">
                    <h5>INVENTARIO NETO</h5>
                    <h3>1,497,851.75 Bs</h3>
                    <p>Productos en stock: 9</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-summary card-green">
                <div class="card-body">
                    <h5>VENTAS 2025</h5>
                    <h3>8,927,284.29 Bs</h3>
                    <p>Pedidos completados: 255</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-summary card-orange">
                <div class="card-body">
                    <h5>ENTRADAS 2025</h5>
                    <h3>3,635,078.10 Bs</h3>
                    <p>Registros realizados: 95</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-summary card-blue">
                <div class="card-body">
                    <h5>DESPACHOS</h5>
                    <h3>7,887</h3>
                    <p>Pedidos entregados</p>
                </div>
            </div>
        </div>
    </div>

    <!-- GRÁFICO DE BARRAS -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5>Reporte de Movimientos 2025</h5>
        </div>
        <div class="card-body">
            <canvas id="grafico"></canvas>
        </div>
    </div>

    <!-- TABLAS DE REGISTROS -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">Últimos pedidos</div>
                <div class="card-body">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID Pedido</th>
                                <th>Empresa</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>0012</td><td>Ecofoods SRL</td><td>02-10-2025</td><td><span class="badge bg-success">Completado</span></td></tr>
                            <tr><td>0013</td><td>Alimentos Vega</td><td>03-10-2025</td><td><span class="badge bg-warning text-dark">En progreso</span></td></tr>
                            <tr><td>0014</td><td>CafeBol</td><td>04-10-2025</td><td><span class="badge bg-danger">Rechazado</span></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">Últimas entradas</div>
                <div class="card-body">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID Entrada</th>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>0021</td><td>Harina Premium</td><td>50 kg</td><td>01-10-2025</td></tr>
                            <tr><td>0022</td><td>Cacao Natural</td><td>30 kg</td><td>02-10-2025</td></tr>
                            <tr><td>0023</td><td>Azúcar sin refinar</td><td>25 kg</td><td>03-10-2025</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('grafico');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre'],
        datasets: [{
            label: 'Entradas',
            data: [1200, 1900, 3000, 2500, 2200, 3500, 2700, 3200, 3800, 4100],
            backgroundColor: '#0d6efd'
        }, {
            label: 'Salidas',
            data: [1000, 1700, 2800, 2300, 2000, 3100, 2500, 3000, 3500, 3900],
            backgroundColor: '#198754'
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>

</body>
<?php include 'footer.php'; ?>
</html>

