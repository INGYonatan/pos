# Documentación del Sistema POS ChatBot Veloz

## Descripción General
Sistema de punto de venta (POS) desarrollado en PHP con MySQL que gestiona inventarios, ventas, cotizaciones, compras y transferencias entre sucursales.

## Estructura Principal del Proyecto

### Archivos Principales
- **index.php** - Página de inicio/dashboard
- **login.php** - Sistema de autenticación
- **menu.php** - Menú principal del sistema
- **pos.php** - Punto de venta principal

### Módulos de Gestión
- **productos.php** - Administración de productos
- **inventario.php** - Control de inventarios
- **ventas.php** - Historial y gestión de ventas
- **cotizaciones-abiertas.php** / **cotizaciones-cerradas.php** - Gestión de cotizaciones
- **compras.php** - Administración de compras
- **clientes.php** - Gestión de clientes
- **proveedores.php** - Administración de proveedores
- **sucursales.php** - Gestión de sucursales

### Módulos de Reportes
- **corte-diario.php** / **corte-mensual.php** - Cortes de caja
- **kardex.php** - Kardex de productos
- **facturas.php** - Gestión de facturación

## CAMBIO CRÍTICO EN TIPOS DE PRODUCTOS

### Situación Anterior
El sistema manejaba solo 2 tipos de productos:
- **equipo** - Para productos que requieren números de serie
- **varios** - Para productos sin números de serie

### Situación Actual ✅
Se eliminó **"varios"** y se agregaron los siguientes tipos:
- **equipo** - SE CONSERVA (maneja números de serie)
- **llantas** - Productos tipo llanta
- **rines** - Productos tipo rin
- **refacciones** - Refacciones automotrices
- **servicios** - Servicios ofrecidos
- **otros** - Productos diversos

### Lógica de Números de Serie
- **Solo "equipo"** maneja números de serie
- **Todos los demás tipos** NO manejan números de serie

## CAMBIOS REALIZADOS ✅

### 1. Modal de Productos (`src/modals/productos.php`)
**✅ ACTUALIZADO PREVIAMENTE**: Ya tenía los nuevos tipos.

### 2. Filtros de Productos (`productos.php`)
**✅ ACTUALIZADO**: Filtro actualizado con los nuevos tipos.

### 3. Filtros de Ventas (`ventas.php`)
**✅ ACTUALIZADO**: Filtro actualizado, cambió "equipo-varios" por "mixto".

### 4. Tabla de Ventas (`data/ventas/ventas_table.php`)
**✅ ACTUALIZADO**: Lógica de tipos y colores actualizada.

### 5. Tabla de Productos (`data/productos/productos_table.php`)
**✅ ACTUALIZADO**: Badges con colores específicos para cada tipo.

### 6. Sistema de Carrito (`data/lib/shopping-cart.php`)
**✅ ACTUALIZADO**: Cambió "equipo-varios" por "mixto".

### 7. Helper de Ventas (`data/lib/helpers/sales.helper.php`)
**✅ ACTUALIZADO**: Función completamente reescrita para manejar los nuevos tipos.

### 8. Tablas de Carrito
**✅ ACTUALIZADAS**:
- `data/pos/pos_carrito_table.php`
- `data/cotizacion-a-venta/cotizacion-a-venta_carrito_table.php`
- `data/editar-orden-compra/editar-orden-compra_carrito_table.php`

### 9. Reportes
**✅ ACTUALIZADOS**:
- `data/corte-diario/corte-diario_table.php`
- `data/cliente-panel/ventas_table.php`

### 10. Funciones de Números de Serie
**✅ DESHABILITADAS TEMPORALMENTE**:
- `data/lib/helpers/purchases.helper.php`
- `inc/specific-functions.inc.php`
- `data/inventario-transferencias/facturas-traslado.php`

## COLORES ASIGNADOS ✅

- **Equipo**: Rojo (`bg-danger`) - CONSERVA números de serie
- **Llantas**: Azul (`bg-primary`)
- **Rines**: Cian (`bg-info`)
- **Refacciones**: Amarillo (`bg-warning`)
- **Servicios**: Verde (`bg-success`)
- **Otros**: Gris (`bg-secondary`)
- **Mixto**: Negro (`bg-dark`) - Para ventas con múltiples tipos

## RESUMEN FINAL ✅

### Tipos Válidos Actuales:
1. **equipo** (conservado) - Con números de serie
2. **llantas** (nuevo) - Sin números de serie
3. **rines** (nuevo) - Sin números de serie
4. **refacciones** (nuevo) - Sin números de serie
5. **servicios** (nuevo) - Sin números de serie
6. **otros** (nuevo) - Sin números de serie

### Tipo Eliminado:
- **varios** - Reemplazado por los nuevos tipos específicos

### Tipo para Ventas Mixtas:
- **mixto** - Cuando una venta contiene múltiples tipos de productos

## CAMBIOS PENDIENTES ⚠️

### 1. Modal de Productos (`src/modals/productos.php`)
**ARCHIVO YA ACTUALIZADO**: Se comentaron las opciones antiguas y se agregaron las nuevas:
```php
<!-- <option value="equipo">Equipo</option>
<option value="varios">Varios</option> -->
<option value="llantas">Llantas</option>
<option value="rines">Rines</option>
<option value="refacciones">Refacciones</option>
<option value="servicios">Servicios</option>
<option value="otros">Otros</option>
```

### 2. Filtros de Productos (`productos.php`)
**PENDIENTE DE ACTUALIZAR** (líneas 115-117):
```php
<option value="equipo">Equipo</option>
<option value="varios">Varios</option>
```

