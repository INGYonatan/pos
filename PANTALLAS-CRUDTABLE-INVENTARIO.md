# Inventario: Migración a componente crudtable

> Estado: completada la conversión de las 12 pantallas pendientes + 4 pantallas que usaban `datatable.js` + 5 del segundo lote (gastos, tipos, usuarios-catalogos, kardex, menu). En las de plugin antiguo solo se aplicó `crudtable`, sin cambiar el plugin.

## Arquitectura del componente

### Componentes disponibles en `src/components/`

| Componente | Propósito |
|---|---|
| `crudtable.php` | Renderiza la vista completa: título, descripción, filtros, tabla, paginación y botones de acción |
| `crudtable-fields.php` | Renderiza los campos de filtro individuales (input, select, render personalizado) |
| `table-pagination.php` | Nuevo paginador con "Mostrando X a Y de Z registros" + enlaces de página |
| `field-fechas-desde-hasta.php` | Campos de fecha "Desde" y "Hasta" con datepicker |
| `cardLoading.php` | Overlay de carga para la tabla |
| `perPage.php` | Selector de registros por página |

### Datos recibidos por `crudtable`

```php
renderComponent("crudtable", [
  "pageId"          => string,      // Identificador único de la pantalla
  "pageTitle"       => string,      // Título de la página
  "pageDescription" => string,      // Descripción corta
  "actions"         => string,      // HTML de botones de acción (getFilterActions)
  "renderedActions" => string,      // HTML adicional para acciones
  "filters"         => array,       // Filtros (plano o con "principal"/"hidden")
  "extraHtmlInFilters" => string,   // HTML extra dentro de los filtros
]);
```

### Estructura de filtros

Cada filtro puede ser:

```php
// Input de texto
["name" => "search", "label" => "Buscar", "type" => "input", "placeholder" => "..."]

// Select con opciones estáticas
["field" => "select", "name" => "status", "label" => "Estatus",
  "selectOptions" => [ ["value" => "", "label" => "--Todos--"], ... ]]

// Select con opciones dinámicas (HTML)
["field" => "select", "name" => "id_sucursal", "label" => "Sucursal",
  "optionsRender" => "<option>...</option>", "visible" => $IS_ADMIN]

// Render personalizado (ej: fechas)
["field" => "render", "render" => getComponent("field-fechas-desde-hasta")]
```

### Paginación en `_table.php`

Reemplazar `<?= paginate($page, $request['num_pages'], 2, 'load'); ?>` por:

```php
<?php renderComponent("table-pagination", [
  "page"     => $page,
  "perPage"  => $per_page,
  "end"      => $table_row_number,
  "numPages" => $request['num_pages'],
  "total"    => $request['total']
]); ?>
```

### Filtros en `_data.php`

Reemplazar los filtros viejos:

```php
// ANTES
$fecha = cleanStr($_POST['fecha']);
if (!empty($fecha)) array_push($c_where, ["(DATE_FORMAT(...))", $fecha]);

// DESPUÉS
$fecha_inicio = cleanStr($_POST['fecha_inicio']);
$fecha_fin    = cleanStr($_POST['fecha_fin']);
$id_sucursal  = $IS_ADMIN ? cleanStr($_POST['id_sucursal']) : getSessionBranchOfficeId();

if ($fecha_inicio && $fecha_fin) array_push($c_where, ["(DATE_FORMAT(...))", [$fecha_inicio, $fecha_fin], "BETWEEN"]);
if ($fecha_inicio && !$fecha_fin) array_push($c_where, ["(DATE_FORMAT(...))", $fecha_inicio]);
if (!$fecha_inicio && $fecha_fin) array_push($c_where, ["(DATE_FORMAT(...))", $fecha_fin]);
if (!empty($id_sucursal)) array_push($c_where, ["col.id_sucursal", $id_sucursal]);
```

### JS que maneja la carga

