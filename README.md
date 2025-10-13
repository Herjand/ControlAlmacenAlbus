# Sistema de Gestión de Almacén - ControlAlmacenAlbus

## Descripción
Sistema PHP para gestión de almacén con roles de administrador y operario.

## Características
- Gestión de productos, clientes y pedidos
- Control de entradas y salidas de almacén
- Auditoría de movimientos
- Roles: Administrador y Operario
- Exportación de reportes (PDF, Excel)

## Estructura de archivos
- `/admin` - Panel de administración
- `/operario` - Panel de operario
- `/tcpdf` - Librería para generar PDF

## Instalación
1. Clonar este repositorio
2. Configurar servidor web (XAMPP, WAMP, etc.)
3. Crear base de datos: `albus_gestion_almacen`
4. Copiar `connect.example.php` a `connect.php`
5. Configurar credenciales de base de datos en `connect.php`
6. Importar la estructura de la base de datos

## Configuración de base de datos
- Archivo de configuración: `connect.php`
- Base de datos: `albus_gestion_almacen`
- Usuario: Personalizar según entorno
- Password: Personalizar según entorno