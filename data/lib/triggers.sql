DELIMITER //

CREATE TRIGGER inventario_ajuste AFTER INSERT ON paal_inventario_ajuste_productos
FOR EACH ROW BEGIN
    DECLARE ia_tipo_movimiento VARCHAR(100);
    DECLARE ia_id_sucursal VARCHAR(100);
    
    SET ia_tipo_movimiento = (SELECT tipo FROM paal_inventario_ajustes WHERE id_inventario_ajuste = NEW.id_inventario_ajuste LIMIT 1);
    SET ia_id_sucursal = (SELECT id_sucursal FROM paal_inventario_ajustes WHERE id_inventario_ajuste = NEW.id_inventario_ajuste LIMIT 1);

    IF (ia_tipo_movimiento = 'incremento') THEN
        UPDATE paal_inventario SET
            stock = stock + NEW.cantidad
        WHERE
            id_producto = NEW.id_producto AND
            id_sucursal = ia_id_sucursal;
    END IF;

    IF (ia_tipo_movimiento = 'decremento') THEN
        UPDATE paal_inventario SET
            stock = stock - NEW.cantidad
        WHERE
            id_producto = NEW.id_producto AND
            id_sucursal = ia_id_sucursal;
    END IF;

END//

DELIMITER ;

/* ::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: 
CANCELAR AJUSTE
::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
DELIMITER //

CREATE TRIGGER inventario_cancelar_ajuste AFTER UPDATE ON paal_inventario_ajustes
FOR EACH ROW BEGIN
    IF (NEW.status != OLD.status AND NEW.status = 'cancelado') THEN
        UPDATE paal_inventario_ajuste_productos SET
            cancelado = 'si'
        WHERE
            id_inventario_ajuste = OLD.id_inventario_ajuste;
    END IF;
END//

DELIMITER ;

/* ::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: 
CANCELAR AJUSTE PRODUCTO
::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */

DELIMITER //

CREATE TRIGGER inventario_cancelar_ajuste_producto AFTER UPDATE ON paal_inventario_ajuste_productos
FOR EACH ROW BEGIN
    DECLARE ia_tipo_movimiento VARCHAR(100);
    DECLARE ia_id_sucursal VARCHAR(100);
    
    SET ia_tipo_movimiento = (SELECT tipo FROM paal_inventario_ajustes WHERE id_inventario_ajuste = NEW.id_inventario_ajuste LIMIT 1);
    SET ia_id_sucursal = (SELECT id_sucursal FROM paal_inventario_ajustes WHERE id_inventario_ajuste = NEW.id_inventario_ajuste LIMIT 1);

    IF (NEW.cancelado != OLD.cancelado AND NEW.cancelado = 'si') THEN
        UPDATE paal_inventario_ajuste_producto_numeros_serie SET cancelado = 'si' WHERE id_inventario_ajuste_producto = NEW.id_inventario_ajuste_producto;

        IF (ia_tipo_movimiento = 'incremento') THEN
            UPDATE paal_inventario SET
                stock = stock - OLD.cantidad
            WHERE
                id_producto = OLD.id_producto AND
                id_sucursal = ia_id_sucursal;
        END IF;

        IF (ia_tipo_movimiento = 'decremento') THEN
            UPDATE paal_inventario SET
                stock = stock + OLD.cantidad
            WHERE
                id_producto = OLD.id_producto AND
                id_sucursal = ia_id_sucursal;
        END IF;
    END IF;
END//

DELIMITER ;

/* ::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: 
CANCELAR AJUSTE PRODUCTO NUMEROS DE SERIE
::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */

DELIMITER //

CREATE TRIGGER inventario_cancelar_ajuste_producto_numeros_serie AFTER UPDATE ON paal_inventario_ajuste_producto_numeros_serie
FOR EACH ROW BEGIN
    DECLARE ia_tipo_movimiento VARCHAR(100);
    DECLARE ia_id_sucursal VARCHAR(100);
    DECLARE ia_id_producto VARCHAR(100);
    DECLARE ia_numero_serie VARCHAR(250);
    
    SET ia_tipo_movimiento = (SELECT tipo FROM paal_inventario_ajustes WHERE id_inventario_ajuste = NEW.id_inventario_ajuste LIMIT 1);
    SET ia_id_sucursal = (SELECT id_sucursal FROM paal_inventario_ajustes WHERE id_inventario_ajuste = NEW.id_inventario_ajuste LIMIT 1);
    SET ia_id_producto = (SELECT id_producto FROM paal_inventario_ajuste_productos WHERE id_inventario_ajuste_producto = NEW.id_inventario_ajuste_producto LIMIT 1);
    SET ia_numero_serie = NEW.numero_serie;

    IF (NEW.cancelado != OLD.cancelado AND NEW.cancelado = 'si') THEN
        IF (ia_tipo_movimiento = 'incremento') THEN
            DELETE FROM paal_producto_numeros_serie
            WHERE
                id_producto  = ia_id_producto  AND
                numero_serie = ia_numero_serie AND
                id_sucursal  = ia_id_sucursal;
        END IF;

        IF (ia_tipo_movimiento = 'decremento') THEN
            INSERT INTO paal_producto_numeros_serie
                (
                    id_producto,
                    numero_serie,
                    id_sucursal
                ) VALUES (
                    ia_id_producto,
                    ia_numero_serie,
                    ia_id_sucursal
                );
        END IF;
    END IF;
