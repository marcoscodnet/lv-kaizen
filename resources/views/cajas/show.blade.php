@extends('layouts.app')

@section('headSection')
    <link rel="stylesheet" href="{{ asset('bower_components/datatables.net-bs/css/dataTables.bootstrap.css') }}">
    <link rel="stylesheet" href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}">
@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5><i class="fas fa-cash-register"></i> Caja #{{ $caja->id }} - {{ ucfirst($caja->estado) }}</h5>
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
            </div>
        </div>
        @include('includes.messages')
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3">
                    <strong>Sucursal:</strong> {{ $caja->sucursal->nombre }}
                </div>
                <div class="col-md-3">
                    <strong>Apertura:</strong> {{ $caja->apertura->format('d/m/Y H:i') }}
                </div>
                <div class="col-md-3">
                    <strong>Usuario:</strong> {{ $caja->user->name }}
                </div>
                <div class="col-md-3">
                    <strong>Monto Inicial:</strong> ${{ number_format($caja->inicial, 2) }}
                </div>
                @if($caja->estado === 'cerrada')
                    <div class="col-md-3">
                        <strong>Monto Final:</strong> ${{ number_format($caja->final, 2) }}
                    </div>
                    <div class="col-md-3">
                        <strong>Cierre:</strong> {{ $caja->cierre->format('d/m/Y H:i') }}
                    </div>
                @endif
            </div>

            @can('caja-movimiento-registrar')
                <div class="mb-3">
                    <a href="{{ route('movimiento_cajas.create', $caja->id) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Registrar Movimiento
                    </a>

                </div>
            @endcan

            <div class="table-responsive">
                <table id="movimientosCaja" class="table table-bordered table-striped table-hover">
                    <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Concepto</th>
                        <th>Entidad</th>
                        <th>Venta</th>
                        <th>Tipo</th>
                        <th>Monto</th>
                        <th>Acreditado</th>
                        <th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($caja->movimientos as $mov)
                        <tr>
                            <td>{{ $mov->fecha->format('d/m/Y H:i') }}</td>
                            <td>{{ optional($mov->concepto)->nombre ?? '-' }}</td>
                            <td>{{ optional($mov->entidad)->nombre ?? '-' }}</td>
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

                            <td>
                                @can('caja-movimiento-editar')
                                    <a href="{{ route('movimiento_cajas.edit', $mov->id) }}" class="btn btn-link p-0" title="Editar" data-bs-toggle="tooltip" data-bs-placement="top">
                                        <span class="text-500 fas fa-edit"></span>
                                    </a>
                                @endcan
                                    @can('caja-acreditar')
                                        @if($mov->tipo === 'Ingreso' && !$mov->acreditado && $caja->estado === 'Abierta')
                                            <form action="{{ route('movimiento_cajas.acreditar', $mov->id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                <button type="submit" onclick="return confirm('¿Acreditar este movimiento?')" title="Acreditar" class="btn btn-link p-0" data-bs-toggle="tooltip" data-bs-placement="top">
                                                    <span class="text-500 fas fa-check"></span>
                                                </button>
                                            </form>
                                        @endif
                                    @endcan
                                @can('caja-movimiento-eliminar')
                                    <form action="{{ route('movimiento_cajas.destroy', $mov->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('¿Eliminar este movimiento?')" title="Eliminar" class="btn btn-link p-0" data-bs-toggle="tooltip" data-bs-placement="top">
                                            <span class="text-500 fas fa-trash-alt"></span>
                                        </button>
                                    </form>
                                @endcan




                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                <strong>Total Ingresos Acreditados:</strong> ${{ number_format($caja->movimientos->where('tipo', 'Ingreso')->where('acreditado', true)->sum('monto'), 2) }} <br>
                <strong>Total Egresos:</strong> ${{ number_format($caja->movimientos->where('tipo', 'Egreso')->sum('monto'), 2) }} <br>
                <strong>Saldo Actual:</strong> ${{ number_format($caja->movimientos->where('tipo', 'Ingreso')->where('acreditado', true)->sum('monto') - $caja->movimientos->where('tipo', 'Egreso')->sum('monto'), 2) }}
            </div>

        </div>
    </div>
@endsection

@section('footerSection')
    <script src="{{ asset('bower_components/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#movimientosCaja').DataTable({
                "order": [[0, "desc"]],
                "language": {
                    "url": "{{ asset('bower_components/datatables.net/lang/es-AR.json') }}"
                }
            });
        });
    </script>
@endsection
