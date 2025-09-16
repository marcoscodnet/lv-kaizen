@extends('layouts.app')

@section('headSection')
    <link rel="stylesheet" href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}">
@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="fs-9 mb-0"><i class="fas fa-cash-register"></i> Apertura de Caja</h5>
        </div>
        <div class="card-body">
            @include('includes.messages')
            <form action="{{ route('cajas.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="sucursal_id">Sucursal:</label>
                            <select name="sucursal_id" id="sucursal_id" class="form-control js-example-basic-single" required>

                                @foreach($sucursals as $sucursalId => $sucursal)
                                    <option value="{{ $sucursalId }}" {{ old('sucursal_id', auth()->user()->sucursal_id) == $sucursalId ? 'selected' : '' }}>{{ $sucursal }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="inicial">Monto Inicial:</label>
                            <input type="number" name="inicial" id="inicial" class="form-control" step="0.01" min="0" required>
                        </div>
                    </div>
                </div>
                <div class="row" style="margin-top: 10px;">
                    <div class="form-group">

                        <button type="submit" class="btn btn-primary">Abrir</button>
                        <a href='{{ route('cajas.index') }}' class="btn btn-warning">Volver</a>
                    </div>
                </div>
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
        });
    </script>
@endsection
