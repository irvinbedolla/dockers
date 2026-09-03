@extends('layouts.app')

@section('title', 'Cumplimiento en Ratificaciones')

@php
    $fechaActual = date('Y-m-d');
@endphp

@section('content')
    <section class="section">
        <div class="section-header d-flex justify-content-between align-items-center mb-4">
            <h3 class="page__heading mb-0">Cumplimiento en Ratificaciones</h3>
            <a class="btn btn-warning shadow-sm fw-semibold" href="{{ route('todas_ratificaciones') }}" onclick="nuevo_poder();" style="background-color: #CEA845; border-color: #CEA845; color: #000000 !important;">
                <i class="bi bi-arrow-left me-1"></i> Regresar
            </a>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            <div class="table-responsive">
                                <table id="example" class="table table-striped table-hover align-middle w-100">
                                    <thead style="background-color: #354647;">
                                        <tr>
                                            <th class="text-center text-white" style="color: #ffffff !important;">N°</th>
                                            <th class="text-center text-white" style="color: #ffffff !important;">Fecha</th>
                                            <th class="text-center text-white" style="color: #ffffff !important;">Hora</th>
                                            <th class="text-center text-white" style="color: #ffffff !important;">Monto</th>
                                            <th class="text-center text-white" style="width: 15%; color: #ffffff !important;">Estatus</th>
                                            <th class="text-center text-white" style="width: 42%; color: #ffffff !important;">Acciones</th>
                                            <th class="text-center text-white" style="width: 7%; color: #ffffff !important;">Documentos</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($solicitudes as $index => $pago)
                                            <tr>
                                                <td class="text-center fw-bold">{{ $index + 1 }}</td>
                                                <td class="text-center">{{ date_format($pago->fecha, "d-m-Y") }}</td>
                                                <td class="text-center">{{ date_format($pago->hora, "H:i:s") }}</td>
                                                <td class="text-center fw-bold">${{ number_format($pago->monto, 2) }}</td>
                                                <td class="text-center">
                                                    @if($pago->estatus == 'Pagado')
                                                        <span class="badge bg-success rounded-pill px-3 py-2">Pagado</span>
                                                    @elseif($pago->estatus == 'Pendiente')
                                                        <span class="badge bg-warning text-dark rounded-pill px-3 py-2">Pendiente</span>
                                                    @elseif($pago->estatus == 'No pagado' || $pago->estatus == 'Incomparecencia trabajador')
                                                        <span class="badge bg-danger rounded-pill px-3 py-2">{{ $pago->estatus }}</span>
                                                    @else
                                                        <span class="badge bg-secondary rounded-pill px-3 py-2">{{ $pago->estatus }}</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($pago->estatus == "Pendiente")
                                                        <div class="d-flex flex-wrap gap-1 justify-content-center">
                                                            <!-- Cumplimiento Normal (Abre Modal) -->
                                                            <button type="button" class="btn btn-info btn-sm text-white open-modal" data-bs-toggle="modal" data-bs-target="#exampleModal" data-id="{{ $pago->id }}" data-tipo="normal">
                                                                <i class="bi bi-check-circle me-1"></i> Generar Cumplimiento Parcial
                                                            </button>

                                                            <!-- Pagar Total (Abre el mismo Modal) -->
                                                            <button type="button" class="btn btn-success btn-sm text-white fw-semibold open-warning" data-bs-toggle="modal" data-bs-target="#warningModal" data-id="{{ $pago->id }}" data-numero="{{ $index + 1 }}" data-tipo="total">
                                                                <i class="bi bi-cash-stack me-1"></i> Generar Cumplimiento Total
                                                            </button>

                                                            <!-- Incumplimiento -->
                                                            <a class="btn btn-danger btn-sm" href="{{ route('cumplimiento_rechazar', $pago->id) }}" onclick="consultar_estadistica();">
                                                                <i class="bi bi-x-circle me-1"></i> Incumplimiento
                                                            </a>

                                                            <!-- Incomparecencia -->
                                                            <form method="POST" action="{{ route('cumplimiento_incomparecencia', $pago->id) }}" class="d-inline mb-0">
                                                                @csrf
                                                                <input type="hidden" name="fecha_audiencia" value="{{ optional($pago->fecha)->format('Y-m-d') }}">
                                                                <input type="hidden" name="hora_audiencia" value="{{ optional($pago->hora)->format('H:i:s') }}">
                                                                <button type="submit" class="btn btn-outline-danger btn-sm" onclick="consultar_estadistica();">
                                                                    Incomparecencia
                                                                </button>
                                                            </form>
                                                        </div>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($pago->estatus == "Pagado")
                                                        @if($pago->monto != 0)
                                                            @if($total == 1)
                                                                <a class="btn btn-success btn-sm px-3 shadow-sm" href="{{ route('PDFcumplimientoR', $pago->id_solicitud) }}" target="_blank">
                                                                    <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                                                                </a>
                                                            @else
                                                                <a class="btn btn-success btn-sm px-3 shadow-sm" href="{{ route('PDFpagos', $pago->id) }}" target="_blank">
                                                                    <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                                                                </a>
                                                            @endif
                                                        @endif
                                                    @elseif($pago->estatus == "No pagado")
                                                        <a class="btn btn-info btn-sm text-white px-3 shadow-sm" href="{{ route('PDFincumplimientoRatificacion', $pago->id) }}" target="_blank">
                                                            <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                                                        </a>
                                                    @elseif($pago->estatus == "Incomparecencia trabajador")
                                                        <a class="btn btn-info btn-sm text-white px-3 shadow-sm" href="{{ route('PDFIncomparecenciaCumplimientoRati', $pago->id) }}" target="_blank">
                                                            <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                                                        </a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                        
                                    </tbody>
                                    @if($estatus === 'Concluida' && !$solicitudes->contains('estatus', 'Pendiente'))
                                        <a class="btn btn-primary btn-sm" href="{{ route('PDFcumplimientoR', $id) }} " target="_blank">Constancia de cumplimiento</a>
                                    @endif
                                </table>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Modal de advertencia -->
    <div class="modal fade" id="warningModal" tabindex="-1" aria-labelledby="warningModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg text-center">
                <div class="modal-header " style="background-color: #354647; border-color: #354647; color: #ffffff !important;">
                    <h5 class="modal-title fw-bold center-text" id="warningModalLabel">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> ¡Advertencia!
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="mb-0 fs-5">¿Estás seguro de que deseas continuar con el pago del cumplimiento total?</p>
                    <small class="text-muted" style="font-size: 14px; font-weight: bold;">Esta acción generará todos los cumplimientos que esten pendientes.</small>
                </div>
                <div class="modal-footer bg-light border-0" >
                    <button type="button" class="btn btn-secondary px-4 btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-warning px-4 fw-bold btn-sm " style="background-color: #CEA845; border-color: #CEA845; color: #ffffff !important;" id="btnContinuarModal">Continuar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Reutilizable de Descripción -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <form id="formCumplimientoModal" class="needs-validation" novalidate method="POST" action="{{ route('ratificacion_pagoA') }}">
            @csrf
            <input type="hidden" id="modal-id" name="id" value="">
            <input type="hidden" id="modal-numero" name="numero_cumplimiento" value="">

            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header text-white" style="background-color: #354647;">
                        <h5 class="modal-title fw-bold text-white mb-0" id="modalTitulo">Descripción de Cumplimiento</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body p-4 text-center">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="efectivo" data-valor="Téngase a la parte patronal por exhibiendo en este acto la cantidad de $**,***.** (** PESOS **/100 M.N.) en efectivo, correspondiente al cumplimiento (total/parcial) -según corresponda- del convenio celebrado entre las partes. Dicho cantidad es recibida a entera satisfacción por la persona trabajadora, C. (NOMBRE COMPLETO DE LA PERSONA TRABAJADORA), quien se identifica con credencial para votar, cuya fotografía coincide con los rasgos del compareciente y de la cual obra copia en el expediente en que se actúa. El trabajador firma al margen como constancia legal y recibo de estilo. Lo anterior para todos los efectos legales a que haya lugar. Doy fe.">
                        Pago en Efectivo</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="cheque" data-valor="Téngase a la parte patronal por exhibiendo en este acto la cantidad de $**,***.** (*** PESOS **/100 M.N.), mediante CHEQUE (señalar si es de caja, no negociable, certificado, digital, etc.), de número 4627010, expedido con fecha 04 de octubre de 2025 por la Institución de Banca Múltiple HSBC México, S.A., Grupo Financiero HSBC (señalar el nombre completo de la institución bancaria que expide) , a la orden del (la) trabajador(a) C. (NOMBRE COMPLETO DE LA PERSONA TRABAJADORA) quien se identifica con credencial para votar, cuya fotografía coincide con los rasgos del compareciente y de la cual obra copia en el expediente. La cantidad referida corresponde al pago (total o de la primera, segunda, tercera, etc., parcialidad) del convenio celebrado entre las partes, y se entrega a entera satisfacción de la persona trabajadora, quien firma al margen como constancia legal y recibo de estilo, salvo el buen cobro del referido título de crédito. Lo anterior para todos los efectos legales a que haya lugar. Doy fe.">
                        Pago en Cheque</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="transferencia" data-valor="Téngase a la parte patronal por haciendo entrega en este acto de la cantidad de $**,***.** (*** PESOS **/100 M.N.), mediante TRANSFERENCIA ELECTRÓNICA realizada a la cuenta bancaria del (la) trabajador(a) C. (NOMBRE COMPLETO DE LA PERSONA TRABAJADORA), a través de Institución de Banca Múltiple HSBC México, S.A., Grupo Financiero HSBC, con los siguientes datos de operación: Número de cuenta de retiro ******, Referencia numérica ******, Clave de rastreo *******, Folio de internet ******, Concepto de pago ******, Fecha y hora de operación ******.
