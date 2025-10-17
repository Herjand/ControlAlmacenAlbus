<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'Administrador') {
    header("Location: ../login.php");
    exit();
}

include 'header_admin.php';
?>

<div class="container-fluid">
    <h2><i class="bi bi-gear-fill"></i> Uso Correcto de Loteadora</h2>
    
    <div class="row">
        <div class="col-md-8">
            <!-- Guía Principal -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Manual de Operación - Loteadora</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> 
                        <strong>Importante:</strong> Siga estas instrucciones para garantizar 
                        el correcto funcionamiento de la máquina loteadora y la seguridad del operador.
                    </div>

                    <h6 class="text-primary">📋 Procedimiento de Operación</h6>
                    <div class="ms-3">
                        <h6>1. Preparación Inicial</h6>
                        <ul class="text-muted">
                            <li>Verificar que la máquina esté conectada a tierra</li>
                            <li>Limpiar la superficie de trabajo</li>
                            <li>Revisar el nivel de tinta o cinta</li>
                            <li>Encender la máquina y esperar el calentamiento (2-3 minutos)</li>
                        </ul>

                        <h6>2. Configuración</h6>
                        <ul class="text-muted">
                            <li>Seleccionar el tipo de etiqueta según el producto</li>
                            <li>Ajustar la temperatura según el material (consultar tabla)</li>
                            <li>Configurar la velocidad de impresión</li>
                            <li>Probar en una etiqueta de muestra antes de la producción</li>
                        </ul>

                        <h6>3. Operación</h6>
                        <ul class="text-muted">
                            <li>Colocar el rollo de etiquetas correctamente</li>
                            <li>Alimentar la etiqueta de forma uniforme</li>
                            <li>Verificar que la numeración sea consecutiva</li>
                            <li>Realizar limpieza cada 1000 etiquetas</li>
                        </ul>
                    </div>

                    <h6 class="text-primary mt-4">🌡️ Tabla de Temperaturas Recomendadas</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-secondary">
                                <tr>
                                    <th>Tipo de Material</th>
                                    <th>Temperatura Recomendada</th>
                                    <th>Velocidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Papel Kraft</td>
                                    <td>130°C - 140°C</td>
                                    <td>Media (4-5)</td>
                                </tr>
                                <tr>
                                    <td>Polipropileno</td>
                                    <td>150°C - 160°C</td>
                                    <td>Media-Baja (3-4)</td>
                                </tr>
                                <tr>
                                    <td>Polietileno</td>
                                    <td>140°C - 150°C</td>
                                    <td>Media (4-5)</td>
                                </tr>
                                <tr>
                                    <td>Vidrio</td>
                                    <td>160°C - 170°C</td>
                                    <td>Baja (2-3)</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Mantenimiento -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-tools"></i> Mantenimiento Preventivo</h5>
                </div>
                <div class="card-body">
                    <h6>Mantenimiento Diario</h6>
                    <ul class="text-muted">
                        <li>Limpiar cabezal de impresión con alcohol isopropílico</li>
                        <li>Verificar estado de los rodillos</li>
                        <li>Limpiar sensores ópticos</li>
                        <li>Revisar nivel de consumibles</li>
                    </ul>

                    <h6>Mantenimiento Semanal</h6>
                    <ul class="text-muted">
                        <li>Lubricar partes móviles (según manual)</li>
                        <li>Verificar ajustes mecánicos</li>
                        <li>Limpiza profunda del sistema de alimentación</li>
                        <li>Calibración de sensores</li>
                    </ul>

                    <h6>Mantenimiento Mensual</h6>
                    <ul class="text-muted">
                        <li>Revisión eléctrica completa</li>
                        <li>Cambio de piezas de desgaste</li>
                        <li>Actualización de software (si aplica)</li>
                        <li>Verificación de seguridad</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Seguridad -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-shield-exclamation"></i> Normas de Seguridad</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <strong>PRECAUCIONES</strong>
                    </div>
                    
                    <h6>⚠️ Prohibido</h6>
                    <ul class="text-muted small">
                        <li>Tocar cabezal caliente con las manos</li>
                        <li>Operar sin conexión a tierra</li>
                        <li>Usar líquidos inflamables cerca</li>
                        <li>Dejar la máquina encendida sin supervisión</li>
                        <li>Realizar reparaciones sin autorización</li>
                    </ul>

                    <h6>✅ Obligatorio</h6>
                    <ul class="text-muted small">
                        <li>Usar guantes térmicos al manipular piezas calientes</li>
                        <li>Mantener área de trabajo limpia y ordenada</li>
                        <li>Reportar fallas inmediatamente al supervisor</li>
                        <li>Apagar y desconectar para mantenimiento</li>
                        <li>Capacitarse antes de operar</li>
                    </ul>
                </div>
            </div>

            <!-- Solución de Problemas -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-wrench"></i> Solución de Problemas Comunes</h5>
                </div>
                <div class="card-body">
                    <h6>Problema: Etiquetas no se adhieren</h6>
                    <p class="text-muted small"><strong>Solución:</strong> Aumentar temperatura 5°C y reducir velocidad</p>

                    <h6>Problema: Numeración salteada</h6>
                    <p class="text-muted small"><strong>Solución:</strong> Verificar sensor y limpiar lente</p>

                    <h6>Problema: Manchas en etiquetas</h6>
                    <p class="text-muted small"><strong>Solución:</strong> Limpiar cabezal y reducir temperatura</p>

                    <h6>Problema: Máquina no enciende</h6>
                    <p class="text-muted small"><strong>Solución:</strong> Verificar fusibles y conexión eléctrica</p>

                    <div class="alert alert-warning mt-3">
                        <small>
                            <i class="bi bi-telephone"></i> 
                            <strong>Soporte Técnico:</strong> 
                            Contactar al área de mantenimiento al interno 205
                        </small>
                    </div>
                </div>
            </div>

            <!-- Checklist Diario -->
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-check2-square"></i> Checklist Diario</h5>
                </div>
                <div class="card-body">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox">
                        <label class="form-check-label small">Limpieza de cabezal realizada</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox">
                        <label class="form-check-label small">Nivel de tinta verificado</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox">
                        <label class="form-check-label small">Temperatura calibrada</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox">
                        <label class="form-check-label small">Prueba de etiqueta OK</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox">
                        <label class="form-check-label small">Area de trabajo ordenada</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox">
                        <label class="form-check-label small">EPP verificado</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>