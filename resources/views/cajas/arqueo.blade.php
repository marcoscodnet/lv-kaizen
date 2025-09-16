@extends('layouts.app')

@section('headSection')
    <link rel="stylesheet" href="{{ asset('bower_components/datatables.net-bs/css/dataTables.bootstrap.css') }}">
    <link rel="stylesheet" href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}">
@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5><i class="fas fa-chart-line"></i> Arqueo - Caja #{{ $caja->id }} - {{ ucfirst($caja->estado) }}</h5>
            <div>
                @if($caja->estado === 'Abierta')
                    <form action="{{ route('cajas.cerrar', $caja->id) }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm"
                                onclick="return confirm('¿Está seguro que desea cerrar la caja?')">
                            Cerrar Caja
                        </button>
                    </form>
                @endif
                    {{-- Botones PDF y Excel --}}
                    <a href="{{ route('cajas.arqueo.export.pdf', $caja->id) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-file-pdf"></i> PDF
                    </a>
                    <a href="{{ route('cajas.arqueo.export.excel', $caja->id) }}" class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel"></i> Excel
                    </a>
            </div>
        </div>

        @include('includes.messages')

        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3"><strong>Sucursal:</strong> {{ $caja->sucursal->nombre }}</div>
                <div class="col-md-3"><strong>Apertura:</strong> {{ $caja->apertura->format('d/m/Y H:i') }}</div>
                <div class="col-md-3"><strong>Usuario:</strong> {{ $caja->user->name }}</div>
                <div class="col-md-3"><strong>Monto Inicial:</strong> ${{ number_format($caja->inicial, 2) }}</div>
                @if($caja->estado === 'cerrada')
                    <div class="col-md-3"><strong>Monto Final:</strong> ${{ number_format($caja->final, 2) }}</div>
                    <div class="col-md-3"><strong>Cierre:</strong> {{ $caja->cierre->format('d/m/Y H:i') }}</div>
                @endif
            </div>

            {{-- Totales resumidos --}}
            <div class="row text-center mb-4">
                <div class="col-md-4">
                    <div class="card bg-success text-white p-2">
                        <strong>Ingresos Acreditados</strong>
                        <h4>${{ number_format($totales['ingresosAcreditados'], 2) }}</h4>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-warning text-dark p-2">
                        <strong>Ingresos Pendientes</strong>
                        <h4>${{ number_format($totales['ingresosPendientes'], 2) }}</h4>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-danger text-white p-2">
                        <strong>Egresos</strong>
                        <h4>${{ number_format($totales['egresos'], 2) }}</h4>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="card bg-info text-white p-2 text-center">
                        <strong>Saldo Actual:</strong>
                        <h4>${{ number_format($totales['ingresosAcreditados'] - $totales['egresos'], 2) }}</h4>

                    </div>
                </div>
            </div>

            {{-- Movimientos detallados --}}
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Concepto</th>
                        <th>Medio de Pago</th>
                        <th>Venta</th>
                        <th>Tipo</th>
                        <th>Monto</th>
                        <th>Acreditado</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($caja->movimientos as $mov)
                        <tr>
                            <td>{{ $mov->fecha->format('d/m/Y H:i') }}</td>
                            <td>{{ optional($mov->concepto)->nombre ?? '-' }}</td>
                            <td>{{ optional($mov->medio)->nombre ?? '-' }}</td>
                            <td>{{ $mov->venta_id ?? '-' }}</td>
                            <td>{{ ucfirst($mov->tipo) }}</td>
                            <td>${{ number_format($mov->monto, 2) }}</td>
                            <td>
                                @if($mov->tipo === 'Ingreso')
                                    {{ $mov->acreditado ? 'Sí' : 'No' }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
@endsection

@section('footerSection')
    <script src="{{ asset('bower_components/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.table').DataTable({
                "order": [[0, "desc"]],
                "language": {
                    "url": "{{ asset('bower_components/datatables.net/lang/es-AR.json') }}"
                }
            });
        });
    </script>
@endsection
