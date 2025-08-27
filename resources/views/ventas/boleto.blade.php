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
            height: 50px;
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
            <p><span style="font-weight: bold">Apellido y nombre/Razón Social:</span> {{$venta->cliente->nombre}}</p>
            <p><span style="font-weight: bold">Domicilio:</span> {{$venta->cliente->calle}} <span style="font-weight: bold">Nº:</span> {{$venta->cliente->nro}} <span style="font-weight: bold">Piso:</span> {{$venta->cliente->piso}} <span style="font-weight: bold">Depto.:</span> {{$venta->cliente->depto}}</p>
            <div class="row2">
                <div class="cell left-section2">
                    <p><span style="font-weight: bold">Localidad:</span> {{$venta->cliente->localidad->nombre}}</p>
                    <p><span style="font-weight: bold">Provincia:</span> {{$venta->cliente->localidad->provincia->nombre}}</p>
                </div>
                <div class="cell left-section2">
                    <p><span style="font-weight: bold">Código postal:</span> {{ $venta->cliente->cp }}</p>
                    <p><span style="font-weight: bold">TE particular:</span> ({{ $venta->cliente->particular_area}})  {{$venta->cliente->particular}}</p>
                </div>
            </div>
        </div>
        <div class="content">
            <!-- Contenido del boleto -->
        </div>
    </div>
@endfor

</body>
</html>
