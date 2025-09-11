@extends('layouts.app')
@section('headSection')

    <link rel="stylesheet" href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}">

@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-header">
            <div class="row flex-between-end">
                <div class="col-auto align-self-center">
                    <h5 class="mb-0" data-anchor="data-anchor"><i class="fa fa-toolbox" aria-hidden="true"></i><span class="ms-2">Crear stock pieza</span></h5>
                </div>
                <div class="col-auto ms-auto">

                </div>
            </div>
        </div>
        <div class="card-body bg-body-tertiary">
            <form role="form" action="{{ route('stockPiezas.store') }}" method="post" >
                {{ csrf_field() }}
                <div class="tab-content">
                    <div class="box-body">

                        @include('includes.messages')

                        <div class="row">
                            <div class="col-lg-9">
                                <div class="form-group d-flex align-items-end gap-2">
                                    <div class="flex-grow-1">
                                        <label for="pieza_id">Pieza</label>
                                        <select name="pieza_id" id="pieza_id" class="form-control js-example-basic-single" required>

                                            @foreach($piezas as $piezaId => $pieza)
                                                <option value="{{ $piezaId }}" {{ old('pieza_id') == $piezaId ? 'selected' : '' }}>{{ $pieza }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button type="button" class="btn btn-success" id="btnNuevaPieza" data-bs-toggle="modal" data-bs-target="#nuevaPiezaModal">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </div>
                            </div>



                        </div>

                        <div class="row">
                            <div class="col-lg-offset-3 col-lg-3 col-md-3">
                                <div class="form-group">
                                    <label for="cantidad">Cantidad</label>
                                    <input type="number" class="form-control" id="cantidad" name="cantidad" placeholder="Cantidad" value="{{ old('cantidad') }}">
                                </div>
                            </div>

                            <div class="col-lg-offset-3 col-lg-3 col-md-3">
                                <div class="form-group">
                                    <label for="costo">Costo</label>
                                    <input type="number" step="0.01" class="form-control" id="costo" name="costo" placeholder="Costo" value="{{ old('costo') }}">
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-3 col-md-3">
                                <div class="form-group">
                                    <label for="precio_minimo">$ mínimo</label>
                                    <input type="number" step="0.01" class="form-control" id="precio_minimo" name="precio_minimo" placeholder="$ mínimo" value="{{ old('precio_minimo') }}">
                                </div>
                            </div>


                        </div>

                        <div class="row">

                            <div class="col-lg-offset-3 col-lg-4 col-md-2">
                                <div class="form-group">
                                    <label for="sucursal_id">Sucursal</label>
                                    <select name="sucursal_id" class="form-control js-example-basic-single" required>

                                        @foreach($sucursals as $sucursalId => $sucursal)
                                            <option value="{{ $sucursalId }}" {{ old('sucursal_id', auth()->user()->sucursal_id) == $sucursalId ? 'selected' : '' }}>{{ $sucursal }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-4 col-md-2">
                                <div class="form-group">
                                    <label for="proveedor">Proveedor</label>
                                    <select name="proveedor" id="proveedor" class="form-control">
                                        <option value="">
                                            Seleccionar...
                                        </option>
                                        @foreach (config('proveedores') as $key => $label)
                                            <option value="{{ $key }}" {{ old('proveedor', $stockPieza->proveedor ?? '') == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        @php
                            $hoy = date('Y-m-d');
                            $hoy_formateado = date('d/m/Y');
                        @endphp
                        <div class="row">

                            <div class="col-lg-offset-3 col-lg-4 col-md-2">
                                <div class="form-group">
                                    <label for="ingreso_visible">F. Ingreso</label>
                                    <input type="text" class="form-control" id="ingreso_visible" value="{{ $hoy_formateado }}" readonly>
                                    <input type="hidden" name="ingreso" value="{{ $hoy }}">
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-4 col-md-2">
                                <div class="form-group">
                                    <div class="form-group">
                                        <label for="remito">Remito</label>
                                        <input type="text" class="form-control" id="remito" name="remito" placeholder="Remito" value="{{ old('remito') }}" >
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row" style="margin-top: 10px;">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Guardar</button>
                                <a href='{{ route('stockPiezas.index') }}' class="btn btn-warning">Volver</a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- Modal Nuevo Cliente -->
    <div class="modal fade" id="nuevaPiezaModal" tabindex="-1" aria-labelledby="nuevaPiezaLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form id="formNuevaPieza">
                @csrf
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="nuevoClienteLabel">Nueva Pieza</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row">

                            <div class="col-lg-offset-3 col-lg-3 col-md-3">
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
                            <div class="col-lg-offset-3 col-lg-5 col-md-3">
                                <div class="form-group">
                                    <label for="descripcion">Descripción</label>
                                    <input type="text" class="form-control" id="descripcion" name="descripcion" placeholder="Descripcion" value="{{ old('descripcion') }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-offset-3 col-lg-3 col-md-3">
                                <div class="form-group">
                                    <label for="stock_minimo">Stock mínimo</label>
                                    <input type="number" class="form-control" id="stock_minimo" name="stock_minimo" placeholder="Stock mínimo" value="{{ old('stock_minimo') }}">
                                </div>
                            </div>

                            <div class="col-lg-offset-3 col-lg-3 col-md-3">
                                <div class="form-group">
                                    <label for="costo">Costo</label>
                                    <input type="number" step="0.01" class="form-control" id="costo" name="costo" placeholder="Costo" value="{{ old('costo') }}">
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-3 col-md-3">
                                <div class="form-group">
                                    <label for="precio_minimo">$ mínimo</label>
                                    <input type="number" step="0.01" class="form-control" id="precio_minimo" name="precio_minimo" placeholder="$ mínimo" value="{{ old('precio_minimo') }}">
                                </div>
                            </div>


                        </div>

                        <div class="row">
                            <div class="col-lg-offset-3 col-lg-6">
                                <div class="form-group">
                                    <label>Foto de la pieza</label>
                                    <div>
                                        <video id="video" width="320" height="240" autoplay></video>
                                        <canvas id="canvas" width="320" height="240" style="display:none;"></canvas>
                                        <img id="photo" src="" alt="Foto de la pieza" style="margin-top:10px; max-width:320px; border:1px solid #ccc;">
                                    </div>
                                    <button type="button" id="capture" class="btn btn-info mt-2">📸 Capturar</button>
                                    <input type="hidden" name="foto" id="foto">
                                </div>
                            </div>
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

                                    <textarea id="observaciones" name="observaciones" class="form-control" rows="3">
                                        @if (old('observaciones')){{ old('observaciones') }}@endif
                                    </textarea>

                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer">

                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <button type="button" class="btn btn-warning" data-bs-dismiss="modal">Cancelar</button>
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

    <script src="{{ asset('assets/js/confirm-exit.js') }}"></script>
    <!-- page script -->
    <script>
        const piezaUrlTemplate = @json(route('api.piezas.getDatos', ['id' => 'PIEZA_ID']));
        $(document).ready(function () {

            $('.js-example-basic-single').select2({
                language: 'es'});
            $('#pieza_id').on('change', function () {
                var piezaId = $(this).val();

                if (piezaId) {
                    let url = piezaUrlTemplate.replace('PIEZA_ID', piezaId);

                        $.ajax({
                        url: url,
                        type: 'GET',
                        success: function (data) {
                            $('#costo').val(data.costo);
                            $('#precio_minimo').val(data.precio_minimo);
                        },
                        error: function () {
                            $('#costo').val('');
                            $('#precio_minimo').val('');
                        }
                    });
                }
            });
        });
        $('#formNuevaPieza').submit(function(e){
            e.preventDefault();
            $.ajax({
                url: '{{ route("piezas.ajaxStore") }}', // <-- nueva ruta
                method: 'POST',
                data: $(this).serialize(),
                success: function(data){
                    var text = (data.codigo ?? '') + ' - ' + (data.descripcion ?? '');
                    var $select = $('#pieza_id');

                    // Agregar la nueva opción
                    var newOption = new Option(text, data.id, false, false);
                    $select.append(newOption);

                    // Seleccionar la nueva opción
                    $select.val(data.id).trigger('change');

                    // Cerrar modal y limpiar formulario
                    $('#nuevaPiezaModal').modal('hide');
                    $('#formNuevaPieza')[0].reset();
                },
                error: function(err){
                    alert('Error al guardar la pieza');
                }
            });
        });
        $('#nuevaPiezaModal').on('shown.bs.modal', function () {
            $('#tipo_pieza_id').select2({
                dropdownParent: $('#nuevaPiezaModal'),
                language: 'es'
            });
        });


        document.addEventListener('DOMContentLoaded', function() {
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            const photo = document.getElementById('photo');
            const capture = document.getElementById('capture');
            const fotoInput = document.getElementById('foto');

            // Solo intentar acceder a la cámara si el navegador soporta getUserMedia
            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                navigator.mediaDevices.getUserMedia({ video: true })
                    .then(stream => {
                        video.srcObject = stream;
                        video.play();
                    })
                    .catch(err => {
                        console.error("No se pudo acceder a la cámara: ", err);
                    });
            } else {
                console.warn("getUserMedia no es soportado en este navegador.");
            }

            capture.addEventListener('click', () => {
                const context = canvas.getContext('2d');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                context.drawImage(video, 0, 0, canvas.width, canvas.height);
                const dataURL = canvas.toDataURL('image/png');
                photo.src = dataURL;
                fotoInput.value = dataURL; // Guardar base64 para enviar al backend
            });
        });
    </script>


@endsection
