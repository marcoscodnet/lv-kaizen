{{--
    Bloqueo de unidades para la venta: modal + helpers para los DataTables de
    "Unidades" y "Unidades a vender". Las filas tienen que traer bloqueada,
    motivo_bloqueo, bloqueo_fecha, bloqueo_user_nombre, vendida y
    puede_desbloquear (UnidadController::columnasBloqueo / marcarPermisosBloqueo).
--}}
<div class="modal fade" id="modalBloquearUnidad" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" id="formBloquearUnidad" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title"><span class="fas fa-lock me-2"></span>Bloquear para la venta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2" id="bloqueoUnidadDesc"></p>
                <label class="form-label" for="motivo_bloqueo">Motivo</label>
                <input type="text" name="motivo_bloqueo" id="motivo_bloqueo" class="form-control"
                       maxlength="255" required placeholder="Ej.: Señada, sin certificado, no está en condiciones de entrega">
                <small class="text-muted">La unidad sigue visible para todos, con el motivo a la vista, pero no se puede vender.
                    La desbloquea quien la bloqueó o un administrador.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-danger">Bloquear</button>
            </div>
        </form>
    </div>
</div>

<form method="POST" id="formDesbloquearUnidad" style="display:none">
    @csrf
</form>

<script>
    (function () {
        var urlBloquear = "{{ route('unidads.bloquear', ':id') }}";
        var urlDesbloquear = "{{ route('unidads.desbloquear', ':id') }}";

        function esc(t) {
            return String(t == null ? '' : t)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        }

        function fecha(f) {
            if (!f) return '';
            var d = new Date(String(f).replace(' ', 'T'));
            if (isNaN(d)) return '';
            return ('0' + d.getDate()).slice(-2) + '/' + ('0' + (d.getMonth() + 1)).slice(-2) + '/' + d.getFullYear();
        }

        /** "Sin certificado (Juan, 23/10/2026)" */
        window.textoBloqueoUnidad = function (row) {
            var detalle = [row.bloqueo_user_nombre, fecha(row.bloqueo_fecha)].filter(Boolean).join(', ');
            return row.motivo_bloqueo + (detalle ? ' (' + detalle + ')' : '');
        };

        /** Cartel de la columna Bloqueo. */
        window.badgeBloqueoUnidad = function (row) {
            if (!row.bloqueada) return '';
            return '<span class="badge bg-danger text-wrap text-start" style="white-space:normal;max-width:260px">'
                + '<span class="fas fa-lock me-1"></span>Bloqueada: ' + esc(window.textoBloqueoUnidad(row)) + '</span>';
        };

        /** Botones bloquear / desbloquear para la columna Acciones. */
        window.accionesBloqueoUnidad = function (row) {
            if (row.vendida) return '';
            var desc = [row.marca_nombre, row.modelo_nombre, 'Motor ' + (row.motor || ''), 'Cuadro ' + (row.cuadro || '')].join(' · ');
            if (!row.bloqueada) {
                return '<a href="#" class="btn btn-link p-0 ms-1" title="Bloquear para la venta"'
                    + ' data-bs-toggle="modal" data-bs-target="#modalBloquearUnidad"'
                    + ' data-id="' + row.id + '" data-desc="' + esc(desc) + '">'
                    + '<span class="text-500 fas fa-lock"></span></a>';
            }
            if (row.puede_desbloquear) {
                return '<a href="#" class="btn btn-link p-0 ms-1 btn-desbloquear-unidad" title="Desbloquear"'
                    + ' data-id="' + row.id + '" data-desc="' + esc(desc) + '">'
                    + '<span class="text-danger fas fa-lock-open"></span></a>';
            }
            return '';
        };

        /** Marca en rojo la fila bloqueada (createdRow del DataTable). */
        window.filaBloqueoUnidad = function (tr, row) {
            if (row.bloqueada) tr.classList.add('table-danger');
        };

        document.addEventListener('DOMContentLoaded', function () {
            var modal = document.getElementById('modalBloquearUnidad');
            modal.addEventListener('show.bs.modal', function (e) {
                var btn = e.relatedTarget;
                document.getElementById('formBloquearUnidad').action = urlBloquear.replace(':id', btn.getAttribute('data-id'));
                document.getElementById('bloqueoUnidadDesc').textContent = btn.getAttribute('data-desc');
                document.getElementById('motivo_bloqueo').value = '';
            });
            modal.addEventListener('shown.bs.modal', function () {
                document.getElementById('motivo_bloqueo').focus();
            });
        });

        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.btn-desbloquear-unidad');
            if (!btn) return;
            e.preventDefault();
            if (!confirm('¿Desbloquear para la venta?\n' + btn.getAttribute('data-desc'))) return;
            var form = document.getElementById('formDesbloquearUnidad');
            form.action = urlDesbloquear.replace(':id', btn.getAttribute('data-id'));
            form.submit();
        });
    })();
</script>