Dicha cantidad corresponde al cumplimiento (total o parcial, según corresponda) del convenio celebrado entre las partes, y ha sido recibida a entera satisfacción por el (la)  trabajador(a), quien acredita su identidad mediante credencial para votar (identificación con que acredite), cuya fotografía coincide con los rasgos del compareciente y de la cual obra copia en el expediente en que se actúa. Firmando al margen como constancia legal y recibo de estilo. Lo anterior para todos los efectos legales a que haya lugar. Doy fe.">
                        Pago en Transferencia</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="deposito" data-valor="Téngase a la parte patronal por haciendo entrega en este acto de la cantidad de $**,***.** (*** PESOS **/100 M.N.), mediante DEPOSITO BANCARIO realizado a la cuenta bancaria del (la) trabajador(a) C. (NOMBRE COMPLETO DE LA PERSONA TRABAJADORA), a través de Institución de Banca Múltiple HSBC México, S.A., Grupo Financiero HSBC, con los siguientes datos de operación: Número de cuenta de depósito ******, Referencia numérica ******, Concepto de pago ******, Fecha y hora de operación ******.
Dicha cantidad corresponde al cumplimiento (total o parcial, según corresponda) del convenio celebrado entre las partes, y ha sido recibida a entera satisfacción por el (la)  trabajador(a), quien acredita su identidad mediante credencial para votar (identificación con que acredite), cuya fotografía coincide con los rasgos del compareciente y de la cual obra copia en el expediente en que se actúa. Firmando al margen como constancia legal y recibo de estilo. Lo anterior para todos los efectos legales a que haya lugar. Doy fe.">
                        Deposito Bancario</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="otro" data-valor="">
                        Otro Metodo</button>

                        <!--<button type="button" class="btn btn-outline-primary btn-sm" id="pena" data-valor="Por lo que ve a la pena de convencional establecida en el convenio, a consecuencia de efectuar el pago fuera del plazo señalado en el convenio, la parte trabajadora manifiesta BAJO PROTESTA DE DECIR VERDAD, que se da por pagada de la misma en este acto, por así convenir a sus intereses y bajo  su más estricta responsabilidad, toda vez que fue explicada por esta autoridad los alcances y consecuencias, lo anterior para todos los efectos legales que hubiere lugar. Doy fe.">
                        Pena Convencional</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="reinstalacion" data-valor="Asimismo, se anexa Constancia de Presentación de Movimientos Afiliatorios de la empresa, con número de folio *******************, con la cual se acredita que la trabajadora fue dada de alta en el Instituto Mexicano del Seguro Social. ">
                        Pago con reinstalación</button> -->
      
                </div>
                    <div class="modal-body p-2">
                        <div class="form-group mb-0">
                                <label class="form-label fw-semibold">Observaciones / Descripción</label>
                                <textarea id="observaciones" name="observaciones" class="form-control" style="height: 400px;" rows="4" placeholder="Escriba aquí los detalles..." required></textarea>
                                <div class="invalid-feedback">Por favor ingrese las observaciones.</div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary px-4" style="background-color: #CEA845; border-color: #CEA845; color: #ffffff !important;">Guardar</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/poderes/general.js') }}"></script>
    <script>
        $(document).ready(function() {
            if ($.fn.DataTable.isDataTable('#example')) {
                $('#example').DataTable().destroy();
            }
            $('#example').DataTable({
                "destroy": true,
                "paging": true,
                "pageLength": 10,
                "searching": true,
                "ordering": true,
                "info": true,
                "language": {
                    "search": "Filtrar en esta pantalla:",
                    "lengthMenu": "Mostrar _MENU_ registros",
                    "info": "Mostrando del _START_ al _END_ de un bloque de _TOTAL_ solicitudes",
                    "infoEmpty": "Mostrando 0 a 0 de 0 filas",
                    "infoFiltered": "(filtrado de un total de _MAX_ registros)",
                    "zeroRecords": "No se encontraron coincidencias en esta página.",
                    "paginate": {
                        "first": "Primero",
                        "last": "Último",
                        "next": "Siguiente",
                        "previous": "Anterior"
                    }
                }
            });

            // Cambiar action y datos dinámicamente al abrir modal
            $(document).on('click', '.open-modal', function() {
                const id = $(this).data('id');
                const tipo = $(this).data('tipo');
                const numero = $(this).data('numero') || '';

                document.getElementById('modal-id').value = id;
                document.getElementById('modal-numero').value = numero;

                const form = document.getElementById('formCumplimientoModal');
                const titulo = document.getElementById('modalTitulo');
                
                if (tipo === 'total') {
                    form.action = "{{ route('ratificacion_pagar_total') }}";
                    titulo.innerText = "Descripción para Cumplimiento Total";
                } else {
                    form.action = "{{ route('ratificacion_pagoA') }}";
                    titulo.innerText = "Descripción de Cumplimiento Parcial";
                }
            });

            let datosTemporales = {};

            // 1. Cuando presionan el botón en la tabla, guardamos los datos
            $(document).on('click', '.open-warning', function() {
                datosTemporales = {
                    id: $(this).data('id'),
                    tipo: $(this).data('tipo'),
                    numero: $(this).data('numero') || ''
                };
            });

            $('#btnContinuarModal').on('click', function() {
                $('#warningModal').modal('hide');

                document.getElementById('modal-id').value = datosTemporales.id;
                document.getElementById('modal-numero').value = datosTemporales.numero;

                const form = document.getElementById('formCumplimientoModal');
                const titulo = document.getElementById('modalTitulo');
                
                if (datosTemporales.tipo === 'total') {
                    form.action = "{{ route('ratificacion_pagar_total') }}";
                    titulo.innerText = "Descripción para Pagar Total";
                } else {
                    form.action = "{{ route('ratificacion_pagoA') }}";
                    titulo.innerText = "Descripción de Cumplimiento";
                }
                setTimeout(function() {
                    $('#exampleModal').modal('show');
                }, 400); 
            });
            
            $('#efectivo, #cheque, #transferencia, #deposito, #pena, #reinstalacion, #otro').on('click', function(e) {
                e.preventDefault(); 
                
                let textoDiferente = $(this).data('valor');
                $('#observaciones').val(textoDiferente);
            });

        });
    </script>
@endsection