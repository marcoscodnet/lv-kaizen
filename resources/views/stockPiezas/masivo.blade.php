@extends('layouts.app')
@section('headSection')

    <link rel="stylesheet" href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}">
<style>
    /* Fijar ancho de toda la tabla */
    #tablaMasiva {
        table-layout: fixed !important;
    }

    /* Ancho mínimo de la columna del select (pieza) */
    #tablaMasiva td:first-child {
        min-width: 250px !important;
    }

    /* Select2 que NO cambia de tamaño */
    .select2-container,
    .select2,
    .select2-container .select2-selection--single {
        width: 100% !important;
    }

    .select2-selection--single {
        height: 38px !important;
        padding: 4px 8px !important;
    }

    .select2-selection__rendered {
        white-space: nowrap !important;
        text-overflow: ellipsis !important;
        overflow: hidden !important;
        line-height: 30px !important;
    }

    .select2-selection__arrow {
        height: 36px !important;
    }


</style>
@endsection
@section('content')

    <div class="card mb-3">
        <div class="card-header">
            <div class="row flex-between-end">
                <div class="col-auto align-self-center">
                    <h5 class="mb-0" data-anchor="data-anchor"><i class="fa fa-toolbox" aria-hidden="true"></i><span class="ms-2">Carga masiva de stock pieza</span></h5>
                </div>
                <div class="col-auto ms-auto">

                </div>
            </div>
        </div>

        <div class="card-body bg-body-tertiary">
            <form action="{{ route('stockPiezas.storeMasivo') }}" method="POST">
                @csrf

                @include('includes.messages')

                <table class="table table-bordered" id="tablaMasiva">
                    <thead class="table-light">
                    <tr>
                        <th style="width: 250px;">Pieza</th>
                        <th style="width: 120px;">Cantidad</th>
                        <th>Sucursal</th>
                        <th>Proveedor</th>
                        <th style="width: 150px;">Remito</th>
                        <th>Ingreso</th>
                        <th style="width: 50px;"><button type="button" class="btn btn-success mb-3" onclick="addRow()"><i class="fa fa-plus"></i></button></th>
                    </tr>
                    </thead>
                    <tbody>

                    <tr>
                        <td>
                            <select name="rows[0][pieza_id]" class="form-control js-example-basic-single" required>
                                <option value="">Seleccionar…</option>
                                @foreach($piezas as $id => $p)
                                    <option value="{{ $id }}">{{ $p }}</option>
                                @endforeach
                            </select>
                        </td>

                        <td>
                            <input type="number" name="rows[0][cantidad]" class="form-control" required>
                        </td>
                        <td>
                            <select name="rows[0][sucursal_id]" class="form-control js-example-basic-single">
                                <option value="">Seleccionar…</option>
                                @foreach($sucursals as $id => $prov)
                                    <option value="{{ $id }}">{{ $prov }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <select name="rows[0][proveedor_id]" class="form-control js-example-basic-single">
                                <option value="">Seleccionar…</option>
                                @foreach($proveedors as $id => $prov)
                                    <option value="{{ $id }}">{{ $prov }}</option>
                                @endforeach
                            </select>
                        </td>

                        <td>
                            <input type="text" name="rows[0][remito]" class="form-control">
                        </td>

                        <td><input type="date" name="rows[0][ingreso]" class="form-control" value="{{ date('Y-m-d') }}" required></td>

                        <td>
                            <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)"><i class="fa fa-times text-white"></i></button>
                        </td>
                    </tr>

                    </tbody>
                </table>



                <br>

                <button type="submit" class="btn btn-primary">Guardar</button>
                <a href="{{ route('stockPiezas.index') }}" class="btn btn-warning">Volver</a>
            </form>
        </div>
    </div>

@endsection

@section('footerSection')
    <!-- jQuery 3 -->
    <script src="{{ asset('bower_components/jquery/dist/jquery.min.js') }}"></script>
    <!-- Bootstrap 3.3.7 -->
    <script src="{{ asset('bower_components/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <!-- SlimScroll -->
    <script src="{{ asset('bower_components/jquery-slimscroll/jquery.slimscroll.min.js') }}"></script>
    <!-- FastClick -->
    <script src="{{ asset('bower_components/fastclick/lib/fastclick.js') }}"></script>
    <!-- FastClick -->
    <script src="{{ asset('bower_components/fastclick/lib/fastclick.js') }}"></script>

    <!-- Select2 -->
    <script src="{{ asset('bower_components/select2/dist/js/select2.min.js') }}"></script>
    <script src="{{ asset('bower_components/select2/dist/js/i18n/es.js') }}"></script>

    <script src="{{ asset('assets/js/confirm-exit.js') }}"></script>
    <script>
        let fila = 1;

        function addRow() {
            const table = document.querySelector('#tablaMasiva tbody');
            const newRow = `
        <tr>
            <td>
                <select name="rows[${fila}][pieza_id]" class="form-control js-example-basic-single" required>
                    @foreach($piezas as $id => $p)
            <option value="{{ $id }}">{{ $p }}</option>
                    @endforeach
            </select>
        </td>

        <td><input type="number" name="rows[${fila}][cantidad]" class="form-control" required></td>
        <td>
                <select name="rows[${fila}][sucursal_id]" class="form-control js-example-basic-single">
                    <option value="">Seleccionar…</option>
                    @foreach($sucursals as $id => $prov)
            <option value="{{ $id }}">{{ $prov }}</option>
                    @endforeach
            </select>
        </td>
            <td>
                <select name="rows[${fila}][proveedor_id]" class="form-control js-example-basic-single">
                    <option value="">Seleccionar…</option>
                    @foreach($proveedors as $id => $prov)
            <option value="{{ $id }}">{{ $prov }}</option>
                    @endforeach
            </select>
        </td>

        <td><input type="text" name="rows[${fila}][remito]" class="form-control"></td>
        <td><input type="date" name="rows[${fila}][ingreso]" class="form-control" value="{{ date('Y-m-d') }}" required></td>
            <td>
                <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)"><i class="fa fa-times text-white"></i></button>
            </td>
        </tr>
    `;
            table.insertAdjacentHTML('beforeend', newRow);
            fila++;
            $('.js-example-basic-single').select2();
        }

        function removeRow(btn) {
            if(confirm("¿Está seguro que desea eliminar esta fila?")) {
                btn.closest('tr').remove();
            }

        }

        $(document).ready(function () {

            $('.js-example-basic-single').select2({
                language: 'es',
                width: '100%'
            });

        });
    </script>
@endsection
