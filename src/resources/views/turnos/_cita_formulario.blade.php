{{--
    Formulario de cita (4 pasos). Lo usan el formulario público /generar-cita
    (modo "publico") y la ventanilla /turnos/nuevo (modo "ventanilla"); así los
    dos se ven y se comportan igual. En ventanilla cambian sólo los textos
    (hablan de "la persona") y los nombres que espera RecepcionController:
    correo y municipio_solicitante. Requiere también _cita_modales,
    _cita_estilos y _cita_scripts.
    Espera: $modo ('publico' | 'ventanilla'), $accion (URL del POST), $municipios.
--}}
@php
    $ventanilla = ($modo ?? 'publico') === 'ventanilla';
@endphp
                            <form class="needs-validation gc-form" novalidate id="form_roles" method="POST" action="{{ $accion }}">
                                @csrf
                                <input type="hidden" name="orientacion" id="orientacion" value="No">
                                <input type="hidden" name="excepcion" id="excepcion" value="No">
                                <input type="hidden" name="motivo_excepcion" id="motivo_excepcion" value="">
                                <input type="hidden" name="delegacion" id="delegacion" value="">
                                <input type="hidden" name="fecha_turno" id="fecha_turno" value="">
                                <input type="hidden" name="hora_turno" id="hora_turno" value="">

                                @if ($ventanilla)
                                <p class="gc-intro">
                                    Captura los cuatro pasos con la persona. Lo marcado con <span class="gc-req">*</span> es obligatorio.
                                    Al guardar verás el folio y podrás <strong>imprimir el acuse con código QR</strong>.
                                </p>
                                @else
                                <p class="gc-intro">
                                    Llena los cuatro pasos. Lo marcado con <span class="gc-req">*</span> es obligatorio.
                                    Al terminar te enviaremos a tu correo el <strong>acuse con código QR</strong> que debes presentar el día de tu cita.
                                </p>
                                @endif

                                <ol class="gc-pasos" id="gcPasos" aria-label="Avance">
                                    <li data-paso="1"><span class="gc-pasos__num">1</span><span class="gc-pasos__texto">Trámite</span></li>
                                    <li data-paso="2"><span class="gc-pasos__num">2</span><span class="gc-pasos__texto">{{ $ventanilla ? 'Datos de la persona' : 'Tus datos' }}</span></li>
                                    <li data-paso="3"><span class="gc-pasos__num">3</span><span class="gc-pasos__texto">{{ $ventanilla ? 'El caso' : 'Tu caso' }}</span></li>
                                    <li data-paso="4"><span class="gc-pasos__num">4</span><span class="gc-pasos__texto">Día y hora</span></li>
                                </ol>

                                {{-- PASO 1 --}}
                                <fieldset class="gc-seccion" data-paso="1">
                                    <legend class="gc-titulo"><span class="gc-num">1</span> {{ $ventanilla ? '¿Qué trámite necesita?' : '¿Qué trámite necesitas?' }}</legend>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label" for="tipo">Tipo de trámite <span class="gc-req">*</span></label>
                                            <select id="tipo" name="tipo" class="form-select" onchange="blockCalendar();" required>
                                                <option value="">Elige una opción</option>
                                                {{-- ?tipo=Solicitud llega ya elegido (accesos rápidos de recepción en el Inicio).
                                                     En ventanilla cada recepción ve sólo los trámites que puede agendar
                                                     (Recepcion::tramitesQuePuedeCrear); si es uno solo, ya va elegido. --}}
                                                @php
                                                    $etiquetasTramite = ['Asesoría' => 'Asesoría', 'Solicitud' => 'Solicitud de conciliación', 'Ratificación' => 'Ratificación de convenio'];
                                                    $tramitesForm = $ventanilla
                                                        ? array_values(array_intersect(array_keys($etiquetasTramite), \App\Support\Recepcion::tramitesQuePuedeCrear(auth()->user())))
                                                        : array_keys($etiquetasTramite);
                                                    $tipoInicial = old('tipo', request()->query('tipo'));
                                                    if (count($tramitesForm) === 1) { $tipoInicial = $tramitesForm[0]; }
                                                @endphp
                                                @foreach ($tramitesForm as $tramite)
                                                    <option value="{{ $tramite }}" @selected($tipoInicial === $tramite)>{{ $etiquetasTramite[$tramite] }}</option>
                                                @endforeach
                                            </select>
                                            <div class="form-text gc-ayuda-tramite" id="gcAyudaTramite">Elige una opción y te explicamos de qué se trata.</div>
                                            <div class="invalid-feedback">Elige el tipo de trámite.</div>
                                        </div>
                                        @unless ($ventanilla)
                                        <div class="col-md-6" style="display:none;" id="calidad">
                                            <label class="form-label" for="folio_patronal">Acudes en calidad de: </label>
                                            <select id="puesto" class="form-select">
                                                <option value="">Elige una opción</option>
                                                <option value="trabajador">Trabajador</option>
                                                <option value="empleador">Empleador</option>
                                            </select>
                                            
                                        </div>
                                        <div class="col-md-6" style="display:none;" id="si_ratificacion">
                                            <label class="form-label" for="folio_patronal">¿Ya tienes un folio de representante patronal?</label>
                                            <select id="folio_patronal" class="form-select">
                                                <option value="">Elige una opción</option>
                                                <option value="si">Sí, ya tengo folio</option>
                                                <option value="no">No tengo folio</option>
                                            </select>
                                            <div class="form-text">Si ya tienes folio, te llevaremos al formulario para ratificar con él.</div>
                                        </div>
                                        @endunless
                                        <div class="col-md-6">
                                            <label class="form-label" for="municipio_citado">{{ $ventanilla ? 'Municipio de la fuente de empleo (o de la persona a citar)' : 'Municipio donde está tu trabajo (o la persona a la que vas a citar)' }} <span class="gc-req">*</span></label>
                                            <select id="municipio_citado" class="form-select" name="{{ $ventanilla ? 'municipio_solicitante' : 'municipio_citado' }}" required>
                                                <option value="">Elige el municipio</option>
                                                @foreach($municipios as $mun)
                                                    <option value="{{ $mun['id'] }}" data-delegacion-id="{{ $mun['delegacion_id'] }}">{{ $mun['nombre'] }}</option>
                                                @endforeach
                                            </select>
                                            <div class="invalid-feedback">Elige el municipio.</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="gc-oficina" id="gcOficina">
                                                <i class="bi bi-geo-alt-fill" aria-hidden="true"></i>
                                                <div>
                                                    <span class="gc-oficina__etiqueta">{{ $ventanilla ? 'Oficina del CCL que le corresponde' : 'Oficina del CCL que te corresponde' }}</span>
                                                    <span id="modulo_delegacion" class="gc-oficina__nombre">Elige tu municipio</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>

                                {{-- PASO 2 --}}
                                <fieldset class="gc-seccion" data-paso="2">
                                    <legend class="gc-titulo"><span class="gc-num">2</span> {{ $ventanilla ? 'Datos de la persona' : 'Tus datos' }}</legend>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label" for="gcNombre">Nombre completo <span class="gc-req">*</span></label>
                                            <input type="text" id="gcNombre" name="nombre" class="form-control gc-mayus" maxlength="100" autocomplete="name"
                                                   placeholder="Nombre(s) y apellidos" oninput="this.value = this.value.toUpperCase();" required>
                                            <div class="invalid-feedback">Escribe tu nombre completo.</div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <label class="form-label" for="gcEdad">Edad <span class="gc-req">*</span></label>
                                            <input type="number" id="gcEdad" name="edad" class="form-control" inputmode="numeric" min="0" max="120" maxlength="3"
                                                   oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" required>
                                            <div class="invalid-feedback">Escribe tu edad.</div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <label class="form-label" for="gcSexo">Sexo <span class="gc-req">*</span></label>
                                            {{-- Los valores son los que acepta recepcion.sexo (H, M, NB, LGBTTTIQ).
                                                 Antes había "Otros", que la base rechazaba y la cita no se guardaba. --}}
                                            <select id="gcSexo" name="sexo" class="form-select" required>
                                                <option value="">Elige</option>
                                                <option value="H">Hombre</option>
                                                <option value="M">Mujer</option>
                                                <option value="NB">No binario</option>
                                                <option value="LGBTTTIQ">Otro (LGBTTTIQ+)</option>
                                            </select>
                                            <div class="invalid-feedback">Elige una opción.</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label" for="gcCorreo">Correo electrónico <span class="gc-req">*</span></label>
                                            <input type="email" id="gcCorreo" name="{{ $ventanilla ? 'correo' : 'email' }}" class="form-control" maxlength="50" autocomplete="email"
                                                   placeholder="nombre@correo.com" required>
                                            <div class="form-text">{{ $ventanilla ? 'Para contactar a la persona sobre su cita.' : 'Aquí te llegará tu acuse con el código QR.' }}</div>
                                            <div class="invalid-feedback">Escribe un correo válido.</div>
                                        </div>
                                        <div class="col-md-6">
                                            {{-- Sin name: sólo sirve para evitar errores de dedo; no se envía. --}}
                                            <label class="form-label" for="gcCorreo2">{{ $ventanilla ? 'Confirma el correo' : 'Confirma tu correo' }} <span class="gc-req">*</span></label>
                                            <input type="email" id="gcCorreo2" class="form-control" maxlength="50" autocomplete="off"
                                                   placeholder="Escríbelo otra vez" required>
                                            <div class="invalid-feedback" id="gcCorreo2Error">Los correos no coinciden.</div>
                                        </div>
                                        {{-- Recepción (Morelia 01, 02 y General): si la persona no trae correo,
                                             se puede usar el institucional (App\Support\Recepcion::CORREO). --}}
                                        @if ($ventanilla && \App\Support\Recepcion::es(auth()->user()))
                                            <div class="col-12">
                                                <div class="gc-correo-recepcion" role="note">
                                                    <i class="bi bi-envelope-at" aria-hidden="true"></i>
                                                    <span>
                                                        <b>¿La persona no tiene correo?</b> Usa el de recepción,
                                                        <b>{{ \App\Support\Recepcion::CORREO }}</b>: es válido para registrar el turno cuando la persona no cuenta con correo.
                                                    </span>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="gcUsarCorreoRecepcion" data-correo="{{ \App\Support\Recepcion::CORREO }}">
                                                        Usar este correo
                                                    </button>
                                                </div>
                                            </div>
                                            <style>
                                                .gc-correo-recepcion { display: flex; align-items: center; flex-wrap: wrap; gap: 8px 12px; padding: 10px 14px; border-radius: 10px; background: #EEF2F2; color: #5E6E6F; font-size: 13px; }
                                                .gc-correo-recepcion > .bi { font-size: 18px; color: #496163; }
                                                .gc-correo-recepcion > span { flex: 1 1 280px; }
                                                .gc-correo-recepcion b { color: #2E3C3D; }
                                                .gc-correo-recepcion.is-usado { background: #E5F3EA; }
                                            </style>
                                            <script>
                                                document.addEventListener('DOMContentLoaded', function () {
                                                    var boton = document.getElementById('gcUsarCorreoRecepcion');
                                                    if (!boton) { return; }
                                                    boton.addEventListener('click', function () {
                                                        ['gcCorreo', 'gcCorreo2'].forEach(function (id) {
                                                            var campo = document.getElementById(id);
                                                            if (!campo) { return; }
                                                            campo.value = boton.getAttribute('data-correo');
                                                            campo.dispatchEvent(new Event('input', { bubbles: true }));
                                                            campo.dispatchEvent(new Event('change', { bubbles: true }));
                                                        });
                                                        boton.closest('.gc-correo-recepcion').classList.add('is-usado');
                                                        boton.textContent = 'Correo de recepción puesto';
                                                    });
                                                });
                                            </script>
                                        @endif
                                        <div class="col-md-6">
                                            <label class="form-label" for="gcTelefono">Teléfono o celular <span class="gc-req">*</span></label>
                                            <input type="tel" id="gcTelefono" name="telefono" class="form-control" inputmode="numeric" autocomplete="tel-national"
                                                   maxlength="10" pattern="[0-9]{10}" placeholder="10 dígitos"
                                                   oninput="this.value = this.value.replace(/\D/g, '').slice(0, 10);" required>
                                            <div class="invalid-feedback">Escribe los 10 dígitos de tu teléfono.</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label" for="gcVulnerables">{{ $ventanilla ? '¿Pertenece a algún grupo de atención prioritaria?' : '¿Perteneces a algún grupo de atención prioritaria?' }} <span class="gc-req">*</span></label>
                                            <select id="gcVulnerables" name="vulnerables" class="form-select" required>
                                                <option value="">Elige una opción</option>
                                                <option value="No aplica">No, a ninguno</option>
                                                <option value="Menores de edad">Menores de edad</option>
                                                <option value="Adultos mayores">Adultos mayores</option>
                                                <option value="Personas con discapacidad">Personas con discapacidad</option>
                                                <option value="Población indígena">Población indígena</option>
                                                <option value="Personas Migrantes">Personas migrantes</option>
                                                <option value="LGBTTTIQ">LGBTTTIQ+</option>
                                            </select>
                                            <div class="form-text">Nos ayuda a darte la atención adecuada.</div>
                                            <div class="invalid-feedback">Elige una opción.</div>
                                        </div>
                                        {{-- CURP y domicilio para el prellenado (App\Support\FlujoCita). --}}
                                        @if (\App\Support\FlujoCita::camposExtra($ventanilla))
                                            @include('turnos._campos_flujo')
                                        @endif
                                    </div>
                                </fieldset>

                                {{-- PASO 3 --}}
                                <fieldset class="gc-seccion" data-paso="3">
                                    <legend class="gc-titulo"><span class="gc-num">3</span> {{ $ventanilla ? 'El caso' : 'Tu caso' }}</legend>
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label" for="gcTipoCaso">
                                                {{ $ventanilla ? '¿El caso es por alguno de estos motivos?' : '¿Tu caso es por alguno de estos motivos?' }} <span class="gc-req">*</span>
                                                <button type="button" class="btn btn-link btn-sm p-0 ms-1 align-baseline" data-bs-toggle="modal" data-bs-target="#exampleModal">¿Por qué lo preguntamos?</button>
                                            </label>
                                            <select id="gcTipoCaso" name="tipo_caso" class="form-select" onchange="cambiaExcepcion(this); blockCalendar();" required>
                                                <option value="">Elige una opción</option>
                                                <option value="Ninguno" style="font-weight: bold;">No, ninguno de estos</option>
                                                {{-- Catálogo en App\Support\CasosExcepcion::MOTIVOS. El value es el de
                                                     siempre (recepcion.tipo_caso, el grupo); data-motivo es el motivo
                                                     exacto y viaja en motivo_excepcion, para el reporte por motivo. --}}
                                                @foreach (\App\Support\CasosExcepcion::motivosAgrupados() as $grupo => $opciones)
                                                    <optgroup label="{{ $grupo }}">
                                                        @foreach ($opciones as $op)
                                                            <option value="{{ $op['valor'] }}" data-motivo="{{ $op['clave'] }}">{{ $op['texto'] }}</option>
                                                        @endforeach
                                                    </optgroup>
                                                @endforeach
                                            </select>
                                            <div class="form-text" id="gcAyudaCaso">Si no estás seguro, elige "No, ninguno de estos".</div>
                                            <div class="invalid-feedback">Elige una opción.</div>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label" for="gcConflicto">{{ $ventanilla ? 'Descripción breve de lo que pasó' : 'Cuéntanos brevemente qué pasó' }} <span class="gc-opcional">(opcional)</span></label>
                                            <textarea id="gcConflicto" name="conflicto" class="form-control gc-mayus" rows="3" maxlength="500"
                                                      placeholder="Por ejemplo: me despidieron sin pagarme el finiquito…" oninput="this.value = this.value.toUpperCase();"></textarea>
                                            <div class="form-text text-end"><span id="gcContador">0</span> / 500</div>
                                        </div>
                                    </div>
                                </fieldset>

                                {{-- PASO 4 --}}
                                <fieldset class="gc-seccion" data-paso="4">
                                    <legend class="gc-titulo"><span class="gc-num">4</span> Elige día y hora</legend>
                                    <div class="gc-horario">
                                        <button type="button" id="botonCalendar" class="btn btn-guinda gc-horario__boton" data-bs-toggle="modal" data-bs-target="#calendarModal" disabled>
                                            <i class="bi bi-calendar-week me-2" aria-hidden="true"></i><span id="gcTextoCalendario">Ver horarios disponibles</span>
                                        </button>
                                        <div class="form-text" id="gcAyudaCalendario">Primero elige el trámite y el municipio (paso 1).</div>
                                        <div id="resumenTurno" class="gc-turno" style="display:none;" aria-live="polite"></div>
                                        <div class="gc-error" id="gcErrorHorario" hidden>
                                            <i class="bi bi-exclamation-circle" aria-hidden="true"></i> Elige el día y la hora de tu cita.
                                        </div>
                                    </div>
                                </fieldset>

                                {{-- Cita de prueba del flujo (App\Support\FlujoCita): sólo Super Usuario. --}}
                                @if ($ventanilla && auth()->check() && auth()->user()->hasRole('Super Usuario')
                                     && \App\Support\FlujoCita::migrado())
                                    <div class="form-check mb-3" style="background:#FBF4E1;border-radius:8px;padding:10px 12px 10px 36px;">
                                        <input class="form-check-input" type="checkbox" name="es_prueba" value="1" id="gcEsPrueba">
                                        <label class="form-check-label" for="gcEsPrueba" style="font-weight:600;color:#7A5D10;">
                                            Cita de prueba del flujo nuevo
                                            <span class="d-block small fw-normal">No aparece en la pantalla de espera ni en los conteos; se borra con <code>php artisan citas:limpiar-pruebas</code>.</span>
                                        </label>
                                    </div>
                                @endif

                                <div class="gc-enviar">
                                    <button id="btnGuardar" type="submit" class="btn btn-oro btn-lg gc-enviar__boton">
                                        <i class="bi bi-check2-circle me-1" aria-hidden="true"></i> {{ $ventanilla ? 'Agendar cita' : 'Agendar mi cita' }}
                                    </button>
                                    @if ($ventanilla)
                                    <p class="gc-enviar__nota">Confirma con la persona su correo y teléfono antes de guardar.</p>
                                    @else
                                    <p class="gc-enviar__nota">
                                        Revisa que tu correo esté bien escrito: ahí te llegará el acuse. El trámite es gratuito.
                                    </p>
                                    @endif
                                </div>
                            </form>
