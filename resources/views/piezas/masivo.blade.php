@extends('layouts.app')

@section('headSection') <link rel="stylesheet" href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}">
@endsection

@section('content')

    <div class="card mb-3">
        <div class="card-header">
            <div class="row flex-between-end">
                <div class="col-auto align-self-center">
                    <h5 class="mb-0" data-anchor="data-anchor"><i class="fa fa-cogs" aria-hidden="true"></i><span class="ms-2">Carga masiva de piezas</span></h5>
                </div>
                <div class="col-auto ms-auto">

                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Video de la cámara -->
            <div class="mb-3">
                <video id="video" width="320" height="240" autoplay class="border"></video>
                <canvas id="canvas" width="320" height="240" style="display:none;"></canvas>
                <div class="mt-2">
                    <button type="button" id="capture" class="btn btn-sm btn-primary">📸 Capturar</button>
                </div>
            </div>


            <form action="{{ route('piezas.storeMasivo') }}" method="POST">
                @csrf
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>Código</th>
                        <th>Tipo</th>
                        <th>Descripción</th>
                        <th>Foto</th>
                        <th><button type="button" id="agregar-fila" class="btn btn-success"><i class="fa fa-plus"></i></button></th>
                    </tr>
                    </thead>
                    <tbody id="piezas-masivo-body">
                    @for($i = 0; $i < 5; $i++)
                        <tr>
                            <td><input type="text" name="codigo[]" class="form-control" required></td>
                            <td>
                                <select name="tipo_pieza_id[]" class="form-control js-example-basic-single" required>
                                    <option value="">Seleccione...</option>
                                    @foreach($tipos as $id => $tipo)
                                        <option value="{{ $id }}">{{ $tipo }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="text" name="descripcion[]" class="form-control" required></td>
                            <td>
                                <img src="" class="foto-preview border" style="width:80px; height:60px; display:none;">
                                <input type="hidden" name="foto[]" class="foto-input">
                                <button type="button" class="btn btn-sm btn-secondary asignar-foto">Asignar foto</button>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-danger eliminar-fila"><i class="fa fa-times text-white"></i></button>
                            </td>
                        </tr>
                    @endfor
                    </tbody>
                </table>

                <button type="submit" class="btn btn-primary">Guardar</button>
                <a href='{{ route('piezas.index') }}' class="btn btn-warning">Volver</a>
            </form>
        </div>


    </div>
@endsection

@section('footerSection')

    <script src="{{ asset('bower_components/jquery/dist/jquery.min.js') }}"></script>

    <script src="{{ asset('bower_components/select2/dist/js/select2.min.js') }}"></script>

    <script src="{{ asset('bower_components/select2/dist/js/i18n/es.js') }}"></script>

    <script>
        $(document).ready(function() {
            $('.js-example-basic-single').select2({ language: 'es' });

            $('#agregar-fila').click(function() {
                let row = `<tr>
            <td><input type="text" name="codigo[]" class="form-control" required></td>
            <td>
                <select name="tipo_pieza_id[]" class="form-control js-example-basic-single" required>
                    <option value="">Seleccione...</option>
                    @foreach($tipos as $id => $tipo)
                <option value="{{ $id }}">{{ $tipo }}</option>
                    @endforeach
                </select>
            </td>
            <td><input type="text" name="descripcion[]" class="form-control" required></td>
            <td>
                <img src="" class="foto-preview border" style="width:80px; height:60px; display:none;">
                <input type="hidden" name="foto[]" class="foto-input">
                <button type="button" class="btn btn-sm btn-secondary asignar-foto">Asignar foto</button>
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger eliminar-fila"><i class="fa fa-times text-white"></i></button>
            </td>
        </tr>`;
                $('#piezas-masivo-body').append(row);
                $('.js-example-basic-single').select2({ language: 'es' });
            });

            // Eliminar fila con confirmación
            $(document).on('click', '.eliminar-fila', function() {
                if(confirm("¿Está seguro que desea eliminar esta fila?")) {
                    $(this).closest('tr').remove();
                }
            });

            // Configurar cámara
            const video = document.getElementById("video");
            const canvas = document.getElementById("canvas");

            // Solicitar acceso a la cámara en alta calidad
            navigator.mediaDevices.getUserMedia({
                video: {
                    width: { ideal: 1920 },
                    height: { ideal: 1080 },
                    aspectRatio: 1.7777777778, // 16:9
                    frameRate: { ideal: 30 }
                },
                audio: false
            })
                .then(stream => {

                    const video = document.getElementById('video');

                    video.srcObject = stream;

                    // IMPORTANTE: esperar a que el video cargue
                    video.onloadedmetadata = () => {
                        video.play();
                        console.log("Resolución real:", video.videoWidth, video.videoHeight);
                    };

                })
                .catch(err => {

                    alert("No se pudo acceder a la cámara:\n" + err.name + "\n" + err.message);
                    console.error("Error cámara:", err);

                });

            let capturedData = '';

            $('#capture').click(function() {
                const context = canvas.getContext('2d');
                context.drawImage(video, 0, 0, canvas.width, canvas.height);
                capturedData = canvas.toDataURL('image/png');
                alert("Foto capturada. Ahora haz clic en 'Asignar foto' en la fila deseada.");
            });

            // Asignar foto a fila
            $(document).on('click', '.asignar-foto', function() {
                if (!capturedData) {
                    alert("Primero captura la foto con el botón 📸 Capturar");
                    return;
                }
                const row = $(this).closest('tr');
                row.find('.foto-preview').attr('src', capturedData).show();
                row.find('.foto-input').val(capturedData);
            });
        });
    </script>

@endsection

