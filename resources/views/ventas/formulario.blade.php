<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 0; }

        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            margin: 0;
            padding: 0;
            position: relative;
        }

        /* Convierte las posiciones FPDF a mm aproximadas */
        .marca { position: absolute; left: 60mm; top: 40mm;}
        .tipo { position: absolute; left: 55mm; top: 46mm;}
        .modelo { position: absolute; left: 65mm; top: 52mm;}
        .marca_motor { position: absolute; left: 75mm; top: 58mm;}
        .nro_motor { position: absolute; left: 70mm; top: 64mm;}
        .marca_cuadro { position: absolute; left: 80mm; top: 70mm;}
        .nro_cuadro { position: absolute; left: 72mm; top: 76mm;}
        .observaciones { position: absolute; left: 50mm; top: 100mm; width: 170mm; }

        .lugar { position: absolute; left: 50mm; top: 178mm;}
        .fecha_dia { position: absolute; left: 102mm; top: 178mm; }
        .fecha_mes { position: absolute; left: 117mm; top: 178mm; }
        .fecha_anio { position: absolute; left: 132mm; top: 178mm; }

        .solicitante_nombre { position: absolute; left: 90mm; top: 201mm;  }
        .solicitante_doc { position: absolute; left: 95mm; top: 207mm; }
        .solicitante_domicilio { position: absolute; left: 65mm; top: 213mm;}
        .solicitante_nro { position: absolute; left: 115mm; top: 213mm; }
        .solicitante_localidad { position: absolute; left: 160mm; top: 213mm;  }

        .sello {
            position: absolute;
            left: 83mm;
            top: 120mm;
            width: 55mm;
            font-size: 7pt;
            color: #7B7B7B;
        }
    </style>

</head>
<body>

<div class="marca">{{ $venta->unidad->producto->marca->nombre }}</div>
<div class="tipo">{{ $venta->unidad->producto->tipoUnidad->nombre }}</div>
<div class="modelo">{{ $venta->unidad->producto->modelo->nombre }}</div>
<div class="marca_motor">{{ $venta->unidad->producto->marca->nombre }}</div>
<div class="nro_motor">{{ $venta->unidad->motor }}</div>
<div class="marca_cuadro">{{ $venta->unidad->producto->marca->nombre }}</div>
<div class="nro_cuadro">{{ $venta->unidad->cuadro }}</div>
<div class="observaciones">{!! $venta->observaciones ?? 'Observaciones?' !!}</div>
<div class="sello">
    He Verificado personalmente la autenticidad de los datos que figuran
    en el presente formulario y me hago personalmente responsable civil y criminalmente
    por los errores u omisiones en que pudiera incurrir sin perjuicio de las que a la empresa
    le correspondan

</div>
<div class="lugar">La Plata</div>

@php
    $fecha = \Carbon\Carbon::parse($venta->fecha);
    $dt_fecha = $fecha->format('d-m-Y');
    $dt = explode('-', $dt_fecha);
@endphp
<div class="fecha_dia">{{ $dt[0] }}</div>
<div class="fecha_mes">{{ $dt[1] }}</div>
<div class="fecha_anio">{{ $dt[2] }}</div>

<div class="solicitante_nombre">{{ $venta->cliente->nombre }}</div>
<div class="solicitante_doc">DNI {{ $venta->cliente->documento }}</div>
<div class="solicitante_domicilio">{{ $venta->cliente->calle }} {{ $venta->cliente->piso }} {{ $venta->cliente->depto }}</div>
<div class="solicitante_nro">{{ $venta->cliente->nro }}</div>
<div class="solicitante_localidad">{{ $venta->cliente->localidad->nombre }}</div>



</body>
</html>
