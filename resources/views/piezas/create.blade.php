@extends('layouts.app')
@section('headSection')

    <link rel="stylesheet" href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}">

@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-header">
            <div class="row flex-between-end">
                <div class="col-auto align-self-center">
                    <h5 class="mb-0" data-anchor="data-anchor"><i class="fa fa-cogs" aria-hidden="true"></i><span class="ms-2">Crear pieza</span></h5>
                </div>
                <div class="col-auto ms-auto">

                </div>
            </div>
        </div>
        <div class="card-body bg-body-tertiary">
            <form role="form" action="{{ route('piezas.store') }}" method="post" >
                {{ csrf_field() }}
                <div class="tab-content">
                    <div class="box-body">

                        @include('includes.messages')
                        <div class="row">

                            <div class="col-12 col-lg-3">
                                <div class="form-group">
                                    <label for="codigo">Código</label>
                                    <input type="text" class="form-control" id="codigo" name="codigo" placeholder="Codigo" value="{{ old('codigo') }}" required>
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <label for="tipo_pieza_id">Tipo</label>
                                <select id="tipo_pieza_id" name="tipo_pieza_id" class="form-control js-example-basic-single" required>
                                    <option value="">Seleccione...</option>
                                    @foreach($tipos as $tipoId => $tipo)
                                        <option value="{{ $tipoId }}" {{ old('tipo_pieza_id') == $tipoId ? 'selected' : '' }}>
                                            {{ $tipo }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-lg-5">
                                <div class="form-group">
                                    <label for="descripcion">Descripción</label>
                                    <input type="text" class="form-control" id="descripcion" name="descripcion" placeholder="Descripcion" value="{{ old('descripcion') }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-lg-3">
                                <div class="form-group">
                                    <label for="stock_minimo">Stock mínimo</label>
                                    <input type="number" class="form-control" id="stock_minimo" name="stock_minimo" placeholder="Stock mínimo" value="{{ old('stock_minimo') }}">
                                </div>
                            </div>

                            <div class="col-12 col-lg-3">
                                <div class="form-group">
                                    <label for="costo">Costo</label>
                                    <input type="number" step="0.01" class="form-control" id="costo" name="costo" placeholder="Costo" value="{{ old('costo') }}">
                                </div>
                            </div>
                            <div class="col-12 col-lg-3">
                                <div class="form-group">
                                    <label for="precio_minimo">$ mínimo</label>
                                    <input type="number" step="0.01" class="form-control" id="precio_minimo" name="precio_minimo" placeholder="$ mínimo" value="{{ old('precio_minimo') }}">
                                </div>
                            </div>


                        </div>
                        <div class="form-group">
                            <table class="table">
                                <thead>
                                <th>Sucursal</th>
                                <th>Ubicación</th>
                                <th>
                                    <a href="#" class="addRowUbicacion btn btn-success btn-sm">
                                        <i class="fa fa-plus"></i>
                                    </a>
                                </th>
                                </thead>

                                <tbody id="cuerpoUbicaciones">

                                </tbody>
                            </table>
                        </div>

                        <div class="row mt-3">
                            <div class="col-lg-6">
                                <label for="foto">Foto de la pieza</label>
                                <div class="d-flex flex-column align-items-start">
                                    <video id="video" width="320" height="240" autoplay class="border"></video>
                                    <canvas id="canvas" width="320" height="240" style="display:none;"></canvas>

                                    <div class="mt-2">
                                        <button type="button" id="capture" class="btn btn-sm btn-primary">📸 Capturar</button>
                                    </div>

                                    <img id="photo" src="" alt="Foto tomada" class="mt-2 border" style="display:none; width: 320px; height: 240px;">
                                </div>

                                {{-- Campo oculto para enviar la foto al servidor --}}
                                <input type="hidden" name="foto" id="foto">
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
                                <a href='{{ route('piezas.index') }}' class="btn btn-warning">Volver</a>
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

    <script>
        var ubicacionUrl = "{{ url('ubicaciones') }}";
    </script>

    <script src="{{ asset('assets/js/combo-sucursal-ubicacion.js') }}"></script>

    <script src="{{ asset('assets/js/confirm-exit.js') }}"></script>
    <!-- page script -->
    <script>
        $(document).ready(function () {

            $('.js-example-basic-single').select2({
                language: 'es'});
            // Renderizar el select de sucursales
            var selectSucursal = `{!! '<select name="sucursal_id[]" class="form-control js-example-basic-single selectSucursal">' !!}
            @foreach($sucursales as $s)
            {!! '<option value="'.$s->id.'">'.$s->nombre.'</option>' !!}
            @endforeach
            {!! '</select>' !!}`;

            // Select de ubicación vacío
            var selectUbicacion = `<select name="ubicacion_id[]" class="form-control js-example-basic-single selectUbicacion">
        <option value="">Seleccionar...</option>
    </select>`;

            // Agregar fila
            $('.addRowUbicacion').on('click', function(e){
                e.preventDefault();
                addRowUbicacion();
            });

            function addRowUbicacion() {
                var tr = `<tr>
            <td style="width:40%;">${selectSucursal}</td>
            <td style="width:40%;">${selectUbicacion}</td>
            <td><a href="#" class="btn btn-danger btn-sm removeUbicacion"><i class="fa fa-times text-white"></i></a></td>
        </tr>`;

                $('#cuerpoUbicaciones').append(tr);

                $('.js-example-basic-single').select2({ language: 'es' });

                // 🔥 Forzar a que NO quede seleccionada la primera sucursal
                $('.selectSucursal').last().val('').trigger('change');
            }

            // Eliminar fila
            $('body').on('click', '.removeUbicacion', function(e){
                var confirmDelete = confirm('¿Estás seguro?');
                if (confirmDelete) {
                    $(this).closest('tr').remove();
                }
            });

            // Cargar ubicaciones al seleccionar sucursal
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
                        },
                        error: function () {
                            ubicacionSelect.empty().append('<option value="">Error al cargar</option>');
                        }
                    });
                }
            });
        });

        document.addEventListener("DOMContentLoaded", function() {
            const video = document.getElementById("video");
            const canvas = document.getElementById("canvas");
            const captureButton = document.getElementById("capture");
            const photo = document.getElementById("photo");
            const fotoInput = document.getElementById("foto");

            // Solicitar acceso a la cámara
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(stream => {
                    const video = document.getElementById('video');
                    video.srcObject = stream;
                })
                .catch(err => {
                    alert("No se pudo acceder a la cámara: " + err.name + " - " + err.message);
                    console.error("Error cámara:", err);
                });

            // Capturar imagen
            captureButton.addEventListener("click", function() {
                const context = canvas.getContext("2d");
                context.drawImage(video, 0, 0, canvas.width, canvas.height);

                // Convertir a Base64
                const dataUrl = canvas.toDataURL("image/png");

                // Mostrar vista previa
                photo.setAttribute("src", dataUrl);
                photo.style.display = "block";

                // Guardar en input hidden
                fotoInput.value = dataUrl;
            });
        });
    </script>
@endsection