END//

DELIMITER ;

/* ::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: 
//.. INVENTARIO TRANSFERENCIA
::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
DELIMITER //

CREATE TRIGGER inventario_transferencia AFTER INSERT ON paal_inventario_transferencia_productos
FOR EACH ROW BEGIN
    DECLARE ia_tipo_movimiento VARCHAR(100);
    DECLARE ia_id_sucursal_origen INT;
    DECLARE ia_id_sucursal_destino INT;
    
    SET ia_tipo_movimiento = (SELECT tipo FROM paal_inventario_transferencias WHERE id_inventario_transferencia = NEW.id_inventario_transferencia LIMIT 1);
    SET ia_id_sucursal_origen = (SELECT id_sucursal_origen FROM paal_inventario_transferencias WHERE id_inventario_transferencia = NEW.id_inventario_transferencia LIMIT 1);
    SET ia_id_sucursal_destino = (SELECT id_sucursal_destino FROM paal_inventario_transferencias WHERE id_inventario_transferencia = NEW.id_inventario_transferencia LIMIT 1);

    IF (ia_tipo_movimiento = 'incremento') THEN
        UPDATE paal_inventario SET
            stock = stock + NEW.cantidad
        WHERE
            id_producto = NEW.id_producto AND
            id_sucursal = ia_id_sucursal_destino;

        UPDATE paal_inventario SET
            stock = stock - NEW.cantidad
        WHERE
            id_producto = NEW.id_producto AND
            id_sucursal = ia_id_sucursal_origen;
    END IF;

    /* IF (ia_tipo_movimiento = 'decremento') THEN
        UPDATE paal_inventario SET
            stock = stock - NEW.cantidad
        WHERE
            id_producto = NEW.id_producto AND
            id_sucursal = ia_id_sucursal;
    END IF; */

END//

DELIMITER ;


/* ::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: 
CANCELAR TRANSFERENCIA
::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
DELIMITER //

CREATE TRIGGER inventario_cancelar_transferencia AFTER UPDATE ON paal_inventario_transferencias
FOR EACH ROW BEGIN
    IF (NEW.status != OLD.status AND NEW.status = 'cancelado') THEN
        UPDATE paal_inventario_transferencia_productos SET
            cancelado = 'si'
        WHERE
            id_inventario_transferencia = OLD.id_inventario_transferencia;
    END IF;
END//

DELIMITER ;

/* ::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: 
CANCELAR TRANSFERENCIA PRODUCTO
::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */

DELIMITER //

CREATE TRIGGER inventario_cancelar_transferencia_producto AFTER UPDATE ON paal_inventario_transferencia_productos
FOR EACH ROW BEGIN
    DECLARE ia_tipo_movimiento VARCHAR(100);
    DECLARE ia_id_sucursal_origen VARCHAR(100);
    DECLARE ia_id_sucursal_destino VARCHAR(100);
    
    SET ia_tipo_movimiento = (SELECT tipo FROM paal_inventario_transferencias WHERE id_inventario_transferencia = OLD.id_inventario_transferencia LIMIT 1);
    SET ia_id_sucursal_origen = (SELECT id_sucursal_origen FROM paal_inventario_transferencias WHERE id_inventario_transferencia = OLD.id_inventario_transferencia LIMIT 1);
    SET ia_id_sucursal_destino = (SELECT id_sucursal_destino FROM paal_inventario_transferencias WHERE id_inventario_transferencia = OLD.id_inventario_transferencia LIMIT 1);

    IF (NEW.cancelado != OLD.cancelado AND NEW.cancelado = 'si') THEN
        UPDATE paal_inventario_transferencia_producto_numeros_serie SET cancelado = 'si' WHERE id_inventario_transferencia_producto = NEW.id_inventario_transferencia_producto;

        IF (ia_tipo_movimiento = 'incremento') THEN
            UPDATE paal_inventario SET
                stock = stock - OLD.cantidad
            WHERE
                id_producto = OLD.id_producto AND
                id_sucursal = ia_id_sucursal_destino;

            UPDATE paal_inventario SET
                stock = stock + OLD.cantidad
            WHERE
                id_producto = OLD.id_producto AND
                id_sucursal = ia_id_sucursal_origen;
        END IF;

        IF (ia_tipo_movimiento = 'decremento') THEN
            UPDATE paal_inventario SET
                stock = stock + OLD.cantidad
            WHERE
                id_producto = OLD.id_producto AND
                id_sucursal = ia_id_sucursal_destino;

            UPDATE paal_inventario SET
                stock = stock - OLD.cantidad
            WHERE
                id_producto = OLD.id_producto AND
                id_sucursal = ia_id_sucursal_origen;
        END IF;
    END IF;
