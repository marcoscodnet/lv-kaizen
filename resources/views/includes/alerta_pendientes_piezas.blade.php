@if(!empty($alertaPendientesPiezas) && $alertaPendientesPiezas > 0)
    <div class="alert alert-warning alert-dismissible fade show mb-0" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        Tenés <strong>{{ $alertaPendientesPiezas }}</strong>
        {{ $alertaPendientesPiezas === 1 ? 'movimiento de pieza pendiente' : 'movimientos de piezas pendientes' }}
        de recepción en tu sucursal.
        <a href="{{ route('movimientoPiezas.index') }}" class="alert-link ms-2">Ver movimientos</a>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
@endif
