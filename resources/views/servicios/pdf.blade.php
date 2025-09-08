<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Orden de Servicio</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; }
        .header { width: 100%; margin-bottom: 10px; }
        .header td { padding: 4px; }
        .bg-gray { background: #e6e6e6; }
        .bg-dark-gray { background: #d2d2d2; }
        .title { font-weight: bold; font-size: 14px; }
        .section-title { font-weight: bold; font-size: 12px; background: #e6e6e6; padding: 4px; }
        table { width: 100%; border-collapse: collapse; }
        td { border: 1px solid #000; padding: 3px; vertical-align: top; }
        /* saca el borde izquierdo del 2do td */
        td.no-right-border {
            border-right: none;
        }
        td.no-left-border {
            border-left: none;
        }
        td.no-top-border {
            border-top: none;
        }
        td.no-bottom-border {
            border-bottom: none;
        }
        /*table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }*/

        /*.section-title { background: #d2d2d2; font-weight: bold; font-size: 12px; }*/
        .obs, .pedido, .diag { min-height: 60px; }
        .firma { font-size: 10px; text-align: center; padding-top: 15px; }
        .linea-fecha {
            display: inline-block;
            border-bottom: 1px solid #000;
            width: 150px;   /* línea corta para fecha */
            height: 20px;
        }

        .linea-firma {
            display: inline-block;
            border-bottom: 1px solid #000;
            width: 200px;   /* línea más larga para firmas */
            height: 20px;
        }
    </style>
</head>
<body>

{{-- Cabecera --}}
<table class="header">
    <tr>
        <td class="bg-gray" style="width: 40%"><span style="font-size: 16px;font-weight: bold">KAIZEN</span><br>
            Dirección: {{ $servicio->sucursal->direccion }}<br>
            CP: {{ $servicio->sucursal->localidad->cp }} - {{ $servicio->sucursal->localidad->nombre }} - {{ $servicio->sucursal->localidad->provincia->nombre }}<br>
            Tel.: {{ $servicio->sucursal->telefono }}<br>
            E-mail: {{ $servicio->sucursal->email }}
        </td>
        <td>
            <table>
                <tr>
                    <td class="bg-dark-gray"><span style="text-decoration: underline">ORDEN DE SERVICIO</span><br><br>
                        Nº: {{ $servicio->id }}

                    </td>
                    <td style="text-align: right">
                        @if ($esHonda)
                            <img src="{{ public_path('/images/logo_service.jpg') }}" width="100">
                        @endif
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="bg-gray" style="font-weight: bold"">
                        Datos del recepcionista: {{ $servicio->user->name }}
                    </td>
                </tr>
            </table>
        </td>

    </tr>
</table>
<table>
    <tr>
        <td class="section-title" style="width: 60%" colspan="2">DATOS DEL CLIENTE</td>
        <td class="section-title">DATOS DEL VEHÍCULO</td>
    </tr>
    <tr>
        <td class="bg-gray" colspan="2">
            Nombre y apellido: {{ $servicio->cliente->nombre }}
        </td>
        <td class="bg-gray">
            Fecha de venta: {{ $servicio->venta ?? date('d/m/Y', strtotime($servicio->venta)) }}
        </td>
    </tr>
    <tr>
        <td class="bg-gray no-right-border">
            Dirección: {{ $servicio->cliente->calle }} {{ $servicio->cliente->nro }} {{ $servicio->cliente->depto }} {{ $servicio->cliente->piso }}
        </td>
        <td class="bg-gray no-left-border" >
            Localidad: {{ $servicio->cliente->localidad->nombre }}
        </td>
        <td class="bg-gray">
            Modelo y Año: {{ $servicio->modelo }} - {{ $servicio->year }}
        </td>
    </tr>
    <tr>
        <td class="bg-gray no-right-border">
            C.P.: {{ $servicio->cliente->cp }}
        </td>
        <td class="bg-gray no-left-border">
            E-mail: {{ $servicio->cliente->email }}
        </td>
        <td class="bg-gray">
            Nº de chasis: {{ $servicio->chasis }}
        </td>
    </tr>
    <tr>
        <td class="bg-gray no-right-border">
            Teléfono: ({{ $servicio->cliente->particular_area }}) {{ $servicio->cliente->particular }}
        </td>
        <td class="bg-gray no-left-border">
            Celular: ({{ $servicio->cliente->celular_area }}) {{ $servicio->cliente->celular }}
        </td>
        <td class="bg-gray">
            Nº de motor: {{ $servicio->motor }}
        </td>
    </tr>
    <tr>
        <td colspan="2" style="font-weight: bold;">ESTADO GENERAL DEL VEHÍCULO</td>
        <td class="bg-dark-gray no-bottom-border" style="font-size: 9px">Compromiso de entrega: {{ date('d/m/Y', strtotime($servicio->entrega)) }}</td>
    </tr>
    <tr>
        <td colspan="2">KILOMETRAJE / HORAS: {{ $servicio->kilometros }}</td>
        <td class="bg-dark-gray no-top-border"></td>
    </tr>
    <tr>
        <td colspan="3" class="obs">
            {{-- Imagen del estado --}}
            <img src="{{ public_path('/images/orden-st-motos.jpg') }}" style="width:100%;">
        </td>
    </tr>
    <tr>
        <td colspan="3" style="font-weight: bold">OBSERVACIONES</td>
    </tr>
    <tr>
        <td colspan="3" class="obs">{{ $servicio->observacion }}</td>
    </tr>
    <tr>
        <td colspan="3" class="obs">
            {{-- Imagen del estado --}}
            <img src="{{ public_path('/images/orden-st-items.jpg') }}" style="width:100%;">
        </td>
    </tr>
    <tr>
        <td colspan="2" style="font-weight: bold">DESCRIPCIÓN DEL PEDIDO DEL CLIENTE</td>
        <td style="font-weight: bold">SERVICIO: {{ $servicio->tipoServicio->nombre }}</td>
    </tr>
    <tr>
        <td colspan="3" class="pedido">{{ $servicio->descripcion }}</td>
    </tr>
    <tr>
        <td colspan="3" class="pedido">Me declaro en conocimiento de la condición en la que se encuentra la unidad, afirmando que los daños en la carrocería detectados en el momento de la recepción, son los indicados en la figura. Autorizo a realizar todos los trabajos descriptos a mi exclusiva cuenta y cargo, y a efectuar todas las pruebas necesarias (incluídas en ruta) de la unidad.</td>
    </tr>
    <tr>
        <td colspan="3" class="pedido no-bottom-border">VEHÍCULO INGRESADO EL</td>
    </tr>
    <tr>
        <td class="no-bottom-border no-right-border no-top-border" style="text-align: center"><span class="linea-fecha">{{ date('d/m/Y H:i:s', strtotime($servicio->ingreso)) }}</span></td>
        <td class="no-bottom-border no-left-border no-top-border no-right-border" style="text-align: center"><span class="linea-firma"></span></td>
        <td class="no-bottom-border no-left-border no-top-border" style="text-align: center"><span class="linea-firma"></span></td>
    </tr>
    <tr>
        <td class="no-bottom-border no-right-border no-top-border" style="text-align: center">FECHA</td>
        <td class="no-bottom-border no-left-border no-top-border no-right-border" style="text-align: center">FIRMA Y ACLARACIÓN DEL CLIENTE</td>
        <td class="no-bottom-border no-left-border no-top-border" style="text-align: center">FIRMA Y ACLARACIÓN DEL RECEPCIONISTA</td>
    </tr>
    <tr>
        <td colspan="3" style="font-weight: bold">DIAGNÓSTICO Y REPARACIÓN REALIZADA</td>
    </tr>
    <tr>
        <td colspan="3" >{{ $servicio->diagnostico }}</td>
    </tr>
    <tr>
        <td colspan="2" ><span style="font-weight: bold">MECANICOS:</span> {{ $servicio->mecanicos }}</td>
        <td ><span style="font-weight: bold">TIEMPO MANO DE OBRA:</span> {{ $servicio->tiempo }}</td>
    </tr>
    <tr>
        <td colspan="3" style="font-weight: bold">REPUESTOS UTILIZADOS</td>
    </tr>
    <tr>
        <td colspan="3" class="obs no-bottom-border">{{ $servicio->repuestos }}</td>
    </tr>
    <tr>
        <td colspan="3" style="font-weight: bold">INSTRUMENTOS DE MEDICIÓN UTILIZADOS</td>
    </tr>
    <tr>
        <td colspan="3" class="obs no-bottom-border">{{ $servicio->instrumentos }}</td>
    </tr>
    <tr>
        <td colspan="3" class="pedido no-bottom-border" style="font-size: 9px;">Dejo expresa constancia que luego de haber sido probada, retiro la unidad antes mencionada con los trabajos de reparacion realizados, declarando conocer y aceptar el estado en que se encuentra la misma. La unidad será retirada por el titular. En caso de no poder asistir, el responsable deberá acreditar la titularidad de la misma. (Fotocopia de DNI).</td>
    </tr>
    <tr>
        <td colspan="3" class="pedido no-bottom-border">VEHÍCULO RETIRADO EL</td>
    </tr>
    <tr>
        <td class="no-bottom-border no-right-border no-top-border" style="text-align: center"><span class="linea-fecha"></span></td>
        <td class="no-bottom-border no-left-border no-top-border no-right-border" style="text-align: center"><span class="linea-firma"></span></td>
        <td class="no-bottom-border no-left-border no-top-border" style="text-align: center"><span class="linea-firma"></span></td>
    </tr>
    <tr>
        <td class="no-right-border no-top-border" style="text-align: center">FECHA</td>
        <td class="no-left-border no-top-border no-right-border" style="text-align: center">FIRMA Y ACLARACIÓN DEL CLIENTE</td>
        <td class="no-left-border no-top-border" style="text-align: center">FIRMA DEL TÉCNICO Y LEGAJO</td>
    </tr>
</table>








</body>
</html>
