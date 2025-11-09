<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit();
}

include 'header_operario.php';
?>

<div class="container-fluid">
    <h2><i class="fas fa-tools"></i> Uso de Equipos</h2>
    <p class="text-muted">Guía rápida para el uso correcto de los equipos del almacén.</p>

    <div class="row">
        <!-- Selladoras -->
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-tag"></i> Selladoras
                </div>
                <div class="card-body">
                    <h5>Procedimiento de Uso:</h5>
                    <ol class="small">
                        <li>Conectar la selladora a la corriente eléctrica</li>
                        <li>Encender el interruptor de potencia</li>
                        <li>Esperar que caliente (luz indicadora se enciende)</li>
                        <li>Colocar la bolsa en la zona de sellado</li>
                        <li>Bajar la palanca firmemente por 2-3 segundos</li>
                        <li>Liberar la palanca y retirar la bolsa sellada</li>
                    </ol>
                    
                    <div class="alert alert-warning mt-3">
                        <small>
                            <strong><i class="fas fa-exclamation-triangle"></i> Precauciones:</strong><br>
                            • No tocar la zona caliente durante el uso<br>
                            • Mantener las manos alejadas del área de sellado<br>
                            • Desconectar después de usar<br>
                            • Limpiar regularmente la banda de sellado
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Máquina Loteadora -->
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-barcode"></i> Máquina Loteadora
                </div>
                <div class="card-body">
                    <h5>Procedimiento de Uso:</h5>
                    <ol class="small">
                        <li>Encender la máquina (interruptor lateral)</li>
                        <li>Cargar el rollo de etiquetas</li>
                        <li>Configurar la fecha y lote en el software</li>
                        <li>Colocar el producto en la banda transportadora</li>
                        <li>Presionar el botón de impresión</li>
                        <li>Verificar que la etiqueta se aplique correctamente</li>
                    </ol>

                    <div class="alert alert-info mt-3">
                        <small>
                            <strong><i class="fas fa-info-circle"></i> Mantenimiento:</strong><br>
                            • Revisar nivel de tinta regularmente<br>
                            • Limpiar el cabezal de impresión semanalmente<br>
                            • Reportar fallas inmediatamente al supervisor
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contactos de Soporte -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <i class="fas fa-headset"></i> Soporte Técnico
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-wrench"></i> Mantenimiento de Equipos</h6>
                            <p class="small mb-1"><strong>Responsable:</strong> Juan Pérez</p>
                            <p class="small mb-1"><strong>Extensión:</strong> 245</p>
                            <p class="small"><strong>Horario:</strong> 8:00 - 17:00 hrs</p>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-exclamation-triangle"></i> Reporte de Fallas</h6>
                            <p class="small mb-1">Reportar inmediatamente cualquier anomalía</p>
                            <p class="small mb-1">No intentar reparar equipos sin autorización</p>
                            <p class="small">Usar el formato de reporte de fallas</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
include '../footer.php';
?>