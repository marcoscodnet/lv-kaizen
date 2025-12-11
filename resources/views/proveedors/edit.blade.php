@extends('layouts.app')
@section('headSection')

    <link rel="stylesheet" href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}">

@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-header">
            <div class="row flex-between-end">
                <div class="col-auto align-self-center">
                    <h5 class="mb-0" data-anchor="data-anchor"><i class="fa fa-truck" aria-hidden="true"></i><span class="ms-2">Editar proveedor</span></h5>
                </div>
                <div class="col-auto ms-auto">

                </div>
            </div>
        </div>
        <div class="card-body bg-body-tertiary">
            <form role="form" action="{{ route('proveedors.update',$proveedor->id) }}" method="post" >
                {{ csrf_field() }}
                {{ method_field('PUT') }}
                <div class="tab-content">
                    <div class="box-body">

                        @include('includes.messages')
                        <div class="row">
                            <div class="col-12 col-lg-3">
                                <div class="form-group">
                                    <label for="nombre">Nombre</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre" value="@if (old('nombre')){{ old('nombre') }}@else{{ $proveedor->nombre }}@endif" required>
                                </div>
                            </div>
                            <div class="col-12 col-lg-3">
                                <div class="form-group">
                                    <label for="razon">Razón Social</label>
                                    <input type="text" class="form-control" id="razon" name="razon" placeholder="Razon" value="@if (old('razon')){{ old('razon') }}@else{{ $proveedor->razon }}@endif" required>
                                </div>
                            </div>
                            <div class="col-12 col-lg-2">
                                <div class="form-group">
                                    <label for="cuil">CUIT</label>
                                    <input type="text" class="form-control" id="cuil" name="cuil" placeholder="XX-XXXXXXXX-X" value="@if (old('cuil')){{ old('cuil') }}@else{{ $proveedor->cuil }}@endif">
                                </div>
                            </div>
                            <div class="col-12 col-lg-3">
                                <div class="form-group">
                                    <label for="iva">Condición IVA</label>

                                    <select name="iva" id="iva" class="form-control" required>
                                        <option value="">
                                            Seleccionar...
                                        </option>
                                        @foreach (config('iva') as $key => $label)
                                            <option value="{{ $key }}" {{ old('iva', $proveedor->iva ?? '') == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>


                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-lg-1">
                                <div class="form-group">
                                    <label for="particular_area">Área</label>
                                    <input type="text" class="form-control" id="particular_area" name="particular_area" placeholder="Área" value="@if (old('particular_area')){{ old('particular_area') }}@else{{ $proveedor->particular_area }}@endif" required>
                                </div>
                            </div>
                            <div class="col-12 col-lg-2">
                                <div class="form-group">
                                    <label for="particular">Particular</label>
                                    <input type="text" class="form-control" id="particular" name="particular" placeholder="Particular" value="@if (old('particular')){{ old('particular') }}@else{{ $proveedor->particular }}@endif" required>
                                </div>
                            </div>
                            <div class="col-12 col-lg-1">
                                <div class="form-group">
                                    <label for="celular_area">Área</label>
                                    <input type="text" class="form-control" id="celular_area" name="celular_area" placeholder="Área" value="@if (old('celular_area')){{ old('celular_area') }}@else{{ $proveedor->celular_area }}@endif" required>
                                </div>
                            </div>
                            <div class="col-12 col-lg-2">
                                <div class="form-group">
                                    <label for="celular">Celular</label>
                                    <input type="text" class="form-control" id="celular" name="celular" placeholder="Celular" value="@if (old('celular')){{ old('celular') }}@else{{ $proveedor->celular }}@endif" required>
                                </div>
                            </div>
                            <div class="col-12 col-lg-5">
                                <div class="form-group">
                                    <label for="email">E-mail</label>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="email" value="@if (old('email')){{ old('email') }}@else{{ $proveedor->email }}@endif">
                                </div>
                            </div>


                        </div>


                        <div class="row">

                                <div class="form-group">
                                    <div class="row">
                                        <label for="observaciones" class="col-md-12">Observaciones</label>
                                    </div>

                                    <!-- Fila 2: Área de texto -->
                                    <div class="row">
                                        <textarea id="observaciones" name="observaciones" class="form-control" rows="3">@if (old('observaciones')){{ old('observaciones') }}@else{{ $proveedor->observaciones }}@endif</textarea>
                                    </div>
                                </div>

                        </div>

                        <div class="row" style="margin-top: 10px;">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Guardar</button>
                                <a href='{{ route('proveedors.index') }}' class="btn btn-warning">Volver</a>
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

    <script src="{{ asset('assets/js/combo-provincia-localidad.js') }}"></script>
    <script src="{{ asset('assets/js/confirm-exit.js') }}"></script>


    <!-- page script -->
    <script>
        $(document).ready(function () {
            $('#cuil').inputmask('99-99999999-9', { placeholder: 'XX-XXXXXXXX-X' });
            $('.js-example-basic-single').select2({
                language: 'es'});
            if ($('.provincia-select').val()) {
                $('.provincia-select').trigger('change');
            }



        });
        var localidadUrl = "{{ url('localidads') }}";

        // Mostrar/ocultar cónyuge
        function toggleConyuge() {
            var estadoCivil = $('#estado_civil').val();
            if (estadoCivil === 'Casado/a' || estadoCivil === 'Concubino/a') {
                $('#conyuge-container').show();
                $('#conyuge').attr('required', true);
            } else {
                $('#conyuge-container').hide();
                $('#conyuge').removeAttr('required').val('');
            }
        }

        $('#estado_civil').on('change', toggleConyuge);

        // Ejecutar al cargar (por si hay old() con Casado/a o Concubino/a)
        toggleConyuge();

    </script>


@endsection