`multidatatable/init.js` crea una instancia de `MultiDataTable` y expone:
```js
const load = (page, identifier) => datatable._load(identifier, page);
```
El formulario `<form id="{identifier}-filters-form">` se serializa automáticamente y se envía por AJAX a `data/{identifier}/{identifier}_data.php` con `action=load-{identifier}`.

---

## Pantallas ya convertidas (15)

Estas ya usan `renderComponent("crudtable", ...)` y `table-pagination`:

| # | Root view | Data | Table | Commit |
|---|---|---|---|---|
| 1 | `compras.php` | `data/compras/compras_data.php` | `compras_table.php` | `ff791a7` |
| 2 | `cotizaciones-abiertas.php` | `data/cotizaciones-abiertas/...` | `cotizaciones-abiertas_table.php` | `77eb48a` |
| 3 | `cotizaciones-cerradas.php` | `data/cotizaciones-cerradas/...` | `cotizaciones-cerradas_table.php` | `6a19899` |
| 4 | `cuentas-por-cobrar.php` | `data/cuentas-por-cobrar/...` | `cuentas-por-cobrar_table.php` | `8ab98a4` |
| 5 | `facturas-anticipo-compra.php` | `data/facturas-anticipo-compra/...` | `facturas-anticipo-compra_table.php` | `c01d44f` |
| 6 | `facturas-nota-credito.php` | `data/facturas-nota-credito/...` | `facturas-nota-credito_table.php` | `1322d64` |
| 7 | `facturas-pagos.php` | `data/facturas-pagos/...` | `facturas-pagos_table.php` | `02f84d8` |
| 8 | `facturas-traspaso.php` | `data/facturas-traspaso/...` | `facturas-traspaso_table.php` | `ac86bdd` |
| 9 | `facturas.php` | `data/facturas/facturas_data.php` | `facturas_table.php` | `0ba5fc1` |
| 10 | `inventario-ajustes-historial.php` | `data/inventario-ajustes-historial/...` | `inventario-ajustes-historial_table.php` | `711477b` |
| 11 | `inventario.php` | `data/inventario/inventario_data.php` | `inventario_table.php` | `f8f18ea` |
| 12 | `ordenes-compra.php` | `data/ordenes-compra/...` | `ordenes-compra_table.php` | `39b7628` |
| 13 | `productos.php` | `data/productos/productos_data.php` | `productos_table.php` | `909c704` |
| 14 | `proveedores.php` | `data/proveedores/proveedores_data.php` | `proveedores_table.php` | `840d5f5` |
| 15 | `ventas.php` | `data/ventas/ventas_data.php` | `ventas_table.php` | `bfc960a` |

---

## Pantallas convertidas en esta iteración (12)

Estas ya usan `multidatatable` + `useDataTable` y fueron convertidas a la nueva estructura: root view con `renderComponent("crudtable", ...)`, filtros de búsqueda + fechas desde/hasta + sucursal (admin/sesión) y `_table.php` con `table-pagination`.

### 1. ✅ `categoria-familias` — Familias de categorías

- **Root**: `categoria-familias.php` — form con search + per-page, breadcrumbs con "Volver"
- **Data**: `data/categoria-familias/categoria-familias_data.php`
  - Filtros actuales: `search`, `id_categoria` (hidden)
  - Sin fecha, sin sucursal, sin status
  - Usa `useDataTable` con JOIN a categorías y marcas
- **Table**: `data/categoria-familias/categoria-familias_table.php` — usa `$table_row_number` + `paginate()`
- **Modal**: sí, CRUD completo

### 2. ✅ `categorias` — Líneas (categorías)

- **Root**: `categorias.php` — form con search + per-page, breadcrumbs, hidden `id_marca`
- **Data**: `data/categorias/categorias_data.php`
  - Filtros: `search`, `id_marca`
  - Sin fecha, sin sucursal, sin status
