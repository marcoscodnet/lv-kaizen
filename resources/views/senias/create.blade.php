@extends('layouts.app')
@section('headSection')
    <link rel="stylesheet" href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/select2-bootstrap-5-theme/select2-bootstrap-5-theme.min.css') }}">
@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-hand-holding-usd"></i><span class="ms-2">Registrar seña</span></h5>
            <p class="text-muted small mb-0 mt-1">
                Lo que se paga en efectivo entra a la caja de hoy. Transferencias, tarjetas y demás formas que requieren
                autorización van a Auditoría: la seña recién se puede aplicar a una venta cuando esté autorizada, y vale lo acreditado.
            </p>
        </div>
        <div class="card-body bg-body-tertiary">
            <form id="formSenia" role="form" action="{{ route('senias.store') }}" method="post" enctype="multipart/form-data">
                {{ csrf_field() }}
                @include('includes.messages')

                <div class="row">
                    <div class="col-12 col-lg-6">
                        <div class="form-group">
                            <label for="cliente_id">Cliente</label>
                            <select name="cliente_id" id="cliente_id" class="form-control" required>
                                @if(old('cliente_id'))
                                    <option value="{{ old('cliente_id') }}" selected>{{ old('cliente_nombre', '') }}</option>
                                @endif
                            </select>
                            <small class="text-muted">Si el cliente no existe, dalo de alta primero en Clientes.</small>
                        </div>
                    </div>
                    <div class="col-12 col-lg-3">
                        <div class="form-group">
                            <label for="sucursal_id">Sucursal</label>
                            @if($sucursals->count() > 1)
                                <select name="sucursal_id" id="sucursal_id" class="form-control" required>
                                    @foreach($sucursals as $id => $nombre)
                                        <option value="{{ $id }}" {{ (string) old('sucursal_id', auth()->user()->sucursal_id) === (string) $id ? 'selected' : '' }}>{{ $nombre }}</option>
                                    @endforeach
                                </select>
                            @else
                                <input type="text" class="form-control" value="{{ $sucursals->first() ?? 'Sin sucursal asignada' }}" readonly>
                            @endif
                        </div>
                    </div>
                    <div class="col-12 col-lg-9">
                        <div class="form-group">
                            <label for="concepto_senia">¿Qué seña?</label>
                            <input type="text" name="concepto_senia" id="concepto_senia" class="form-control" maxlength="255"
                                   placeholder="Ej.: Moto Honda Wave 110 roja / Cubierta trasera / Service 10.000 km"
                                   value="{{ old('concepto_senia') }}">
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header bg-secondary text-white py-2"><h6 class="mb-0 text-white">Forma de pago de la seña</h6></div>
                    <div class="card-body">
                        @include('includes.cobro', [
                            'entidads'    => $entidads,
                            'formaActual' => 'Contado',
                        ])
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Registrar seña</button>
                    <a href="{{ route('senias.index') }}" class="btn btn-warning">Volver</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('footerSection')
    <script src="{{ asset('bower_components/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('bower_components/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('bower_components/select2/dist/js/select2.min.js') }}"></script>
    <script src="{{ asset('bower_components/select2/dist/js/i18n/es.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/autonumeric@4.10.5/dist/autoNumeric.min.js"></script>
    <script>
        var entidadsData = {!! json_encode($entidads->map(function($e) {
            return [
                'id' => $e->id,
                'nombre' => $e->nombre,
                'forma' => $e->forma,
                'autorizacion' => $e->autorizacion,
                'tangible' => $e->tangible,
            ];
        })) !!};
    </script>
    <script src="{{ asset('assets/js/cobro.js') }}"></script>
    <script>
        $(document).ready(function () {
            $('#cliente_id').select2({
                minimumInputLength: 3,
                language: 'es',
                theme: 'bootstrap-5',
                ajax: {
                    url: '{{ route("cliente.search") }}',
                    type: 'get',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) { return { search: params.term }; },
                    processResults: function (response) { return { results: response }; },
                    cache: true
                }
            });

            $('#forma').on('change', function () {
                $(document).trigger('forma:changed', [$(this).val()]);
            });
            $(document).trigger('forma:changed', [$('#forma').val()]);

            $('#formSenia').on('submit', function (e) {
                if ($('#cuerpoPago .pago-item').length === 0) {
                    e.preventDefault();
                    alert('Cargá al menos una forma de pago.');
                }
            });
        });
    </script>
@endsection
