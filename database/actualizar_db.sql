INSERT INTO roles (id, name, guard_name, created_at, updated_at)
SELECT
    p.cd_perfil,
    p.ds_perfil,
    'web',
    NOW(),
    NOW()
FROM perfil p
WHERE NOT EXISTS (
    SELECT 1 FROM roles r WHERE r.id = p.cd_perfil
);

INSERT INTO users (
    id, name, email, password, created_at, updated_at,
    sucursal_id, activo
)
SELECT
    u.cd_usuario,
    u.ds_apynom,
    u.ds_mail,
    SHA2('123456', 256),   -- nueva contraseña para todos
    NOW(),
    NOW(),
    u.cd_sucursal,
    IF(u.bl_activo = 1, 1, 0)
FROM usuario u
WHERE NOT EXISTS (
    SELECT 1 FROM users us WHERE us.id = u.cd_usuario
);

INSERT INTO colors (id, nombre, created_at, updated_at)
SELECT c.cd_color, c.ds_color, NOW(), NOW()
FROM color c
    ON DUPLICATE KEY UPDATE
                         nombre = VALUES(nombre),
                         updated_at = NOW();



INSERT INTO entidads (id, nombre, activa, created_at, updated_at)
SELECT c.cd_entidad, c.ds_entidad, c.bl_activo, NOW(), NOW()
FROM entidad c
    ON DUPLICATE KEY UPDATE
                         nombre = VALUES(nombre),
                         activa = VALUES(activa),
                         updated_at = NOW();

INSERT INTO marcas (id, nombre, created_at, updated_at)
SELECT c.cd_marca, c.ds_marca, NOW(), NOW()
FROM marca c
    ON DUPLICATE KEY UPDATE
                         nombre = VALUES(nombre),
                         updated_at = NOW();


INSERT INTO modelos (id, nombre, marca_id, created_at, updated_at)
SELECT c.cd_modelo, c.ds_modelo, c.cd_marca, NOW(), NOW()
FROM modelo c
    ON DUPLICATE KEY UPDATE
                         nombre = VALUES(nombre),
                         marca_id = VALUES(marca_id),
                         updated_at = NOW();


INSERT INTO sucursals (
    id, nombre, direccion, telefono, email, comentario, localidad_id, created_at, updated_at
)
SELECT
    c.cd_sucursal, c.ds_nombre, c.ds_domicilio, c.ds_telefono, c.ds_email, c.ds_comentario, c.cd_localidad, NOW(), NOW()
FROM sucursal c
    ON DUPLICATE KEY UPDATE
                         nombre = VALUES(nombre),
                         direccion = VALUES(direccion),
                         telefono = VALUES(telefono),
                         email = VALUES(email),
                         comentario = VALUES(comentario),
                         localidad_id = VALUES(localidad_id),
                         updated_at = NOW();

INSERT INTO tipo_servicios (id, nombre, created_at, updated_at)
SELECT c.cd_tipo_servicio, c.ds_tipo_servicio, NOW(), NOW()
FROM tipo_servicio c
    ON DUPLICATE KEY UPDATE
                         nombre = VALUES(nombre),
                         updated_at = NOW();


INSERT INTO tipo_unidads (id, nombre, created_at, updated_at)
SELECT c.cd_tipo_unidad, c.ds_tipo_unidad, NOW(), NOW()
FROM tipo_unidad c
    ON DUPLICATE KEY UPDATE
                         nombre = VALUES(nombre),
                         updated_at = NOW();


INSERT INTO productos (
    id, tipo_unidad_id, marca_id, modelo_id, color_id,
    precio, minimo, discontinuo, created_at, updated_at
)
SELECT
    p.cd_producto,
    p.cd_tipo_unidad,
    p.cd_marca,
    p.cd_modelo,
    p.cd_color,
    p.nu_monto_sugerido,
    p.nu_stock_minimo,
    IF(p.bl_discontinuo = 1, 1, 0),
    NOW(),
    NOW()
FROM producto p
    ON DUPLICATE KEY UPDATE
                         tipo_unidad_id = VALUES(tipo_unidad_id),
                         marca_id = VALUES(marca_id),
                         modelo_id = VALUES(modelo_id),
                         color_id = VALUES(color_id),
                         precio = VALUES(precio),
                         minimo = VALUES(minimo),
                         discontinuo = VALUES(discontinuo),
                         updated_at = NOW();

