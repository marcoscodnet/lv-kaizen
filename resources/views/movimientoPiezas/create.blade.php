@extends('layouts.app')
@section('headSection')

    <link rel="stylesheet" href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}">

@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-header">
            <div class="row flex-between-end">
                <div class="col-auto align-self-center">
                    <h5 class="mb-0" data-anchor="data-anchor"><i class="fa fa-exchange-alt" aria-hidden="true"></i><span class="ms-2">Crear movimiento de unidad</span></h5>
                </div>
                <div class="col-auto ms-auto">

                </div>
            </div>
        </div>
        <div class="card-body bg-body-tertiary">
            <form role="form" action="{{ route('movimientoPiezas.store') }}" method="post" >
                {{ csrf_field() }}
                <div class="tab-content">
                    <div class="box-body">

                        @include('includes.messages')
                        <div class="row">

                            <div class="col-12 col-lg-3">
                                <div class="form-group">
                                    <label for="sucursal_origen_id">Origen</label>
                                    <select name="sucursal_origen_id" class="form-control js-example-basic-single" required>

                                        @foreach($origens as $sucursalId => $sucursal)
                                            <option value="{{ $sucursalId }}" {{ old('sucursal_origen_id', auth()->user()->sucursal_id) == $sucursalId ? 'selected' : '' }}>{{ $sucursal }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-lg-3">
                                <div class="form-group">
                                    <label for="sucursal_destino_id">Destino</label>
                                    <select name="sucursal_destino_id" class="form-control js-example-basic-single" required>

                                        @foreach($destinos as $sucursalId => $sucursal)
                                            <option value="{{ $sucursalId }}" {{ old('sucursal_destino_id') == $sucursalId ? 'selected' : '' }}>{{ $sucursal }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-lg-3">
                                <div class="form-group">
                                    <label for="fecha">Fecha</label>
                                    <input type="date" class="form-control" id="fecha" name="fecha"  value="{{ old('fecha') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">


                            <table class="table">
                                <thead>
                                <tr>
                                    <th>Pieza</th>
                                    <th>Cantidad</th>
                                    <th>
                                        <a href="#" class="addRowPieza btn btn-success btn-sm">
                                            <i class="fa fa-plus"></i>
                                        </a>
                                    </th>
                                </tr>
                                </thead>

                                <tbody id="cuerpoPieza">
                                <tbody id="cuerpoPieza">
                                @php
                                    $oldPiezas = old('pieza_id', []);
                                    $oldCantidades = old('cantidad', []);
                                @endphp

                                @if(count($oldPiezas))
                                    @foreach($oldPiezas as $i => $piezaId)
                                        <tr>
                                            <td style="width:50%;">
                                                <select name="pieza_id[]" class="form-control js-example-basic-single" required>
                                                    <option value="">Seleccionar...</option>
                                                    @foreach($piezas as $id => $pieza)
                                                        <option value="{{ $id }}" {{ $piezaId == $id ? 'selected' : '' }}>
                                                            {{ $pieza }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>

                                            <td style="width:30%;">
                                                <input type="number"
                                                       name="cantidad[]"
                                                       class="form-control"
                                                       min="1"
                                                       value="{{ $oldCantidades[$i] ?? '' }}"
                                                       required>
                                            </td>

                                            <td>
                                                <a href="#" class="btn btn-danger btn-sm removePieza">
                                                    <i class="fa fa-times text-white"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>

                                </tbody>
                            </table>
                        </div>

                        <div class="row">
                            <div class="col-12 col-lg-9">
                                <div class="form-group">

                                        <label for="observaciones" class="col-md-12">Observaciones</label>
                                </div>
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-12 col-lg-9">
                                <div class="form-group">


                                    <!-- Fila 2: Área de texto -->

                                        <textarea id="observaciones" name="observaciones" class="form-control" rows="3">
                                            @if (old('observaciones')){{ old('observaciones') }}@endif
                                        </textarea>

                                </div>
                            </div>

                        </div>

                        <div class="row" style="margin-top: 10px;">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Guardar</button>
                                <a href='{{ route('movimientos.index') }}' class="btn btn-warning">Volver</a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- /.content-wrapper -->
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

    <!-- Inputmask -->
    <script src="{{ asset('bower_components/inputmask/dist/min/jquery.inputmask.bundle.min.js') }}"></script>


    <script src="{{ asset('assets/js/confirm-exit.js') }}"></script>


    <!-- page script -->
    <script>
        $(document).ready(function () {
            $('.js-example-basic-single').select2({
                language: 'es',
                width: '100%'
            });


            // Select de piezas renderizado desde Blade
            var selectPieza = `{!! '<select name="pieza_id[]" class="form-control js-example-basic-single" required>' !!}
            <option value="">Seleccionar...</option>
@foreach($piezas as $piezaId => $pieza)
            {!! '<option value="'.$piezaId.'">'.$pieza.'</option>' !!}
            @endforeach
            {!! '</select>' !!}`;

            // Agregar fila
            $('.addRowPieza').on('click', function(e){
                e.preventDefault();
                addRowPieza();
            });

            function addRowPieza()
            {
                var tr = `
            <tr>
                <td style="width:50%;">${selectPieza}</td>
                <td style="width:30%;">
                    <input type="number" name="cantidad[]" class="form-control" min="1" required>
                </td>
                <td>
                    <a href="#" class="btn btn-danger btn-sm removePieza">
                        <i class="fa fa-times text-white"></i>
                    </a>
                </td>
            </tr>
        `;

                $('#cuerpoPieza').append(tr);

                // Inicializar Select2 en los nuevos selects
                $('.js-example-basic-single').select2({
                    language: 'es'
                });
            }

            // Eliminar fila
            $('body').on('click', '.removePieza', function(e){
                e.preventDefault();

                if (confirm('¿Eliminar fila?')) {
                    $(this).closest('tr').remove();
                }
            });

        });
    </script>



@endsection
