-- ========================================
-- MIGRACIÓN: Actualización de tipos de productos
-- Fecha: 2025-07-30
-- Descripción: Actualizar ENUMs para incluir nuevos tipos de productos
-- ========================================

-- PASO 1: Actualizar la tabla productos
-- Agregar los nuevos tipos manteniendo 'equipo' y eliminando 'varios'
ALTER TABLE `paal_productos` 
MODIFY `tipo` enum('equipo','llantas','rines','refacciones','servicios','otros') COLLATE utf8mb4_bin NOT NULL;

-- PASO 2: Actualizar la tabla ventas  
-- Agregar los nuevos tipos y cambiar 'equipo-varios' por 'mixto'
ALTER TABLE `paal_ventas` 
MODIFY `tipo_productos` enum('equipo','llantas','rines','refacciones','servicios','otros','mixto') COLLATE utf8mb4_bin NOT NULL;

-- PASO 3: Migrar datos existentes (si es necesario)
-- Actualizar ventas que tenían 'equipo-varios' a 'mixto'
UPDATE `paal_ventas` 
SET `tipo_productos` = 'mixto' 
WHERE `tipo_productos` = 'equipo-varios';

-- PASO 4: Migrar productos que tenían 'varios' (REQUIERE DECISIÓN)
-- IMPORTANTE: Necesitas decidir a qué tipo convertir los productos que eran 'varios'
-- Opciones sugeridas:
--   - Convertir a 'otros' si son productos diversos
--   - Convertir a 'refacciones' si son principalmente refacciones
--   - Convertir a 'servicios' si son servicios
--   - Convertir a tipos específicos según el producto

-- Ejemplo para convertir 'varios' a 'otros':
-- UPDATE `paal_productos` 
-- SET `tipo` = 'otros' 
-- WHERE `tipo` = 'varios';

-- VERIFICACIONES POST-MIGRACIÓN
-- Verificar que no queden registros con tipos antiguos
SELECT COUNT(*) as productos_varios FROM `paal_productos` WHERE `tipo` = 'varios';
SELECT COUNT(*) as ventas_equipo_varios FROM `paal_ventas` WHERE `tipo_productos` = 'equipo-varios';

-- Verificar distribución de tipos actuales
SELECT `tipo`, COUNT(*) as cantidad FROM `paal_productos` GROUP BY `tipo`;
SELECT `tipo_productos`, COUNT(*) as cantidad FROM `paal_ventas` GROUP BY `tipo_productos`;

-- ========================================
-- NOTAS IMPORTANTES:
-- 1. Ejecutar primero en ambiente de pruebas
-- 2. Hacer respaldo de la base de datos antes de ejecutar
-- 3. Decidir qué hacer con los productos que eran tipo 'varios'
-- 4. Solo 'equipo' maneja números de serie
-- ========================================
