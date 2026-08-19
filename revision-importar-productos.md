# Revisión: `case "importar-productos"` en `productos_data.php`

## Origen del código

El `case` fue copiado de otro proyecto que tenía una estructura diferente (usaba "bots" / agentes). Este proyecto usa **sucursales** en su lugar.

---

## 1. Modelos faltantes (no existen en este proyecto)

| Modelo usado | ¿Existe en este proyecto? |
|---|---|
| `BotsModel` | ❌ No existe. Este proyecto usa `SucursalesModel` |
| `CategoriasModel` | ❌ La tabla `paal_categorías` sí existe, pero no hay modelo |
| `ProductosModel` | ❌ La tabla `paal_productos` sí existe, pero no hay modelo |
| `ProductoCategoríasModel` | ❌ No hay tabla pivote; `paal_productos` ya tiene `id_categoria` directo |
| `InventarioModel` | ❌ La tabla `paal_inventario` sí existe, pero no hay modelo |
| `AjustesInventarioModel` | ❌ La tabla `paal_inventario_ajustes` sí existe, pero no hay modelo |
| `AjusteProductosModel` | ❌ La tabla `paal_inventario_ajuste_productos` sí existe, pero no hay modelo |

**Conclusión:** Hay que crear 6 modelos (los que realmente se necesiten).

---

## 2. Funciones helper que no existen

| Función | ¿Existe? |
|---|---|
| `cleanStr()` | ✅ Sí, en `inc/global-functions.inc.php` |
| `createSlug()` | ✅ Sí, en `inc/global-functions.inc.php` |
| `parseGoogleSheetCurrency()` | ❌ No existe |
| `getGoogleSheetTireStats()` | ❌ No existe (era específico para llantas) |

---

## 3. Diferencias en la tabla `paal_productos`

### Columnas actuales de la tabla:

- `id_producto`, `id_marca`, `id_categoria`, `id_categoria_familia`, `id_proveedor`
- `id_clave_unidad`, `id_clave_producto_servicio`
- `codigo`, `nombre_producto`
- `unidad` (Pieza/A granel), `contenido`
- **`precio_costo_original`**, **`precio_costo`**
- **`precio_venta_original`**, **`precio_venta`**
- `cantidad_mayoreo`, `precio_mayoreo_original`, `precio_mayoreo`
- **`en_dolares`** (si/no)
- **`tipo`** (equipo/llantas/rines/refacciones/servicios/otros)
- **`aplica_iva`** (si/no)
- **`aplica_ieps`** (si/no) — agregado vía migración
- **`ieps_porcentaje`** (decimal 6,2, default 8.00) — agregado vía migración
- `unidad_entrada` (caja/unidad)
- `unidad_salida` (caja/unidad)
- `numero_piezas`
- `fecha_creacion`, `status`

### Lo que NO existe en la BD:
- ❌ `precio_venta_2` — no hay columna
- ❌ `precio_venta_3` — no hay columna

*(Habría que agregarlos o manejarlos de otra forma)*

---

## 4. Cambios lógicos necesarios

### 4.1 "Bots" → "Sucursales"

El `case` actual itera sobre `$bots` (obtenidos con `BotsModel::getAllByuserId()`). En este proyecto debe iterar sobre **sucursales** usando `SucursalesModel::getAll()`.

Los `$_POST["position_stock_{$md5BotId}"]` en el form ya usan `md5($branch->getId())` porque el frontend ya se actualizó con `SucursalesModel`.

### 4.2 Precios con IVA

El código actual divide entre 1.16 cuando `$pricesWithIva` está activo:
```php
$costPrice = $costPrice / 1.16;
$salePrice = $salePrice / 1.16;
```

Habría que agregar también la lógica para **Precios con IEPS** (el nuevo checkbox).

### 4.3 Manejo de categorías

El código actual busca/crea categorías con `CategoriasModel`. La tabla `paal_categorias` solo tiene `id_categoria`, `id_marca`, `categoria` — no tiene `user_id`, `slug`, etc. como espera el código copiado. Habría que adaptar.

Además, el producto ya tiene `id_categoria` directo, así que quizás no se necesita `ProductoCategoriasModel`.

### 4.4 Precio venta 2 y 3

Como no existen en la BD, se necesita decidir: ¿agregar columnas nuevas o manejarlos de otra forma?

### 4.5 Nuevos campos del form

El form ahora envía estos campos que el `case` actual no procesa:
- `position_type` (tipo)
- `position_unit` (unidad de entrada)
- `position_apply_ieps` (aplica IEPS)
- `position_ieps` (porcentaje IEPS)
- `position_usd` (en dólares)
- `position_sale_price_2` (precio venta 2)
- `position_sale_price_3` (precio venta 3)

### 4.6 Lógica de llantas (tire stats)

`getGoogleSheetTireStats()` ya no aplica — el proyecto actual maneja tipos de producto generales (equipo, llantas, rines, refacciones, servicios, otros). Esa lógica se puede eliminar.

---

## 5. Lo que se conserva igual

- La estructura del `case` con `break` y `$response` al final
- El procesamiento por lotes (CSV → array de productos)
- Los `adjustIds` para mantener consistencia entre lotes
- Asignación de stock por sucursal mediante `position_stock_{md5}`
- Creación de ajustes de inventario

---

## Resumen de tareas necesarias

1. **Crear modelos**: `ProductosModel`, `CategoriasModel`, `InventarioModel`, `AjustesInventarioModel`, `AjusteProductosModel` (adaptados a la estructura real de la BD)
2. **Adaptar lógica de "bots" a "sucursales"**
3. **Agregar soporte de IEPS** (lectura de campos, cálculo si aplica)
4. **Agregar soporte para `precio_venta_2` y `precio_venta_3`** (quizás como columnas nuevas en la BD)
5. **Procesar nuevos campos**: tipo, unidad_entrada, en_dolares
6. **Eliminar lógica de llantas** (tire stats)
7. **Manejar categorías** según la tabla real `paal_categorias`
8. **Revisar la función `parseGoogleSheetCurrency()`** o reemplazarla por algo similar

---

*Documento generado para revisión — cuando tengas las instrucciones, las ejecuto.*
