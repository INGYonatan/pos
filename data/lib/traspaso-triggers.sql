DELIMITER $$

CREATE TRIGGER `tg_inv_transf_prod_restar_origen_ins` AFTER INSERT ON `paal_inventario_transferencia_productos`
FOR EACH ROW
BEGIN
    -- tg_inventario_transferencia_productos_restar_origen_al_insertar
    -- Resta el stock del inventario de la sucursal de origen al insertar un producto en la transferencia

    DECLARE ia_id_sucursal_origen INT DEFAULT NULL;
    DECLARE ia_id_sucursal_destino INT DEFAULT NULL;

    SELECT
      id_sucursal_origen,
      id_sucursal_destino
    INTO
      ia_id_sucursal_origen,
      ia_id_sucursal_destino
    FROM paal_inventario_transferencias
    WHERE
      id_inventario_transferencia = NEW.id_inventario_transferencia
    LIMIT 1;

    -- Restar al inventario de origen pero no sumar al de destino hasta que se complete la transferencia
    UPDATE paal_inventario SET
      stock = stock - NEW.cantidad
    WHERE
      id_producto = NEW.id_producto AND
      id_sucursal = ia_id_sucursal_origen;
END$$

DELIMITER ;


DELIMITER $$

CREATE TRIGGER `tg_inv_transf_prod_sumar_destino_comp` AFTER UPDATE ON `paal_inventario_transferencia_productos`
FOR EACH ROW
BEGIN
    -- tg_inventario_transferencia_productos_sumar_destino_al_completar
    -- Suma el stock al inventario de la sucursal de destino al completar la transferencia

    DECLARE ia_id_sucursal_origen INT DEFAULT NULL;
    DECLARE ia_id_sucursal_destino INT DEFAULT NULL;
    DECLARE ia_status VARCHAR(100) DEFAULT NULL;

    SELECT
      id_sucursal_origen,
      id_sucursal_destino,
      `status`
    INTO
      ia_id_sucursal_origen,
      ia_id_sucursal_destino,
      ia_status
    FROM
      paal_inventario_transferencias
    WHERE
      id_inventario_transferencia = NEW.id_inventario_transferencia
    LIMIT 1;

    IF ((ia_status = 'procesado-correctamente' OR ia_status = 'procesado-con-diferencias') AND NEW.completado = 'si' AND OLD.completado = 'no') THEN
        -- Sumar al inventario de destino cuando se complete la transferencia
        UPDATE paal_inventario SET
          stock = stock + NEW.recibido
        WHERE
          id_producto = NEW.id_producto AND
          id_sucursal = ia_id_sucursal_destino;
    END IF;
END$$

DELIMITER ;

DELIMITER $$

CREATE TRIGGER `tg_inv_transf_prods_prod_revt_canc` AFTER UPDATE ON `paal_inventario_transferencia_productos`
FOR EACH ROW
BEGIN
    -- tg_inventario_transferencia_productos_revertir_por_cancelacion
    -- Revertir los cambios en el inventario si se cancela la transferencia

    DECLARE ia_id_sucursal_origen INT DEFAULT NULL;
    DECLARE ia_id_sucursal_destino INT DEFAULT NULL;
    DECLARE ia_status VARCHAR(100) DEFAULT NULL;

    SELECT
      id_sucursal_origen,
      id_sucursal_destino,
      `status`
    INTO
      ia_id_sucursal_origen,
      ia_id_sucursal_destino,
      ia_status
    FROM
      paal_inventario_transferencias
    WHERE
      id_inventario_transferencia = NEW.id_inventario_transferencia
    LIMIT 1;

    IF (NEW.cancelado = 'si' AND OLD.cancelado = 'no') THEN
        -- Revertir los cambios en el inventario si se cancela la transferencia
        UPDATE paal_inventario SET
          stock = stock + OLD.cantidad
        WHERE
          id_producto = OLD.id_producto AND
          id_sucursal = ia_id_sucursal_origen;

        -- Si la transferencia ya estaba completada, también restar del inventario de destino
        IF (ia_status = 'procesado-correctamente' OR ia_status = 'procesado-con-diferencias') THEN
            UPDATE paal_inventario SET
              stock = stock - OLD.recibido
            WHERE
              id_producto = OLD.id_producto AND
              id_sucursal = ia_id_sucursal_destino;
        END IF;
    END IF;
