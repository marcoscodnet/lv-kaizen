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

                            <div class="col-lg-offset-3 col-lg-3 col-md-3">
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
                            <div class="col-lg-offset-3 col-lg-5 col-md-3">
                                <div class="form-group">
                                    <label for="descripcion">Descripción</label>
                                    <input type="text" class="form-control" id="descripcion" name="descripcion" placeholder="Descripcion" value="@if (old('descripcion')){{ old('descripcion') }}@else{{ $pieza->descripcion }}@endif" required @cannot('pieza-editar') @cannot('pieza-modificar-descripcion') disabled @endcannot @endcannot>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-offset-3 col-lg-3 col-md-3">
                                <div class="form-group">
                                    <label for="stock_minimo">Stock mínimo</label>
                                    <input type="number" class="form-control" id="stock_minimo" name="stock_minimo" placeholder="Stock mínimo" value="@if (old('stock_minimo')){{ old('stock_minimo') }}@else{{ $pieza->stock_minimo }}@endif" @cannot('pieza-editar') disabled @endcannot>
                                </div>
                            </div>

                            <div class="col-lg-offset-3 col-lg-3 col-md-3">
                                <div class="form-group">
                                    <label for="costo">Costo</label>
                                    <input type="number" step="0.01" class="form-control" id="costo" name="costo" placeholder="Costo" value="@if (old('costo')){{ old('costo') }}@else{{ $pieza->costo }}@endif" @cannot('pieza-editar') disabled @endcannot>
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-3 col-md-3">
                                <div class="form-group">
                                    <label for="precio_minimo">$ mínimo</label>
                                    <input type="number" step="0.01" class="form-control" id="precio_minimo" name="precio_minimo" placeholder="$ mínimo" value="@if (old('precio_minimo')){{ old('precio_minimo') }}@else{{ $pieza->precio_minimo }}@endif" @cannot('pieza-editar') disabled @endcannot>
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
                            <div class="col-lg-offset-3 col-lg-6">
                                <div class="form-group">
                                    <label>Foto de la pieza</label>
                                    <div>
                                        <video id="video" width="320" height="240" autoplay></video>
                                        <canvas id="canvas" width="320" height="240" style="display:none;"></canvas>
                                        <img id="photo" src="{{ $pieza->foto ? asset($pieza->foto) : '' }}"  alt="Foto actual" style="margin-top:10px; max-width:320px; border:1px solid #ccc;">
                                    </div>
                                    <button type="button" id="capture" class="btn btn-info mt-2" @cannot('pieza-editar') disabled @endcannot>📸 Capturar</button>
                                    <input type="hidden" name="foto" id="foto">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-offset-3 col-lg-9 col-md-2">
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

    <script src="{{ asset('assets/js/confirm-exit.js') }}"></script>
    <!-- page script -->
    <script>
        $(document).ready(function () {

            $('.js-example-basic-single').select2({
                language: 'es'});
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
