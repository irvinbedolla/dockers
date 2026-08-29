<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <meta name="description" content="SiConcilio - Datos del Solicitante">
    <meta name="author" content="Centro de Conciliación Laboral de Michoacán">
    <link rel="icon" href="{{ asset('public/assets/images/ccl-r.png') }}" type="image/x-icon">
    
    <title>SiConcilio - Datos del Solicitante</title>

    <!-- Bootstrap 5.3 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Select2 & SweetAlert2 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        :root {
            --primary-guinda: #496163;
            --primary-hover: #4a0a33;
            --accent-dorado: #CEA845;
            --accent-hover: #b8933b;
            --bg-light: #f4f6f9;
            --text-dark: #2c3e50;
        }

        body {
            background-color: var(--bg-light);
            color: var(--text-dark);
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Navbar */
        .navbar-institutional {
            background-color: #ffffff;
            border-bottom: 3px solid var(--accent-dorado);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .nav-link-custom {
            color: var(--text-dark) !important;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: color 0.2s ease;
        }

        .nav-link-custom:hover {
            color: var(--primary-guinda) !important;
        }

        /* Tarjeta Principal */
        .main-card {
            background: #ffffff;
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            padding: 2rem;
        }

        .title-header {
            color: var(--primary-guinda);
            font-weight: 800;
            text-transform: uppercase;
        }

        /* Banners de Subsecciones */
        .section-banner {
            background: linear-gradient(135deg, var(--primary-guinda), var(--primary-hover));
            color: #ffffff;
            border-radius: 8px;
            padding: 0.6rem 1rem;
            margin-top: 1.5rem;
            margin-bottom: 1.25rem;
        }

        .section-banner h5 {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        /* Labels y Form */
        .form-label {
            font-weight: 600;
            color: #333;
            font-size: 0.88rem;
            margin-bottom: 0.35rem;
        }

        .text-required {
            color: #dc3545;
            font-weight: bold;
        }

        /* Indicador de CURP */
        #resultado {
            font-size: 0.8rem;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            margin-top: 0.25rem;
            display: none;
        }

        /* Botones */
        .btn-dorado {
            background-color: var(--accent-dorado);
            border-color: var(--accent-dorado);
            color: #ffffff !important;
            font-weight: 700;
            padding: 0.6rem 2.5rem;
            border-radius: 6px;
            box-shadow: 0 4px 10px rgba(206, 168, 69, 0.25);
            transition: all 0.2s ease;
        }

        .btn-dorado:hover {
            background-color: var(--accent-hover);
            border-color: var(--accent-hover);
            transform: translateY(-1px);
        }

        /* Overlay Loader */
        .loader-overlay {
            position: fixed;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            z-index: 9999;
            background: rgba(255, 255, 255, 0.85) url('{{ asset("assets/images/pageLoader.gif") }}') 50% 50% no-repeat;
        }
    </style>
</head>
<body>

    <!-- Loader de Envíos -->
    <div id="crear_poder" class="loader-overlay" style="display: none;"></div>

    <!-- Navegación Fija -->
    <nav class="navbar navbar-expand-lg navbar-institutional fixed-top py-2">
        <div class="container-fluid px-4">
            <a class="navbar-brand py-0" href="{{ route('login') }}">
                <img src="{{ asset('assets/images/Logos 2.png') }}" alt="Logo CCL Michoacán" height="55">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom d-flex align-items-center gap-1" href="{{ route('login') }}">
                            <i class="bi bi-house-door-fill"></i> INICIO
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div style="margin-top: 90px;"></div>

    <!-- Contenedor Principal -->
    <main class="container my-4 flex-grow-1">
        <div id="app">
            <section class="section">
                <div class="main-card">

                    <div class="text-center mb-3">
                        <h2 class="title-header h3 mb-1">Solicitud de Conciliación</h2>
                    </div>

                    <!-- Mensajes de Alertas -->
                    @if(session()->has('success'))
                        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
                            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                            <div><strong>¡Registro correcto!</strong> {{ session()->get('success') }}</div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <div class="d-flex align-items-center mb-1">
                                <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                                <strong>¡Por favor revise los siguientes campos!</strong>
                            </div>
                            <ul class="mb-0 ps-4 small">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Formulario Principal -->
                    <form id="form-solicitante" class="needs-validation" novalidate method="POST" action="{{ route('parte2') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="id" value="{{ $id }}">
                        <input type="hidden" name="draft_id" value="{{ $draftId ?? request('draft_id') }}">
                        <input type="hidden" name="tipo" value="Fisica">
                        <input type="hidden" name="excepcion" value="No">

                        <!-- SECCIÓN 1: DATOS PERSONALES -->
                        <div class="section-banner shadow-sm">
                            <h5><i class="bi bi-person-fill me-2"></i>1. Datos Personales del Solicitante</h5>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-8">
                                <div class="form-group">
                                    <label for="nombre" class="form-label">Nombre(s) y Apellidos del Solicitante <span class="text-required">(*)</span></label>
                                    <input type="text" name="nombre" id="nombre" maxlength="150" class="form-control" value="{{ old('nombre') }}" required>
                                    <div class="invalid-feedback">El nombre es obligatorio.</div>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="curp_input" class="form-label">CURP / No. de Migración <span class="text-required">(*)</span></label>
                                    <input type="text" name="curp" id="curp_input" maxlength="18" class="form-control" value="{{ old('curp') }}" required>
                                    <div id="resultado"></div>
                                    <div class="invalid-feedback">La CURP debe ser válida (18 caracteres).</div>
                                </div>
                            </div>

                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento <span class="text-required">(*)</span></label>
                                    <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" class="form-control" value="{{ old('fecha_nacimiento') }}" onchange="validarfechaNacimiento()" required>
                                    <div class="invalid-feedback">La fecha de nacimiento es obligatoria.</div>
                                </div>
                            </div>

                            <div class="col-12 col-md-2">
                                <div class="form-group">
                                    <label for="años_edad" class="form-label">Edad <span class="text-required">(*)</span></label>
                                    <input type="number" min="0" name="edad" class="form-control" id="años_edad" value="{{ old('edad') }}" required readonly>
                                    <div class="invalid-feedback">La edad es obligatoria.</div>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="rfc" class="form-label">RFC del Solicitante <span class="text-muted">(Opcional)</span></label>
                                    <input type="text" name="rfc" id="rfc" class="form-control" minlength="13" maxlength="13" value="{{ old('rfc') }}">
                                </div>
                            </div>

                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label for="genero" class="form-label">Sexo <span class="text-required">(*)</span></label>
                                    <select name="genero" id="genero" class="form-select" required>
                                        <option value="">SELECCIONE...</option>
                                        <option value="H" {{ old('genero') == 'H' ? 'selected' : '' }}>HOMBRE</option>
                                        <option value="M" {{ old('genero') == 'M' ? 'selected' : '' }}>MUJER</option>
                                        <option value="NC" {{ old('genero') == 'NC' ? 'selected' : '' }}>PREFIERO NO CONTESTAR</option>
                                    </select>
                                    <div class="invalid-feedback">El sexo es obligatorio.</div>
                                </div>
                            </div>

                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label for="nacionalidad" class="form-label">Nacionalidad <span class="text-required">(*)</span></label>
                                    <select name="nacionalidad" id="nacionalidad" class="form-select" required>
                                        <option value="">SELECCIONE...</option>
                                        <option value="Mexicana" {{ old('nacionalidad', 'Mexicana') == 'Mexicana' ? 'selected' : '' }}>MEXICANA</option>
                                        <option value="Otra" {{ old('nacionalidad') == 'Otra' ? 'selected' : '' }}>OTRA</option>
                                    </select>
                                    <div class="invalid-feedback">La nacionalidad es obligatoria.</div>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="estado_nacimiento" class="form-label">Entidad Federativa de Nacimiento <span class="text-required">(*)</span></label>
                                    <select id="estado_nacimiento" name="estado_nacimiento" class="form-select" required>
                                        <option value="">SELECCIONE...</option>
                                        @foreach($estados as $est)
                                            <option value="{{ $est['id'] }}" {{ old('estado_nacimiento') == $est['id'] ? 'selected' : '' }}>{{ $est['nombre'] }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">La entidad de nacimiento es obligatoria.</div>
                                </div>
                            </div>

                            <!-- Switches / Checkboxes Adaptados -->
                            <div class="col-12 col-md-5 d-flex align-items-center pt-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="check_lenguaje" name="traductor" value="Si">
                                    <label class="form-check-label fw-semibold" for="check_lenguaje">¿Requiere traductor o intérprete?</label>
                                </div>
                            </div>

                            <div class="col-12 col-md-7" id="lenguaje_señas" style="display: none;">
                                <div class="form-group">
                                    <label for="lenguajeRequerido" class="form-label">Especifique el tipo de lenguaje / idioma <span class="text-required">(*)</span></label>
                                    <input type="text" name="lenguaje" id="lenguajeRequerido" class="form-control" value="{{ old('lenguaje') }}">
                                    <div class="invalid-feedback">Debe especificar el idioma o lengua requerida.</div>
                                </div>
                            </div>

                            <div class="col-12 col-md-5 d-flex align-items-center pt-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="check_discapacidad" name="discapacidad" value="Si">
                                    <label class="form-check-label fw-semibold" for="check_discapacidad">¿Tiene alguna discapacidad?</label>
                                </div>
                            </div>

                            <div class="col-12 col-md-7" id="discapacidad_container" style="display: none;">
                                <div class="form-group">
                                    <label for="discapacidadRequerida" class="form-label">Especifique la discapacidad <span class="text-required">(*)</span></label>
                                    <input type="text" name="tipo_discapacidad" id="discapacidadRequerida" class="form-control" value="{{ old('tipo_discapacidad') }}">
                                    <div class="invalid-feedback">Debe especificar el tipo de discapacidad.</div>
                                </div>
                            </div>
                        </div>

                        <!-- SECCIÓN 2: CONTACTO -->
                        <div class="section-banner shadow-sm">
                            <h5><i class="bi bi-telephone-fill me-2"></i>2. Información de Contacto</h5>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="telefono1" class="form-label">Teléfono Celular <span class="text-required">(*)</span></label>
                                    <input type="tel" name="telefono1" id="telefono1" maxlength="10" class="form-control" value="{{ old('telefono1') }}" required>
                                    <div class="invalid-feedback">El teléfono celular debe tener 10 dígitos.</div>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="telefono2" class="form-label">Teléfono Fijo <span class="text-muted">(Opcional)</span></label>
                                    <input type="tel" name="telefono2" id="telefono2" maxlength="10" class="form-control" value="{{ old('telefono2') }}">
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="correo" class="form-label">Correo Electrónico <span class="text-required">(*)</span></label>
                                    <input type="email" name="correo" id="correo" maxlength="60" class="form-control" value="{{ old('correo') }}" required>
                                    <div class="invalid-feedback">Ingrese un correo electrónico válido.</div>
                                </div>
                            </div>
                        </div>

                        <!-- SECCIÓN 3: DOMICILIO -->
                        <div class="section-banner shadow-sm">
                            <h5><i class="bi bi-geo-alt-fill me-2"></i>3. Domicilio del Solicitante</h5>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label for="vialidad" class="form-label">Tipo de Vialidad <span class="text-required">(*)</span></label>
                                    <select name="vialidad" id="vialidad" class="form-select" required>
                                        <option value="">SELECCIONE...</option>
                                        @foreach(['AMPLIACIÓN','ANDADOR','AUTOPISTA','AVENIDA','BOULEVARD','CALLE','CALLEJÓN','CALZADA','CARRETERA','CERRADA','CIRCUITO','CIRCUNVALACIÓN','CONTINUACIÓN','CORREDOR','DIAGONAL','EJE VIAL','PERIFÉRICO','PROLONGACIÓN','PRIVADA','RETORNO','VIADUCTO','PASEO'] as $vial)
                                            <option value="{{ $vial }}" {{ old('vialidad') == $vial ? 'selected' : '' }}>{{ $vial }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">El tipo de vialidad es obligatorio.</div>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="vialidad_calle" class="form-label">Nombre de la Calle / Vialidad <span class="text-required">(*)</span></label>
                                    <input type="text" name="vialidad_calle" id="vialidad_calle" maxlength="100" class="form-control" value="{{ old('vialidad_calle') }}" required>
                                    <div class="invalid-feedback">El nombre de la vialidad es obligatorio.</div>
                                </div>
                            </div>

                            <div class="col-12 col-md-2">
                                <div class="form-group">
                                    <label for="numExt" class="form-label">Num. Exterior <span class="text-required">(*)</span></label>
                                    <input type="text" name="numExt" id="numExt" maxlength="20" class="form-control" value="{{ old('numExt') }}" required>
                                    <div class="invalid-feedback">El número exterior es obligatorio.</div>
                                </div>
                            </div>

                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label for="numInt" class="form-label">Num. Interior <span class="text-muted">(Opcional)</span></label>
                                    <input type="text" name="numInt" id="numInt" maxlength="20" class="form-control" value="{{ old('numInt') }}">
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="colonia_solicitante" class="form-label">Colonia <span class="text-required">(*)</span></label>
                                    <input type="text" name="colonia_solicitante" id="colonia_solicitante" maxlength="80" class="form-control" value="{{ old('colonia_solicitante') }}" required>
                                    <div class="invalid-feedback">La colonia es obligatoria.</div>
                                </div>
                            </div>

                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label for="estado_solicitante" class="form-label">Estado <span class="text-required">(*)</span></label>
                                    <select id="estado_solicitante" name="estado_solicitante" class="form-select" required>
                                        <option value="">SELECCIONE...</option>
                                        @foreach($estados as $est)
                                            <option value="{{ $est['id'] }}" {{ old('estado_solicitante') == $est['id'] ? 'selected' : '' }}>{{ $est['nombre'] }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">El estado es obligatorio.</div>
                                </div>
                            </div>

                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label for="municipio_solicitante" class="form-label">Municipio / Alcaldía <span class="text-required">(*)</span></label>
                                    <select id="municipio_solicitante" name="municipio_solicitante" class="form-select" required>
                                        <option value="">SELECCIONE...</option>
                                        @foreach($municipios as $mun)
                                            <option value="{{ $mun['id'] }}" {{ old('municipio_solicitante') == $mun['id'] ? 'selected' : '' }}>{{ $mun['nombre'] }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">El municipio es obligatorio.</div>
                                </div>
                            </div>

                            <div class="col-12 col-md-2">
                                <div class="form-group">
                                    <label for="cp" class="form-label">Código Postal <span class="text-required">(*)</span></label>
                                    <input type="text" name="cp" id="cp" maxlength="5" minlength="5" class="form-control" value="{{ old('cp') }}" required>
                                    <div class="invalid-feedback">El CP debe tener 5 dígitos.</div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="calle1" class="form-label">Entre Calle <span class="text-muted">(Opcional)</span></label>
                                    <input type="text" name="calle1" id="calle1" maxlength="50" class="form-control" value="{{ old('calle1') }}">
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="calle2" class="form-label">Y Calle <span class="text-muted">(Opcional)</span></label>
                                    <input type="text" name="calle2" id="calle2" maxlength="50" class="form-control" value="{{ old('calle2') }}">
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-group">
                                    <label for="referencias" class="form-label">Referencias del Domicilio <span class="text-muted">(Opcional)</span></label>
                                    <textarea class="form-control" id="referencias" name="referencias" rows="2" placeholder="Describa fachada, color de casa, comercios cercanos...">{{ old('referencias') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- SECCIÓN 4: DATOS LABORALES -->
                        <div class="section-banner shadow-sm">
                            <h5><i class="bi bi-briefcase-fill me-2"></i>4. Datos Laborales</h5>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label for="seguro" class="form-label">Número de Seguro Social (NSS) <span class="text-muted">(Opcional)</span></label>
                                    <input type="text" name="seguro" id="seguro" minlength="11" maxlength="11" class="form-control" value="{{ old('seguro') }}">
                                </div>
                            </div>

                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label for="puesto" class="form-label">Puesto o Cargo <span class="text-required">(*)</span></label>
                                    <input type="text" name="puesto" id="puesto" maxlength="80" class="form-control" value="{{ old('puesto') }}" required>
                                    <div class="invalid-feedback">El campo puesto es obligatorio.</div>
                                </div>
                            </div>

                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label for="periodo_pago" class="form-label">Frecuencia de Pago <span class="text-required">(*)</span></label>
                                    <select name="periodo_pago" id="periodo_pago" class="form-select" required>
                                        <option value="">SELECCIONE...</option>
                                        <option value="Diario" {{ old('periodo_pago') == 'Diario' ? 'selected' : '' }}>DIARIO</option>
                                        <option value="Semanal" {{ old('periodo_pago') == 'Semanal' ? 'selected' : '' }}>SEMANAL</option>
                                        <option value="Quincenal" {{ old('periodo_pago') == 'Quincenal' ? 'selected' : '' }}>QUINCENAL</option>
                                        <option value="Mensual" {{ old('periodo_pago') == 'Mensual' ? 'selected' : '' }}>MENSUAL</option>
                                    </select>
                                    <div class="invalid-feedback">La frecuencia de pago es obligatoria.</div>
                                </div>
                            </div>

                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label for="pago" class="form-label">Salario / Percepción ($) <span class="text-required">(*)</span></label>
                                    <input type="number" step="0.01" min="0" name="pago" id="pago" class="form-control" value="{{ old('pago') }}" placeholder="0.00" required>
                                    <div class="invalid-feedback">El salario es obligatorio.</div>
                                </div>
                            </div>

                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label for="horas" class="form-label">Horas trabajadas por semana <span class="text-required">(*)</span></label>
                                    <input type="number" name="horas" id="horas" min="1" max="168" class="form-control" value="{{ old('horas') }}" required>
                                    <div class="invalid-feedback">Las horas trabajadas son obligatorias.</div>
                                </div>
                            </div>

                            <div class="col-12 col-md-3 d-flex align-items-center pt-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="check_fecha" name="labora" value="1">
                                    <label class="form-check-label fw-semibold" for="check_fecha">¿Labora actualmente?</label>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="jornada" class="form-label">Horario Laboral / Jornada <span class="text-required">(*)</span></label>
                                    <input type="text" name="jornada" id="jornada" maxlength="200" class="form-control" placeholder="Ej: Lunes a Viernes de 9:00 AM a 5:00 PM" value="{{ old('jornada') }}" required>
                                    <div class="invalid-feedback">El horario laboral es obligatorio.</div>
                                </div>
                            </div>

                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label for="fecha_ingreso" class="form-label">Fecha de Ingreso <span class="text-required">(*)</span></label>
                                    <input type="date" name="fecha_ingreso" id="fecha_ingreso" class="form-control" value="{{ old('fecha_ingreso') }}" required>
                                    <div class="invalid-feedback">La fecha de ingreso es obligatoria.</div>
                                </div>
                            </div>

                            <div class="col-12 col-md-3" id="fecha_fin">
                                <div class="form-group">
                                    <label for="fecha_salida" class="form-label">Fecha de Salida / Despido</label>
                                    <input type="date" name="fecha_salida" id="fecha_salida" class="form-control" value="{{ old('fecha_salida') }}">
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-group">
                                    <label for="descripcionSolicitud" class="form-label">Describa brevemente el motivo de su solicitud <span class="text-required">(*)</span></label>
                                    <textarea class="form-control" name="descripcionSolicitud" id="descripcionSolicitud" rows="3" required>{{ old('descripcionSolicitud') }}</textarea>
                                    <div class="invalid-feedback">La descripción del motivo es obligatoria.</div>
                                </div>
                            </div>
                        </div>

                        <!-- SECCIÓN 5: SOPORTE Y APOYO -->
                        <div class="row g-3 mt-1">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="solicito_apoyo" class="form-label">¿Ha acudido a su sindicato o unidad administrativa en búsqueda de apoyo? <span class="text-required">(*)</span></label>
                                    <select name="solicito_apoyo" id="solicito_apoyo" class="form-select" required>
                                        <option value="">SELECCIONE...</option>
                                        <option value="Si" {{ old('solicito_apoyo') == 'Si' ? 'selected' : '' }}>SÍ</option>
                                        <option value="No" {{ old('solicito_apoyo') == 'No' ? 'selected' : '' }}>NO</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-12 col-md-6" id="resultado_container" style="display: none;">
                                <div class="form-group">
                                    <label for="continuacion_solicto_apoyo" class="form-label">¿Qué resultado obtuvo? <span class="text-required">(*)</span></label>
                                    <textarea name="continuacion_solicto_apoyo" id="continuacion_solicto_apoyo" class="form-control" rows="2">{{ old('continuacion_solicto_apoyo') }}</textarea>
                                    <div class="invalid-feedback">Especifique el resultado obtenido.</div>
                                </div>
                            </div>
                        </div>

                        <!-- SECCIÓN 6: DOCUMENTOS DE IDENTIFICACIÓN -->
                        <div class="section-banner shadow-sm">
                            <h5><i class="bi bi-file-earmark-pdf-fill me-2"></i>5. Identificación Oficial</h5>
                        </div>

                        <div class="alert alert-info py-2" role="alert">
                            <i class="bi bi-info-circle-fill me-1"></i> En caso de ser mayor de edad adjuntar su identificación oficial vigente. Si es menor de edad, adjuntar Acta de Nacimiento.
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="identificacion" class="form-label">Tipo de Identificación <span class="text-required">(*)</span></label>
                                    <select name="identificacion" id="identificacion" class="form-select" required>
                                        <option value="">SELECCIONE...</option>
                                        <option value="Credencial de elector">CREDENCIAL DE ELECTOR (INE)</option>
                                        <option value="Pasaporte">PASAPORTE</option>
                                        <option value="Cédula profesional">CÉDULA PROFESIONAL</option>
                                        <option value="Licencia de conducir">LICENCIA DE CONDUCIR</option>
                                        <option value="Credencial de inapam">CREDENCIAL DE INAPAM</option>
                                        <option value="Cartilla militar">CARTILLA MILITAR</option>
                                        <option value="Documento migratorio">DOCUMENTO MIGRATORIO</option>
                                        <option value="Constancia de identidad">CONSTANCIA DE IDENTIDAD / ACTA DE NACIONALIDAD</option>
                                        <option value="Otro">OTROS</option>
                                    </select>
                                    <div class="invalid-feedback">El tipo de identificación es obligatorio.</div>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="num_identificacion" class="form-label">
                                        Número de Identificación / Clave <span class="text-required">(*)</span> 
                                        <i class="bi bi-question-circle-fill text-primary ms-1" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#helpModal" title="¿Dónde encontrar este número?"></i>
                                    </label>
                                    <input type="text" name="num_identificacion" id="num_identificacion" maxlength="50" class="form-control" value="{{ old('num_identificacion') }}" required>
                                    <div class="invalid-feedback">El número de identificación es obligatorio.</div>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="documentoIdentificacion" class="form-label">Subir Documento (PDF, máx. 5MB) <span class="text-required">(*)</span></label>
                                    <input type="file" id="documentoIdentificacion" name="documentoIdentificacion" class="form-control" accept=".pdf" required>
                                    <div class="invalid-feedback">El archivo en formato PDF es obligatorio.</div>
                                </div>
                            </div>
                        </div>

                        <!-- Botón Guardar -->
                        <div class="d-flex justify-content-end align-items-center gap-3 mt-4 pt-3 border-top">
                            <a href="{{ route('publico') }}" class="btn btn-secondary px-4">Cancelar</a>
                            <button type="submit" class="btn btn-dorado">
                                Guardar y Continuar <i class="bi bi-arrow-right-circle-fill ms-1"></i>
                            </button>
                        </div>
                    </form>

                </div>
            </section>
        </div>
    </main>

    <!-- MODALES DE AYUDA -->
    <div class="modal fade" id="helpModal" tabindex="-1" aria-labelledby="helpModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="helpModalLabel"><i class="bi bi-card-heading me-2"></i>Ubicación de Número de Identificación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="{{ asset('public/assets/images/capturaIne.png') }}" alt="Guía Identificación" class="img-fluid rounded border">
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle & SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- LÓGICA JAVASCRIPT REFACTORIZADA -->
    <script>
        // Convertir automáticamente inputs a mayúsculas
        document.addEventListener('DOMContentLoaded', () => {
            const inputsText = document.querySelectorAll('input[type="text"], textarea');
            inputsText.forEach(el => {
                el.addEventListener('input', function() {
                    this.value = this.value.toUpperCase();
                });
            });

            // Toggle para traductores y discapacidad
            const checkLenguaje = document.getElementById('check_lenguaje');
            const boxLenguaje = document.getElementById('lenguaje_señas');
            const inputLenguaje = document.getElementById('lenguajeRequerido');

            checkLenguaje.addEventListener('change', function() {
                if(this.checked) {
                    boxLenguaje.style.display = 'block';
                    inputLenguaje.setAttribute('required', 'required');
                } else {
                    boxLenguaje.style.display = 'none';
                    inputLenguaje.removeAttribute('required');
                    inputLenguaje.value = '';
                }
            });

            const checkDiscapacidad = document.getElementById('check_discapacidad');
            const boxDiscapacidad = document.getElementById('discapacidad_container');
            const inputDiscapacidad = document.getElementById('discapacidadRequerida');

            checkDiscapacidad.addEventListener('change', function() {
                if(this.checked) {
                    boxDiscapacidad.style.display = 'block';
                    inputDiscapacidad.setAttribute('required', 'required');
                } else {
                    boxDiscapacidad.style.display = 'none';
                    inputDiscapacidad.removeAttribute('required');
                    inputDiscapacidad.value = '';
                }
            });

            // Toggle Solicitó Apoyo
            const selectApoyo = document.getElementById('solicito_apoyo');
            const containerResultado = document.getElementById('resultado_container');
            const fieldResultado = document.getElementById('continuacion_solicto_apoyo');

            selectApoyo.addEventListener('change', function() {
                if (this.value === 'Si') {
                    containerResultado.style.display = 'block';
                    fieldResultado.setAttribute('required', 'required');
                } else {
                    containerResultado.style.display = 'none';
                    fieldResultado.removeAttribute('required');
                    fieldResultado.value = '';
                }
            });

            // Validar tamaño máximo de PDF a 5MB
            const docInput = document.getElementById('documentoIdentificacion');
            if (docInput) {
                docInput.addEventListener('change', function(e) {
                    const archivo = e.target.files[0];
                    if (archivo) {
                        const limite = 5 * 1024 * 1024; // 5 MB
                        if (archivo.size > limite) {
                            Swal.fire('Archivo muy pesado', 'El archivo PDF no puede exceder el límite de 5 MB.', 'warning');
                            this.value = '';
                        }
                    }
                });
            }

            // Carga de Municipios dinámica por Estado
            const estadoSelect = document.getElementById('estado_solicitante');
            const municipioSelect = document.getElementById('municipio_solicitante');
            const baseUrl = "{{ url('') }}";

            if (estadoSelect && municipioSelect) {
                estadoSelect.addEventListener('change', function() {
                    const estadoId = this.value;
                    if (!estadoId) {
                        municipioSelect.innerHTML = '<option value="">SELECCIONE...</option>';
                        return;
                    }
                    municipioSelect.innerHTML = '<option value="">Cargando...</option>';

                    $.get(baseUrl + '/api/munSolicitante/' + estadoId, function(data) {
                        let options = '<option value="">SELECCIONE...</option>';
                        data.forEach(m => {
                            options += `<option value="${m.id}">${m.nombre}</option>`;
                        });
                        municipioSelect.innerHTML = options;
                    }).fail(function() {
                        municipioSelect.innerHTML = '<option value="">Error al cargar municipios</option>';
                    });
                });
            }
        });

        // Función para calcular la edad automáticamente según la Fecha de Nacimiento
        function validarfechaNacimiento() {
            const fechaNacStr = document.getElementById("fecha_nacimiento").value;
            const campoEdad = document.getElementById("años_edad");

            if (!fechaNacStr) {
                campoEdad.value = '';
                return;
            }

            const hoy = new Date();
            const nac = new Date(fechaNacStr + 'T00:00:00');
            let edad = hoy.getFullYear() - nac.getFullYear();
            const m = hoy.getMonth() - nac.getMonth();

            if (m < 0 || (m === 0 && hoy.getDate() < nac.getDate())) {
                edad--;
            }

            campoEdad.value = edad;

            if (edad < 15) {
                Swal.fire('Atención', 'Debes tener al menos 15 años cumplidos para iniciar la solicitud.', 'info');
            } else if (edad >= 15 && edad < 18) {
                Swal.fire('Aviso', 'Por ser menor de edad, deberás presentarte acompañado de tu padre, madre o tutor legal.', 'info');
            }
        }

        // Interceptación y validación del formulario
        (function() {
            'use strict';
            const form = document.getElementById('form-solicitante');

            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();

                    // Hacer scroll hacia el primer campo no válido
                    const invalidInput = form.querySelector(':invalid');
                    if (invalidInput) {
                        invalidInput.focus();
                        invalidInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                } else {
                    document.getElementById('crear_poder').style.display = 'block';
                }

                form.classList.add('was-validated');
            }, false);
        })();
    </script>
</body>
</html>