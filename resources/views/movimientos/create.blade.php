@extends('layouts.app')
@section('headSection')

    <link rel="stylesheet" href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}">

@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-header">
            <div class="row flex-between-end">
                <div class="col-auto align-self-center">
                    <h5 class="mb-0" data-anchor="data-anchor"><i class="fa fa-exchange-alt" aria-hidden="true"></i><span class="ms-2">Crear movimiento</span></h5>
                </div>
                <div class="col-auto ms-auto">

                </div>
            </div>
        </div>
        <div class="card-body bg-body-tertiary">
            <form role="form" action="{{ route('movimientos.store') }}" method="post" >
                {{ csrf_field() }}
                <div class="tab-content">
                    <div class="box-body">

                        @include('includes.messages')
                        <div class="row">

                            <div class="col-lg-offset-3 col-lg-3 col-md-2">
                                <div class="form-group">
                                    <label for="sucursal_origen_id">Origen</label>
                                    <select name="sucursal_origen_id" class="form-control js-example-basic-single" required>

                                        @foreach($origens as $sucursalId => $sucursal)
                                            <option value="{{ $sucursalId }}" {{ old('sucursal_origen_id') == $sucursalId ? 'selected' : '' }}>{{ $sucursal }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-3 col-md-2">
                                <div class="form-group">
                                    <label for="sucursal_destino_id">Destino</label>
                                    <select name="sucursal_destino_id" class="form-control js-example-basic-single" required>

                                        @foreach($destinos as $sucursalId => $sucursal)
                                            <option value="{{ $sucursalId }}" {{ old('sucursal_destino_id') == $sucursalId ? 'selected' : '' }}>{{ $sucursal }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-3 col-md-3">
                                <div class="form-group">
                                    <label for="fecha">Fecha</label>
                                    <input type="date" class="form-control" id="fecha" name="fecha"  value="{{ old('fecha') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group col-md-12">


                            <table class="table">
                                <thead>

                                <th>Producto</th>
                                <th>Unidad</th>

                                <th><a href="#" class="addRowProducto btn btn-success btn-sm">
                                        <i class="fa fa-plus"></i>
                                    </a></th>

                                </thead>

                                <tbody id="cuerpoProducto">


                                </tbody>




                            </table>
                        </div>

                        <div class="row">
                            <div class="col-lg-offset-3 col-lg-9 col-md-2">
                                <div class="form-group">

                                        <label for="observaciones" class="col-md-12">Observaciones</label>
                                </div>
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-lg-offset-3 col-lg-9 col-md-2">
                                <div class="form-group">


                                    <!-- Fila 2: Área de texto -->

                                        <textarea id="observaciones" name="observaciones" class="form-control" rows="3"></textarea>

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

    <!-- Inputmask -->
    <script src="{{ asset('bower_components/inputmask/dist/min/jquery.inputmask.bundle.min.js') }}"></script>


    <script src="{{ asset('assets/js/confirm-exit.js') }}"></script>


    <!-- page script -->
    <script>
        $(document).ready(function () {

            const sucursalSelect = $('select[name="sucursal_origen_id"]');
            const agregarBtn = $('.addRowProducto');
            const cuerpoProducto = $('#cuerpoProducto');

            function actualizarEstado() {
                const sucursalId = sucursalSelect.val();
                cuerpoProducto.empty();
                if (!sucursalId) {
                    agregarBtn.addClass('disabled').css('pointer-events', 'none').attr('aria-disabled', 'true');
                } else {
                    agregarBtn.removeClass('disabled').css('pointer-events', 'auto').removeAttr('aria-disabled');
                }
            }

            sucursalSelect.on('change', function() {

                actualizarEstado();
            });

            // Inicialmente
            actualizarEstado();
            // Renderizás el HTML de Blade en una variable JS:
            var selectProducto = `{!! '<select name="producto_id[]" class="form-control js-example-basic-single selectProducto">' !!}
            @foreach($productos as $productoId => $producto)
            {!! '<option value="'.$productoId.'">'.$producto.'</option>' !!}
            @endforeach
            {!! '</select>' !!}`;

            $('.addRowProducto').on('click',function(e){
                e.preventDefault();
                addRowProducto();
            });
            function addRowProducto()
            {

                var tr='<tr>'+
                    '<td style="width:40%;">' + selectProducto + '</td>' +

                    '<td style="width:40%;"><select name="unidad_id[]" class="form-control js-example-basic-single unidadSelect"><option value="">Seleccionar...</option></select></td>' +
                    '<td><a href="#" class="btn btn-danger btn-sm removeProducto"><i class="fa fa-times text-white"></i></a></td>'+

                    '</tr>';
                $('#cuerpoProducto').append(tr);
                $('.js-example-basic-single').select2();
            };
            $('body').on('click', '.removeProducto', function(e){

                var confirmDelete = confirm('¿Estás seguro?');

                if (confirmDelete) {
                    $(this).parent().parent().remove();
                }

            });
            const unidadUrlTemplate = @json(route('api.unidads.getUnidadsPorProducto', ['productoId' => 'PRODUCTO_ID']));
// AJAX: Cargar unidades al seleccionar producto
            $('body').on('change', '.selectProducto', function() {
                var productoId = $(this).val();
                var unidadSelect = $(this).closest('tr').find('.unidadSelect');
                var sucursalOrigenId = $('select[name="sucursal_origen_id"]').val();
                unidadSelect.empty();
                unidadSelect.append('<option value="">Cargando...</option>');

                if (productoId && sucursalOrigenId) {
                    let url = unidadUrlTemplate.replace('PRODUCTO_ID', productoId);
                    url += `?sucursal_origen_id=${sucursalOrigenId}`;

                    $.ajax({
                        url: url,
                        method: 'GET',
                        success: function(data) {
                            unidadSelect.empty();
                            unidadSelect.append('<option value="">Seleccionar..</option>');
                            data.forEach(function(unidad) {
                                unidadSelect.append('<option value="' + unidad.id + '">' + unidad.texto + '</option>');
                            });
                        },
                        error: function() {
                            unidadSelect.empty();
                            unidadSelect.append('<option value="">Error al cargar unidades</option>');
                        }
                    });
                } else {
                    unidadSelect.empty();
                    unidadSelect.append('<option value="">Seleccionar...</option>');
                }
            });


        });

    </script>


@endsection
