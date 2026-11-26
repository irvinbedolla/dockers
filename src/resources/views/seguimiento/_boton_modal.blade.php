{{--
    Botón "Seguimiento del caso" para las pantallas de audiencia (revisar,
    iniciada y patronal iniciada). Abre un modal con el seguimiento general
    de la solicitud a la que pertenece la audiencia (SeguimientoController@modal
    → seguimiento/_caso). Se carga la primera vez que se abre.

    Uso: @include('seguimiento._boton_modal', ['solicitudId' => $id])
--}}
@if (! empty($solicitudId))
    <button type="button" class="btn tn-boton-dorado btn-sm" data-bs-toggle="modal" data-bs-target="#sgCasoModal">
        <i class="bi bi-diagram-3 me-1"></i> Ver seguimiento del caso
    </button>

    @push('modals')
        @include('turnos._estilos')
        @include('seguimiento._estilos')
        <div class="modal fade" id="sgCasoModal" tabindex="-1" aria-labelledby="sgCasoModalTitulo" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content" style="background: #F5F7F7;">
                    <div class="modal-header" style="background:#fff;">
                        <h5 class="modal-title" id="sgCasoModalTitulo">
                            Seguimiento del caso
                            <span class="sg-evento__etapa" style="font-size:11px;"><i class="bi bi-eye"></i> Sólo consulta</span>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="tn-tarjeta" id="sgCasoModalCuerpo">
                            <div class="text-center py-5" style="color:#8A9899;"><div class="spinner-border spinner-border-sm me-2"></div> Cargando el seguimiento…</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
        (function () {
            var modal = document.getElementById('sgCasoModal');
            if (!modal) { return; }
            var cargado = false;
            modal.addEventListener('show.bs.modal', function () {
                if (cargado) { return; }
                var cuerpo = document.getElementById('sgCasoModalCuerpo');
                fetch(@json(route('seguimiento.modal', $solicitudId)), { headers: { 'Accept': 'text/html' }, credentials: 'same-origin' })
                    .then(function (r) {
                        if (r.status === 404) { throw new Error('404'); }
                        if (!r.ok) { throw new Error(r.status); }
                        return r.text();
                    })
                    .then(function (html) { cuerpo.innerHTML = html; cargado = true; })
                    .catch(function (e) {
                        cuerpo.innerHTML = e.message === '404'
                            ? '<div class="alert alert-secondary mb-0">El seguimiento de este caso no está disponible para tus sedes.</div>'
                            : '<div class="alert alert-warning mb-0">No se pudo cargar el seguimiento. Cierra y vuelve a intentarlo.</div>';
                    });
            });
        })();
        </script>
    @endpush
@endif
