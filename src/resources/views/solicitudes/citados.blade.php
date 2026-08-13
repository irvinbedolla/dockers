<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Sistema Integral Concilio">
    <meta name="generator" content="Ing. ISBM">
    <title>Si Concilio - Datos del Citado</title>

    <!-- Bootstrap 5.3.3 CSS -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css"/>
    <link rel="icon" href="{{ asset('assets/images/ccl-r.png') }}" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('assets/css/all.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/iziToast.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/sweetalert.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" type="text/css"/>

    <!-- Dynamic Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <style>
        :root {
            --primary-color: #6A0F49;
            --secondary-color: #CEA845;
            --secondary-hover: #b89338;
            --bg-light: #F8F9FA;
            --text-muted: #6C757D;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-light);
            color: #333;
        }

        /* Navbar Styling */
        .navbar-custom {
            background-color: #ffffff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            padding: 10px 20px;
        }

        .navbar-brand img {
            height: 65px;
            width: auto;
        }

        .nav-link-custom {
            color: var(--primary-color) !important;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
            transition: color 0.3s ease;
        }

        .nav-link-custom:hover {
            color: var(--secondary-color) !important;
        }

        /* Card and Headers */
        .main-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.05);
            background: #ffffff;
            margin-top: 100px;
            margin-bottom: 40px;
            overflow: hidden;
        }

        .section-header {
            background-color: var(--primary-color);
            color: #ffffff;
            padding: 14px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            box-shadow: 0 4px 12px rgba(106, 15, 73, 0.15);
        }

        .section-header h3, .section-header h4 {
            margin: 0;
            font-weight: 600;
            font-size: 1.25rem;
            letter-spacing: 0.5px;
        }

        .subsection-header {
            background-color: #f1f3f5;
            color: var(--primary-color);
            padding: 10px 15px;
            border-left: 4px solid var(--primary-color);
            border-radius: 4px;
            margin-top: 15px;
            margin-bottom: 20px;
            font-weight: 600;
        }

        /* Form Controls */
        .form-label, label {
            font-weight: 500;
            color: #495057;
            margin-bottom: 6px;
            font-size: 0.88rem;
        }

        .form-control, .form-select {
            border-radius: 6px;
            border: 1px solid #ced4da;
            padding: 9px 13px;
            font-size: 0.9rem;
            transition: all 0.2s ease-in-out;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(106, 15, 73, 0.15);
        }

        select option {
            text-transform: uppercase;
        }

        /* Buttons */
        .btn-gold {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            color: #ffffff;
            font-weight: 600;
            padding: 10px 22px;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .btn-gold:hover {
            background-color: var(--secondary-hover);
            border-color: var(--secondary-hover);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(206, 168, 69, 0.3);
        }

        .btn-maps {
            background-color: #2563eb;
            border-color: #2563eb;
            color: #ffffff;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-maps:hover {
            background-color: #1d4ed8;
            color: #ffffff;
        }

        .btn-disabled {
            pointer-events: none;
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Loader */
        .loader {
            position: fixed;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            z-index: 9999;
            background: url('{{ asset("assets/images/pageLoader.gif") }}') 50% 50% no-repeat rgba(255, 255, 255, 0.85);
        }

        #resultado {
            background-color: #dc3545;
            color: white;
            font-weight: bold;
            border-radius: 4px;
            padding: 4px;
            margin-top: 4px;
        }

        #resultado.ok {
            background-color: #198754;
        }

        .required-star {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light navbar-custom fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('login') }}">
                <img src="{{ asset('assets/images/Logos 2.png') }}" alt="Logo Si Concilio">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom" href="{{ route('login') }}">INICIO</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Content Area -->
    <div id="app" class="container">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="card main-card">
                    <div class="card-body p-4 p-md-5">

                        <!-- Flash Messages -->
                        @if(session()->has('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <strong><i class="fas fa-check-circle me-1"></i> ¡Registro correcto!</strong>
                                {{ session()->get('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong><i class="fas fa-exclamation-triangle me-1"></i> ¡Revise los campos!</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <!-- Section Header -->
                        <div class="section-header text-center">
                            <h3>Ingresa los datos del citado(s)</h3>
                        </div>
                        <p class="text-muted small mb-4"><span class="required-star">*</span> Debes capturar al menos un citado para continuar con el trámite.</p>

                        <!-- Main Form -->
                        <form id="form-solicitante" class="needs-validation" novalidate method="POST" action="{{ route('seer.citados') }}" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="id" value="{{ $id }}">
                            <input type="hidden" name="draft_id" value="{{ $draftId ?? request('draft_id') }}">
                            
                            <!-- Section 1: Personal Data -->
                            <div class="row g-3" id="div_datos_citado">
                                <div class="col-xs-12 col-sm-12 col-md-3">
                                    <div class="form-group">
                                        <label for="tipo">Tipo de persona <span class="required-star">(*)</span></label>
                                        <select name="tipo" id="tipo" class="form-select" required>
                                            <option value="">Seleccione</option>
                                            <option value="Fisica">Física</option>
                                            <option value="Moral">Moral</option>
                                        </select>
                                        <div class="invalid-feedback">El tipo de persona es obligatorio.</div>
                                    </div>
                                </div>

                                <div class="col-xs-12 col-sm-12 col-md-3" id="campo_curp">
                                    <div class="form-group">
                                        <label for="curp_input">CURP (Opcional)</label>
                                        <input type="text" name="curp" maxlength="18" id="curp_input" oninput="validarInput(this)" class="form-control"> 
                                        <pre id="resultado"></pre>
                                    </div>
                                </div>

                                <div class="col-xs-12 col-sm-12 col-md-6" id="tipoPersona_razon" style="display:none;">
                                    <div class="form-group">
                                        <label for="razon">Razón social <span class="required-star">(*)</span></label>
                                        <input type="text" name="razon" id="razon" class="form-control" oninput="this.value = this.value.toUpperCase()"> 
                                        <div class="invalid-feedback">La razón social es obligatoria.</div>
                                    </div>
                                </div>

                                <div class="col-xs-12 col-sm-12 col-md-3">
                                    <div class="form-group">
                                        <label for="rfc">RFC (Opcional)</label>
                                        <input type="text" name="rfc" id="rfc" class="form-control" minlength="12" maxlength="13" oninput="this.value = this.value.toUpperCase()">   
                                    </div>
                                </div>

                                <div class="col-xs-12 col-sm-12 col-md-3 d-flex align-items-center">
                                    <div class="form-check mt-3">
                                        <input type="checkbox" class="form-check-input" id="check_lenguaje" name="traductor" autocomplete="off">
                                        <label class="form-check-label" for="check_lenguaje">¿Requiere Traductor?</label>
                                    </div>
                                </div>

                                <div class="col-xs-12 col-sm-12 col-md-4" id="lenguaje_señas">
                                    <div class="form-group">
                                        <label for="lenguajeRequerido">¿Qué tipo de lenguaje requiere?</label>
                                        <input type="text" name="lenguaje" class="form-control" id="lenguajeRequerido" oninput="this.value = this.value.toUpperCase()">
                                    </div>
                                </div>

                                <div class="col-12" id="tipoPersona_nombre" style="display:none;">
                                    <div class="row g-3">
                                        <div class="col-xs-12 col-sm-12 col-md-4">
                                            <div class="form-group">
                                                <label for="nombre">Nombre(s) <span class="required-star">(*)</span></label>
                                                <input type="text" name="nombre" id="nombre" class="form-control" oninput="this.value = this.value.toUpperCase()"> 
                                                <div class="invalid-feedback">El nombre es obligatorio.</div>
                                            </div>
                                        </div>

                                        <div class="col-xs-12 col-sm-12 col-md-4">
                                            <div class="form-group">
                                                <label for="primer_apellido">Primer apellido <span class="required-star">(*)</span></label>
                                                <input type="text" name="primer_apellido" id="primer_apellido" maxlength="50" class="form-control" oninput="this.value = this.value.toUpperCase()"> 
                                                <div class="invalid-feedback">El primer apellido es obligatorio.</div>
                                            </div>
                                        </div>

                                        <div class="col-xs-12 col-sm-12 col-md-4">
                                            <div class="form-group">
                                                <label for="segundo_apellido">Segundo apellido</label>
                                                <input type="text" name="segundo_apellido" id="segundo_apellido" maxlength="50" class="form-control" oninput="this.value = this.value.toUpperCase()"> 
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <input type="hidden" name="resulte_responsable" id="responsable" value="Si" required>
                            </div>

                            <!-- Section 2: Address Data -->
                            <div class="subsection-header text-center mt-4">
                                Dirección de la fuente de empleo
                            </div>

                            <input type="hidden" name="notificacion" value="Centro" required>

                            <div class="row g-3">
                                <div class="col-xs-12 col-sm-12 col-md-3">
                                    <div class="form-group">
                                        <label for="vialidad">Tipo de vialidad <span class="required-star">(*)</span></label>
                                        <select name="vialidad" id="vialidad" class="form-select" required>
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
                                            <option value="PROLONGACIÓN">Prolongación</option>
                                            <option value="PRIVADA">Privada</option>
                                            <option value="RETORNO">Retorno</option>
                                            <option value="VIADUCTO">Viaducto</option>
                                            <option value="PASEO">Paseo</option>
                                        </select>
                                        <div class="invalid-feedback">El campo vialidad es obligatorio.</div>
                                    </div>
                                </div>

                                <div class="col-xs-12 col-sm-12 col-md-3">
                                    <div class="form-group">
                                        <label for="calle">Nombre de la vialidad <span class="required-star">(*)</span></label>
                                        <input type="text" name="calle" id="calle" maxlength="100" class="form-control" oninput="this.value = this.value.toUpperCase()" required> 
                                        <div class="invalid-feedback">El campo calle es obligatorio.</div>
                                    </div>
                                </div>

                                <div class="col-xs-12 col-sm-12 col-md-3">
                                    <div class="form-group">
                                        <label for="exterior">Núm. ext. <span class="required-star">(*)</span></label>
                                        <input type="text" name="exterior" id="exterior" min="0" maxlength="50" class="form-control" oninput="this.value = this.value.toUpperCase()" required> 
                                        <div class="invalid-feedback">El núm. exterior es obligatorio.</div>
                                    </div>
                                </div>

                                <div class="col-xs-12 col-sm-12 col-md-3">
                                    <div class="form-group">
                                        <label for="interior">Núm. int.</label>
                                        <input type="text" name="interior" id="interior" min="0" maxlength="50" class="form-control" oninput="this.value = this.value.toUpperCase()"> 
                                    </div>
                                </div>

                                <div class="col-xs-12 col-sm-12 col-md-3">
                                    <div class="form-group">
                                        <label for="colonia">Colonia <span class="required-star">(*)</span></label>
                                        <input type="text" name="colonia" id="colonia" maxlength="100" class="form-control" oninput="this.value = this.value.toUpperCase()" required> 
                                        <div class="invalid-feedback">El campo colonia es obligatorio.</div>
                                    </div>
                                </div>

                                <div class="col-xs-12 col-sm-12 col-md-3">
                                    <div class="form-group">
                                        <label for="cp">Código postal <span class="required-star">(*)</span></label>
                                        <input type="text" name="cp" id="cp" class="form-control soloNumeros" minlength="5" maxlength="5" required> 
                                        <div class="invalid-feedback">El campo Código Postal es obligatorio.</div>
                                    </div>
                                </div>

                                <div class="col-xs-12 col-sm-12 col-md-3">
                                    <div class="form-group">
                                        <label for="calle1">Entre calle (Opcional)</label>
                                        <input type="text" name="calle1" id="calle1" maxlength="50" class="form-control" oninput="this.value = this.value.toUpperCase()"> 
                                    </div>
                                </div>

                                <div class="col-xs-12 col-sm-12 col-md-3">
                                    <div class="form-group">
                                        <label for="calle2">y calle (Opcional)</label>
                                        <input type="text" name="calle2" id="calle2" maxlength="50" class="form-control" oninput="this.value = this.value.toUpperCase()"> 
                                    </div>
                                </div>

                                <div class="col-xs-12 col-sm-12 col-md-3">
                                    <div class="form-group">
                                        <label for="estado_citado">Estado <span class="required-star">(*)</span></label>
                                        <select id="estado_citado" class="form-select" name="estado_citado" required>
                                            <option value="">Seleccione</option>
                                            @foreach($estados as $es)
                                                <option value="{{$es['id']}}">{{$es['nombre']}}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">El campo Estado es obligatorio.</div>
                                    </div>
                                </div>

                                <div class="col-xs-12 col-sm-12 col-md-3">
                                    <div class="form-group">
                                        <label for="municipio_citado">Municipio o Alcaldía <span class="required-star">(*)</span></label>
                                        <select id="municipio_citado" class="form-select" name="municipio_citado" required>
                                            <option value="">Seleccione</option>
                                            @foreach($municipios as $mun)
                                                <option value="{{$mun['id']}}">{{$mun['nombre']}}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">El campo municipio o alcaldía es obligatorio.</div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="referencia">Referencias del domicilio <span class="required-star">(*)</span></label>
                                        <textarea class="form-control" id="referencia" placeholder="Ejemplo: Referencias visuales, como color de casa/portón, junto a: tienda, parque, escuela, etc." name="referencia" style="height: 90px;" oninput="this.value = this.value.toUpperCase()" required></textarea>
                                        <div class="invalid-feedback">El campo referencias es obligatorio.</div>
                                    </div>
                                </div>

                                <div class="col-12 mt-3">
                                    <label class="fw-semibold">Ubica tu domicilio laboral y adjunta una captura.</label>
                                </div>

                                <div class="col-xs-12 col-sm-6 col-md-3">
                                    <div class="form-group pt-2">
                                        <a class="btn btn-maps w-100" 
                                           href="https://www.google.com.mx/maps/@19.6837376,-101.1712,14z?entry=ttu&g_ep=EgoyMDI1MDgzMC4wIKXMDSoASAFQAw%3D%3D" 
                                           target="_blank">
                                            <img src="https://www.gstatic.com/images/branding/product/1x/maps_64dp.png" alt="Google Maps" style="width:20px; height:20px;">
                                            Google Maps
                                        </a>
                                    </div>
                                </div>                                       

                                <div class="col-xs-12 col-sm-6 col-md-4">
                                    <div class="form-group">
                                        <label for="foto1">Imagen del lugar <span class="required-star">(*)</span></label>
                                        <input type="file" id="foto1" class="form-control" name="foto1" accept="image/*">
                                    </div>
                                </div>

                                <div class="col-xs-12 col-sm-6 col-md-5">
                                    <div class="form-group">
                                        <label for="foto2">Imagen de la fachada</label>
                                        <input type="file" id="foto2" class="form-control" name="foto2" accept="image/*">
                                    </div>
                                </div>
                            </div>

                            <!-- Buttons Section -->
                            <div class="row mt-4 pt-3 border-top">
                                <div class="col-12 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                                    <div>
                                        <!-- Botón para guardar/agregar el citado actual -->
                                        <button type="submit" class="btn btn-gold">
                                            <i class="fas fa-plus-circle me-1"></i> Agregar citado
                                        </button>
                                    </div>
                        </form>
                                    <div class="d-flex flex-column align-items-end">
                                        {{-- Si ya hay al menos 1 citado registrado, mostrar el botón para Concluir --}}
                                        @if(($citados ?? 0) > 0)
                                            <form method="POST" action="{{ route('aviso') }}" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $id }}">
                                                <input type="hidden" name="draft_id" value="{{ $draftId ?? request('draft_id') }}">
                                                <!-- Si tienes la delegación o mensaje en variables/sesión, pásalos aquí -->
                                                <input type="hidden" name="delegacion" value="{{ $delegacion ?? 'Morelia' }}">
                                                <input type="hidden" name="mensaje" value="{{ $mensaje ?? '' }}">

                                                <button type="submit" id="btn-conclude" class="btn btn-gold">
                                                    <i class="fas fa-check-circle me-1"></i> Concluir solicitud
                                                </button>
                                            </form>

                                            <div id="conclude-warning" class="text-danger small mt-2" style="display:none;">
                                                <i class="fas fa-exclamation-circle me-1"></i> Guarde los cambios del citado actual antes de concluir.
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                       

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loaders -->
    <div id="crear_poder" style="display: none;">
        <div class="loader"></div>
    </div>
    <div id="submit_loader" style="display:none;">
        <div class="loader"></div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/js/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/sweetalert.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.nicescroll.js') }}"></script>
    <script src="{{ asset('assets/js/moment.js') }}"></script>
    <script src="{{ asset('assets/js/stisla.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.js') }}"></script>
    <script src="{{ asset('assets/js/profile.js') }}"></script>
    <script src="{{ asset('assets/js/custom.js') }}"></script>
    <script src="https://cdn.datatables.net/2.1.5/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.1.5/js/dataTables.bootstrap4.js"></script>
    <script src="{{ asset('assets/js/validaciones.js') }}"></script>

    <script>
        // Limpieza automática del parámetro ?draft_id= en la barra de direcciones
        document.addEventListener('DOMContentLoaded', function () {
            const url = new URL(window.location.href);
            if (url.searchParams.has('draft_id')) {
                url.searchParams.delete('draft_id');
                window.history.replaceState({}, document.title, url.pathname + url.search);
            }
        });

        // Mayúsculas automáticas
        function convertirAMayusculas() {
            const elementos = document.querySelectorAll('input[type="text"], textarea');
            elementos.forEach(elemento => {
                elemento.addEventListener('input', function() {
                    this.value = this.value.toUpperCase();
                });
                if (elemento.value) {
                    elemento.value = elemento.value.toUpperCase();
                }
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            convertirAMayusculas();

            // Validación Boostrap
            (function () {
                'use strict'
                var forms = document.querySelectorAll('.needs-validation')
                Array.prototype.slice.call(forms).forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
            })();
        });

        // Manejo del estado del botón Concluir
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('form.needs-validation');
            const conclude = document.getElementById('btn-conclude');
            const concludeWarning = document.getElementById('conclude-warning');
            if (!form || !conclude) return;

            function updateConcludeState() {
                try {
                    const elements = form.querySelectorAll('input:not([type=hidden]):not([type=submit]), textarea, select');
                    let hasValue = false;
                    elements.forEach(function (el) {
                        if (!el) return;
                        if (el.tagName.toLowerCase() === 'select') {
                            if (el.value && el.value !== '') hasValue = true;
                            return;
                        }
                        const t = (el.type || '').toLowerCase();
                        if (t === 'checkbox' || t === 'radio') {
                            if (el.checked) hasValue = true;
                        } else if (t === 'file') {
                            if (el.files && el.files.length) hasValue = true;
                        } else {
                            if (el.value && el.value.trim() !== '') hasValue = true;
                        }
                    });

                    if (hasValue) {
                        conclude.classList.add('btn-disabled');
                        conclude.setAttribute('aria-disabled', 'true');
                        if (concludeWarning) concludeWarning.style.display = 'block';
                    } else {
                        conclude.classList.remove('btn-disabled');
                        conclude.removeAttribute('aria-disabled');
                        if (concludeWarning) concludeWarning.style.display = 'none';
                    }
                } catch (err) { console.warn('updateConcludeState', err); }
            }

            updateConcludeState();
            form.addEventListener('input', updateConcludeState);
            form.addEventListener('change', updateConcludeState);

            conclude.addEventListener('click', function (e) {
                if (conclude.getAttribute('aria-disabled') === 'true') {
                    e.preventDefault();
                    return false;
                }
                $('#submit_loader').show();
            });
        });

        // Carga dinámica de Municipios
        document.addEventListener('DOMContentLoaded', function () {
            var base_url = "{{ url('') }}";

            function cargarMunicipiosCitado(estadoId) {
                var $municipio = $('#municipio_citado');
                if (!$municipio.length) return;
                $municipio.html('<option value="">Cargando...</option>');
                if (!estadoId) {
                    $municipio.html('<option value="">Seleccione</option>');
                    return;
                }

                $.get(base_url + '/api/munCitado/' + estadoId, function (data) {
                    var html = '<option value="">Seleccione</option>';
                    data.forEach(function (m) {
                        html += '<option value="' + m.id + '">' + m.nombre + '</option>';
                    });
                    $municipio.html(html);
                }).fail(function () {
                    $.get(base_url + '/munCitado/' + estadoId, function (data) {
                        var html = '<option value="">Seleccione</option>';
                        data.forEach(function (m) {
                            html += '<option value="' + m.id + '">' + m.nombre + '</option>';
                        });
                        $municipio.html(html);
                    }).fail(function () {
                        $municipio.html('<option value="">Error cargando municipios</option>');
                    });
                });
            }

            var $estadoCitado = $('#estado_citado');
            if ($estadoCitado.length) {
                $estadoCitado.on('change', function () {
                    cargarMunicipiosCitado(this.value);
                });
                var inicial = $estadoCitado.val();
                if (inicial) cargarMunicipiosCitado(inicial);
            }
        });

        // Alternar campos según Tipo de Persona
        document.addEventListener('DOMContentLoaded', function () {
            const selectTipo = document.getElementById('tipo');
            const nombreDiv = document.getElementById('tipoPersona_nombre');
            const razonDiv = document.getElementById('tipoPersona_razon');
            const curpDiv = document.getElementById('campo_curp');

            function actualizarTipoPersona() {
                const valor = selectTipo.value;
                nombreDiv.style.display = 'none';
                razonDiv.style.display = 'none';
                curpDiv.style.display = 'none';

                const inputNombre = document.querySelector('input[name="nombre"]');
                const inputPrimer = document.querySelector('input[name="primer_apellido"]');
                const inputRazon = document.querySelector('input[name="razon"]');

                if (inputNombre) inputNombre.required = false;
                if (inputPrimer) inputPrimer.required = false;
                if (inputRazon) inputRazon.required = false;

                if (valor === 'Fisica') {
                    nombreDiv.style.display = 'block';
                    curpDiv.style.display = 'block';
                    if (inputNombre) inputNombre.required = true;
                    if (inputPrimer) inputPrimer.required = true;
                } else if (valor === 'Moral') {
                    razonDiv.style.display = 'block';
                    if (inputRazon) inputRazon.required = true;
                }
            }

            if (selectTipo) {
                selectTipo.addEventListener('change', actualizarTipoPersona);
                actualizarTipoPersona();
            }

            const form = document.querySelector('form.needs-validation');
            if (form) {
                form.addEventListener('submit', function() {
                    const checkLanguage = document.getElementById('check_lenguaje');
                    const languageRequired = document.getElementById('lenguajeRequerido');
                    if (checkLanguage && languageRequired) {
                        languageRequired.required = checkLanguage.checked;
                    }
                });
            }
        });
    </script>
</body>
</html>