INSERT INTO unidads (
    id, producto_id, sucursal_id, motor, cuadro, patente, remito,
    year, envio, ingreso, observaciones, created_at, updated_at
)
SELECT
    u.cd_unidad,
    u.cd_producto,
    u.cd_sucursal_actual,
    u.nu_motor,
    u.nu_cuadro,
    u.nu_patente,
    u.nu_remito_ingreso,
    u.nu_aniomodelo,
    u.nu_envio,
    u.dt_ingreso,
    u.ds_observacion,
    NOW(),
    NOW()
FROM unidad u
    ON DUPLICATE KEY UPDATE
                         producto_id = VALUES(producto_id),
                         sucursal_id = VALUES(sucursal_id),
                         motor = VALUES(motor),
                         cuadro = VALUES(cuadro),
                         patente = VALUES(patente),
                         remito = VALUES(remito),
                         year = VALUES(year),
                         envio = VALUES(envio),
                         ingreso = VALUES(ingreso),
                         observaciones = VALUES(observaciones),
                         updated_at = NOW();

INSERT INTO piezas (
    id, codigo, descripcion, stock_minimo, costo, precio_minimo,
    stock_actual, observaciones, created_at, updated_at
)
SELECT
    p.cd_pieza,
    p.ds_codigo,
    p.ds_descripcion,
    p.nu_stock_minimo,
    p.qt_costo,
    p.qt_minimo,
    p.nu_stock_actual,
    p.ds_observacion,
    NOW(),
    NOW()
FROM pieza p
    ON DUPLICATE KEY UPDATE
                         codigo = VALUES(codigo),
                         descripcion = VALUES(descripcion),
                         stock_minimo = VALUES(stock_minimo),
                         costo = VALUES(costo),
                         precio_minimo = VALUES(precio_minimo),
                         stock_actual = VALUES(stock_actual),
                         observaciones = VALUES(observaciones),
                         updated_at = NOW();


INSERT INTO stock_piezas (
    id, pieza_id, sucursal_id, remito, cantidad, costo, precio_minimo,
    proveedor, ingreso, created_at, updated_at
)
SELECT
    s.cd_stockpieza,
    s.cd_pieza,
    s.cd_sucursal,
    s.ds_remito,
    s.nu_cantidad,
    s.qt_costo,
    s.qt_minimo,
    'Honda',        -- asignando valor fijo según la nueva tabla
    s.dt_ingreso,
    NOW(),
    NOW()
FROM stockpieza s
    ON DUPLICATE KEY UPDATE
                         pieza_id = VALUES(pieza_id),
                         sucursal_id = VALUES(sucursal_id),
                         remito = VALUES(remito),
                         cantidad = VALUES(cantidad),
                         costo = VALUES(costo),
                         precio_minimo = VALUES(precio_minimo),
                         proveedor = VALUES(proveedor),
                         ingreso = VALUES(ingreso),
                         updated_at = NOW();


INSERT INTO venta_piezas (
    id, precio, precio_minimo, cliente, documento, telefono, moto,
    sucursal_id, pedido, user_id, fecha, descripcion, destino,
    created_at, updated_at, user_name
)
SELECT
    v.cd_ventapieza,
    v.nu_preciocobrado,
    v.nu_preciomin,
    v.ds_apynomcliente,
    v.nu_docCliente,
    v.ds_telcliente,
    v.ds_motocliente,
    v.cd_sucursal,
    v.nu_pedidoreparacion,
    u.id,                -- ahora apunta al id de la tabla users
    v.dt_ventapieza,
    v.ds_descripcion,
    CASE v.nu_destino
        WHEN 1 THEN 'Salón'
        WHEN 2 THEN 'Sucursal'
        WHEN 3 THEN 'Taller'
        ELSE NULL
        END,
    NOW(),
    NOW(),
    u.name              -- nombre del usuario desde la tabla users
FROM ventapieza v
         LEFT JOIN users u ON u.id = v.cd_usuario
    ON DUPLICATE KEY UPDATE
                         precio = VALUES(precio),
                         precio_minimo = VALUES(precio_minimo),
                         cliente = VALUES(cliente),
                         documento = VALUES(documento),
                         telefono = VALUES(telefono),
                         moto = VALUES(moto),
                         sucursal_id = VALUES(sucursal_id),
                         pedido = VALUES(pedido),
                         user_id = VALUES(user_id),
                         fecha = VALUES(fecha),
                         descripcion = VALUES(descripcion),
                         destino = VALUES(destino),
                         user_name = VALUES(user_name),
                         updated_at = NOW();


