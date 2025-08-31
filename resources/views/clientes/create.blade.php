@extends('layouts.app')
@section('headSection')

    <link rel="stylesheet" href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}">

@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-header">
            <div class="row flex-between-end">
                <div class="col-auto align-self-center">
                    <h5 class="mb-0" data-anchor="data-anchor"><i class="fa fa-user-friends" aria-hidden="true"></i><span class="ms-2">Crear cliente</span></h5>
                </div>
                <div class="col-auto ms-auto">

                </div>
            </div>
        </div>
        <div class="card-body bg-body-tertiary">
            <form role="form" action="{{ route('clientes.store') }}" method="post" >
                {{ csrf_field() }}
                <div class="tab-content">
                    <div class="box-body">

                        @include('includes.messages')
                        <div class="row">
                            <div class="col-lg-offset-3 col-lg-4 col-md-3">
                                <div class="form-group">
                                    <label for="nombre">Nombre</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre" value="{{ old('nombre') }}" required>
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-2 col-md-3">
                                <div class="form-group">
                                    <label for="documento">Documento</label>
                                    <input type="text" class="form-control" id="documento" name="documento" placeholder="Documento" value="{{ old('documento') }}" required>
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-2 col-md-3">
                                <div class="form-group">
                                    <label for="cuil">CUIL</label>
                                    <input type="text" class="form-control" id="cuil" name="cuil" placeholder="XX-XXXXXXXX-X" value="{{ old('cuil') }}">
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-3 col-md-3">
                                <div class="form-group">
                                    <label for="nacimiento">F. Nacimiento</label>
                                    <input type="date" class="form-control" id="nacimiento" name="nacimiento"  value="{{ old('nacimiento') }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-offset-3 col-lg-1 col-md-2">
                                <div class="form-group">
                                    <label for="particular_area">Área</label>
                                    <input type="text" class="form-control" id="particular_area" name="particular_area" placeholder="Área" value="{{ old('particular_area') }}" required>
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-2 col-md-2">
                                <div class="form-group">
                                    <label for="particular">Particular</label>
                                    <input type="text" class="form-control" id="particular" name="particular" placeholder="Particular" value="{{ old('particular') }}" required>
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-1 col-md-2">
                                <div class="form-group">
                                    <label for="celular_area">Área</label>
                                    <input type="text" class="form-control" id="celular_area" name="celular_area" placeholder="Área" value="{{ old('celular_area') }}" required>
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-2 col-md-2">
                                <div class="form-group">
                                    <label for="celular">Celular</label>
                                    <input type="text" class="form-control" id="celular" name="celular" placeholder="Celular" value="{{ old('celular') }}" required>
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-5 col-md-2">
                                <div class="form-group">
                                    <label for="email">E-mail</label>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="email" value="{{ old('email') }}" required>
                                </div>
                            </div>


                        </div>
                        <div class="row">
                            <div class="col-lg-offset-3 col-lg-3 col-md-2">
                                <div class="form-group">
                                    <label for="calle">Calle</label>
                                    <input type="text" class="form-control" id="calle" name="calle" placeholder="Calle" value="{{ old('calle') }}" required>
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-2 col-md-2">
                                <div class="form-group">
                                    <label for="nro">Nro.</label>
                                    <input type="text" class="form-control" id="nro" name="nro" placeholder="Nro." value="{{ old('nro') }}" required>
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-2 col-md-2">
                                <div class="form-group">
                                    <label for="piso">Piso</label>
                                    <input type="text" class="form-control" id="piso" name="piso" placeholder="Piso" value="{{ old('piso') }}">
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-2 col-md-2">
                                <div class="form-group">
                                    <label for="depto">Depto.</label>
                                    <input type="text" class="form-control" id="depto" name="depto" placeholder="Depto." value="{{ old('depto') }}">
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-2 col-md-2">
                                <div class="form-group">
                                    <label for="cp">CP</label>
                                    <input type="text" class="form-control" id="cp" name="cp" placeholder="CP" value="{{ old('cp') }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            @include('includes.select-provincia-localidad')

                        </div>
                        <div class="row">
                            <div class="col-lg-offset-3 col-lg-3 col-md-3">
                                <div class="form-group">
                                    <label for="nacionalidad">Nacionalidad</label>
                                    <input type="text" class="form-control" id="nacionalidad" name="nacionalidad" placeholder="Nacionalidad" value="{{ old('nacionalidad') }}" required>
                                </div>
                            </div>

                            <div class="col-lg-offset-3 col-lg-2 col-md-3">
                                <div class="form-group">
                                    <label for="estado_civil">Estado Civil</label>

                                    <select name="estado_civil" id="estado_civil" class="form-control" required>
                                        <option value="">
                                            Seleccionar...
                                        </option>
                                        @foreach (config('civiles') as $key => $label)
                                            <option value="{{ $key }}" {{ old('estado_civil', $cliente->estado_civil ?? '') == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>


                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-3 col-md-3" id="conyuge-container" style="display: none;">
                                <div class="form-group">
                                    <label for="conyuge">Cónyuge</label>
                                    <input type="text" class="form-control" id="conyuge" name="conyuge" placeholder="Cónyuge" value="{{ old('conyuge') }}" required>
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-2 col-md-3">
                                <div class="form-group">
                                    <label for="llego">Como llegó?</label>

                                    <select name="llego" id="llego" class="form-control" required>
                                        <option value="">
                                            Seleccionar...
                                        </option>
                                        @foreach (config('llego') as $key => $label)
                                            <option value="{{ $key }}" {{ old('llego', $cliente->llego ?? '') == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                        </div>
                        <div class="row">


                            <div class="col-lg-offset-3 col-lg-3 col-md-3">
                                <div class="form-group">
                                    <label for="iva">Condición IVA</label>

                                    <select name="iva" id="iva" class="form-control" required>
                                        <option value="">
                                            Seleccionar...
                                        </option>
                                        @foreach (config('iva') as $key => $label)
                                            <option value="{{ $key }}" {{ old('iva', $cliente->iva ?? '') == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>


                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-4 col-md-3">
                                <div class="form-group">
                                    <label for="ocupacion">Actividad/Ocupación</label>
                                    <input type="text" class="form-control" id="ocupacion" name="ocupacion" placeholder="Actividad/Ocupación" value="{{ old('ocupacion') }}">
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-4 col-md-3">
                                <div class="form-group">
                                    <label for="trabajo">Lugar de trabajo</label>
                                    <input type="text" class="form-control" id="trabajo" name="trabajo" placeholder="Lugar de trabajo" value="{{ old('trabajo') }}">
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
                                        <textarea id="observaciones" name="observaciones" class="form-control" rows="3">
                                            @if (old('observaciones')){{ old('observaciones') }}@endif
                                        </textarea>
                                    </div>
                                </div>

                        </div>

                        <div class="row" style="margin-top: 10px;">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Guardar</button>
                                <a href='{{ route('clientes.index') }}' class="btn btn-warning">Volver</a>
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

    <script src="{{ asset('assets/js/combo-provincia-localidad.js') }}"></script>
    <script src="{{ asset('assets/js/confirm-exit.js') }}"></script>


    <!-- page script -->
    <script>
        $(document).ready(function () {
            $('#cuil').inputmask('99-99999999-9', { placeholder: 'XX-XXXXXXXX-X' });
            $('.js-example-basic-single').select2();
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
