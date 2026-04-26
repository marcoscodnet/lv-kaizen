@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h4 class="mb-4"><i class="fas fa-university me-2"></i>Cuentas</h4>

        <div class="row">
            @forelse($entidades as $entidad)
                <div class="col-md-4 mb-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title">{{ $entidad->nombre }}</h5>
                            <span class="badge bg-secondary mb-2">{{ $entidad->forma }}</span>
                            <div class="d-flex justify-content-between mt-2">
                                <small class="text-success">
                                    <i class="fas fa-arrow-up"></i>
                                    ${{ number_format($entidad->ingresos, 2, ',', '.') }}
                                </small>
                                <small class="text-danger">
                                    <i class="fas fa-arrow-down"></i>
                                    ${{ number_format($entidad->egresos, 2, ',', '.') }}
                                </small>
                            </div>
                            <div class="mt-2 fs-5 fw-bold {{ $entidad->saldo >= 0 ? 'text-success' : 'text-danger' }}">
                                ${{ number_format($entidad->saldo, 2, ',', '.') }}
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('cuentas.show', $entidad->id) }}"
                               class="btn btn-primary btn-sm w-100">
                                <i class="fas fa-chart-line"></i> Ver movimientos
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info">No hay entidades no-tangibles activas.</div>
                </div>
            @endforelse
        </div>
    </div>
@endsection