INSERT INTO pieza_venta_piezas (
    id, pieza_id, sucursal_id, cantidad, precio, created_at, updated_at, venta_pieza_id
)
SELECT
    v.cd_ventapieza,       -- asignamos cd_ventapieza como id temporal, o generar secuencia si quieres autoincrement
    v.cd_pieza,
    v.cd_sucursal,
    v.nu_cantidadpedida,
    v.qt_montoacobrar,
    NOW(),
    NOW(),
    v.cd_ventapieza        -- venta_pieza_id referenciando a ventapieza
FROM ventapieza_unidad v
    ON DUPLICATE KEY UPDATE
                         pieza_id = VALUES(pieza_id),
                         sucursal_id = VALUES(sucursal_id),
                         cantidad = VALUES(cantidad),
                         precio = VALUES(precio),
                         venta_pieza_id = VALUES(venta_pieza_id),
                         updated_at = NOW();

INSERT INTO movimientos (
    id, sucursal_origen_id, sucursal_destino_id, user_id,
    fecha, observaciones, created_at, updated_at, user_name
)
SELECT
    m.cd_movimiento,
    m.cd_sucursal_origen,
    m.cd_sucursal_destino,
    NULL,  -- user_id siempre NULL
    m.dt_movimiento,
    m.ds_observacion,
    NOW(),
    NOW(),
    u.ds_nomusuario   -- traído desde usuario
FROM movimiento m
         LEFT JOIN usuario u ON u.cd_usuario = m.cd_usuario
    ON DUPLICATE KEY UPDATE
                         sucursal_origen_id = VALUES(sucursal_origen_id),
                         sucursal_destino_id = VALUES(sucursal_destino_id),
                         user_id = VALUES(user_id),
                         fecha = VALUES(fecha),
                         observaciones = VALUES(observaciones),
                         user_name = VALUES(user_name),
                         updated_at = NOW();

INSERT INTO unidad_movimientos (
    unidad_id, movimiento_id, created_at, updated_at
)
SELECT
    um.cd_unidad,
    um.cd_movimiento,
    NOW(),
    NOW()
FROM unidad_movimiento um
WHERE NOT EXISTS (
    SELECT 1
    FROM unidad_movimientos umn
    WHERE umn.unidad_id = um.cd_unidad
      AND umn.movimiento_id = um.cd_movimiento
);




###############################################27/05/2025#################################################
SELECT
    ventapieza.cd_ventapieza AS id,
    ventapieza.nu_preciocobrado AS precio,
    ventapieza.nu_preciomin AS precio_minimo,
    ventapieza.ds_apynomcliente AS cliente,
    ventapieza.nu_docCliente AS documento,
    ventapieza.ds_telcliente AS telefono,
    ventapieza.ds_motocliente AS moto,
    CASE
        WHEN ventapieza.cd_sucursal = '0' THEN NULL
        ELSE ventapieza.cd_sucursal
        END AS sucursal_id,
    ventapieza.nu_pedidoreparacion AS pedido,
    ventapieza.dt_ventapieza AS fecha,
    ventapieza.ds_descripcion AS descripcion,
    CASE ventapieza.nu_destino
        WHEN '1' THEN 'Salón'
        WHEN '2' THEN 'Sucursal'
        WHEN '3' THEN 'Taller'
        END AS destino,
    usuario.ds_nomusuario AS user_name
FROM ventapieza
         LEFT JOIN usuario ON ventapieza.cd_usuario = usuario.cd_usuario;

