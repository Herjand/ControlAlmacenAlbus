<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'Administrador') {
    header("Location: ../login.php");
    exit();
}

include 'header_admin.php';
?>

<div class="container-fluid">
    <h2><i class="bi bi-tag-fill"></i> Uso Correcto de Selladoras</h2>
    
    <div class="row">
        <div class="col-md-8">
            <!-- Guía Principal -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-box-seam"></i> Manual de Operación - Selladoras</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> 
                        <strong>Propósito:</strong> Las selladoras se utilizan para cerrar herméticamente 
                        los empaques de productos médicos, garantizando su esterilidad y conservación.
                    </div>

                    <h6 class="text-primary">📋 Tipos de Selladoras Disponibles</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card border-primary">
                                <div class="card-body">
                                    <h6><i class="bi bi-thermometer text-warning"></i> Selladora Térmica</h6>
                                    <p class="text-muted small">Para bolsas de polietileno y polipropileno</p>
                                    <ul class="text-muted small">
                                        <li>Temperatura: 150°C - 180°C</li>
                                        <li>Tiempo: 2-4 segundos</li>
                                        <li>Presión: Media-Alta</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card border-success">
                                <div class="card-body">
                                    <h6><i class="bi bi-scissors text-success"></i> Selladora de Impulso</h6>
                                    <p class="text-muted small">Para materiales más gruesos</p>
                                    <ul class="text-muted small">
                                        <li>Enfriamiento rápido</li>
                                        <li>Sin residuos térmicos</li>
                                        <li>Para PVC y materiales especiales</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h6 class="text-primary mt-4">🔧 Procedimiento de Sellado</h6>
                    <div class="ms-3">
                        <h6>1. Preparación del Material</h6>
                        <ul class="text-muted">
                            <li>Verificar que la bolsa esté limpia y seca</li>
                            <li>Asegurar que el producto esté correctamente ubicado</li>
                            <li>Dejar 2-3 cm de espacio para el sellado</li>
                            <li>Eliminar aire excedente de la bolsa</li>
                        </ul>

                        <h6>2. Configuración de la Máquina</h6>
                        <ul class="text-muted">
                            <li>Seleccionar temperatura según material (ver tabla)</li>
                            <li>Ajustar tiempo de sellado</li>
                            <li>Configurar presión de cierre</li>
                            <li>Realizar prueba con material de descarte</li>
                        </ul>

                        <h6>3. Proceso de Sellado</h6>
                        <ul class="text-muted">
                            <li>Colocar boca de bolsa en posición correcta</li>
                            <li>Activar mecanismo de cierre</li>
                            <li>Mantener presión hasta completar ciclo</li>
                            <li>Verificar integridad del sellado</li>
                        </ul>
                    </div>

                    <h6 class="text-primary mt-4">🌡️ Parámetros por Tipo de Material</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-secondary">
                                <tr>
                                    <th>Material</th>
                                    <th>Temperatura</th>
                                    <th>Tiempo</th>
                                    <th>Presión</th>
                                    <th>Prueba de Calidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Polietileno (PE)</td>
                                    <td>150°C - 160°C</td>
                                    <td>3 segundos</td>
                                    <td>Media</td>
                                    <td>Estanqueidad al vacío</td>
                                </tr>
                                <tr>
                                    <td>Polipropileno (PP)</td>
                                    <td>160°C - 170°C</td>
                                    <td>3-4 segundos</td>
                                    <td>Media-Alta</td>
                                    <td>Resistencia al desgarre</td>
                                </tr>
                                <tr>
                                    <td>Polipropileno Biorientado</td>
                                    <td>140°C - 150°C</td>
                                    <td>2-3 segundos</td>
                                    <td>Media</td>
                                    <td>Transparencia del sellado</td>
                                </tr>
                                <tr>
                                    <td>Láminas Metalizadas</td>
                                    <td>170°C - 180°C</td>
                                    <td>4-5 segundos</td>
                                    <td>Alta</td>
                                    <td>Integridad de capas</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Control de Calidad -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-award"></i> Control de Calidad en Sellado</h5>
                </div>
                <div class="card-body">
                    <h6>Inspección Visual</h6>
                    <ul class="text-muted">
                        <li>Línea de sellado uniforme y continua</li>
                        <li>Sin burbujas o espacios sin sellar</li>
                        <li>Color uniforme en toda la línea</li>
                        <li>Sin quemaduras o derretimientos excesivos</li>
                    </ul>

                    <h6>Pruebas de Integridad</h6>
                    <ul class="text-muted">
                        <li><strong>Prueba de tracción:</strong> El sellado no debe separarse</li>
                        <li><strong>Prueba de estanqueidad:</strong> No debe pasar aire</li>
                        <li><strong>Prueba de hermeticidad:</strong> Para productos estériles</li>
                        <li><strong>Prueba de envejecimiento:</strong> En lotes de control</li>
                    </ul>

                    <h6>Frecuencia de Verificación</h6>
                    <ul class="text-muted">
                        <li><strong>Cada hora:</strong> 5 unidades del lote en producción</li>
                        <li><strong>Cambio de material:</strong> 10 unidades de prueba</li>
                        <li><strong>Inicio de turno:</strong> Verificación completa de parámetros</li>
                        <li><strong>Lote nuevo:</strong> Pruebas destructivas (3 unidades)</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Seguridad -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-shield-exclamation"></i> Seguridad en Operación</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <strong>ZONA DE ALTA TEMPERATURA</strong>
                    </div>
                    
                    <h6>Equipo de Protección Personal (EPP)</h6>
                    <ul class="text-muted small">
                        <li>Guantes térmicos certificados</li>
                        <li>Gafas de seguridad</li>
                        <li>Manga larga (algodón)</li>
                        <li>Zapatos de seguridad</li>
                    </ul>

                    <h6>Procedimientos de Emergencia</h6>
                    <ul class="text-muted small">
                        <li>Conocer ubicación de extintores</li>
                        <li>Saber usar botiquín de primeros auxilios</li>
                        <li>Tener números de emergencia visibles</li>
                        <li>Conocer rutas de evacuación</li>
                    </ul>

                    <h6>Prohibiciones</h6>
                    <ul class="text-muted small">
                        <li>❌ Operar con manos húmedas</li>
                        <li>❌ Usar joyas o relojes</li>
                        <li>❌ Dejar máquina encendida sin supervisión</li>
                        <li>❌ Realizar ajustes con máquina en operación</li>
                    </ul>
                </div>
            </div>

            <!-- Mantenimiento -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-tools"></i> Mantenimiento de Selladoras</h5>
                </div>
                <div class="card-body">
                    <h6>Mantenimiento Diario</h6>
                    <ul class="text-muted small">
                        <li>Limpiar bandas de sellado con alcohol</li>
                        <li>Verificar estado de elementos calefactores</li>
                        <li>Lubricar guías y partes móviles</li>
                        <li>Revisar conexiones eléctricas</li>
                    </ul>

                    <h6>Mantenimiento Semanal</h6>
                    <ul class="text-muted small">
                        <li>Limpieza profunda de toda la máquina</li>
                        <li>Verificación de parámetros térmicos</li>
                        <li>Calibración de sensores</li>
                        <li>Revisión de mangueras y conexiones</li>
                    </ul>

                    <h6>Mantenimiento Mensual</h6>
                    <ul class="text-muted small">
                        <li>Cambio de bandas de teflón (si aplica)</li>
                        <li>Revisión completa del sistema eléctrico</li>
                        <li>Verificación de seguridad</li>
                        <li>Actualización de registros de mantenimiento</li>
                    </ul>
                </div>
            </div>

            <!-- Registro de Operación -->
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-clipboard-check"></i> Registro Diario</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label small">Operador:</label>
                        <input type="text" class="form-control form-control-sm" placeholder="Nombre del operador">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small">Turno:</label>
                        <select class="form-select form-select-sm">
                            <option>Mañana (6:00 - 14:00)</option>
                            <option>Tarde (14:00 - 22:00)</option>
                            <option>Noche (22:00 - 6:00)</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small">Temperatura de Trabajo:</label>
                        <input type="text" class="form-control form-control-sm" placeholder="Ej: 160°C">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small">Incidencias:</label>
                        <textarea class="form-control form-control-sm" rows="2" placeholder="Problemas o observaciones..."></textarea>
                    </div>
                    
                    <button class="btn btn-sm btn-success w-100">
                        <i class="bi bi-check-circle"></i> Registrar Operación
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>