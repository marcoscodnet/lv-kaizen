<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin-top: 150px; /* espacio reservado para el membrete */
            margin-right: 30px;
            margin-left: 30px;
            margin-bottom: 40px;
        }

        body {
            font-family: sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }

        .duplicado {
            margin-bottom: 20px;
            page-break-after: always;
        }

        .duplicado:last-child {
            page-break-after: auto;
        }

        .header {
            border: 1px solid #000;
            /*padding: 20px;
            margin-bottom: 10px;*/
            height: 40px;
            box-sizing: border-box;
            position: relative;
        }

        .subtitle {
            border: 1px solid #000;
            background-color: darkgrey;
            box-sizing: border-box;
            position: relative;
        }

        .titulo {
            text-align: center;
            margin: 0;
        }

        .description {
            border: 1px solid #000;

            box-sizing: border-box;
            position: relative;
        }

        .nro-boleto {
            position: absolute;
            right: 20px;

        }

        .row2 {
            display: table;
            width: 100%;
            table-layout: fixed;
            /*border: 1px solid #000;*/
            padding: 10px;
            box-sizing: border-box;
        }

        .cell {
            display: table-cell;
            vertical-align: middle;
            /*padding: 10px 15px;*/
        }

        .left-section2 {
            width: 50%;
            text-align: left;
        }

        .right-section2 {
            width: 40%;
            text-align: right;
        }
        p{
            margin: 5px;
        }

        .datos-titular {
            width: 100%;
            border-collapse: collapse;

        }

        .datos-titular td {
            vertical-align: top;
            padding: 0 8px;
            width: 50%;
        }

        .datos-bien {
            width: 100%;
            border-collapse: collapse;
        }

        .datos-bien td {
            width: 25%; /* 4 columnas iguales */
            padding: 0 8px;
            vertical-align: top;
        }

        .datos-vendedor {
            width: 100%;
            border-collapse: collapse;
        }

        .datos-vendedor td {

            padding: 0 8px;
            vertical-align: top;
        }

        .datos-vendedor .label {
            width: 25%;
            background-color: darkgrey;
        }
        .signature { margin-top: 50px; text-align: center; }
        .signature div { display: inline-block; width: 30%; text-align: center; }
        .signature-line {
            border-top: 1px solid black;
            margin: 20px 10px 10px 10px;
        }
    </style>
</head>
<body>

@for ($i = 0; $i < 2; $i++)
    <div class="duplicado">
        <div class="header">
            <p class="titulo">BOLETO DE COMPRA VENTA PARA MOTOVEHÍCULOS/Otros Bienes</p>
            <p class="nro-boleto">BOLETO Nº {{ $venta->id+1000 ?? '_____' }}</p>
        </div>
        <div class="description">
            <p> Conste por el presente que en el lugar y fecha indicados en el pie de la presente, se realiza la operación de Venta detallando los datos del titular y del respectivo bien.</p>

        </div>
        <div class="subtitle">
            <p class="titulo">DATOS DEL TITULAR</p>

        </div>

        <div class="description">
            <table class="datos-titular">
                <tr>
                    <td colspan="2"><strong>Apellido y nombre/Razón Social:</strong> {{$venta->cliente->nombre}}</td>

                </tr>
                <tr>
                    <td><strong>Domicilio:</strong> {{$venta->cliente->calle}}

                    </td>
                    <td><strong>Nº:</strong> {{$venta->cliente->nro}}
                        <strong>Piso:</strong> {{$venta->cliente->piso}}
                        <strong>Depto.:</strong> {{$venta->cliente->depto}}</td>
                </tr>
                <tr>
                    <td><strong>Localidad:</strong> {{$venta->cliente->localidad->nombre}}</td>
                    <td><strong>Código postal:</strong> {{$venta->cliente->cp}}</td>
                </tr>
                <tr>
                    <td><strong>Provincia:</strong> {{$venta->cliente->localidad->provincia->nombre}}</td>
                    <td><strong>TE particular:</strong> ({{$venta->cliente->particular_area}}) {{$venta->cliente->particular}}</td>
                </tr>
                <tr>
                    <td><strong>Fecha nacimiento:</strong> {{ ($venta->cliente->nacimiento)?date('d/m/Y', strtotime($venta->cliente->nacimiento)):'' }}</td>
                    <td><strong>TE móvil:</strong> ({{$venta->cliente->celular_area}}) {{$venta->cliente->celular}}</td>
                </tr>
                <tr>
                    <td><strong>Estado civil:</strong> {{$venta->cliente->estado_civil}}</td>
                    <td><strong>E-mail:</strong> {{$venta->cliente->email}}</td>
                </tr>
                <tr>
                    <td><strong>Cónyuge:</strong> {{$venta->cliente->conyuge}}</td>
                    <td></td>
                </tr>
                <tr>
                    <td><strong>Nacionalidad:</strong> {{$venta->cliente->nacionalidad}}</td>
                    <td></td>
                </tr>
                <tr>
                    <td><strong>DNI:</strong> {{$venta->cliente->documento}}</td>
                    <td><strong>CUIT/CUIL:</strong> {{$venta->cliente->cuil}}</td>
                </tr>

            </table>
        </div>

        <div class="subtitle">
            <p class="titulo">DATOS DEL BIEN</p>

        </div>
        <div class="description">
            <table class="datos-bien">
                <tr>
                    <td><strong>Tipo</strong> </td>
                    <td><strong>Marca</strong> </td>
                    <td><strong>Modelo</strong> </td>
                    <td><strong>Año - Modelo</strong></td>
                </tr>
                <tr>
                    <td>{{$venta->unidad->producto->tipoUnidad->nombre}}</td>
                    <td>{{$venta->unidad->producto->marca->nombre}}</td>
                    <td>{{$venta->unidad->producto->modelo->nombre}}</td>
                    <td> {{$venta->unidad->year}}</td>
                </tr>
                <tr>

                    <td><strong>Patente</strong> </td>
                    <td><strong>Nro. Motor</strong> </td>
                    <td><strong>Nro. Cuadro</strong> </td>
                    <td><strong>Color</strong> </td>
                </tr>
                <tr>
                    <td>{{$venta->unidad->patente}}</td>
                    <td>{{$venta->unidad->motor}}</td>
                    <td>{{$venta->unidad->cuadro}}</td>
                    <td>{{$venta->unidad->producto->color->nombre}}</td>
                </tr>



            </table>
        </div>
        <div class="description">
            <table class="datos-vendedor">
                <tr>
                    <td class="label"><strong>Vendedor</strong> </td>
                    <td>{{(isset($venta->user))?$venta->user->name:$venta->user_name}}</td>

                </tr>



            </table>
        </div>
        <div class="description" style="white-space: pre-wrap;">
            {!! $parametro->contenido !!}
            @php
                setlocale(LC_TIME, 'es_ES.UTF-8'); // Para que el mes salga en español
                $fecha = \Carbon\Carbon::now();    // Fecha actual
                $dia = $fecha->day;
                $mes = $fecha->translatedFormat('F'); // Mes en español
                $anio = $fecha->year;
            @endphp
            En prueba de conformidad se firman dos ejemplares del mismo tenor y a un solo efecto en la ciudad de La Plata, a los {{ $dia }} días, del mes de {{ ucfirst($mes) }} de {{ $anio }}.
        </div>
        <div class="signature">
            <div class="signature-line">Firma del titular</div>
            <div class="signature-line">Firma del Autorizado</div>
            <div class="signature-line">Sello del concesionario</div>
        </div>

    </div>
@endfor

</body>
</html>
