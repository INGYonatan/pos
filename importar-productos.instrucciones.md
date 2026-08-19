# Cosas que faltan para el importador, nuevos campos
* requiere_numero_serie | 1, 0
* numero_serie
* Marca
* Proveedor
* Unidad de medida (SAT)
* Clave del sat

# Para poder hacer el case del importador

# Lo que se quita a parte de lo que es lógico que ya no va.
* parseGoogleSheetCurrency
* categoriesmodel, en este caso no se usan categorías aún

# Lo que cambia
* BotsModel pasa a SucursalesModel (el modelo ya existe)
* AjusteProductosModel a AjusteInventarioProductosModel

# Lo que se creará/nuevo
* TypesModel // Son los tipo de productos, está relacionado al campo tipo del importador
* BrandsModel
* SuppliersModel
* ProductServiceCodesModel
* UnitCodesModel
* InvetarioModel
* ProductosModel
* AjustesInventarioModel
* AjusteInventarioProductosModel

# Ajustes provisionales
* Para los tipos, se buscará por slug, si existe, usar el tipo para la importación, en caso contrario, crear el tipo con la configuración requiere_numero_serie, para tangible, dejar en 1, los demas campos dejarlos talcual, para crear el slug, hay una función llamada createSlug, OJO, hay un campo llamado tipo en productos, ese no es, es el de id_tipo.
* Para las marcas, hacer algo parecido que tipos, como no hay slug en esta tabla, buscar por el nombre, nota: hacer uppercase a los nombres de marcas para hacer la búsqueda y agregarlos asi también en caso de no existir
* Proveedor: Lo mismo que marcas
* Unidad de medida (SAT): para este caso buscar por clave, la clave es el que pondrán en el archivo a importar
* Clave del sat: Lo mismo que unidad de medida

# Campos de tablas en DB
* paal_tipos
	1	id_tipo Primaria	int(11)			No	Ninguna		AUTO_INCREMENT	Cambiar Cambiar	Eliminar Eliminar	
	2	nombre	varchar(100)	utf8mb4_bin		No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
	3	slug Índice	varchar(100)	utf8mb4_bin		No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
	4	requiere_numero_serie	tinyint(1)			No	0			Cambiar Cambiar	Eliminar Eliminar	
	5	tangible	tinyint(1)			No	1			Cambiar Cambiar	Eliminar Eliminar	
	6	es_anticipo	tinyint(1)			No	0			Cambiar Cambiar	Eliminar Eliminar	
	7	es_nota_credito	tinyint(1)			No	0			Cambiar Cambiar	Eliminar Eliminar	

* paal_marcas
	1	id_marca Primaria	int(11)			No	Ninguna		AUTO_INCREMENT	Cambiar Cambiar	Eliminar Eliminar	
	2	marca	varchar(60)	utf8mb4_bin		No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	

* paal_proveedores
	1	id_proveedor Primaria	int(11)			No	Ninguna		AUTO_INCREMENT	Cambiar Cambiar	Eliminar Eliminar	
	2	nombre_proveedor	varchar(100)	utf8mb4_bin		No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
	3	nombre_comercial	varchar(150)	utf8mb4_bin		Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	4	correo	varchar(200)	utf8mb4_bin		Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	5	telefono	varchar(15)	utf8mb4_bin		Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	6	status	enum('activo', 'eliminado')	utf8mb4_bin		Sí	activo			Cambiar Cambiar	Eliminar Eliminar	
	7	fecha_creacion	datetime			Sí	current_timestamp()			Cambiar Cambiar	Eliminar Eliminar

* paal_clave_producto_servicios (Este es para Unidad de medida SAT)
	1	id_clave_producto_servicio Primaria	int(11)			No	Ninguna		AUTO_INCREMENT	Cambiar Cambiar	Eliminar Eliminar	
	2	clave	varchar(8)	utf8mb4_bin		Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	3	descripcion	varchar(147)	utf8mb4_bin		Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	

* paal_clave_unidades
	1	id_clave_unidad Primaria	int(11)			No	Ninguna		AUTO_INCREMENT	Cambiar Cambiar	Eliminar Eliminar	
	2	clave	varchar(3)	utf8mb4_bin		Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	3	nombre	varchar(106)	utf8mb4_bin		Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	

* paal_inventario
	1	id_inventario Primaria	int(11)			No	Ninguna		AUTO_INCREMENT	Cambiar Cambiar	Eliminar Eliminar	
	2	id_sucursal Índice	int(11)			No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
	3	id_producto Índice	int(11)			No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
	4	stock	decimal(22,6)			Sí	NULL			Cambiar Cambiar	Eliminar Eliminar

