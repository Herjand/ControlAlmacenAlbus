<?php
session_start();
require_once '../../connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../pedidos_admin.php?error=1");
    exit();
}

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'Administrador') {
    header("Location: ../../login.php");
    exit();
}

// Obtener datos del formulario
$id_cliente = intval($_POST['id_cliente'] ?? 0);
$empresa_cliente = trim($_POST['empresa_cliente'] ?? '');
$persona_contacto = trim($_POST['persona_contacto'] ?? '');
$fecha_entrega = $_POST['fecha_entrega'] ?? '';
$nota_remision = trim($_POST['nota_remision'] ?? '');
$lugar_entrega = trim($_POST['lugar_entrega'] ?? '');
$productos = $_POST['productos'] ?? [];

// Debug: Verificar datos recibidos
error_log("Datos recibidos - id_cliente: $id_cliente, empresa: $empresa_cliente, productos: " . count($productos));

// Validaciones
if (empty($empresa_cliente) || empty($persona_contacto) || empty($fecha_entrega)) {
    error_log("Error: Campos vacíos");
    header("Location: ../pedidos_admin.php?error=2");
    exit();
}

// Validar que haya al menos un producto
if (empty($productos) || !is_array($productos)) {
    error_log("Error: No hay productos");
    header("Location: ../pedidos_admin.php?error=2&message=Debe agregar al menos un producto");
    exit();
}

// Contar productos válidos
$productos_validos = 0;
foreach ($productos as $producto) {
    if (!empty($producto['id_producto']) && !empty($producto['cantidad']) && intval($producto['cantidad']) > 0) {
        $productos_validos++;
    }
}

if ($productos_validos === 0) {
    error_log("Error: No hay productos válidos");
    header("Location: ../pedidos_admin.php?error=2&message=Debe agregar al menos un producto válido");
    exit();
}

try {
    // Iniciar transacción
    $conn->begin_transaction();

    // 1. Insertar el pedido
    $sql_pedido = "INSERT INTO pedidos (id_cliente, empresa_cliente, persona_contacto, fecha_entrega, nota_remision, lugar_entrega) 
                   VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_pedido = $conn->prepare($sql_pedido);
    
    if (!$stmt_pedido) {
        throw new Exception("Error al preparar consulta de pedido: " . $conn->error);
    }
    
    $stmt_pedido->bind_param("isssss", $id_cliente, $empresa_cliente, $persona_contacto, $fecha_entrega, $nota_remision, $lugar_entrega);
    
    if (!$stmt_pedido->execute()) {
        throw new Exception("Error al ejecutar consulta de pedido: " . $stmt_pedido->error);
    }
    
    $id_pedido = $stmt_pedido->insert_id;
    error_log("Pedido creado con ID: $id_pedido");

    // 2. Insertar los productos del pedido
    $sql_detalle = "INSERT INTO detalle_pedidos (id_pedido, id_producto, cantidad) VALUES (?, ?, ?)";
    $stmt_detalle = $conn->prepare($sql_detalle);
    
    if (!$stmt_detalle) {
        throw new Exception("Error al preparar consulta de detalle: " . $conn->error);
    }
    
    $total_productos = 0;
    $total_items = 0;
    
    foreach ($productos as $index => $producto) {
        $id_producto = intval($producto['id_producto'] ?? 0);
        $cantidad = intval($producto['cantidad'] ?? 0);
        
        // Solo procesar productos válidos
        if ($id_producto > 0 && $cantidad > 0) {
            error_log("Agregando producto: ID=$id_producto, Cantidad=$cantidad");
            
            $stmt_detalle->bind_param("iii", $id_pedido, $id_producto, $cantidad);
            if (!$stmt_detalle->execute()) {
                throw new Exception("Error al registrar producto $id_producto: " . $stmt_detalle->error);
            }
            $total_productos++;
            $total_items += $cantidad;
        } else {
            error_log("Producto inválido en índice $index: ID=$id_producto, Cantidad=$cantidad");
        }
    }

    // Confirmar transacción
    $conn->commit();
    
    // Registrar en logs
    $accion = "Registró nuevo pedido #" . $id_pedido . " - " . $empresa_cliente . " (" . $total_productos . " productos, " . $total_items . " items)";
    $sql_log = "INSERT INTO logs (id_usuario, accion, modulo) VALUES (?, ?, 'Pedidos')";
    $stmt_log = $conn->prepare($sql_log);
    $stmt_log->bind_param("is", $_SESSION['usuario_id'], $accion);
    $stmt_log->execute();
    
    error_log("Pedido registrado exitosamente");
    header("Location: ../pedidos_admin.php?success=1");

} catch (Exception $e) {
    // Revertir transacción
    $conn->rollback();
    error_log("Error al registrar pedido: " . $e->getMessage());
    header("Location: ../pedidos_admin.php?error=1");
}

if (isset($stmt_pedido)) $stmt_pedido->close();
if (isset($stmt_detalle)) $stmt_detalle->close();
if (isset($stmt_log)) $stmt_log->close();
$conn->close();
exit();
?>