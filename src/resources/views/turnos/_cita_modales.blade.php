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

            @include('turnos._horario_modal')