END//

DELIMITER ;

/* ::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: 
CANCELAR TRANSFERENCIA PRODUCTO NUMEROS DE SERIE
::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */

DELIMITER //

CREATE TRIGGER inventario_cancelar_transferencia_producto_numeros_serie AFTER UPDATE ON paal_inventario_transferencia_producto_numeros_serie
FOR EACH ROW BEGIN
    DECLARE ia_tipo_movimiento VARCHAR(100);
    DECLARE ia_id_sucursal_origen VARCHAR(100);
    DECLARE ia_id_sucursal_destino VARCHAR(100);
    DECLARE ia_id_producto VARCHAR(100);
    DECLARE ia_numero_serie VARCHAR(250);

    IF (NEW.cancelado != OLD.cancelado AND NEW.cancelado = 'si') THEN
        SET ia_tipo_movimiento = (SELECT tipo FROM paal_inventario_transferencias WHERE id_inventario_transferencia = OLD.id_inventario_transferencia LIMIT 1);
        SET ia_id_sucursal_origen = (SELECT id_sucursal_origen FROM paal_inventario_transferencias WHERE id_inventario_transferencia = OLD.id_inventario_transferencia LIMIT 1);
        SET ia_id_sucursal_destino = (SELECT id_sucursal_destino FROM paal_inventario_transferencias WHERE id_inventario_transferencia = OLD.id_inventario_transferencia LIMIT 1);
        SET ia_id_producto = (SELECT id_producto FROM paal_inventario_transferencia_productos WHERE id_inventario_transferencia_producto = OLD.id_inventario_transferencia_producto LIMIT 1);
        SET ia_numero_serie = NEW.numero_serie;

        UPDATE paal_producto_numeros_serie SET
            id_sucursal = ia_id_sucursal_origen
        WHERE
            id_producto  = ia_id_producto  AND
            numero_serie = ia_numero_serie AND
            id_sucursal  = ia_id_sucursal_destino;
    END IF;
END//

DELIMITER ;


/* ::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: 
//-- VENTAS
::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */

DELIMITER //

CREATE TRIGGER venta AFTER INSERT ON paal_venta_productos
FOR EACH ROW BEGIN
    DECLARE tipo_movimiento_venta VARCHAR(100);
    DECLARE id_sucursal_venta VARCHAR(100);
    
    SET tipo_movimiento_venta = (SELECT tipo FROM paal_ventas WHERE id_venta = NEW.id_venta LIMIT 1);
    SET id_sucursal_venta = (SELECT id_sucursal FROM paal_ventas WHERE id_venta = NEW.id_venta LIMIT 1);

    IF (tipo_movimiento_venta = 'incremento') THEN
        UPDATE paal_inventario SET
            stock = stock - NEW.cantidad
        WHERE
            id_producto = NEW.id_producto AND
            id_sucursal = id_sucursal_venta;
    END IF;

    IF (tipo_movimiento_venta = 'decremento') THEN
        UPDATE paal_inventario SET
            stock = stock + NEW.cantidad
        WHERE
            id_producto = NEW.id_producto AND
            id_sucursal = id_sucursal_venta;
    END IF;

END//

DELIMITER ;

