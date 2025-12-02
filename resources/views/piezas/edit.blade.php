@extends('layouts.app')
@section('headSection')

    <link rel="stylesheet" href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}">

@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-header">
            <div class="row flex-between-end">
                <div class="col-auto align-self-center">
                    <h5 class="mb-0" data-anchor="data-anchor"><i class="fa fa-cogs" aria-hidden="true"></i><span class="ms-2">Editar pieza</span></h5>
                </div>
                <div class="col-auto ms-auto">

                </div>
            </div>
        </div>
        <div class="card-body bg-body-tertiary">
            <form role="form" action="{{ route('piezas.update',$pieza->id) }}" method="post" >
                {{ csrf_field() }}
                {{ method_field('PUT') }}
                <div class="tab-content">
                    <div class="box-body">

                        @include('includes.messages')


                        <div class="row">

                            <div class="col-12 col-lg-3">
                                <div class="form-group">
                                    <label for="codigo">Código</label>
                                    <input type="text" class="form-control" id="codigo" name="codigo" placeholder="Codigo" value="@if (old('codigo')){{ old('codigo') }}@else{{ $pieza->codigo }}@endif" required @cannot('pieza-editar') disabled @endcannot>
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <label for="tipo_pieza_id">Tipo</label>
                                <select id="tipo_pieza_id" name="tipo_pieza_id" class="form-control js-example-basic-single" required @cannot('pieza-editar') disabled @endcannot>
                                    <option value="">Seleccione...</option>
                                    @foreach($tipos as $tipoId => $tipo)
                                        <option value="{{ $tipoId }}" {{ old('tipo_pieza_id',$pieza->tipo_pieza_id) == $tipoId ? 'selected' : '' }}>
                                            {{ $tipo }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-lg-5">
                                <div class="form-group">
                                    <label for="descripcion">Descripción</label>
                                    <input type="text" class="form-control" id="descripcion" name="descripcion" placeholder="Descripcion" value="@if (old('descripcion')){{ old('descripcion') }}@else{{ $pieza->descripcion }}@endif" required @cannot('pieza-editar') @cannot('pieza-modificar-descripcion') disabled @endcannot @endcannot>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-lg-3">
                                <div class="form-group">
                                    <label for="stock_minimo">Stock mínimo</label>
                                    <input type="number" class="form-control" id="stock_minimo" name="stock_minimo" placeholder="Stock mínimo" value="@if (old('stock_minimo')){{ old('stock_minimo') }}@else{{ $pieza->stock_minimo }}@endif" @cannot('pieza-editar') disabled @endcannot>
                                </div>
                            </div>

                            <div class="col-12 col-lg-3">
                                <div class="form-group">
                                    <label for="costo">Costo</label>
                                    <input type="number" step="0.01" class="form-control" id="costo" name="costo" placeholder="Costo" value="@if (old('costo')){{ old('costo') }}@else{{ $pieza->costo }}@endif" @cannot('pieza-editar') disabled @endcannot>
                                </div>
                            </div>
                            <div class="col-12 col-lg-3">
                                <div class="form-group">
                                    <label for="precio_minimo">$ mínimo</label>
                                    <input type="number" step="0.01" class="form-control" id="precio_minimo" name="precio_minimo" placeholder="$ mínimo" value="@if (old('precio_minimo')){{ old('precio_minimo') }}@else{{ $pieza->precio_minimo }}@endif" @cannot('pieza-editar') disabled @endcannot>
                                </div>
                            </div>


                        </div>

                        <div class="row">
                            <div class="form-group">
                                <table class="table">
                                    <thead>
                                    <th>Sucursal</th>
                                    <th>Ubicación</th>
                                    <th>
                                        @can('pieza-editar')
                                            <a href="#" class="addRowUbicacion btn btn-success btn-sm">
                                                <i class="fa fa-plus"></i>
                                            </a>
                                        @endcan
                                    </th>
                                    </thead>

                                    <tbody id="cuerpoUbicaciones">
                                    {{-- Las filas se crean desde JavaScript --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-lg-9">
                                <div class="form-group">

                                    <label for="observaciones" class="col-md-12">Observaciones</label>
                                </div>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-12 col-lg-6">
                                <div class="form-group">
                                    <label>Foto de la pieza</label>
                                    <div>
                                        <video id="video" width="320" height="240" autoplay></video>
                                        <canvas id="canvas" width="320" height="240" style="display:none;"></canvas>
                                        <img id="photo" src="{{ $pieza->foto ? asset('images/'.$pieza->foto) : '' }}"  alt="Foto actual" style="margin-top:10px; max-width:320px; border:1px solid #ccc;">
                                    </div>
                                    <button type="button" id="capture" class="btn btn-info mt-2" @cannot('pieza-editar') disabled @endcannot>📸 Capturar</button>
                                    <input type="hidden" name="foto" id="foto">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-lg-9">
                                <div class="form-group">


                                    <!-- Fila 2: Área de texto -->

                                    <textarea id="observaciones" name="observaciones" class="form-control" rows="3" @cannot('pieza-editar') disabled @endcannot>@if (old('observaciones')){{ old('observaciones') }}@else{{ $pieza->observaciones }}@endif</textarea>

                                </div>
                            </div>

                        </div>

                        <div class="row" style="margin-top: 10px;">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Guardar</button>
                                <a href='{{ route('piezas.index') }}' class="btn btn-warning">Volver</a>
                            </div>
                        </div>
                    </div>
                </div>



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

    <script>
        var ubicacionesActuales = @json(
            $pieza->ubicacions->map(function($u){
                return [
                    'sucursal_id' => $u->sucursal_id,   // ✔ viene de la ubicación
                    'ubicacion_id' => $u->id            // ✔ ID de la ubicación
                ];
            })
        );

        // Correcto: select completo construido una sola vez
        var selectSucursalHTML = `
<select name="sucursal_id[]" class="form-control selectSucursal">
    <option value="">Seleccionar...</option>
    @foreach($sucursales as $s)
        <option value="{{ $s->id }}">{{ $s->nombre }}</option>
    @endforeach
        </select>`;

        // Correcto
        var selectUbicacionHTML = `
<select name="ubicacion_id[]" class="form-control js-example-basic-single selectUbicacion">
    <option value="">Seleccionar...</option>
</select>`;

        var ubicacionUrl = "{{ url('ubicaciones') }}";
    </script>



    <script src="{{ asset('assets/js/combo-sucursal-ubicacion.js') }}"></script>

    <script src="{{ asset('assets/js/confirm-exit.js') }}"></script>
    <!-- page script -->
    <script>
        $(document).ready(function () {

            $('.js-example-basic-single').select2({ language: 'es' });

            // ➤ AGREGAR FILA
            function addRowUbicacion(sucursal_id = '', ubicacion_id = '') {

                var tr = `<tr>
        <td style="width:40%;">${selectSucursalHTML}</td>
        <td style="width:40%;">${selectUbicacionHTML}</td>
        <td>
            @can('pieza-editar')
                <a href="#" class="btn btn-danger btn-sm removeUbicacion">
                    <i class="fa fa-times text-white"></i>
                </a>
            @endcan
                </td>
            </tr>`;

                $('#cuerpoUbicaciones').append(tr);

                let row = $('#cuerpoUbicaciones tr').last();
                let sucSelect = row.find('.selectSucursal');
                let ubiSelect = row.find('.selectUbicacion');

                sucSelect.select2({ language: 'es' });
                ubiSelect.select2({ language: 'es' });

                // Seleccionamos sucursal
                sucSelect.val(sucursal_id).trigger('change');

                // ⚠️ SI TIENE SUCURSAL → CARGO UBICACIONES Y SOLO LUEGO SELECCIONO LA UBICACIÓN
                if (sucursal_id) {
                    $.ajax({
                        url: ubicacionUrl + '/' + sucursal_id,
                        method: 'GET',
                        success: function(data) {

                            ubiSelect.empty()
                                .append('<option value="">Seleccionar...</option>');

                            data.forEach(function(ubi) {
                                ubiSelect.append(`<option value="${ubi.id}">${ubi.nombre}</option>`);
                            });

                            // 👇👇👇 CORRECCIÓN IMPORTANTE 👇👇👇
                            // Recién cuando el AJAX terminó → selecciono ubicación
                            setTimeout(() => {
                                ubiSelect.val(ubicacion_id).trigger('change');
                            }, 50);  // pequeño delay para que Select2 procese la lista
                        }
                    });
                }
            }



            // ➤ Botón "+"
            $('body').on('click', '.addRowUbicacion', function(e){
                e.preventDefault();
                addRowUbicacion();
            });

            // ➤ Eliminar fila
            $('body').on('click', '.removeUbicacion', function(e){
                if (confirm('¿Estás seguro?')) {
                    $(this).closest('tr').remove();
                }
            });

            // ➤ Cambio de sucursal → cargar ubicaciones
            $('body').on('change', '.selectSucursal', function () {
                let row = $(this).closest('tr');
                let sucursalId = $(this).val();
                let ubicacionSelect = row.find('.selectUbicacion');

                ubicacionSelect.empty().append('<option value="">Cargando...</option>');

                if (sucursalId) {
                    $.ajax({
                        url: ubicacionUrl + '/' + sucursalId,
                        method: 'GET',
                        success: function(data) {
                            ubicacionSelect.empty();
                            ubicacionSelect.append('<option value="">Seleccionar...</option>');
                            data.forEach(function(ubi) {
                                ubicacionSelect.append(`<option value="${ubi.id}">${ubi.nombre}</option>`);
                            });
                        }
                    });
                }
            });

            // ➤ Precargar ubicaciones existentes
            if (ubicacionesActuales.length > 0) {
                ubicacionesActuales.forEach(u => {
                    addRowUbicacion(u.sucursal_id, u.ubicacion_id);
                });
            } else {
                addRowUbicacion(); // una fila vacía
            }

        });
    </script>


@endsection
