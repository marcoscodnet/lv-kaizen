<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Orden de Servicio</title>
    <style>
        @page { margin: 7mm 8mm; }
        body { font-family: Arial, sans-serif; font-size: 8px; margin: 0; }
        table { width: 100%; border-collapse: collapse; }
        td, th { border: 1px solid #000; padding: 1px 3px; vertical-align: top; }
        .bg-gray { background: #e6e6e6; }
        .bg-dark-gray { background: #d2d2d2; }
        .section-title { font-weight: bold; font-size: 8.5px; background: #d2d2d2; }
        .b { font-weight: bold; }
        .center { text-align: center; }
        .nb-r { border-right: none; }
        .nb-l { border-left: none; }
        .nb-t { border-top: none; }
        .nb-b { border-bottom: none; }
        .obs { height: 28px; }
        .pedido { height: 40px; }
        .legal { font-size: 6.5px; padding-right: 10px; }
        .legal-honda { font-size: 5.3px; line-height: 1.15; padding-right: 14px; }
        .sep { height: 3px; border: none; padding: 0; }

        /* Inspección de 24 ítems */
        .insp td, .insp th { padding: 0 3px; height: 10px; vertical-align: middle; }
        .insp th { font-size: 6.5px; line-height: 1; }
        .insp .chk { width: 34px; text-align: center; }
        .insp th.ok { background: #00b050; color: #000; }
        .insp th.ns { background: #ffff00; color: #000; }
        .insp th.ci { background: #ff0000; color: #000; }
        .insp td.ok { background: #e2f5e9; }
        .insp td.ns { background: #fffbd6; }
        .insp td.ci { background: #fde2e2; }
        .box { display: inline-block; width: 8px; height: 6px; border: 1px solid #000; background: #fff; }

        /* Firmas */
        .firmas td { border: none; text-align: center; padding: 0 3px; }
        .firmas td.linea { border-bottom: 1px solid #000; height: 14px; font-size: 8px; vertical-align: bottom; }
        .rotulo { font-size: 6.5px; }
    </style>
</head>
<body>

@php
    $tel = function ($area, $numero) {
        return trim($numero) === '' ? '-' : trim((trim($area) !== '' ? '(' . trim($area) . ') ' : '') . $numero);
    };
    // El modelo se guarda por modelo_id; la columna de texto "modelo" quedó de la versión vieja
    $modeloNombre = trim(optional($servicio->marca)->nombre . ' ' . ($servicio->modelo()->value('nombre') ?: $servicio->getAttribute('modelo')));
    $fecha = function ($valor, $formato = 'd/m/Y') {
        return $valor ? date($formato, strtotime($valor)) : '-';
    };
    $itemsIzq = [
        'Luces tablero (iluminación, neutro, etc.)',
        'Bocina',
        'Giros',
        'Puños',
        'Luz baja / alta',
        'Palanca de embrague (si aplica)',
        'Cable de embrague',
        'Palanca de freno',
        'Cable de freno',
        'Luz de freno &nbsp;&nbsp; Palanca: <span class="box"></span> &nbsp; Pedal: <span class="box"></span>',
        'Cable de acelerador',
        'Espejos retrovisores',
    ];
    $itemsDer = [
        'Amortiguadores delanteros',
        'Cinta o pastilla freno delantero',
        'Disco/s freno delantero',
        'Neumático delantero',
        'Pedal freno trasero',
        'Amortiguadores traseros',
        'Cinta o pastilla freno trasero',
        'Disco/s freno trasero',
        'Neumático trasero',
        'Sistema de transmisión',
        'Pedal de cambios',
        'Sostén lateral / Caballete / Pedalines',
    ];
@endphp

{{-- Cabecera --}}
<table>
    <tr>
        <td class="bg-gray" style="width: 55%">
            <span style="font-size: 13px; font-weight: bold">KAIZEN</span>
            @if($servicio->sucursal) <span style="font-size: 8px"> - Sucursal {{ $servicio->sucursal->nombre }}</span> @endif<br>
            Dirección: {{ optional($servicio->sucursal)->direccion }}<br>
            C.P.: {{ optional(optional($servicio->sucursal)->localidad)->cp }} - {{ optional(optional($servicio->sucursal)->localidad)->nombre }} - {{ optional(optional(optional($servicio->sucursal)->localidad)->provincia)->nombre }}<br>
            Tel.: {{ optional($servicio->sucursal)->telefono }} &nbsp;&nbsp; E-mail: {{ optional($servicio->sucursal)->email }}
        </td>
        <td class="bg-dark-gray nb-r" style="width: 25%">
            <span class="b" style="font-size: 11px; text-decoration: underline">ORDEN DE SERVICIO</span><br>
            <span class="b" style="font-size: 11px">Nº {{ str_pad($servicio->id, 6, '0', STR_PAD_LEFT) }}</span><br>
            <span style="font-size: 7px">R270 Rev.03</span>
        </td>
        <td class="bg-dark-gray nb-l" style="width: 20%; text-align: right; vertical-align: middle">
            @if ($esHonda)
                <img src="{{ public_path('/images/logo_service.jpg') }}" height="38">
            @endif
        </td>
    </tr>
    <tr>
        <td colspan="3" class="bg-gray b">Datos del recepcionista: {{ optional($servicio->user)->name }}</td>
    </tr>
</table>

<table style="margin-top: 3px">
    <tr>
        <td class="section-title" style="width: 64%" colspan="2">DATOS DEL CLIENTE</td>
        <td class="section-title">DATOS DEL VEHÍCULO</td>
    </tr>
    <tr>
        <td class="bg-gray nb-r" style="width: 36%">Nombre y apellido: {{ $servicio->cliente->nombre }}</td>
        <td class="bg-gray nb-l">Documento: {{ $servicio->cliente->documento }}</td>
        <td class="bg-gray">Patente: {{ $patente ?: '-' }}</td>
    </tr>
    <tr>
        <td class="bg-gray nb-r">Dirección: {{ $servicio->cliente->calle }} {{ $servicio->cliente->nro }} {{ $servicio->cliente->piso }} {{ $servicio->cliente->depto }}</td>
        <td class="bg-gray nb-l">Localidad: {{ optional($servicio->cliente->localidad)->nombre }} &nbsp; C.P.: {{ $servicio->cliente->cp }}</td>
        <td class="bg-gray">Modelo y año: {{ $modeloNombre ?: '-' }} - {{ $servicio->year }}</td>
    </tr>
    <tr>
        <td class="bg-gray nb-r">E-mail: {{ $servicio->cliente->email }}</td>
        <td class="bg-gray nb-l">Celular: {{ $tel($servicio->cliente->celular_area, $servicio->cliente->celular) }}</td>
        <td class="bg-gray">Nº de chasis: {{ $servicio->chasis }}</td>
    </tr>
    <tr>
        <td class="bg-gray nb-r">Teléfono: {{ $tel($servicio->cliente->particular_area, $servicio->cliente->particular) }}</td>
        <td class="bg-gray nb-l"></td>
        <td class="bg-gray">Nº de motor: {{ $servicio->motor }}</td>
    </tr>
    <tr>
        <td colspan="2" class="section-title">ESTADO GENERAL DEL VEHÍCULO &nbsp;&nbsp;&nbsp; <span style="font-weight: normal">Kilometraje / Horas:</span> {{ $servicio->kilometros }}</td>
        <td class="bg-gray">Fecha de venta: {{ $fecha($servicio->venta) }}</td>
    </tr>
    <tr>
        <td colspan="3" style="padding: 2px">
            <img src="{{ public_path('/images/orden-st-estado-r270.jpg') }}" style="width: 100%">
        </td>
    </tr>
    <tr>
        <td colspan="3" class="b nb-b">OBSERVACIONES:</td>
    </tr>
    <tr>
        <td colspan="3" class="obs nb-t">{!! nl2br(e($servicio->observacion)) !!}</td>
    </tr>
</table>

{{-- Inspección de 24 ítems --}}
<table class="insp" style="margin-top: 3px">
    <tr>
        <th colspan="8" class="section-title center">INSPECCIÓN DE 24 ÍTEMS (CON LA PRESENCIA DEL CLIENTE)</th>
    </tr>
    <tr>
        <th style="width: 25%"></th>
        <th class="chk ok">OK</th>
        <th class="chk ns">Necesita servicio</th>
        <th class="chk ci">Cambio inmediato</th>
        <th style="width: 25%"></th>
        <th class="chk ok">OK</th>
        <th class="chk ns">Necesita servicio</th>
        <th class="chk ci">Cambio inmediato</th>
    </tr>
    @foreach($itemsIzq as $i => $item)
        <tr>
            <td>{!! $item !!}</td>
            <td class="chk ok"><span class="box"></span></td>
            <td class="chk ns"><span class="box"></span></td>
            <td class="chk ci"><span class="box"></span></td>
            <td>{{ $itemsDer[$i] }}</td>
            <td class="chk ok"><span class="box"></span></td>
            <td class="chk ns"><span class="box"></span></td>
            <td class="chk ci"><span class="box"></span></td>
        </tr>
    @endforeach
</table>

{{-- Recepción --}}
<table style="margin-top: 3px">
    <tr>
        <td class="section-title" style="width: 45%">DESCRIPCIÓN DEL PEDIDO DEL CLIENTE</td>
        <td class="section-title" style="width: 27%">SERVICIO: {{ optional($servicio->tipoServicio)->nombre }}</td>
        <td class="section-title">FECHA DE COMPROMISO DE ENTREGA: {{ $fecha($servicio->entrega) }}</td>
    </tr>
    <tr>
        <td colspan="3" class="pedido">{!! nl2br(e($servicio->descripcion)) !!}</td>
    </tr>
    <tr>
        <td colspan="3" class="legal nb-b">Me declaro en conocimiento de la condición en la que se encuentra la unidad, afirmando que los daños en la carrocería detectados en el momento de la recepción son los indicados en la figura. Autorizo a realizar todos los trabajos descriptos a mi exclusiva cuenta y cargo, y a efectuar todas las pruebas necesarias (incluidas en ruta) de la unidad.<br>
            <span class="b">VEHÍCULO INGRESADO EL:</span>
        </td>
    </tr>
    <tr>
        <td colspan="3" class="nb-t">
            <table class="firmas">
                <tr>
                    <td class="linea" style="width: 22%">{{ $fecha($servicio->ingreso, 'd/m/Y H:i') }}</td>
                    <td style="width: 4%"></td>
                    <td class="linea" style="width: 35%"></td>
                    <td style="width: 4%"></td>
                    <td class="linea" style="width: 35%"></td>
                </tr>
                <tr>
                    <td class="rotulo">FECHA</td><td></td>
                    <td class="rotulo">FIRMA Y ACLARACIÓN DEL CLIENTE</td><td></td>
                    <td class="rotulo">FIRMA Y ACLARACIÓN DEL RECEPCIONISTA</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

{{-- Taller --}}
<table style="margin-top: 3px">
    <tr>
        <td colspan="2" class="section-title">DIAGNÓSTICO Y REPARACIÓN REALIZADA</td>
    </tr>
    <tr>
        <td colspan="2" class="pedido">{!! nl2br(e($servicio->diagnostico)) !!}</td>
    </tr>
    <tr>
        <td style="width: 60%"><span class="b">MECÁNICOS:</span> {{ $servicio->mecanicos }}</td>
        <td><span class="b">TIEMPO MANO DE OBRA:</span> {{ $servicio->tiempo }}</td>
    </tr>
    <tr>
        <td class="section-title">REPUESTOS UTILIZADOS</td>
        <td class="section-title">INSTRUMENTOS DE MEDICIÓN UTILIZADOS</td>
    </tr>
    <tr>
        <td class="obs">{!! nl2br(e($servicio->repuestos)) !!}</td>
        <td class="obs">{!! nl2br(e($servicio->instrumentos)) !!}</td>
    </tr>
    <tr>
        <td colspan="2" class="legal nb-b">Dejo expresa constancia que luego de haber sido probada, retiro la unidad antes mencionada con los trabajos de reparación realizados, declarando conocer y aceptar el estado en el que se encuentra la misma. Si los trabajos señalados no fueron aceptados, el cliente retira el producto bajo su exclusiva responsabilidad, liberando al concesionario de las posibles consecuencias de su utilización sin las reparaciones recomendadas. La unidad será retirada por el titular. En caso de no poder asistir, el responsable deberá acreditar la titularidad de la misma (fotocopia de DNI).<br>
            <span class="b">VEHÍCULO RETIRADO EL:</span>
        </td>
    </tr>
    <tr>
        <td colspan="2" class="nb-t">
            <table class="firmas">
                <tr>
                    <td class="linea" style="width: 22%"></td>
                    <td style="width: 4%"></td>
                    <td class="linea" style="width: 35%"></td>
                    <td style="width: 4%"></td>
                    <td class="linea" style="width: 35%"></td>
                </tr>
                <tr>
                    <td class="rotulo">FECHA</td><td></td>
                    <td class="rotulo">FIRMA Y ACLARACIÓN DEL CLIENTE</td><td></td>
                    <td class="rotulo">FIRMA DEL TÉCNICO Y LEGAJO</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

@if ($esHonda)
{{-- Consentimiento de datos personales exigido por Honda (R270 Rev.03) --}}
<table style="margin-top: 3px">
    <tr>
        <td class="legal-honda nb-b">
            Consiento que al momento de adquirir un producto Honda en un concesionario oficial de HONDA MOTOR DE ARGENTINA S.A. (en adelante, “HONDA”) y/o al requerir servicios a un concesionario oficial de HONDA mis datos personales como así también información sobre mi producto Honda podrán ser cedidos y/o compartidos por el concesionario oficial con HONDA para que ésta pueda (i) cumplir las obligaciones que le correspondan de acuerdo con los términos y condiciones de la garantía contractual sobre los productos Honda que fabrica y/o importa; (ii) medir el grado de satisfacción de los clientes con dichos productos y/o con los servicios de su red oficial de concesionarios y/o servicios oficiales; (iii) realizar campañas publicitarias y/o de marketing tendientes a mantenerme actualizado sobre nuevos productos, promociones y/o distintos servicios que ofrezca por sí y/o a través de su red oficial de concesionarios y/o servicios oficiales; (iv) confeccionar estadísticas; (v) comunicar aquella información de seguridad que HONDA estime necesaria para una mejor utilización de los productos que comercializa.
            En este sentido, HONDA podrá ceder y/o compartir los datos que proporcione (tanto personales como del vehículo) con (i) agencias de publicidad para realizar eventos y/o promociones y/o cualquier otra acción de marketing; (ii) empresas que le provean cualquier tipo de servicios informáticos y/o de archivo de información electrónica y/o física; (iii) empresas que operen sus canales de atención al cliente; (iv) cualquier empresa vinculada a HONDA que ofrezca líneas de crédito o financiación para su gama de productos; (v) HONDA MOTOR CO., LTD., sus empresas relacionadas y/o vinculadas; y (vi) la red de Concesionarios y Servicios Oficiales de HONDA.
            Las compañías mencionadas en los puntos i) a v) podrían estar radicadas en países que no proporcionen medidas de seguridad adecuadas de acuerdo con los estándares de la normativa argentina en materia de protección de datos personales. HONDA se compromete a incluir en sus acuerdos con dichas compañías la obligación de respetar la normativa argentina vigente en materia de protección de datos personales.
            He sido informado que (i) tengo la facultad de ejercer el derecho de acceso a mis datos personales en forma gratuita a intervalos no inferiores a seis meses, salvo que se acredite un interés legítimo al efecto conforme lo establecido en el artículo 14, inciso 3 de la Ley 25.326; (ii) que la Agencia de Acceso a la Información Pública es el órgano de Control de la Ley 25.326 y tiene la atribución de atender las denuncias y reclamos que se interpongan con relación al incumplimiento de las normas sobre protección de datos personales; y (iii) podré acceder a mis datos personales a fin de actualizar y/o modificar y/o pedir la supresión de los mencionados datos comunicándome vía correo electrónico a har_dataprivacy@honda.com.ar.
            HONDA MOTOR DE ARGENTINA S.A. (CUIT 30-57754677-7) - Suipacha 1111, piso 18, Ciudad Autónoma de Buenos Aires.
        </td>
    </tr>
    <tr>
        <td class="nb-t">
            <table class="firmas">
                <tr>
                    <td style="width: 39%"></td>
                    <td class="linea" style="width: 22%"></td>
                    <td style="width: 4%"></td>
                    <td class="linea" style="width: 35%"></td>
                </tr>
                <tr>
                    <td></td>
                    <td class="rotulo">FECHA</td><td></td>
                    <td class="rotulo">FIRMA Y ACLARACIÓN DEL CLIENTE</td>
                </tr>
            </table>
        </td>
    </tr>
</table>
@endif

</body>
</html>
