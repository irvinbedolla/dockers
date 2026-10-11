{{-- Modales del formulario de cita: ayuda de casos de excepción y selector de día y hora. --}}
            <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">¿Por qué te preguntamos esto?</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>En estos casos la Ley Federal del Trabajo (artículo 685 Ter) <strong>no te obliga a pasar por la conciliación</strong>, y te atenderá personal especializado:</p>
                            <ul class="mb-2">
                                <li>Discriminación en el empleo, acoso u hostigamiento sexual.</li>
                                <li>Designación de beneficiarios por muerte.</li>
                                <li>Prestaciones de seguridad social.</li>
                                <li>Libertad de asociación y sindical, negociación colectiva, trabajo infantil o forzoso.</li>
                                <li>Disputas por contratos colectivos o estatutos sindicales.</li>
                            </ul>
                            <p class="mb-0">Si tu caso no es ninguno de estos o no estás seguro, elige <strong>"No, ninguno de estos"</strong>.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>

            {{--
                Elegir día y hora. Antes era FullCalendar con vistas de semana/lista;
                ahora es una tira de días con cuántos horarios libres tiene cada uno
                y abajo sólo las horas libres de ese día. Mismos datos
                (/api/obtenerTurnosDisponibles) y el mismo botón #confirmarTurno.
            --}}
            <div class="modal fade" id="calendarModal" tabindex="-1" aria-labelledby="hcTitulo" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg hc-dialogo">
                    <div class="modal-content">
                        <div class="modal-header card-header-guinda py-3">
                            <div>
                                <h5 class="modal-title text-white mb-0" id="hcTitulo"><i class="bi bi-calendar-week me-2" aria-hidden="true"></i>Elige día y hora</h5>
                                <span class="hc-contexto" id="hcContexto"></span>
                            </div>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>

                        <div class="modal-body hc-cuerpo">
                            <div class="hc-semana">
                                <button type="button" class="hc-flecha" id="hcAnterior" aria-label="Semana anterior"><i class="bi bi-chevron-left"></i></button>
                                <span class="hc-semana__texto" id="hcSemana" aria-live="polite"></span>
                                <button type="button" class="hc-flecha" id="hcSiguiente" aria-label="Semana siguiente"><i class="bi bi-chevron-right"></i></button>
                            </div>

                            <div class="hc-dias" id="hcDias" role="tablist" aria-label="Días de la semana"></div>

                            <div class="hc-horas" id="hcHoras" aria-live="polite"></div>
                        </div>

                        <div class="modal-footer hc-pie">
                            <div class="hc-elegido" id="hcElegido">Toca un horario para elegirlo.</div>
                            <div class="hc-pie__botones">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="button" class="btn btn-oro" id="confirmarTurno" disabled>
                                    <i class="bi bi-check2 me-1" aria-hidden="true"></i>Confirmar horario
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

