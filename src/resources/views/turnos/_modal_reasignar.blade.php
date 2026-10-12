{{--
    Diálogo "Reasignar módulo" de una cita. Lo usan Administración → Módulos y
    Turnos → Todos los turnos.

    Espera:
      $opciones      id de cita => opciones (App\Support\ReasignacionCita).
      $urlReasignar  ruta del POST con __ID__ en lugar del id de la cita.
      $camposOcultos opcional, [nombre => valor] que viajan con el formulario
                     (p. ej. la fecha que se estaba viendo).

    El botón que lo abre lleva data-bs-toggle="modal" data-bs-target="#mdModalReasignar"
    y data-cita, data-tipo, data-linea (1/0) y data-resumen.

    Va en @push('modals'): dentro de .main-content el diálogo quedaba debajo
    del fondo oscuro.
--}}
@push('modals')
    <style>
        .md-opcion {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            padding: 10px 12px;
            border: 1px solid var(--tn-borde, #E3E8E8);
            border-radius: 10px;
            margin-bottom: 8px;
            cursor: pointer;
        }
        .md-opcion:has(input:checked) { border-color: var(--tn-verde, #496163); box-shadow: inset 0 0 0 1px var(--tn-verde, #496163); }
        .md-opcion.is-bloqueada { cursor: not-allowed; background: var(--tn-fondo, #F5F7F7); color: var(--tn-tenue, #8A9899); }
        .md-opcion input { margin-top: 4px; }
        .md-opcion__titulo { font-weight: 700; font-size: 14px; }
        .md-opcion__nota { display: block; font-size: 12px; }
        .md-opcion__nota--mal { color: #B4530F; }
        .md-opcion__nota--ok { color: #1E7B3C; }
    </style>

    <div class="modal fade" id="mdModalReasignar" tabindex="-1" aria-labelledby="mdReasignarTitulo" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <form class="modal-content" method="POST" id="mdFormReasignar">
                @csrf
                @foreach (($camposOcultos ?? []) as $nombreCampo => $valorCampo)
                    <input type="hidden" name="{{ $nombreCampo }}" value="{{ $valorCampo }}">
                @endforeach
                <div class="modal-header">
                    <h5 class="modal-title" id="mdReasignarTitulo">Reasignar módulo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3" id="mdReasignarResumen"></p>
                    <div id="mdReasignarOpciones"></div>
                    <p class="small mb-0" id="mdReasignarCorreo" style="color: var(--tn-suave, #5E6E6F);"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn tn-boton-dorado" id="mdReasignarBoton" disabled>Mover cita</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            // Las opciones vienen calculadas del servidor, que las vuelve a validar al guardar.
            var OPCIONES = @json($opciones);
            var urlReasignar = @json($urlReasignar);
            var modal = document.getElementById('mdModalReasignar');
            var form = document.getElementById('mdFormReasignar');
            var cajaOpciones = document.getElementById('mdReasignarOpciones');
            var boton = document.getElementById('mdReasignarBoton');

            var TEXTOS = {
                actual:      'Aquí está ahora',
                libre:       'Libre a esa hora',
                ocupado:     'Ocupado: ',
                comida:      'Hora de comida',
                inactivo:    'Módulo inactivo',
                sin_persona: 'Sin persona asignada'
            };

            modal.addEventListener('show.bs.modal', function (e) {
                var b = e.relatedTarget;
                if (!b) { return; }
                var id = b.getAttribute('data-cita');
                var tipo = b.getAttribute('data-tipo');
                var enLinea = b.getAttribute('data-linea') === '1';

                form.setAttribute('action', urlReasignar.replace('__ID__', id));
                document.getElementById('mdReasignarResumen').textContent = b.getAttribute('data-resumen');
                document.getElementById('mdReasignarCorreo').textContent = enLinea
                    ? 'Es una cita en línea: al moverla se le reenvía el acuse actualizado a su correo.'
                    : '';
                boton.disabled = true;
                boton.textContent = 'Mover cita';
                cajaOpciones.textContent = '';

                (OPCIONES[id] || []).forEach(function (op) {
                    var elegible = op.estado === 'libre';
                    var etiqueta = document.createElement('label');
                    etiqueta.className = 'md-opcion' + (elegible ? '' : ' is-bloqueada');

                    var radio = document.createElement('input');
                    radio.type = 'radio';
                    radio.name = 'modulo_id';
                    radio.value = op.id;
                    radio.className = 'form-check-input';
                    radio.disabled = !elegible;
                    radio.addEventListener('change', function () { boton.disabled = false; });

                    var cuerpo = document.createElement('span');
                    var titulo = document.createElement('span');
                    titulo.className = 'md-opcion__titulo d-block';
                    titulo.textContent = op.nombre + (op.persona ? ' · ' + op.persona : '');

                    var nota = document.createElement('span');
                    nota.className = 'md-opcion__nota ' + (elegible ? 'md-opcion__nota--ok' : 'md-opcion__nota--mal');
                    nota.textContent = TEXTOS[op.estado] + (op.choque || '');

                    cuerpo.appendChild(titulo);
                    cuerpo.appendChild(nota);

                    if (elegible && !op.recibe) {
                        var aviso = document.createElement('span');
                        aviso.className = 'md-opcion__nota md-opcion__nota--mal';
                        aviso.textContent = 'Normalmente no recibe ' + tipo + '.';
                        cuerpo.appendChild(aviso);
                    }

                    etiqueta.appendChild(radio);
                    etiqueta.appendChild(cuerpo);
                    cajaOpciones.appendChild(etiqueta);
                });

                if (!cajaOpciones.children.length) {
                    cajaOpciones.textContent = 'No hay módulos en esta sede.';
                }
            });

            // Evita doble envío.
            form.addEventListener('submit', function () {
                boton.disabled = true;
                boton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span> Moviendo…';
            });
        })();
    </script>
@endpush