END$$

DELIMITER ;


DELIMITER $$

CREATE TRIGGER `tg_inv_transf_prod_nums_resv_ins` AFTER INSERT ON `paal_inventario_transferencia_producto_numeros_serie`
 FOR EACH ROW
 BEGIN
    -- tg_inventario_transferencia_producto_numeros_serie_reservar_al_insertar
    -- Reserva el número de serie en el inventario de la sucursal de origen al insertar un número de serie en la transferencia

    DECLARE ia_id_sucursal_origen VARCHAR(100);
    DECLARE ia_id_producto VARCHAR(100);

    SET ia_id_sucursal_origen = (SELECT id_sucursal_origen FROM paal_inventario_transferencias WHERE id_inventario_transferencia = NEW.id_inventario_transferencia LIMIT 1);
    SET ia_id_producto = (SELECT id_producto FROM paal_inventario_transferencia_productos WHERE id_inventario_transferencia_producto = NEW.id_inventario_transferencia_producto LIMIT 1);

    UPDATE paal_producto_numeros_serie SET
        status = 'reservado-para-transferencia'
    WHERE
        id_producto  = ia_id_producto  AND
        numero_serie = NEW.numero_serie AND
        id_sucursal  = ia_id_sucursal_origen;
END$$

DELIMITER ;

DELIMITER $$

CREATE TRIGGER `tg_inv_transf_prod_nums_asign_comp` AFTER UPDATE ON `paal_inventario_transferencia_producto_numeros_serie`
 FOR EACH ROW
 BEGIN
    -- tg_inventario_transferencia_producto_numeros_serie_asignar_al_completar
    -- Asigna el número de serie al inventario de la sucursal de destino al completar la transferencia
    -- Cambia el estado del número de serie según si fue recibido o no
 
    DECLARE ia_id_sucursal_origen INT DEFAULT NULL;
    DECLARE ia_id_sucursal_destino INT DEFAULT NULL;
    DECLARE ia_status VARCHAR(100) DEFAULT NULL;
    DECLARE ia_id_producto INT DEFAULT NULL;

    SELECT
      id_sucursal_origen,
      id_sucursal_destino,
      `status`
    INTO
      ia_id_sucursal_origen,
      ia_id_sucursal_destino,
      ia_status
    FROM
      paal_inventario_transferencias
    WHERE
      id_inventario_transferencia = NEW.id_inventario_transferencia
    LIMIT 1;

    SET ia_id_producto = (SELECT id_producto FROM paal_inventario_transferencia_productos WHERE id_inventario_transferencia_producto = NEW.id_inventario_transferencia_producto LIMIT 1);

    IF ((ia_status = 'procesado-correctamente' OR ia_status = 'procesado-con-diferencias') AND (NEW.completado = 'si' AND OLD.completado = 'no') AND NEW.recibido = 'si') THEN
        UPDATE paal_producto_numeros_serie SET
            id_sucursal = ia_id_sucursal_destino,
            status = 'disponible'
        WHERE
            id_producto  = ia_id_producto  AND
            numero_serie = NEW.numero_serie AND
            id_sucursal  = ia_id_sucursal_origen;
    END IF;

    IF ((ia_status = 'procesado-correctamente' OR ia_status = 'procesado-con-diferencias') AND (NEW.completado = 'si' AND OLD.completado = 'no') AND NEW.recibido = 'no') THEN
        UPDATE paal_producto_numeros_serie SET
            status = 'pendiente-de-ajuste'
        WHERE
            id_producto  = ia_id_producto  AND
            numero_serie = NEW.numero_serie AND
            id_sucursal  = ia_id_sucursal_origen;
    END IF;
END$$