/* ::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: 
//-- CANCELAR VENTA
::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
DELIMITER //

CREATE TRIGGER cancelar_venta AFTER UPDATE ON paal_ventas
FOR EACH ROW BEGIN
    IF (NEW.status != OLD.status AND NEW.status = 'cancelado') THEN
        UPDATE paal_venta_productos SET
            cancelado = 'si'
        WHERE
            id_venta = OLD.id_venta;
    END IF;
END//

DELIMITER ;

/* ::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: 
CANCELAR VENTA PRODUCTO
::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */

DELIMITER //

CREATE TRIGGER cancelar_venta_producto AFTER UPDATE ON paal_venta_productos
FOR EACH ROW BEGIN
    DECLARE tipo_movimiento_venta VARCHAR(100);
    DECLARE id_sucursal_venta VARCHAR(100);
    
    SET tipo_movimiento_venta = (SELECT tipo FROM paal_ventas WHERE id_venta = NEW.id_venta LIMIT 1);
    SET id_sucursal_venta = (SELECT id_sucursal FROM paal_ventas WHERE id_venta = NEW.id_venta LIMIT 1);

    IF (NEW.cancelado != OLD.cancelado AND NEW.cancelado = 'si') THEN
        IF (tipo_movimiento_venta = 'incremento') THEN
            UPDATE paal_inventario SET
                stock = stock + OLD.cantidad
            WHERE
                id_producto = OLD.id_producto AND
                id_sucursal = id_sucursal_venta;
        END IF;

        IF (tipo_movimiento_venta = 'decremento') THEN
            UPDATE paal_inventario SET
                stock = stock - OLD.cantidad
            WHERE
                id_producto = OLD.id_producto AND
                id_sucursal = id_sucursal_venta;
        END IF;
    END IF;
END//

DELIMITER ;


/* ::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: 
//-- COMPRAS
::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */

DELIMITER //

CREATE TRIGGER compra AFTER INSERT ON paal_compra_productos
FOR EACH ROW BEGIN
    DECLARE tipo_movimiento_compra VARCHAR(100);
    DECLARE id_sucursal_compra VARCHAR(100);
    
    SET tipo_movimiento_compra = (SELECT tipo FROM paal_compras WHERE id_compra = NEW.id_compra LIMIT 1);
    SET id_sucursal_compra = (SELECT id_sucursal FROM paal_compras WHERE id_compra = NEW.id_compra LIMIT 1);

    IF (tipo_movimiento_compra = 'incremento') THEN
        UPDATE paal_inventario SET
            stock = stock + NEW.cantidad
        WHERE
            id_producto = NEW.id_producto AND
            id_sucursal = id_sucursal_compra;
    END IF;

    IF (tipo_movimiento_compra = 'decremento') THEN
        UPDATE paal_inventario SET
            stock = stock - NEW.cantidad
        WHERE
            id_producto = NEW.id_producto AND
            id_sucursal = id_sucursal_compra;
    END IF;

END//

DELIMITER ;

/* ::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: 
//-- CANCELAR COMPRA
::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
DELIMITER //

CREATE TRIGGER cancelar_compra AFTER UPDATE ON paal_compras
FOR EACH ROW BEGIN
    IF (NEW.status != OLD.status AND NEW.status = 'cancelado') THEN
        UPDATE paal_compra_productos SET
            cancelado = 'si'
        WHERE
            id_compra = OLD.id_compra;
    END IF;
END//

DELIMITER ;

/* ::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: 
CANCELAR COMPRA PRODUCTO
::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */

DELIMITER //

CREATE TRIGGER cancelar_compra_producto AFTER UPDATE ON paal_compra_productos
FOR EACH ROW BEGIN
    DECLARE tipo_movimiento_compra VARCHAR(100);
    DECLARE id_sucursal_compra VARCHAR(100);
    
    SET tipo_movimiento_compra = (SELECT tipo FROM paal_compras WHERE id_compra = NEW.id_compra LIMIT 1);
    SET id_sucursal_compra = (SELECT id_sucursal FROM paal_compras WHERE id_compra = NEW.id_compra LIMIT 1);

    IF (NEW.cancelado != OLD.cancelado AND NEW.cancelado = 'si') THEN
        IF (tipo_movimiento_compra = 'incremento') THEN
            UPDATE paal_inventario SET
                stock = stock - OLD.cantidad
            WHERE
                id_producto = OLD.id_producto AND
                id_sucursal = id_sucursal_compra;
        END IF;

        IF (tipo_movimiento_compra = 'decremento') THEN
            UPDATE paal_inventario SET
                stock = stock + OLD.cantidad
            WHERE
                id_producto = OLD.id_producto AND
                id_sucursal = id_sucursal_compra;
        END IF;
    END IF;
END//

DELIMITER ;