INSERT INTO clientes (
    id, nombre, documento, cuil, nacimiento, estado_civil, conyuge,
    email, particular, celular, calle, nro, piso, depto,
    localidad_id, cp, nacionalidad, ocupacion, trabajo, iva, llego,
    created_at, updated_at
)
SELECT
    c.cd_cliente,
    c.ds_apynom,
    c.nu_doc,
    c.ds_cuil_cuit,
    c.dt_nacimiento,
    CASE c.cd_estadocivil
        WHEN 1 THEN 'Soltero/a'
        WHEN 2 THEN 'Casado/a'
        WHEN 3 THEN 'Divorciado/a'
        WHEN 4 THEN 'Concubino/a'
        WHEN 5 THEN 'Viudo/a'
        ELSE NULL
        END,
    c.ds_conyuge,
    c.ds_email,
    c.ds_telparticular,
    c.ds_tellaboral,
    c.ds_dircalle,
    c.ds_dirnro,
    c.ds_dirpiso,
    c.ds_dirdepto,
    c.cd_localidad,
    c.ds_cp,
    c.ds_nacionalidad,
    c.ds_actividad_ocupacion,
    c.ds_lugar_trabajo,
    CASE c.cd_condiva
        WHEN 1 THEN 'Responsable Inscripto'
        WHEN 2 THEN 'Responsable No inscripto'
        WHEN 3 THEN 'Excento'
        WHEN 4 THEN 'No Inscripto'
        WHEN 5 THEN 'Monotributista'
        WHEN 6 THEN 'Consumidor Final'
        ELSE NULL
        END,
    CASE c.cd_comollego
        WHEN 1 THEN 'Google'
        WHEN 2 THEN 'Diario'
        WHEN 3 THEN 'Recomendado'
        WHEN 4 THEN 'Radio'
        WHEN 5 THEN 'Ya compró'
        WHEN 6 THEN 'Página Web'
        WHEN 7 THEN 'Ya conocía'
        WHEN 8 THEN 'Mercado Libre'
        ELSE 'Otro'
        END,
    NOW(),
    NOW()
FROM cliente c
WHERE NOT EXISTS (
    SELECT 1
    FROM clientes cl
    WHERE cl.id = c.cd_cliente
);

INSERT INTO ventas (
    id, user_id, user_name, cliente_id, sucursal_id, unidad_id,
    monto, total, fecha, forma, observacion, created_at, updated_at
)
SELECT
    v.cd_venta,
    u.id,                -- user_id desde users
    u.name,              -- user_name desde users
    c.id,                -- cliente_id desde clientes
    v.cd_sucursal,
    v.cd_unidad,
    v.nu_montosugerido,
    v.nu_total,
    v.dt_venta,
    CASE v.cd_formapago
        WHEN 1 THEN 'Contado'
        WHEN 2 THEN 'Crédito'
        ELSE NULL
        END,
    v.ds_observacion,
    NOW(),
    NOW()
FROM venta v
         LEFT JOIN users u ON u.id = v.cd_usuario
         LEFT JOIN clientes c ON c.id = v.cd_cliente
WHERE NOT EXISTS (
    SELECT 1
    FROM ventas ve
    WHERE ve.id = v.cd_venta
);

INSERT INTO pagos (
    id, venta_id, entidad_id, monto, fecha, pagado,
    contadora, detalle, observacion, created_at, updated_at
)
SELECT
    i.cd_itempago,
    i.cd_venta,
    i.cd_entidad,
    i.nu_importe,
    i.dt_pagado,
    i.nu_pagado,
    i.dt_contadora,
    i.ds_detalle,
    i.ds_observacion,
    NOW(),
    NOW()
FROM itempago i
WHERE NOT EXISTS (
    SELECT 1
    FROM pagos p
    WHERE p.id = i.cd_itempago
);




########################################### crear una pieza para apuntar los stock piezas que se quedan sin relacion########################################
    INSERT INTO piezas (codigo, descripcion)
VALUES ('PIEZA ELIMINADA', 'Pieza asociada a registros de stock inconsistentes');

####OJO!!!! ver el id creado #################
UPDATE stock_piezas
SET pieza_id = 5732
WHERE pieza_id NOT IN (SELECT id FROM piezas);

UPDATE pieza_venta_piezas
SET pieza_id = 5732
WHERE pieza_id NOT IN (SELECT id FROM piezas);


SELECT `cd_ventapieza` as venta_pieza_id, `cd_pieza` as pieza_id,`cd_sucursal` as sucursal_id,`nu_cantidadpedida` as cantidad, `qt_montoacobrar` as precio
FROM `ventapieza_unidad` WHERE 1

###############################################25/08/2025 me traigo las tablas enteras para sinitizarlas#################################################
cliente hasta 18728
venta hasta 20473
servicio hasta 21234

    🔹 Paso 1: Identificar el cliente principal por nu_doc

Dejamos como “principal” el de menor cd_cliente:

CREATE TEMPORARY TABLE clientes_principales AS
SELECT nu_doc, MIN(cd_cliente) AS cliente_principal
FROM cliente
WHERE nu_doc IS NOT NULL AND nu_doc <> ''
GROUP BY nu_doc
HAVING COUNT(*) > 1;

🔹 Paso 2: Actualizar relaciones en venta

Migramos todas las ventas que apuntaban a duplicados hacia el cliente principal:

UPDATE venta v
    JOIN cliente c ON v.cd_cliente = c.cd_cliente
    JOIN clientes_principales cp
    ON c.nu_doc = cp.nu_doc
    AND c.cd_cliente <> cp.cliente_principal
    SET v.cd_cliente = cp.cliente_principal;

🔹 Paso 3: Actualizar relaciones en servicio

Hacemos lo mismo para los servicios:

UPDATE servicio s
    JOIN cliente c ON s.cd_cliente = c.cd_cliente
    JOIN clientes_principales cp
    ON c.nu_doc = cp.nu_doc
    AND c.cd_cliente <> cp.cliente_principal
    SET s.cd_cliente = cp.cliente_principal;

🔹 Paso 4: Eliminar los clientes duplicados

Una vez actualizadas las relaciones, eliminamos los clientes sobrantes:

DELETE c
FROM cliente c
JOIN clientes_principales cp
     ON c.nu_doc = cp.nu_doc
     AND c.cd_cliente <> cp.cliente_principal;

🔹 Paso 5: Verificación

Podés chequear si quedó todo correcto con:

SELECT nu_doc, COUNT(*) AS cantidad
FROM cliente
GROUP BY nu_doc
HAVING COUNT(*) > 1;


Debería devolverte cero filas ✅




###############################################26/08/2025#################################################

ALTER TABLE `clientes`
    CHANGE COLUMN `iva` `iva` ENUM('Responsable Inscripto','Responsable No inscripto','Excento','No Inscripto','Monotributista','Consumidor Final') NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci' AFTER `trabajo`;



INSERT INTO clientes (
    id,
    nombre,
    documento,
    cuil,
    nacimiento,
    estado_civil,
    conyuge,
    email,
    particular_area,
    particular,
    celular_area,
    celular,
    calle,
    nro,
    piso,
    depto,
    localidad_id,
    cp,
    nacionalidad,
    ocupacion,
    trabajo,
    iva,
    llego,
    created_at,
    updated_at
)
SELECT
    cd_cliente AS id,
    ds_apynom AS nombre,
    nu_doc AS documento,
    CASE
        WHEN LENGTH(REGEXP_REPLACE(ds_cuil_cuit, '[^0-9]', '')) = 11
            THEN CONCAT(
            SUBSTRING(REGEXP_REPLACE(ds_cuil_cuit, '[^0-9]', ''),1,2), '-',
            SUBSTRING(REGEXP_REPLACE(ds_cuil_cuit, '[^0-9]', ''),3,8), '-',
            SUBSTRING(REGEXP_REPLACE(ds_cuil_cuit, '[^0-9]', ''),11,1)
                 )
        ELSE ds_cuil_cuit
        END AS cuil,
    dt_nacimiento AS nacimiento,

    CASE cd_estadocivil
        WHEN 1 THEN 'Viudo/a'
        WHEN 2 THEN 'Casado/a'
        WHEN 3 THEN 'Soltero/a'
        WHEN 4 THEN 'Divorciado/a'
        WHEN 5 THEN 'Concubino/a'
        ELSE NULL
        END AS estado_civil,
    ds_conyuge AS email,
    ds_email AS email,

    CASE
        WHEN ds_telparticular LIKE '%-%'
            THEN SUBSTRING_INDEX(REGEXP_REPLACE(ds_telparticular, '[^0-9-]', ''), '-', 1)
        WHEN LENGTH(num_solo_particular) > 7
            THEN LEFT(num_solo_particular, LENGTH(num_solo_particular) - 7)
    ELSE NULL
END AS particular_area,
    CASE
        WHEN ds_telparticular LIKE '%-%'
            THEN SUBSTRING_INDEX(REGEXP_REPLACE(ds_telparticular, '[^0-9-]', ''), '-', -1)
        WHEN LENGTH(num_solo_particular) > 7
            THEN RIGHT(num_solo_particular, 7)
        ELSE num_solo_particular