DELIMITER ;

DELIMITER $$

CREATE TRIGGER `tg_inv_transf_prod_nums_revt_canc` AFTER UPDATE ON `paal_inventario_transferencia_producto_numeros_serie`
 FOR EACH ROW
 BEGIN
    -- tg_inventario_transferencia_producto_numeros_serie_revertir_por_cancelacion
    -- Revertir los cambios en el inventario si se cancela la transferencia
    -- Cambia el estado del número de serie según si fue recibido o no

    DECLARE ia_id_sucursal_origen INT DEFAULT NULL;
    DECLARE ia_id_sucursal_destino INT DEFAULT NULL;
    DECLARE ia_status VARCHAR(100) DEFAULT NULL;
    DECLARE ia_id_producto INT DEFAULT NULL;

    SELECT
      id_sucursal_origen,
      id_sucursal_destino,
      `status`
    INTO
      ia_id_sucursal_origen,
      ia_id_sucursal_destino,
      ia_status
    FROM
      paal_inventario_transferencias
    WHERE
      id_inventario_transferencia = NEW.id_inventario_transferencia
    LIMIT 1;

    SET ia_id_producto = (SELECT id_producto FROM paal_inventario_transferencia_productos WHERE id_inventario_transferencia_producto = NEW.id_inventario_transferencia_producto LIMIT 1);

    IF (NEW.cancelado = 'si' AND OLD.cancelado = 'no') THEN
        IF (NEW.completado = 'no') THEN
            UPDATE paal_producto_numeros_serie SET
                status = 'disponible'
            WHERE
                id_producto  = ia_id_producto  AND
                numero_serie = NEW.numero_serie AND
                id_sucursal  = ia_id_sucursal_origen;
        END IF;

        IF (NEW.completado = 'si' AND NEW.recibido = 'si') THEN
            UPDATE paal_producto_numeros_serie SET
                id_sucursal = ia_id_sucursal_origen,
                status = 'disponible'
            WHERE
                id_producto  = ia_id_producto  AND
                numero_serie = NEW.numero_serie AND
                id_sucursal  = ia_id_sucursal_destino;
        END IF;
    END IF;
END$$

DELIMITER ;


DELIMITER $$

CREATE TRIGGER `tg_inv_transf_cancelar_prod_y_nums_canc` AFTER UPDATE ON `paal_inventario_transferencias`
 FOR EACH ROW
 BEGIN
    -- tg_inventario_transferencia_cancelar_productos_y_numeros_serie_al_cancelar
    -- Marca como cancelados los productos y números de serie asociados a la transferencia al cancelarla

    IF (NEW.status != OLD.status AND NEW.status = 'cancelado') THEN
        UPDATE paal_inventario_transferencia_productos SET
            cancelado = 'si'
        WHERE
            id_inventario_transferencia = OLD.id_inventario_transferencia;

        UPDATE paal_inventario_transferencia_producto_numeros_serie SET
            cancelado = 'si'
        WHERE
            id_inventario_transferencia = OLD.id_inventario_transferencia;
    END IF;
END$$

DELIMITER ;

DELIMITER $$

CREATE TRIGGER `tg_inv_transf_completar_prod_y_nums_canc` AFTER UPDATE ON `paal_inventario_transferencias`
 FOR EACH ROW
 BEGIN
    -- tg_inventario_transferencia_completar_productos_y_numeros_serie_al_cancelar
    -- Marca como completados los productos y números de serie asociados a la transferencia al completarla

    IF (NEW.status != OLD.status AND (NEW.status = 'procesado-correctamente' OR NEW.status = 'procesado-con-diferencias')) THEN
        UPDATE paal_inventario_transferencia_productos SET
            completado = 'si'
        WHERE
            id_inventario_transferencia = OLD.id_inventario_transferencia;

        UPDATE paal_inventario_transferencia_producto_numeros_serie SET
            completado = 'si'
        WHERE
            id_inventario_transferencia = OLD.id_inventario_transferencia;
    END IF;
END$$

DELIMITER ;