# Plan de implementación — Importador de productos

## Paso 1 — Frontend: `form-importar-productos.php`

Agregar los campos faltantes a la tabla de asignación de columnas:

- `requiere_numero_serie` (1/0) — select de mapeo
- `numero_serie` — select de mapeo
- `Marca` — select de mapeo
- `Proveedor` — select de mapeo
- `Unidad de medida (SAT)` — select de mapeo
- `Clave del SAT` — select de mapeo

También:
- Actualizar el `data-subtitle` del filepicker con los nuevos campos
- Los selects llevan la clase `<?= $elementId; ?>-csv-match` para integrarse al flujo existente

**Entregable:** form con los campos completos (12 + 6 nuevos = 18 campos).

---

## Paso 2 — Crear modelos (9)

Base: estructura de `sucursales.model.php` + métodos CRUD de `model.example.md`.

| Modelo | Tabla | Notas |
|---|---|---|
| `TypesModel` | `paal_tipos` | getBySlug, create |
| `BrandsModel` | `paal_marcas` | getByName (upper), create (upper) |
| `SuppliersModel` | `paal_proveedores` | getByName (upper), create (upper) |
| `ProductServiceCodesModel` | `paal_clave_producto_servicios` | getByClave |
| `UnitCodesModel` | `paal_clave_unidades` | getByClave |
| `InventarioModel` | `paal_inventario` | getBySucursalIdAndProductoId, update stock |
| `ProductosModel` | `paal_productos` | getByCodigo, create, update |
| `AjustesInventarioModel` | `paal_inventario_ajustes` | create, getById |
| `AjusteInventarioProductosModel` | `paal_inventario_ajuste_productos` | create |

**Entregable:** 9 modelos funcionales en `data/lib/models/`.

---

## Paso 3 — Adaptar `case "importar-productos"` en `productos_data.php`

### 3.1 Quitar
- `parseGoogleSheetCurrency()` → usar `floatval()` o similar
- `getGoogleSheetTireStats()` → eliminar lógica de llantas (width/height/diameter)
- `CategoriasModel` → eliminar toda la lógica de categorías
- `ProductoCategoriasModel` → eliminar

### 3.2 Cambiar
- `BotsModel` → `SucursalesModel` (ya existe)
- `AjusteProductosModel` → `AjusteInventarioProductosModel`
- `setVat()` → `setAplicaIva()`
- `$productsModel->setPrice()` → `setPrecioVenta()`, `setPrecioVenta2()`, `setPrecioVenta3()`
- `setCostPrice()` → `setPrecioCosto()`

### 3.3 Ajustes provisionales (crear si no existe)
- **Tipo**: buscar por slug (`createSlug`), si no existe crear con `requiere_numero_serie` (del CSV), `tangible=1`
- **Marca**: buscar por nombre en UPPERCASE, crear en UPPERCASE si no existe
- **Proveedor**: igual que marcas
- **Unidad SAT**: buscar por clave
- **Clave SAT**: buscar por clave

### 3.4 Nuevos campos a procesar
- `position_type` → tipo
- `position_unit` → unidad de entrada
- `position_apply_ieps` → aplica_ieps
- `position_ieps` → ieps_porcentaje (default 8)
- `position_usd` → en_dolares
- `position_sale_price_2` → precio_venta2
- `position_sale_price_3` → precio_venta3
- `position_requires_serial` → requiere_numero_serie
- `position_serial_number` → numero_serie
- `position_brand` → marca
- `position_supplier` → proveedor
- `position_unit_code` → unidad SAT
- `position_service_code` → clave SAT

### 3.5 Lógica de precios
- IVA: dividir entre 1.16 si `prices_with_iva` está activo
- IEPS: aplicar cálculo si `prices_with_ieps` está activo (dividir entre 1 + ieps_porcentaje/100)
- Guardar `precio_venta_original`, `precio_venta2_original`, `precio_venta3_original` (valores brutos del CSV)

### 3.6 Ajustes de inventario por sucursal
- Iterar sobre sucursales (no bots)
- `position_stock_{md5(sucursal_id)}`
- Mantener la lógica de `adjustIds` entre lotes
- `AjustesInventarioModel` → `tipo` = 'incremento'/'decremento' según stock
- `AjusteInventarioProductosModel` con id_tipo y tipo (nombre del tipo)

---

## Paso 4 — Verificación final

- Revisar que el form envíe todos los `position_*` correctamente
- Probar el flujo completo de importación (idealmente con un CSV de prueba)
- Verificar que los ajustes de inventario se creen por sucursal

---

*Nota: los pasos pueden ajustarse según lo que vaya surgiendo durante la implementación.*