### 3. Filtros de Ventas (`ventas.php`)
**PENDIENTE DE ACTUALIZAR** (líneas 71-73):
```php
<option value="equipo">Equipo</option>
<option value="varios">Varios</option>
<option value="equipo-varios">Equipo y Varios</option>
```

### 4. Procesamiento de Datos de Ventas (`data/ventas/ventas_data.php`)
**PENDIENTE DE ACTUALIZAR** (líneas 30-32):
```php
if ($row["tipo_productos"] == "equipo")         $type = "Equipo";
if ($row["tipo_productos"] == "varios")         $type = "Varios";
if ($row["tipo_productos"] == "equipo-varios")  $type = "Equipo y Varios";
```

### 5. Tabla de Productos (`data/productos/productos_table.php`)
**PENDIENTE DE ACTUALIZAR** (línea 52):
```php
<span class="text-capitalize badge <?= $row['tipo'] == 'equipo' ? 'bg-danger' : 'bg-primary'; ?>"><?= $row['tipo']; ?></span>
```

### 6. Sistema de Carrito (`data/lib/shopping-cart.php`)
**CRÍTICO - PENDIENTE** (línea 447):
```php
if (count($product_types) > 1) $cart->product_type = 'equipo-varios';
```

### 7. Helper de Ventas (`data/lib/helpers/sales.helper.php`)
**PENDIENTE DE ACTUALIZAR** (líneas 483, 467):
```php
if ($equipment_type > 0 && $various_type > 0) return 'Equipo y Varios';
P.tipo = '{$type}'
```

### 8. Tablas de Carrito (Múltiples archivos)
**PENDIENTE DE ACTUALIZAR**:
- `data/pos/pos_carrito_table.php` (líneas 56-60)
- `data/cotizacion-a-venta/cotizacion-a-venta_carrito_table.php` (líneas 53-59)
- `data/editar-orden-compra/editar-orden-compra_carrito_table.php` (líneas 79-85)

### 9. Procesamiento de Transferencias
**PENDIENTE DE ACTUALIZAR**:
- `data/inventario-transferencias/facturas-traslado.php` (líneas 206, 220)

### 10. Cortes Diario y Cliente Panel
**PENDIENTE DE ACTUALIZAR**:
- `data/corte-diario/corte-diario_table.php` (líneas 36-38)
- `data/cliente-panel/ventas_table.php` (líneas 31-33)

## BASE DE DATOS ✅

### Archivo db.sql actualizado
- **Tabla `productos`**: `tipo` enum('equipo','llantas','rines','refacciones','servicios','otros')
- **Tabla `ventas`**: `tipo_productos` enum('equipo','llantas','rines','refacciones','servicios','otros','mixto')

### Script de migración creado
- **Archivo**: `migration_tipos_productos.sql`
- **Propósito**: Actualizar base de datos existente en producción
- **Incluye**: 
  - ALTER TABLE para ambas tablas
  - Migración de 'equipo-varios' → 'mixto'
  - Consultas de verificación
  - **PENDIENTE**: Decidir qué hacer con productos tipo 'varios' existentes

### Acciones requeridas antes de ejecutar migración:
1. **Respaldar base de datos**
2. **Decidir destino de productos 'varios'** (otros, refacciones, servicios, etc.)
3. **Ejecutar en ambiente de pruebas primero**
4. **Verificar integridad de datos**

## Funcionalidades Críticas Afectadas

### 1. Números de Serie
Actualmente solo los productos tipo "equipo" manejan números de serie. **REQUIERE ANÁLISIS** para determinar si alguno de los nuevos tipos necesita esta funcionalidad.

### 2. Clasificación de Ventas
El sistema clasifica las ventas según el tipo de productos vendidos. La lógica actual en `shopping-cart.php` necesita actualizarse para manejar los nuevos tipos.

### 3. Reportes
Los reportes y cortes que filtran por tipo de producto necesitan actualizarse para incluir los nuevos tipos.

## Archivos de Configuración

### Variables de Sesión
- `SESSION_CARRITO_NUEVA_VENTA`
- `SESSION_CARRITO_NUEVA_COTIZACION`
- `SESSION_CARRITO_NUEVA_COMPRA`
- `SESSION_CARRITO_NUEVA_ORDEN_COMPRA`

### Helpers Principales
- `data/lib/helpers/products.helper.php`
- `data/lib/helpers/sales.helper.php`
- `data/lib/shopping-cart.php`

## Recomendaciones

1. **Actualizar inmediatamente** los archivos de filtros para mostrar los nuevos tipos
2. **Revisar la lógica de números de serie** para determinar si aplica a los nuevos tipos
3. **Actualizar las condiciones de clasificación** en el carrito de compras
4. **Probar exhaustivamente** el flujo completo de ventas con los nuevos tipos
5. **Actualizar reportes** para incluir filtros de los nuevos tipos

## Impacto en Funcionalidades

### Alto Impacto
- Sistema de ventas (POS)
- Gestión de inventarios
- Generación de reportes
- Sistema de carrito de compras

### Medio Impacto
- Cotizaciones
- Órdenes de compra
- Transferencias entre sucursales

### Bajo Impacto
- Gestión de usuarios
- Configuración del sistema
- Autenticación

---

**Nota**: Este es un sistema legacy con "código espagueti" como mencionó el usuario. Se recomienda realizar cambios graduales y probar cada modificación antes de continuar con la siguiente.