END AS particular,

    CASE
        WHEN ds_tellaboral LIKE '%-%'
            THEN SUBSTRING_INDEX(REGEXP_REPLACE(ds_tellaboral, '[^0-9-]', ''), '-', 1)
        WHEN LENGTH(num_solo_laboral) > 7
            THEN LEFT(num_solo_laboral, LENGTH(num_solo_laboral) - 7)
        ELSE NULL
END AS celular_area,
    CASE
        WHEN ds_tellaboral LIKE '%-%'
            THEN SUBSTRING_INDEX(REGEXP_REPLACE(ds_tellaboral, '[^0-9-]', ''), '-', -1)
        WHEN LENGTH(num_solo_laboral) > 7
            THEN RIGHT(num_solo_laboral, 7)
        ELSE num_solo_laboral
END AS celular,

    ds_dircalle AS calle,
    ds_dirnro AS nro,
    ds_dirpiso AS piso,
    ds_dirdepto AS depto,

    -- Control de localidad_id para FK
    CASE
        WHEN cd_localidad = 0 THEN NULL
        ELSE cd_localidad
END AS localidad_id,

    ds_cp AS cp,
    ds_nacionalidad AS nacionalidad,
    ds_actividad_ocupacion AS ocupacion,
    ds_lugar_trabajo AS trabajo,

    CASE cd_condiva
        WHEN 1 THEN 'Responsable Inscripto'
        WHEN 2 THEN 'Responsable No inscripto'
        WHEN 3 THEN 'Excento'
        WHEN 4 THEN 'No Inscripto'
        WHEN 5 THEN 'Monotributista'
        WHEN 6 THEN 'Consumidor Final'
        ELSE NULL
END AS iva,

    CASE cd_comollego
        WHEN 1 THEN 'Google'
        WHEN 2 THEN 'Diario'
        WHEN 3 THEN 'Recomendado'
        WHEN 4 THEN 'Radio'
        WHEN 5 THEN 'Ya compró'
        WHEN 6 THEN 'Página Web'
        WHEN 7 THEN 'Ya conocía'
        WHEN 8 THEN 'Mercado Libre'
        WHEN 9 THEN 'Otro'
        ELSE NULL
END AS llego,

    NOW() AS created_at,
    NOW() AS updated_at

FROM (
    SELECT
        cliente.*,
        REGEXP_REPLACE(ds_telparticular, '[^0-9]', '') AS num_solo_particular,
        REGEXP_REPLACE(ds_tellaboral, '[^0-9]', '') AS num_solo_laboral
    FROM cliente
) AS cliente;


################################# 27/08/2025 ########################################################
ALTER TABLE `clientes`
    ADD COLUMN `conyuge` VARCHAR(255) NULL DEFAULT NULL AFTER `estado_civil`;

###############################################29/08/2025#################################################
INSERT INTO ventas (
    id,
    total,
    user_name,
    sucursal_id,
    cliente_id,
    fecha,
    unidad_id,
    monto,
    forma,
    observacion,
    created_at,
    updated_at
)
SELECT `cd_venta` as id, `nu_total` as total, usuario.ds_nomusuario as user_name, venta.`cd_sucursal` as sucursal_id, `cd_cliente` as cliente_id, `dt_venta` as fecha,
       `cd_unidad` as unidad_id,`nu_montosugerido` as monto,
       CASE `cd_formapago`
         WHEN '1' THEN 'Contado'
        WHEN '2' THEN 'Crédito'
    END AS forma, `ds_observacion` as observacion, NOW() AS created_at,
       NOW() AS updated_at
FROM `venta`
         LEFT JOIN usuario on venta.cd_usuario = usuario.cd_usuario
WHERE 1



SELECT `cd_itempago` id, `cd_venta` as venta_id, `cd_entidad` as entidad_id, `nu_importe` as monto, `dt_pagado` as fecha, `nu_pagado` as pagado, `ds_detalle` as detalle,
       `dt_contadora` as contadora, `ds_observacion` as observacion
FROM `itempago` WHERE 1

SELECT `cd_autorizacion` as id, usuario.ds_nomusuario as user_name, `dt_autorizacion` as fecha,
       `cd_unidad` as unidad_id, NOW() AS created_at,
       NOW() AS updated_at
FROM `autorizacion`
         LEFT JOIN usuario on autorizacion.cd_usuario = usuario.cd_usuario
WHERE 1
