<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>Sí Concilio - Cita para Ratificación</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    
    <!-- Icono y Fuentes -->
    <link rel="icon" href="{{ asset('assets/images/ccl-r.png') }}" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Libraries CSS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/locales-all.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet"/>
    <link href="public/assets/css/iziToast.min.css" rel="stylesheet">
    <link href="public/assets/css/sweetalert.css" rel="stylesheet">
    
    <!-- jQuery & Select2 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    
    <style>
        :root {
            --color-guinda: #496163;
            --color-guinda-dark: #530c3a;
            --color-oro: #CEA845;
            --color-oro-dark: #b59238;
            --color-gris-bg: #f8f9fa;
        }
        body {
            font-family: 'Roboto', sans-serif;
            background-color: var(--color-gris-bg);
            color: #333;
            padding-top: 100px;
        }
        /* Navbar Institucional */
        .navbar-institucional {
            background-color: #ffffff;
            border-bottom: 3px solid var(--color-oro);
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        /* Card Header Institucional */
        .card-header-guinda {
            background-color: var(--color-guinda);
            color: #ffffff;
            font-weight: 600;
            border-top-left-radius: 0.375rem !important;
            border-top-right-radius: 0.375rem !important;
        }
        .section-title-banner {
            background-color: #f1f3f5;
            border-left: 4px solid var(--color-guinda);
            padding: 8px 15px;
            font-weight: 700;
            color: var(--color-guinda);
            margin-bottom: 20px;
        }
        /* Botones Personalizados */
        .btn-guinda {
            background-color: var(--color-guinda);
            color: #ffffff;
            border: none;
        }
        .btn-guinda:hover, .btn-guinda:focus {
            background-color: var(--color-guinda-dark);
            color: #ffffff;
        }
        .btn-oro {
            background-color: var(--color-oro);
            color: #ffffff;
            border: none;
            font-weight: 500;
        }
        .btn-oro:hover, .btn-oro:focus {
            background-color: var(--color-oro-dark);
            color: #ffffff;
        }
        /* Styles FullCalendar Eventos */
        #calendar {
            width: 100%;
            min-height: 520px;
        }
        .fc-event-disponible {
            background-color: #26c03a !important;
            border-color: #26c03a !important;
            color: #fff !important;
            cursor: pointer;
        }
        .fc-event-expirado {
            background-color: #8a959e !important;
            border-color: #8a959e !important;
            color: #fff !important;
            cursor: not-allowed;
        }
        .fc-event-inhabil {
            background-color: #3B78DB !important;
            border-color: #3B78DB !important;
            color: #fff !important;
            cursor: not-allowed;
        }
        .fc-event-ocupado {
            background-color: #dc3545 !important;
            border-color: #dc3545 !important;
            color: #fff !important;
            cursor: not-allowed;
        }
        .fc-event-selected {
            border: 3px solid var(--color-oro) !important;
            box-shadow: 0 0 10px rgba(206, 168, 69, 0.8);
        }
        /* Loader */
        .loader {
            position: fixed;
            left: 0; top: 0;
            width: 100%; height: 100%;
            z-index: 9999;
            background: url("{{ asset('assets/images/pageLoader.gif') }}") 50% 50% no-repeat rgba(255,255,255,0.85);
        }
    </style>
    @livewireStyles
