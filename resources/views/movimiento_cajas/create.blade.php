@extends('layouts.app')

@section('headSection')
    <link rel="stylesheet" href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}">
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-plus"></i> Registrar Movimiento - Caja #{{ $caja->id }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('movimiento_cajas.store') }}" method="POST">
                @csrf
                <input type="hidden" name="caja_id" value="{{ $caja->id }}">

                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label for="concepto_id" class="form-label">Concepto</label>
                        <select name="concepto_id" id="concepto_id" class="form-control select2" required>
                            <option value="">Seleccione</option>
                            @foreach($conceptos as $concepto)
                                <option value="{{ $concepto->id }}">{{ $concepto->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="medio_id" class="form-label">Medio de Pago</label>
                        <select name="medio_id" id="medio_id" class="form-control select2">
                            <option value="">Seleccione</option>
                            @foreach($medios as $medio)
                                <option value="{{ $medio->id }}">{{ $medio->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>

                <div class="row g-3 mt-2">
                    <div class="col-12 col-md-2">
                        <label for="tipo" class="form-label">Tipo</label>
                        <select name="tipo" id="tipo" class="form-control" required>
                            <option value="ingreso">Ingreso</option>
                            <option value="egreso">Egreso</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-2">
                        <label for="monto" class="form-label">Monto</label>
                        <input type="number" name="monto" id="monto" class="form-control" step="0.01" required>
                    </div>
                    <div class="col-12 col-md-2">
                        <label for="acreditado" class="form-label">Acreditado</label>
                        <select name="acreditado" id="acreditado" class="form-control">
                            <option value="1">Sí</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-2">
                        <label for="referencia" class="form-label">Referencia</label>
                        <input type="text" name="referencia" id="referencia" class="form-control">
                    </div>
                </div>

                <div class="mt-3 d-flex flex-column flex-md-row gap-2">

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <a href='{{ route('cajas.show', $caja->id) }}' class="btn btn-warning">Volver</a>
                    </div>
                </div>

            </form>
        </div>
    </div>
@endsection

@section('footerSection')
    <script src="{{ asset('bower_components/select2/dist/js/select2.full.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({ width: '100%' });
        });
    </script>
@endsection