* paal_productos
	1	id_producto Primaria	int(11)			No	Ninguna		AUTO_INCREMENT	Cambiar Cambiar	Eliminar Eliminar	
	2	id_tipo	int(11)			No	1			Cambiar Cambiar	Eliminar Eliminar	
	3	id_marca	int(11)			No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
	4	id_categoria	int(11)			No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
	5	id_categoria_familia	int(11)			No	0			Cambiar Cambiar	Eliminar Eliminar	
	6	id_proveedor	int(11)			No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
	7	id_clave_unidad	int(11)			No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
	8	id_clave_producto_servicio	int(11)			No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
	9	codigo Índice	varchar(250)	utf8mb4_bin		No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
	10	nombre_producto	varchar(250)	utf8mb4_bin		No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
	11	unidad	enum('Pieza', 'A granel')	utf8mb4_bin		No	Pieza			Cambiar Cambiar	Eliminar Eliminar	
	12	contenido	decimal(22,6)			Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	13	precio_costo_original	decimal(22,6)			Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	14	precio_costo	decimal(22,6)			Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	15	precio_venta_original	decimal(22,6)			Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	16	precio_venta	decimal(22,6)			Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	17	precio_venta2_original	decimal(22,6)			Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	18	precio_venta2	decimal(22,6)			Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	19	precio_venta3_original	decimal(22,6)			Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	20	precio_venta3	decimal(22,6)			Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	21	cantidad_mayoreo	decimal(22,6)			Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	22	precio_mayoreo_original	decimal(22,6)			Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	23	en_dolares	enum('si', 'no')	utf8mb4_bin		No	no			Cambiar Cambiar	Eliminar Eliminar	
	24	tipo	enum('equipo', 'llantas', 'rines', 'refacciones', ...	utf8mb4_bin		No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
	25	aplica_iva	enum('si', 'no')	utf8mb4_bin		No	no			Cambiar Cambiar	Eliminar Eliminar	
	26	aplica_ieps	enum('si', 'no')	utf8mb4_bin		No	no			Cambiar Cambiar	Eliminar Eliminar	
	27	ieps_porcentaje	decimal(6,2)			No	8.00			Cambiar Cambiar	Eliminar Eliminar	
	28	fecha_creacion	datetime			No	current_timestamp()			Cambiar Cambiar	Eliminar Eliminar	
	29	status	enum('activo', 'eliminado')	utf8mb4_bin		No	activo			Cambiar Cambiar	Eliminar Eliminar	
	30	precio_mayoreo	decimal(22,6)			Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	31	unidad_entrada	enum('caja', 'unidad')	utf8mb4_bin		No	unidad			Cambiar Cambiar	Eliminar Eliminar	
	32	unidad_salida	enum('caja', 'unidad')	utf8mb4_bin		No	unidad			Cambiar Cambiar	Eliminar Eliminar	
	33	numero_piezas	int(11)			No	0			Cambiar Cambiar	Eliminar Eliminar	
	34	control_inventario	enum('si', 'no')	utf8mb4_bin		No	si			Cambiar Cambiar	Eliminar Eliminar	

* paal_inventario_ajustes
  1	id_inventario_ajuste Primaria	int(11)			No	Ninguna		AUTO_INCREMENT	Cambiar Cambiar	Eliminar Eliminar	
	2	id_usuario	int(11)			No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
	3	id_sucursal Índice	int(11)			No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
	4	folio	varchar(20)	utf8mb4_bin		No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
	5	observaciones	varchar(250)	utf8mb4_bin		Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	6	status	enum('activo', 'cancelado')	utf8mb4_bin		No	activo			Cambiar Cambiar	Eliminar Eliminar	
	7	tipo	enum('incremento', 'decremento')	utf8mb4_bin		No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
	8	motivo_ajuste	varchar(250)	utf8mb4_bin		Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	9	fecha_creacion	datetime			No	current_timestamp()			Cambiar Cambiar	Eliminar Eliminar	
	10	tipo_ajuste	enum('merma', 'perdida', 'muestra', 'ajuste')	utf8mb4_bin		No	ajuste			Cambiar Cambiar	Eliminar Eliminar	

* paal_inventario_ajuste_productos
  1	id_inventario_ajuste_producto Primaria	int(11)			No	Ninguna		AUTO_INCREMENT	Cambiar Cambiar	Eliminar Eliminar	
	2	id_inventario_ajuste Índice	int(11)			No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
	3	id_producto	int(11)			No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
	4	id_tipo	int(11)			No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
	5	tipo	varchar(100)	utf8mb4_bin		Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	6	cantidad	decimal(22,6)			Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	7	cancelado	enum('no', 'si')	utf8mb4_bin		No	no			Cambiar Cambiar	Eliminar Eliminar	

# Modelos guía
* Hay un modelo recién creado: sucursales.model.php, que es una buena base para definir la estructura general de los modelos a crear, pero está incompleto, no tiene métodos crud, que se necesitarán también, para saber como hacerlo, guíarse del modelo guia, model.example.md, es de otro proyecto, pero se complementa con lo que falta.

# Recomendaciones no obligatorias
* Plantearse un paso a paso para ir mejor