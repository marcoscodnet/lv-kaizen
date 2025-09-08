<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Orden de Servicio</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        td { border: 1px solid #000; padding: 3px; vertical-align: top; }
        .section-title { background: #d2d2d2; font-weight: bold; font-size: 12px; }
        .obs, .pedido, .diag { min-height: 60px; }
        .firma { font-size: 10px; text-align: center; padding-top: 15px; }
    </style>
</head>
<body>

{{-- Cabecera --}}
<table class="header">
    <tr>
        <td class="bg-gray" style="width: 80%;">KAIZEN</td>
        <td class="bg-dark-gray" style="width: 55%;">ORDEN DE SERVICIO</td>
        <td style="width: 55%;">
            @if ($esHonda)
                <img src="{{ public_path('img/logo_service.jpg') }}" width="100">
            @endif
        </td>
    </tr>
</table>

<table>
    <tr>
        <td class="bg-gray" style="width: 80%;">
            Dirección: {{ $servicio->sucursal->direccion }}
        </td>
        <td class="bg-dark-gray" style="width: 55%;">
            Nº: {{ $servicio->id }}
        </td>
        <td></td>
    </tr>
    <tr>
        <td class="bg-gray">
            CP: {{ $servicio->sucursal->localidad->cp }} - {{ $servicio->sucursal->localidad->nombre }} - {{ $servicio->sucursal->localidad->provincia->nombre }}
        </td>
        <td class="bg-dark-gray"></td>
        <td></td>
    </tr>
    <tr>
        <td class="bg-gray">
            Tel.: {{ $servicio->sucursal->telefono }}
        </td>
        <td colspan="2"></td>
    </tr>
    <tr>
        <td class="bg-gray">
            E-mail: {{ $servicio->sucursal->email }}
        </td>
        <td colspan="2" class="bg-dark-gray">
            Datos del recepcionista: {{ $servicio->user->name }}
        </td>
    </tr>
</table>

<br>

<table>
    <tr>
        <td class="section-title" style="width: 110px;">DATOS DEL CLIENTE</td>
        <td class="section-title" style="width: 80px;">DATOS DEL VEHÍCULO</td>
    </tr>
    <tr>
        <td>
            Nombre y apellido: {{ $servicio->cliente->nombre }}
        </td>
        <td>
            Fecha de venta: {{ date('d/m/Y', strtotime($servicio->venta)) }}
        </td>
    </tr>
    <tr>
        <td>
            Dirección: {{ $servicio->cliente->calle }} {{ $servicio->cliente->nro }} {{ $servicio->cliente->depto }} {{ $servicio->cliente->piso }}
            <br>
            Localidad: {{ $servicio->cliente->localidad->nombre }}
            <br>
            C.P.: {{ $servicio->cliente->cp }}
            <br>
            E-mail: {{ $servicio->cliente->email }}
            <br>
            Teléfono: ({{ $servicio->cliente->particular_area }}) {{ $servicio->cliente->particular }} / ({{ $servicio->cliente->celular_area }}) {{ $servicio->cliente->celular }}
        </td>
        <td>
            Modelo y Año: {{ $servicio->modelo }} - {{ $servicio->year }}
            <br>
            Nº de chasis: {{ $servicio->chasis }}
            <br>
            Nº de motor: {{ $servicio->motor }}
        </td>
    </tr>
</table>

{{-- Estado general --}}
<table>
    <tr>
        <td style="width: 60%;" class="section-title">ESTADO GENERAL DEL VEHÍCULO</td>
        <td style="width: 40%;">Compromiso de entrega: {{ date('d/m/Y', strtotime($servicio->entrega)) }}</td>
    </tr>
    <tr>
        <td>KILOMETRAJE / HORAS: {{ $servicio->kilometros }}</td>
        <td></td>
    </tr>
    <tr>
        <td colspan="2" class="obs">
            {{-- Imagen del estado --}}
            <img src="{{ public_path('img/orden-st-motos.jpg') }}" style="width:100%;">
        </td>
    </tr>
</table>

{{-- Observaciones --}}
<table>
    <tr>
        <td class="section-title">OBSERVACIONES</td>
    </tr>
    <tr>
        <td class="obs">{{ $servicio->observacion }}</td>
    </tr>
</table>

{{-- Pedido del cliente --}}
<table>
    <tr>
        <td style="width: 70%;" class="section-title">DESCRIPCIÓN DEL PEDIDO DEL CLIENTE</td>
        <td style="width: 30%;">SERVICIO: {{ $servicio->tipoServicio->nombre }}</td>
    </tr>
    <tr>
        <td colspan="2" class="pedido">{{ $servicio->descripcion }}</td>
    </tr>
</table>

{{-- Diagnóstico y reparación --}}
<table>
    <tr>
        <td class="section-title">DIAGNÓSTICO Y REPARACIÓN REALIZADA</td>
    </tr>
    <tr>
        <td class="diag">{{ $servicio->diagnostico }}</td>
    </tr>
</table>

{{-- Repuestos y mecánicos --}}
<table>
    <tr>
        <td style="width: 50%;" class="section-title">REPUESTOS UTILIZADOS</td>
        <td style="width: 50%;" class="section-title">MECÁNICOS</td>
    </tr>
    <tr>
        <td>{{ $servicio->repuestos }}</td>
        <td>{{ $servicio->mecanicos }}</td>
    </tr>
    <tr>
        <td colspan="2">
            <b>INSTRUMENTOS DE MEDICIÓN UTILIZADOS:</b><br>
            {{ $servicio->instrumentos }}
            <br><b>TIEMPO MANO DE OBRA:</b> {{ $servicio->tiempo }}
        </td>
    </tr>
</table>

{{-- Firmas --}}
<table>
    <tr>
        <td class="firma">FIRMA CLIENTE Y ACLARACIÓN</td>
        <td class="firma">FIRMA RECEPCIONISTA</td>
        <td class="firma">FIRMA TÉCNICO Y LEGAJO</td>
    </tr>
</table>

</body>
</html>