- **Table**: `data/categorias/categorias_table.php` — `$table_row_number` + `paginate()`
- Particular: vista con breadcrumbs "Dashboard > Marcas > Líneas"

### 3. ✅ `cliente-direcciones` — Direcciones de clientes

- **Root**: `cliente-direcciones.php` — form con search + per-page, recibe `uid` del cliente
- **Data**: `data/cliente-direcciones/cliente-direcciones_data.php`
  - Filtros: `search`, `id_cliente`
  - Sin fecha, sin sucursal
- **Table**: `data/cliente-direcciones/cliente-direcciones_table.php` — `$table_row_number` + `paginate()`
- Particular: Tiene script para cargar ciudades/colonias al editar

### 4. ✅ `clientes` — Clientes

- **Root**: `clientes.php` — form con search + per-page, layout estándar
- **Data**: `data/clientes/clientes_data.php`
  - Filtros: `search` (nombre, correo, teléfono)
  - Sin fecha, sin sucursal, status fijo `activo`
- **Table**: `data/clientes/clientes_table.php` — `$table_row_number` + `paginate()`
- Incluye helpers: `catalogs.helper.php`

### 5. ✅ `cortes` — Cortes de caja

- **Root**: `cortes.php` — form con search + fecha + sucursal + per-page
- **Data**: `data/cortes/cortes_data.php`
  - Filtros: `search` (folio), `id_sucursal`, `fecha` (single)
  - Sin admin/session logic aún
- **Table**: `data/cortes/cortes_table.php` — `$table_row_number` + `paginate()`
- Incluye datepicker y ambos plugins (datatable + multidatatable)

### 6. ✅ `inventario-traspasos-realizados` — Traspasos realizados

- **Root**: `inventario-traspasos-realizados.php` — form con search + per-page + filtros
- **Data**: `data/inventario-traspasos-realizados/inventario-traspasos-realizados_data.php`
  - Filtros: `search`, `date`, `originBranchId` (admin logic), `destinyBranchId`
  - Ya tiene `$IS_ADMIN` y `getSessionBranchOfficeId()`
- **Table**: `data/inventario-traspasos-realizados/inventario-traspasos-realizados_table.php` — `$table_row_number` + `paginate()`
- Acciones: cancelar, completar, generar-factura, imprimir ticket

### 7. ✅ `inventario-traspasos-recibidos` — Traspasos recibidos

- **Root**: `inventario-traspasos-recibidos.php` — form con search + per-page + filtros
- **Data**: `data/inventario-traspasos-recibidos/inventario-traspasos-recibidos_data.php`
  - Filtros: `search`, `date`, `originBranchId`, `destinyBranchId` (admin logic)
  - Ya tiene `$IS_ADMIN` y `getSessionBranchOfficeId()`
- **Table**: `data/inventario-traspasos-recibidos/inventario-traspasos-recibidos_table.php` — `$table_row_number` + `paginate()`

### 8. ✅ `inventario-transferencias` — Transferencias

- **Root**: `inventario-transferencias.php` — form con search + fecha + sucursales + per-page
- **Data**: `data/inventario-transferencias/inventario-transferencias_data.php`
  - Filtros: `search`, `fecha` (single), `id_sucursal_origen`, `id_sucursal_destino`
  - Sin admin/session logic
- **Table**: `data/inventario-transferencias/inventario-transferencias_table.php` — `$table_row_number` + `paginate()`

### 9. ✅ `marcas` — Marcas

- **Root**: `marcas.php` — form con search + per-page, estándar
- **Data**: `data/marcas/marcas_data.php`
  - Filtros: `search` (marca)
  - Sin fecha, sin sucursal
  - CRUD completo (add, edit, delete)
- **Table**: `data/marcas/marcas_table.php` — `$table_row_number` + `paginate()`

### 10. ✅ `reporte-ventas-facturadas` — Reporte de ventas facturadas

