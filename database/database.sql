-- =====================================================
-- SCRIPT CORREGIDO Y OPTIMIZADO - ALBUS GESTIÓN ALMACÉN
-- Compatible con MariaDB / phpMyAdmin / GitHub
-- Fecha: 2025-10-17
-- =====================================================

-- Eliminar base de datos anterior y crear una nueva
DROP DATABASE IF EXISTS albus_gestion_almacen;
CREATE DATABASE albus_gestion_almacen CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE albus_gestion_almacen;

-- =====================================================
-- TABLA: usuarios
-- =====================================================
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    correo VARCHAR(50) NOT NULL UNIQUE,
    contrasena VARCHAR(64) NOT NULL, -- Hash SHA256
    rol VARCHAR(20) NOT NULL, -- 'Administrador' o 'Operario'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- TABLA: productos
-- =====================================================
CREATE TABLE productos (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    descripcion VARCHAR(100) NULL,
    unidad_medida VARCHAR(30) NOT NULL,
    stock INT DEFAULT 0,
    stock_minimo INT DEFAULT 10,
    tamaño_peso VARCHAR(50) DEFAULT '',
    presentacion VARCHAR(50) DEFAULT '',
    cantidad_unidad VARCHAR(50) DEFAULT '',
    tipo_especifico VARCHAR(50) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- TABLA: clientes
-- =====================================================
CREATE TABLE clientes (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    empresa VARCHAR(50) NOT NULL,
    contacto VARCHAR(50) NOT NULL,
    telefono VARCHAR(15) NULL,
    email VARCHAR(50) NULL,
    nit VARCHAR(20) NULL,
    direccion VARCHAR(100) NULL,
    ciudad VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- TABLA: pedidos
-- =====================================================
CREATE TABLE pedidos (
    id_pedido INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    empresa_cliente VARCHAR(50) NOT NULL,
    persona_contacto VARCHAR(50) NOT NULL,
    fecha_entrega DATE NOT NULL,
    nota_remision VARCHAR(15) NULL,
    lugar_entrega VARCHAR(100) NULL,
    estado VARCHAR(20) NOT NULL DEFAULT 'Pendiente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente)
);

-- =====================================================
-- TABLA: detalle_pedidos
-- =====================================================
CREATE TABLE detalle_pedidos (
    id_detalle INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad INT NOT NULL,
    FOREIGN KEY (id_pedido) REFERENCES pedidos(id_pedido),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto)
);

-- =====================================================
-- TABLA: entradas
-- =====================================================
CREATE TABLE entradas (
    id_entrada INT AUTO_INCREMENT PRIMARY KEY,
    id_producto INT NOT NULL,
    cantidad INT NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usuario_responsable INT NOT NULL,
    motivo VARCHAR(50) NOT NULL,
    observaciones VARCHAR(100) NULL,
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
    FOREIGN KEY (usuario_responsable) REFERENCES usuarios(id_usuario)
);

-- =====================================================
-- TABLA: salidas
-- =====================================================
CREATE TABLE salidas (
    id_salida INT AUTO_INCREMENT PRIMARY KEY,
    id_producto INT NOT NULL,
    cantidad INT NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usuario_responsable INT NOT NULL,
    motivo VARCHAR(50) NOT NULL,
    observaciones VARCHAR(100) NULL,
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
    FOREIGN KEY (usuario_responsable) REFERENCES usuarios(id_usuario)
);

-- =====================================================
-- TABLA: logs (auditoría)
-- =====================================================
CREATE TABLE logs (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    accion VARCHAR(100) NOT NULL,
    modulo VARCHAR(30) NOT NULL,
    detalles TEXT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

-- =====================================================
-- TABLA: productos_quimicos
-- =====================================================
CREATE TABLE productos_quimicos (
    id_quimico INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    stock INT DEFAULT 0,
    stock_minimo INT DEFAULT 10,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- TABLA: envases
-- =====================================================
CREATE TABLE envases (
    id_envase INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    stock INT DEFAULT 0,
    stock_minimo INT DEFAULT 10,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- ÍNDICES DE OPTIMIZACIÓN
-- =====================================================
CREATE INDEX idx_pedidos_remision ON pedidos(nota_remision);
CREATE INDEX idx_pedidos_estado ON pedidos(estado);
CREATE INDEX idx_pedidos_fecha_entrega ON pedidos(fecha_entrega);
CREATE INDEX idx_clientes_nit ON clientes(nit);
CREATE INDEX idx_clientes_empresa ON clientes(empresa);
CREATE INDEX idx_productos_stock ON productos(stock);
CREATE INDEX idx_quimicos_nombre ON productos_quimicos(nombre);
CREATE INDEX idx_envases_nombre ON envases(nombre);

-- =====================================================
-- DATOS INICIALES
-- =====================================================

-- Usuarios
INSERT INTO usuarios (nombre, correo, contrasena, rol) VALUES 
('Administrador Principal', 'admin@albus.com', SHA2('admin123', 256), 'Administrador'),
('Operario 1', 'operario1@albus.com', SHA2('operario123', 256), 'Operario'),
('Operario 2', 'operario2@albus.com', SHA2('operario123', 256), 'Operario');

-- Productos
INSERT INTO productos (nombre, descripcion, unidad_medida, stock, stock_minimo, tamaño_peso, presentacion, cantidad_unidad, tipo_especifico) VALUES
('Compresas', 'Compresas de gasa', 'Cajas', 100, 10, '5x5cm', 'Caja', '40 unidades', 'Venda'),
('Compresas', 'Compresas de gasa', 'Cajas', 80, 8, '5x5cm', 'Caja', '40 unidades', 'Gasa'),
('Vendas', 'Vendas de gasa', 'Bolsas', 50, 5, '5cm', 'Bolsa', '', 'Normal'),
('Vendas', 'Vendas de gasa', 'Bolsas', 45, 5, '5cm', 'Bolsa', '', 'Policotton'),
('Gasa', 'Gasa blanca', 'Bolsas', 75, 8, '100yds', 'Bolsa', '', 'Blanca'),
('Algodón', 'Algodón entero', 'Paquetes', 30, 5, '100gr', 'Paquete', '', 'Entero'),
('Barbijos quirúrgicos', 'Barbijos quirúrgicos', 'Cajas', 200, 20, '', 'Caja', '50 unidades', 'Quirúrgico'),
('Algodón', 'Algodón entero', 'Paquetes', 60, 10, '5gr', 'Paquete', '', 'Entero'),
('Tapa ojos', 'Tapa ojos blancos', 'Bolsas', 40, 5, '6.5x5.5cm', 'Bolsa', '', 'Blancos'),
('Torundas de gasa', 'Torundas de gasa bolitas', 'Bolsas', 35, 5, '', 'Bolsa', '', 'Bolita');

-- Productos químicos
INSERT INTO productos_quimicos (nombre, stock, stock_minimo) VALUES
('SODA CAUSTICA', 200, 50),
('CARBONATO DE SODIO', 50, 20),
('BLANQUEADOR OPTICO', 25, 10),
('AGUA OXIGENADA', 130, 40),
('ACIDO FORMICO', 75, 25),
('SECUESTRANTE', 25, 10),
('COAGULANTE', 50, 20),
('INCASOF', 25, 10),
('TANAZIM', 30, 15),
('ALCALIFONO', 5, 2);

-- Envases
INSERT INTO envases (nombre, stock, stock_minimo) VALUES
('ETIQUETAS RECTANGULARES 1000 GRS', 4012, 1000),
('ETIQUETAS RECTANGULARES 800 GRS', 24000, 5000),
('ETIQUETAS RECTANGULARES 500 GRS', 9000, 2000),
('ETIQUETAS RECTANGULARES 400 GRS', 78000, 15000),
('ETIQUETAS RECTANGULARES 200 GRS', 10775, 2500),
('ETIQUETAS RECTANGULARES 100 GRS', 16500, 4000),
('ETIQUETAS REDONDAS GRANDES', 96000, 20000),
('ETIQUETAS REDONDAS MEDIANAS', 29000, 6000),
('ETIQUETAS LAMINADO 10 CM', 1000, 200),
('ETIQUETAS LAMINADO 15 CM', 627, 150),
('ETIQUETAS LAMINADO 20 CM', 503, 100),
('BOLSAS DISCO 250 GRS', 3672, 800),
('BOLSAS DISCO 100 GRS', 7800, 1500),
('BOLSAS DISCO 50 GRS', 20600, 4000),
('BOLSAS 50 GRS', 13700, 3000),
('BOLSA 10 GRS', 2813, 500),
('BOLSAS DE ZIGZAG', 7900, 1500),
('BOLSAS DE BOLITAS', 3000, 600),
('BOLSAS DE COMPRESAS 5 X 5', 25627, 5000),
('CAJAS DE COMPRESAS 5 X 5', 10, 5),
('BOLSAS DE COMPRESAS 7,5 X 7,5', 3600, 800),
('CAJAS DE COMPRESA 7,5 X 7,5', 541, 100),
('BOLSAS DE COMPRESA 10 X 10', 78800, 15000),
('CAJAS DE COMPRESA 10 X 10', 240, 50),
('CAJAS DE BARBIJO', 2244, 500),
('CAJAS DE ESPONJAS DE 2 X 2', 100, 20),
('CAJAS DE ESPONJAS DE 4 X 4', 450, 100),
('VENDAS DE 5 CM', 86894, 15000),
('VENDAS DE 7,5 CM', 76333, 15000),
('VENDAS DE 10 CM', 66825, 12000),
('VENDAS DE 12,5 CM', 29575, 6000),
('VENDAS DE 15 CM', 57108, 10000),
('VENDAS DE 20 CM', 55340, 10000),
('ETIQUETAS 150 YDS', 7156, 1500),
('ETIQUETAS DE 2 YDS', 3800, 800),
('ETIQUETAS 4,50 MTS', 487, 100),
('BOBINA DE 65 CM', 66, 20),
('BOBINA DE 60 CM', 74, 20);

-- =====================================================
-- FIN DEL SCRIPT
-- =====================================================