</head>
<body onload="validarcheckfolio()">
    <!-- Navbar Superior -->
    <nav class="navbar navbar-expand-lg navbar-light navbar-institucional fixed-top px-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="{{ asset('assets/images/Logos 2.png') }}" alt="CCL Michoacán" height="65">
            </a>
            <span class="navbar-text fw-bold d-none d-md-block text-secondary">
                Centro de Conciliación Laboral del Estado de Michoacán
            </span>
        </div>
    </nav>
    <main class="container mb-5">
        <div id="app">
            <section class="section">
                <div class="card shadow-sm border-0">
                    <div class="card-header card-header-guinda py-3">
                        <h4 class="m-0 text-center fs-5"><i class="bi bi-calendar-check me-2"></i>Genera tu Cita para Ratificación</h4>
                    </div>
                    <div class="card-body p-4">
                        <!-- Alertas Flash Mejoradas -->
                        @if(session()->has('success'))
                            <div class="alert alert-success border-2 shadow-sm rounded-3 p-4 mb-4" role="alert">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-check-circle-fill text-success fs-2 me-3"></i>
                                    <div>
                                        <h5 class="alert-heading mb-0 fw-bold">¡Solicitud Registrada Exitosamente!</h5>
                                        <p class="mb-0 text-muted small">Por favor tome captura o conserve los datos de su cita.</p>
                                    </div>
                                </div>
                                <hr>
                                <p class="mb-2 fs-6">
                                    {{ session()->get('success') }}
                                </p>
                                <div class="d-flex justify-content-end gap-2 mt-3">
                                    <button onclick="window.print();" class="btn btn-sm btn-outline-success">
                                        <i class="bi bi-printer me-1"></i> Imprimir Confirmación
                                    </button>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            </div>
                        @endif
                        @if (session()->has('error'))
                            <div class="alert alert-danger alert-dismissible fade show shadow-sm p-3 mb-4" role="alert">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-exclamation-octagon-fill text-danger fs-3 me-3"></i>
                                    <div>
                                        <strong>¡Error al procesar la solicitud!</strong>
                                        <div>{{ session()->get('error') }}</div>
                                    </div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        <form class="needs-validation" novalidate method="POST" action="{{ route('turnos.publico') }}" enctype="multipart/form-data" id="formRatificacion">
                            @csrf
                            <!-- Folio Interno -->
                            <div class="row g-3 mb-4 align-items-end">
                                <div class="col-12 col-md-8">
                                    <div class="p-3 bg-light rounded border">
                                        <label class="form-label mb-1 fw-bold">¿No cuenta con un Folio Interno?</label>
                                        <p class="text-muted small mb-0">Puede registrarse previamente en la siguiente liga para trámites posteriores: 
                                            <a href="{{ route('poder-crear') }}" target="_blank" class="btn btn-sm btn-outline-primary ms-2"><i class="bi bi-person-plus"></i> Registrar</a>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label for="folio_input" class="form-label fw-bold">Folio Interno de Registro <span class="text-danger">*</span></label>
                                    <input type="number" name="folio" id="folio_input" class="form-control" placeholder="Ingrese número de folio" required>
                                    <div class="invalid-feedback">El folio es obligatorio.</div>
                                </div>
                            </div>
                            <div id="abogado_info" class="mb-3"></div>
                            
                            <!-- Bloque del Formulario Dinámico -->
                            <div id="datos_formulario" style="display:none;">
                                <!-- SECCIÓN 1: DATOS DEL TRABAJADOR -->
                                <div class="section-title-banner mt-3">
                                    <i class="bi bi-person-badge me-2"></i>1. Datos del Trabajador
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Primer apellido <span class="text-danger">*</span></label>
                                        <input type="text" name="primero_trabajador" class="form-control text-uppercase soloLetras" required>
                                        <div class="invalid-feedback">El primer apellido es obligatorio.</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Segundo apellido</label>
                                        <input type="text" name="segundo_trabajador" id="segundo_apellido" class="form-control text-uppercase soloLetras">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Nombre(s) <span class="text-danger">*</span></label>
                                        <input type="text" name="trabajador" class="form-control text-uppercase soloLetras" required>
                                        <div class="invalid-feedback">El nombre es obligatorio.</div>
                                    </div>
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Nacionalidad <span class="text-danger">*</span></label>
                                        <select name="nacionalidad" id="nacionalidad" class="form-select" required>
                                            <option value="">SELECCIONE</option>
                                            <option value="MEXICANA">MEXICANA</option>
                                            <option value="EXTRANJERA">EXTRANJERA</option>
                                        </select>
                                        <div class="invalid-feedback">La nacionalidad es obligatoria.</div>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-bold">Edad <span class="text-danger">*</span></label>
                                        <input type="number" min="15" max="120" name="trabajador_edad" class="form-control" required>
                                        <div class="invalid-feedback">Edad válida requerida.</div>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-bold">Sexo <span class="text-danger">*</span></label>
                                        <select name="trabajador_sexo" class="form-select" required>
                                            <option value="">Seleccione</option>
                                            <option value="H">Hombre</option>
                                            <option value="M">Mujer</option>
                                        </select>
                                        <div class="invalid-feedback">Seleccione sexo.</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">CURP del trabajador <span class="text-danger">*</span></label>
                                        <input type="text" name="trabajador_curp" id="trabajador_curp" class="form-control text-uppercase" maxlength="18" required>
                                        <div class="invalid-feedback">El CURP es obligatorio.</div>
                                    </div>
                                </div>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Identificación Oficial <span class="text-danger">*</span></label>
                                        <select id="tipo_identificacion" name="tipo_identificacion" class="form-select" required>
                                            <option value="">Seleccione tipo...</option>
                                            <option value="Credencial de elector">Credencial de Elector (INE)</option>
                                            <option value="Pasaporte">Pasaporte</option>
                                            <option value="Cédula profesional">Cédula Profesional</option>
                                            <option value="Licencia de conducir">Licencia de Conducir</option>
                                            <option value="Credencial de inapam">Credencial de INAPAM</option>
                                            <option value="Cartilla militar">Cartilla Militar</option>
                                            <option value="Documento migratorio">Documento Migratorio</option>
                                            <option value="Constancia de identidad">Constancia de Identidad</option>
                                            <option value="Otro">Otros</option>
                                        </select>
                                        <div class="invalid-feedback">Seleccione un tipo de identificación.</div>
                                    </div>
                                    <div class="col-md-4" id="espesificar_tipo_identificacion" style="display:none">
                                        <label class="form-label fw-bold">Especificar Identificación <span class="text-danger">*</span></label>
                                        <input type="text" name="tipo_otros" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Núm. de Identificación <span class="text-danger">*</span> 
                                            <i class="bi bi-question-circle-fill text-primary" data-bs-toggle="modal" data-bs-target="#helpModal" style="cursor: pointer;" title="¿Dónde ubicarlo?"></i>
                                        </label>
                                        <input type="text" name="num_identificacion" maxlength="13" minlength="3" class="form-control text-uppercase" placeholder="De 3 a 13 caracteres" required>
                                        <div class="invalid-feedback">Ingrese número válido.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Subir Identificación (PDF máx. 10MB) <span class="text-danger">*</span></label>
                                        <input type="file" id="documentoidentificacion" name="documentoidentificacion" class="form-control" accept=".pdf" required>
                                        <div class="invalid-feedback">Adjunte identificación en formato PDF.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Documento de la CURP (Opcional) (PDF máx. 10MB) </label>
                                        <input type="file" id="documentoCurp" name="documentoCurp" class="form-control" accept=".pdf" >
                                    </div>
                                </div>
                                
                                <!-- SECCIÓN 2: DATOS DE LA RELACIÓN LABORAL -->
                                <div class="section-title-banner mt-4">
                                    <i class="bi bi-briefcase me-2"></i>2. Datos de la Relación Laboral
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Estado <span class="text-danger">*</span></label>
                                        <select name="estado_rat" class="form-select" required>
                                            <option value="">Seleccione</option>
                                            @foreach($estados as $est)
                                                <option value="{{$est['id']}}">{{$est['nombre']}}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">Este campo es obligatorio.</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Municipio o Alcaldía <span class="text-danger">*</span></label>
                                        <select id="municipio_rat" name="municipio_rat" class="form-select" required>
                                            <option value="">Seleccione</option>
                                            @foreach($municipios as $mun)
                                                <option value="{{$mun['id']}}">{{$mun['nombre']}}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">Municipio obligatorio.</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Tipo de Vialidad <span class="text-danger">*</span></label>
                                        <select id="tipo_vialidad" name="tipo_vialidad" class="form-select" required>
                                            <option value="">SELECCIONE</option>
                                            <option value="AMPLIACIÓN">Ampliación</option>
                                            <option value="ANDADOR">Andador</option>
                                            <option value="AUTOPISTA">Autopista</option>
                                            <option value="AVENIDA">Avenida</option>
                                            <option value="BOULEVARD">Boulevard</option>
                                            <option value="CALLE">Calle</option>
                                            <option value="CALLEJÓN">Callejón</option>
                                            <option value="CALZADA">Calzada</option>
                                            <option value="CARRETERA">Carretera</option>
                                            <option value="CERRADA">Cerrada</option>
                                            <option value="CIRCUITO">Circuito</option>
                                            <option value="CIRCUNVALACIÓN">Circunvalación</option>
                                            <option value="CONTINUACIÓN">Continuación</option>
                                            <option value="CORREDOR">Corredor</option>
                                            <option value="DIAGONAL">Diagonal</option>
                                            <option value="EJE VIAL">Eje vial</option>
                                            <option value="PERIFÉRICO">Periférico</option>
                                            <option value="PRIVADA">Privada</option>
                                            <option value="PROLONGACIÓN">Prolongación</option>
                                            <option value="RETORNO">Retorno</option>
                                            <option value="VIADUCTO">Viaducto</option>
                                        </select>
                                        <div class="invalid-feedback">Categoría requerida.</div>
                                    </div>
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Nombre de la vialidad <span class="text-danger">*</span></label>
                                        <input type="text" maxlength="50" name="vialidad_calle" id="vialidad_calle" class="form-control" oninput="this.value = this.value.toUpperCase()" required>
                                        <div class="invalid-feedback">Vialidad obligatoria.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Colonia <span class="text-danger">*</span></label>
                                        <input type="text" maxlength="50" name="colonia" id="colonia" oninput="this.value = this.value.toUpperCase()" class="form-control" required>
                                        <div class="invalid-feedback">Colonia obligatoria.</div>
                                    </div>
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-2">
                                        <label class="form-label fw-bold">Núm. Ext. <span class="text-danger">*</span></label>
                                        <input type="text" maxlength="20" name="N_Ext" id="N_Ext" oninput="this.value = this.value.toUpperCase()" class="form-control" required>
                                        <div class="invalid-feedback">Dato obligatorio.</div>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-bold">Núm. Int. (Opcional) </label>
                                        <input type="text" maxlength="10" name="N_Int" id="N_Int" oninput="this.value = this.value.toUpperCase()" class="form-control text-uppercase">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-bold">Código Postal<span class="text-danger">*</span></label>
                                        <input type="number" min="0" max="99999" maxlength="5" class="form-control text-uppercase" name="cp" id="cp" oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" required>
                                        <div class="invalid-feedback">C.P. obligatorio.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">¿Existe procedimiento previo en la junta local de conciliación y arbitraje? <span class="text-danger">*</span></label>
                                        <select name="JLCA" class="form-select" required>
                                            <option value="">Seleccione</option>
                                            <option value="Si">Si</option>
                                            <option value="No">No</option>
                                        </select>
                                        <div class="invalid-feedback">Dato requerido.</div>
                                    </div>
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">Fecha Inicio <span class="text-danger">*</span></label>
                                        <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" required>
                                        <div class="invalid-feedback">Fecha inicio obligatoria.</div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">Fecha Término (Opcional) </label>
                                        <input type="date" name="fecha_termino" id="fecha_termino" class="form-control">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">Categoría o Puesto <span class="text-danger">*</span></label>
                                        <input type="text" name="categoria" class="form-control text-uppercase" required>
                                        <div class="invalid-feedback">Categoría requerida.</div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">Frecuencia de Pago <span class="text-danger">*</span></label>
                                        <select name="frecuencia" class="form-select" required>
                                            <option value="">Seleccione</option>
                                            <option value="Diario">Diario</option>
                                            <option value="Semanal">Semanal</option>
                                            <option value="Quincenal">Quincenal</option>
                                            <option value="Mensual">Mensual</option>
                                        </select>
                                        <div class="invalid-feedback">Seleccione frecuencia.</div>
                                    </div>
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">Salario ($) <span class="text-danger">*</span></label>
                                        <input type="text" name="salario" id="salario" placeholder="0.00" class="form-control soloMontos" required>
                                        <div class="invalid-feedback">Ingrese un salario válido.</div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">Días trabajados/semana <span class="text-danger">*</span></label>
                                        <input type="number" min="1" max="7" name="dias" class="form-control" required>
                                        <div class="invalid-feedback">Ingrese días.</div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">Motivo Conciliación <span class="text-danger">*</span></label>
                                        <select id="motivo" name="motivo" class="form-select" required>
                                            <option value="">Seleccione</option>
                                            <option value="Pago de prestaciones">Pago de prestaciones</option>
                                            <option value="Terminación voluntaria de la relación de trabajo">Terminación voluntaria</option>
                                            <option value="PTU">Pago de PTU</option>
                                        </select>
                                        <div class="invalid-feedback">Motivo requerido.</div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">Monto total convenio ($) <span class="text-danger">*</span></label>
                                        <input type="text" name="monto" id="monto" placeholder="0.00" class="form-control soloMontos" required>
                                        <div class="invalid-feedback">Ingrese un monto válido.</div>
                                        <div class="mt-1">
                                            <a href="https://cclmichoacan.gob.mx/calculadora.html" target="_blank" class="small text-decoration-none">
                                                <i class="bi bi-calculator"></i> Calcular monto aproximado
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Checkboxes de Prestaciones -->
                                <div class="row g-3 mb-3" id="motivo_pago" style="display:none">
                                    <div class="col-12">
                                        <div class="p-3 border rounded bg-white">
                                            <label class="form-label fw-bold mb-2">Prestaciones a incluir:</label>
                                            <div class="row">
                                                <div class="col-md-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="Aguinaldo" id="chk1"><label class="form-check-label" for="chk1">Aguinaldo</label></div></div>
                                                <div class="col-md-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="Vacaciones" id="chk2"><label class="form-check-label" for="chk2">Vacaciones</label></div></div>
                                                <div class="col-md-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="PrimaVacacional" id="chk3"><label class="form-check-label" for="chk3">Prima Vacacional</label></div></div>
                                                <div class="col-md-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="PagoPTU" id="chk4"><label class="form-check-label" for="chk4">Pago de PTU</label></div></div>
                                                <div class="col-md-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="Gratificación" id="chk5"><label class="form-check-label" for="chk5">Gratificación</label></div></div>
                                                <div class="col-md-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="PrimaAntigüedad" id="chk6"><label class="form-check-label" for="chk6">Prima Antigüedad</label></div></div>
                                                <div class="col-md-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="Otras" id="otras"><label class="form-check-label" for="otras">Otras</label></div></div>
                                            </div>
                                            <div id="motivoPagoWarning" class="text-danger small mt-2" style="display:none;">
                                                Debes seleccionar al menos una casilla.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3" id="div_otras" style="display:none">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Especifique otras prestaciones</label>
                                        <input type="text" name="Especifique" class="form-control">
                                    </div>
                                </div>
                                
                                <div class="row g-3 mb-4">
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">Forma de Pago <span class="text-danger">*</span></label>
                                        <select name="tipo_pago" class="form-select" required>
                                            <option value="">Seleccione</option>
                                            <option value="Efectivo">Efectivo</option>
                                            <option value="Transferencia">Transferencia</option>
                                            <option value="Cheque">Cheque</option>
                                            <option value="Cheque Electrónico">Cheque Electrónico</option>
                                            <option value="Orden de Pago">Orden de Pago</option>
                                        </select>
                                        <div class="invalid-feedback">Forma de pago requerida.</div>
                                    </div>
                                    <!-- AQUI ESTA EL CAMPO FALTANTE -->
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">Sube tu cuantificación (Opcional)</label>
                                        <input type="file" id="cuantificacion" name="cuantificacion" class="form-control" accept=".pdf">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">Sede para el trámite <span class="text-danger">*</span></label>
                                        <select id="sede" name="sede" class="form-select" onchange="modalCalendar();" required>
                                            <option value="">Seleccione Sede</option>
                                            <option value="Morelia">Morelia</option>
                                            <option value="Uruapan">Uruapan</option>
                                            <option value="Zamora">Zamora</option>
                                            <option value="Zitácuaro">Zitácuaro</option>
                                            <option value="Lázaro Cárdenas">Lázaro Cárdenas</option>
                                            <option value="Sahuayo">Sahuayo</option>
                                        </select>
                                        <div class="invalid-feedback">Sede obligatoria.</div>
                                    </div>
                                    <div class="col-md-3 d-flex align-items-end">
                                        <button type="button" id="botonCalendar" class="btn btn-guinda w-100 py-2" data-bs-toggle="modal" data-bs-target="#calendarModal" disabled>
                                            <i class="bi bi-calendar3 me-2"></i>Seleccionar Fecha/Horario
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Entradas ocultas para fecha/hora -->
                                <input type="hidden" name="fecha" id="fechaSeleccionada" required>
                                <input type="hidden" name="hora" id="horaSeleccionada" required>
                                
                                <!-- Card Resumen Cita -->
                                <div id="resumenCita" class="mb-4" style="display: none;">
                                    <div class="alert alert-success d-flex align-items-center mb-0">
                                        <i class="bi bi-calendar-check-fill fs-4 me-3"></i>
                                        <div>
                                            <strong>Cita Programada:</strong> <span id="fechaResumen" class="fw-bold"></span> a las <span id="horaResumen" class="fw-bold"></span> hrs.
                                        </div>
                                    </div>
                                </div>
                                <div id="fechaHoraWarning" class="alert alert-danger text-center mb-4" style="display: none;">
                                    Debes seleccionar una fecha y horario en el calendario antes de guardar la cita.
                                </div>

                                <!-- Botones de Acción -->
                                <div class="text-center pt-3 border-top">
                                    <button type="submit" class="btn btn-oro btn-lg px-5 me-2">
                                        <i class="bi bi-box-arrow-down me-1"></i> Guardar Cita
                                    </button>
                                    <a href="{{ route('publico') }}" class="btn btn-outline-secondary btn-lg px-4">
                                        Regresar
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Modal FullCalendar -->
    <div class="modal fade" id="calendarModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header card-header-guinda py-2">
                    <h5 class="modal-title text-white fs-6"><i class="bi bi-clock me-2"></i>Seleccionar Horario Disponible</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="calendar"></div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-oro btn-sm px-4" id="confirmarSeleccion">Confirmar Horario</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Ayuda INE -->
    <div class="modal fade" id="helpModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fs-6">Ubicación del Número de Identificación (INE)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="{{ asset('assets/images/capturaIne.png') }}" alt="Identificación INE" class="img-fluid rounded border">
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts Bootstrap y Funcionalidades de Frontend -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function modalCalendar() {
            const sede = document.getElementById("sede").value;
            const btn = document.getElementById("botonCalendar");
            if(sede !== "") {
                btn.removeAttribute("disabled");
            } else {
                btn.setAttribute("disabled", "true");
            }
        }
        function validarcheckfolio() {
            // Se mantiene por consistencia del body onload, pero la vista actual oculta y muestra el 
            // form dinamicamente basado en la API fetch de validar_folio_abogado.
        }

        document.addEventListener('DOMContentLoaded', function () {
            
            // 1. CARGA DINAMICA DE MUNICIPIOS
            function cargarMunicipiosSolicitante(estadoId) {
                var $municipio = $('#municipio_rat');
                if (!$municipio.length) return;
                $municipio.html('<option value="">Cargando...</option>');
                if (!estadoId) {
                    $municipio.html('<option value="">Seleccione</option>');
                    return;
                }
                $.get(base_url + '/api/munSolicitante/' + estadoId, function (data) {
                    var html = '<option value="">Seleccione</option>';
                    data.forEach(function (m) {
                        html += '<option value="' + m.id + '">' + m.nombre + '</option>';
                    });
                    $municipio.html(html);
                }).fail(function (jqXHR, textStatus, errorThrown) {
                    $.get(base_url + '/munSolicitante/' + estadoId, function (data) {
                        var html = '<option value="">Seleccione</option>';
                        data.forEach(function (m) {
                            html += '<option value="' + m.id + '">' + m.nombre + '</option>';
                        });
                        $municipio.html(html);
                    }).fail(function (jq2, t2, e2) {
                        $municipio.html('<option value="">Error cargando municipios</option>');
                    });
                });
            }
            var $estadoSolicitante = $('select[name="estado_rat"]');
            var base_url = "{{ url('') }}";
            if ($estadoSolicitante.length) {
                $estadoSolicitante.on('change', function () {
                    cargarMunicipiosSolicitante(this.value);
                });
                var inicial = $estadoSolicitante.val();
                if (inicial) cargarMunicipiosSolicitante(inicial);
            }

            // 2. RESTRICCIÓN DE SÓLO NÚMEROS Y DECIMALES PARA SALARIO Y MONTO
            const inputsMontos = document.querySelectorAll('.soloMontos');
            inputsMontos.forEach(input => {
                input.addEventListener('input', function () {
                    this.value = this.value.replace(/[^0-9.]/g, '');
                    if ((this.value.match(/\./g) || []).length > 1) {
                        this.value = this.value.replace(/\.+$/, '');
                    }
                });
            });

            // 3. RESTRICCIÓN DE TAMAÑO MÁXIMO DE ARCHIVOS A 10 MB (IDENTIFICACION, CURP, CUANTIFICACION)
            const inputsArchivos = ['documentoidentificacion', 'documentoCurp', 'cuantificacion'];
            inputsArchivos.forEach(id => {
                const inputFile = document.getElementById(id);
                if (inputFile) {
                    inputFile.addEventListener('change', function(e) {
                        const archivo = e.target.files[0];
                        if (archivo) {
                            const limite10MB = 10 * 1024 * 1024;
                            if (archivo.size > limite10MB) {
                                alert("El documento en PDF no puede ser mayor a 10 Megabytes (10 MB).");
                                this.value = ""; 
                            }
                        }
                    });
                }
            });

            // 4. BÚSQUEDA AJAX DE FOLIO DE ABOGADO
            const folioInput = document.getElementById('folio_input');
            const abogadoInfoDiv = document.getElementById('abogado_info');
            const datosFormulario = document.getElementById('datos_formulario');
            let timeout = null;
            const baseUrl = "{{ url('/validar_folio_abogado') }}";
            
            if (folioInput) {
                folioInput.addEventListener('keyup', function () {
                    clearTimeout(timeout);
                    const folio = this.value.trim();
                    if (folio === '') {
                        abogadoInfoDiv.textContent = '';
                        abogadoInfoDiv.className = '';
                        if (datosFormulario) datosFormulario.style.display = 'none';
                        return;
                    }
                    timeout = setTimeout(() => {
                        fetch(`${baseUrl}/${folio}`, {
                            method: 'GET',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            }
                        })
                        .then(response => {
                            if (!response.ok) {
                                if (response.status === 404) throw new Error('Folio no encontrado');
                                throw new Error('Error en la petición');
                            }
                            return response.json();
                        })
                        .then(data => {
                            abogadoInfoDiv.className = 'alert mt-2';
                            const status = data && data.status ? data.status : null;
                            const msg = data && data.message ? data.message : '';
                            if (status === 'elegible') {
                                abogadoInfoDiv.innerHTML = `<i class="bi bi-check-circle-fill me-2"></i><strong>Representante:</strong> ${data.nombre} — ${msg}`;
                                abogadoInfoDiv.classList.add('alert-success');
                                if (datosFormulario) datosFormulario.style.display = 'block';
                            } else {
                                abogadoInfoDiv.innerHTML = `<i class="bi bi-exclamation-circle-fill me-2"></i><strong>Representante:</strong> ${data.nombre} — ${msg}`;
                                abogadoInfoDiv.classList.add('alert-danger');
                                if (datosFormulario) datosFormulario.style.display = 'none';
                            }
                        })
                        .catch(error => {
                            abogadoInfoDiv.className = 'alert alert-danger mt-2';
                            abogadoInfoDiv.innerHTML = `<i class="bi bi-x-circle-fill me-2"></i> ${error.message === 'Folio no encontrado' ? 'El folio no existe. Por favor, verifique el número.' : 'Ocurrió un error al buscar.'}`;
                            if (datosFormulario) datosFormulario.style.display = 'none';
                        });
                    }, 400);
                });
            }

            // 5. MANEJO DINÁMICO DE "OTROS" E IDENTIFICACIONES
            const tipoIden = document.getElementById('tipo_identificacion');
            if (tipoIden) {
                tipoIden.addEventListener('change', function() {
                    const divEspecificar = document.getElementById('espesificar_tipo_identificacion');
                    const inputEspecificar = divEspecificar.querySelector('input');
                    if (this.value === 'Otro') {
                        divEspecificar.style.display = 'block';
                        inputEspecificar.setAttribute('required', '');
                    } else {
                        divEspecificar.style.display = 'none';
                        inputEspecificar.removeAttribute('required');
                    }
                });
            }

            const motivo = document.getElementById('motivo');
            if (motivo) {
                motivo.addEventListener('change', function() {
                    document.getElementById('motivo_pago').style.display = (this.value === 'Pago de prestaciones') ? 'block' : 'none';
                });
            }

            const otras = document.getElementById('otras');
            if (otras) {
                otras.addEventListener('change', function() {
                    document.getElementById('div_otras').style.display = this.checked ? 'block' : 'none';
                });
            }
            
            // 6. VALIDACIÓN DE FECHAS (No mayor a hoy y lógica inicio/término)
            const inicio = document.querySelector('input[name="fecha_inicio"]');
            const termino = document.querySelector('input[name="fecha_termino"]');
            
            function obtenerFechaHoyFormato() {
                return new Date().toISOString().split('T')[0];
            }
            function esFechaValida(fechaStr) {
                return /^\d{4}-\d{2}-\d{2}$/.test(fechaStr) && !isNaN(new Date(fechaStr).getTime());
            }
            function validarFechas() {
                const fechaHoyStr = obtenerFechaHoyFormato();
                const fechaInicioStr = inicio.value;
                const fechaTerminoStr = termino.value;

                if (!esFechaValida(fechaInicioStr) && fechaInicioStr !== "") return;
                if (!esFechaValida(fechaTerminoStr) && fechaTerminoStr !== "") return;

                if (fechaInicioStr === fechaHoyStr) {
                    alert("La fecha de inicio no puede ser la fecha actual.");
                    inicio.value = ""; return;
                }
                if (fechaInicioStr && new Date(fechaInicioStr) > new Date(fechaHoyStr)) {
                    alert("La fecha de inicio no puede ser mayor a la fecha actual.");
                    inicio.value = ""; return;
                }
                if (fechaTerminoStr && new Date(fechaTerminoStr) > new Date(fechaHoyStr)) {
                    alert("La fecha de término no puede ser mayor a la fecha actual.");
                    termino.value = ""; return;
                }
                if (fechaInicioStr && fechaTerminoStr && new Date(fechaInicioStr) > new Date(fechaTerminoStr)) {
                    alert("La fecha de inicio no puede ser mayor que la fecha de término.");
                    termino.value = ""; return;
                }
            }
            if(inicio) inicio.addEventListener("blur", validarFechas);
            if(termino) termino.addEventListener("blur", validarFechas);

            // 7. CALENDARIO FULLCALENDAR 6
            const calendarEl = document.getElementById('calendar');
            if (calendarEl) {
                const calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridWeek',
                    locale: 'es',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridWeek,timeGridDay'
                    },
                    events: function(fetchInfo, successCallback, failureCallback) {
                        const sede = document.getElementById('sede').value;
                        $.ajax({
                            url: 'api/obtenerEventos',
                            method: 'GET',
                            data: { sede: sede, start: fetchInfo.startStr, end: fetchInfo.endStr },
                            success: function(data) { successCallback(data); },
                            error: function() { failureCallback('Error al cargar eventos'); }
                        });
                    },
                    eventClick: function(info) {
                        const ahora = new Date();
                        const slotDate = new Date(info.event.start);
                        if (info.event.extendedProps.estado === 'disponible' && slotDate > ahora) {
                            document.querySelectorAll('.fc-event-selected').forEach(el => {
                                el.classList.remove('fc-event-selected');
                            });
                            info.el.classList.add('fc-event-selected');
                            window.selectedEvent = info.event;
                        } else {
                            alert('Este horario no se encuentra disponible.');
                        }
                    },
                    eventDidMount: function(info) {
                        const estado = info.event.extendedProps.estado;
                        if (estado === 'disponible') info.el.classList.add('fc-event-disponible');
                        else if (estado === 'expirado') info.el.classList.add('fc-event-expirado');
                        else if (estado === 'inhabil') info.el.classList.add('fc-event-inhabil');
                        else info.el.classList.add('fc-event-ocupado');
                    }
                });
                calendar.render();
                $('#calendarModal').on('shown.bs.modal', function () {
                    calendar.refetchEvents();
                    calendar.updateSize();
                });
                document.getElementById('confirmarSeleccion').addEventListener('click', function() {
                    if (window.selectedEvent) {
                        const fechaHora = new Date(window.selectedEvent.start);
                        const fecha = fechaHora.toISOString().split('T')[0];
                        const hora = fechaHora.toTimeString().substring(0, 5);
                        document.getElementById('fechaSeleccionada').value = fecha;
                        document.getElementById('horaSeleccionada').value = hora;
                        document.getElementById('fechaResumen').textContent = fecha;
                        document.getElementById('horaResumen').textContent = hora;
                        document.getElementById('resumenCita').style.display = 'block';
                        document.getElementById('fechaHoraWarning').style.display = 'none';
                        const modal = bootstrap.Modal.getInstance(document.getElementById('calendarModal'));
                        modal.hide();
                    } else {
                        alert('Por favor, selecciona un horario disponible en el calendario.');
                    }
                });
            }

            // 8. NACIONALIDAD (OBLIGATORIO SEGUNDO APELLIDO SI ES MEXICANA)
            const selectNacionalidad = document.getElementById('nacionalidad');
            const apellido = document.getElementById('segundo_apellido');
            function actualizarApellido() {
                if (!selectNacionalidad) return;
                const valor = selectNacionalidad.value;
                if (valor === 'MEXICANA' || valor === '') {
                    apellido.setAttribute('required', '');
                } else if (valor === 'EXTRANJERA') {
                    apellido.removeAttribute('required');
                }
            }
            if (selectNacionalidad) selectNacionalidad.addEventListener('change', actualizarApellido);
            actualizarApellido();

            // 9. VALIDACIONES DE FORMULARIO PRE-SUBMIT (FECHA SELECCIONADA Y PRESTACIONES)
            const form = document.getElementById('formRatificacion');
            const checkboxesPrestaciones = document.querySelectorAll('#motivo_pago input[type="checkbox"]');
            const motivoPagoWarning = document.getElementById('motivoPagoWarning');
            const fechaHoraWarning = document.getElementById('fechaHoraWarning');

            function algunaCasillaMarcada() {
                return Array.from(checkboxesPrestaciones).some(cb => cb.checked);
            }

            checkboxesPrestaciones.forEach(function(cb) {
                cb.addEventListener('change', function() {
                    if (algunaCasillaMarcada()) {
                        motivoPagoWarning.style.display = 'none';
                    }
                });
            });

            if (form) {
                form.addEventListener('submit', function (event) {
                    let detieneEnvio = false;

                    // Validaciones nativas
                    if (!form.checkValidity()) {
                        detieneEnvio = true;
                    }

                    // Valida si seleccionó cita en calendario
                    const fecha = document.getElementById('fechaSeleccionada').value;
                    const hora = document.getElementById('horaSeleccionada').value;
                    if (!fecha || !hora) {
                        fechaHoraWarning.style.display = 'block';
                        document.getElementById('botonCalendar').scrollIntoView({ behavior: 'smooth', block: 'center' });
                        detieneEnvio = true;
                    } else {
                        fechaHoraWarning.style.display = 'none';
                    }

                    // Valida Checkboxes de Prestaciones
                    if (motivo && motivo.value === 'Pago de prestaciones' && !algunaCasillaMarcada()) {
                        motivoPagoWarning.style.display = 'block';
                        document.getElementById('motivo_pago').scrollIntoView({ behavior: 'smooth', block: 'center' });
                        detieneEnvio = true;
                    }

                    if(detieneEnvio) {
                        event.preventDefault();
                        event.stopPropagation();
                    }

                    form.classList.add('was-validated');
                }, false);
            }
        });
    </script>
</body>
</html>