- **Root**: `reporte-ventas-facturadas.php` — form con search + sucursal admin + per-page + columna stats
- **Data**: `data/reporte-ventas-facturadas/reporte-ventas-facturadas_data.php`
  - Filtros: `search`, `date` (hidden), `id_sucursal` (admin/session)
  - Ya tiene `$IS_ADMIN` y `getSessionBranchOfficeId()`
  - Tabla con stats de totales
- **Table**: `data/reporte-ventas-facturadas/reporte-ventas-facturadas_table.php` — usa `paginate()` (sin `$table_row_number`)
- Particular: Reporte especial, no es CRUD puro

### 11. ✅ `sucursales` — Sucursales

- **Root**: `sucursales.php` — form con search + per-page, estándar
- **Data**: `data/sucursales/sucursales_data.php`
  - Filtros: `search` (nombre, correo, teléfono, cp)
  - Sin fecha, sin sucursal (obvio), status fijo `activo`
- **Table**: `data/sucursales/sucursales_table.php` — `$table_row_number` + `paginate()`
- CRUD completo (add, edit)

### 12. ✅ `usuarios-permisos` — Permisos de usuarios

- **Root**: `usuarios-permisos.php` — form con search, sin per-page, card-overlay
- **Data**: `data/usuarios-permisos/usuarios-permisos_data.php`
  - Filtros: `search`
  - Sin fecha, sin sucursal
  - Tabla especial con switches de permisos
- **Table**: `data/usuarios-permisos/usuarios-permisos_table.php` — lista `<ul>` con switches, paginate comentado
- Particular: UI especial con permisos, no es tabla estándar

---

## Pantallas adicionales convertidas (5) — segundo lote

| # | Root view | Data | Table | Notas |
|---|---|---|---|---|
| 1 | `gastos.php` | `data/gastos/gastos_data.php` | `gastos_table.php` | Model-based; búsqueda + sucursal admin; conserva datepicker del modal |
| 2 | `tipos.php` | `data/tipos/tipos_data.php` | `tipos_table.php` | Model-based; búsqueda simple |
| 3 | `usuarios-catalogos.php` | `data/usuarios-catalogos/usuarios-catalogos_data.php` | `usuarios-catalogos_table.php` | Model-based; hidden `userId` |
| 4 | `kardex.php` | `data/kardex/kardex_data.php` | `kardex_table.php` | Fechas desde/hasta; hidden `id_producto` e `id_sucursal` |
| 5 | `menu.php` | `data/menu/menu_data.php` | `menu_table.php` | Lista sortable con jQuery UI (CDN); conserva plugin `datatable.js` |

---

## Pantallas adicionales convertidas (4)

Estas usaban el plugin `datatable.js` y solo se les aplicó el componente `crudtable` en la root view, **conservando su plugin original** (`datatable.js` + `datatable-init.js`, y `src/main/usuarios.js` en usuarios):

| # | Root view | Data | Table | Notas |
|---|---|---|---|---|
| 1 | `acciones.php` | `data/acciones/acciones_data.php` | `acciones_table.php` | Filtro de tipo + JS `checkType` del modal; conserva su plugin |
| 2 | `modulos.php` | `data/modulos/modulos_data.php` | `modulos_table.php` | Lista sortable con jQuery UI (CDN), sin paginación; conserva su plugin |
| 3 | `roles.php` | `data/roles/roles_data.php` | `roles_table.php` | Búsqueda simple |
| 4 | `usuarios.php` | `data/usuarios/usuarios_data.php` | `usuarios_table.php` | Conserva `datatable.js` + `src/main/usuarios.js`; filtros rol y sucursal |

---

## Pantalla especial (diferente estructura)

| Root view | Motivo |
|---|---|
| `corte-diario.php` | Reporte con selectores día/mes/año, layout diferente, sin per-page estándar |
| `ventas-pagos.php` | Subtabla cargada dentro de la vista de venta, no es root CRUD independiente |
