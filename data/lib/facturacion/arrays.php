<?php
$arrayFormasPagos = array(
  '01' => 'Efectivo',
  '02' => 'Cheque nominativo',
  '03' => 'Transferencia electrónica de fondos',
  '04' => 'Tarjeta de crédito',
  '05' => 'Monederos electrónicos',
  '06' => 'Dinero electrónico',
  '07' => 'Tarjetas digitales',
  '08' => 'Vales de despensa',
  '09' => 'Bienes',
  '10' => 'Servicio',
  '11' => 'Por cuenta de tercero',
  '12' => 'Dación en pago',
  '13' => 'Pago por subrogación',
  '14' => 'Pago por consignación',
  '15' => 'Condonación',
  '16' => 'Cancelación',
  '17' => 'Compensación',
  '23' => 'Novación',
  '24' => 'Confusión',
  '25' => 'Remisión de deuda',
  '26' => 'Preescripción o caducidad',
  '27' => 'A satisfacción del acreedor',
  '28' => 'Tarjeta de débito',
  '29' => 'Tarjeta de servicios',
  '30' => 'Aplicación de anticipos',
  '98' => 'NA',
  '99' => 'Por definir'
);

$arrayMetodosPagos = array('PUE' => 'Pago en una sola exhibición', 'PPD' => 'Pago en parcialidades o diferido');

$arrayUsoCFDI = array(
  'G01' => 'G01 - Adquisición de mercancias',
  'G02' => 'G02 - Devoluciones, descuentos o bonificaciones',
  'G03' => 'G03 - Gastos en general',
  'I01' => 'I01 - Construcciones',
  'I02' => 'I02 - Mobilario y equipo de oficina por inversiones',
  'I03' => 'I03 - Equipo de transporte',
  'I04' => 'I04 - Equipo de computo y accesorios',
  'I05' => 'I05 - Dados, troqueles, moldes, matrices y herramental',
  'I06' => 'I06 - Comunicaciones telefónicas',
  'I07' => 'I07 - Comunicaciones satelitales',
  'I08' => 'I08 - Otra maquinaria y equipo',
  'D01' => 'D01 - Honorarios médicos, dentales y gastos hospitalarios',
  'D02' => 'D02 - Gastos médicos por incapacidad o discapacidad',
  'D03' => 'D03 - Gastos funerales',
  'D04' => 'D04 - Donativos',
  'D05' => 'D05 - Intereses reales efectivamente pagados por créditos hipotecarios(casa habitación)',
  'D06' => 'D06 - Aportaciones voluntarias al SAR',
  'D07' => 'D07 - Primas por seguros de gastos médicos',
  'D08' => 'D08 - Gastos de transportación escolar obligatoria',
  'D09' => 'D09 - Depósitos en cuentas para el  ahorro, primas que tengan como base planes de pensiones',
  'D10' => 'D10 - Pagos por servicios educativos(colegiaturas)',
  'P01' => 'P01 - Por definir'
);

$arrayObjetoImpuesto = array(
  '01' => 'No objeto de impuesto',
  '02' => 'Sí objeto de impuesto',
  '03' => 'Sí objeto del impuesto y no obligado al desglose'
);

$arrayRelationTypes = [
  "01" => "Nota de crédito de los documentos relacionados",
  "02" => "Nota de débito de los documentos relacionados",
  "03" => "Devolución de mercancía sobre facturas o traslados previos",
  "04" => "Sustitución de los CFDI previos",
  "05" => "Traslados de mercancias facturados previamente",
  "06" => "Factura generada por los traslados previos",
  "07" => "CFDI por aplicación de anticipo"
];
