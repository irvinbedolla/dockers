<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="SiConcilio - Datos generales de la solicitud">
    <meta name="author" content="Centro de Conciliación Laboral de Michoacán">
    <link rel="icon" href="{{ asset('assets/images/logo-ccl.png') }}" type="image/x-icon">
    <title>Si Concilio - Solicitud de Conciliación</title>

    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Select2 & jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

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

        /* Contenedor Tarjeta Principal */
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

        .section-banner {
            background: linear-gradient(135deg, var(--primary-guinda), var(--primary-hover));
            color: #ffffff;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            margin-bottom: 1.5rem;
        }

        .section-banner h4 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        /* Caja Informativa de Requisitos */
        .info-requirements-box {
            background-color: #f8f9fa;
            border-left: 4px solid var(--primary-guinda);
            border-radius: 6px;
            padding: 1rem 1.2rem;
            margin-bottom: 1.5rem;
        }

        .info-requirements-box h6 {
            color: var(--primary-guinda);
            font-weight: 700;
            margin-bottom: 0.3rem;
        }

        .info-requirements-box p {
            color: #555;
            margin: 0;
            font-size: 0.9rem;
        }

        /* Form Labels */
        .form-label {
            font-weight: 600;
            color: #333;
            font-size: 0.9rem;
            margin-bottom: 0.4rem;
        }

        .text-required {
            color: #dc3545;
            font-weight: bold;
        }

        /* Tablas */
        .table-custom {
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e3e8ee;
        }

        .table-custom thead {
            background-color: #f1f3f5;
            color: var(--text-dark);
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
        }

        /* Botones */
        .btn-dorado {
            background-color: var(--accent-dorado);
            border-color: var(--accent-dorado);
            color: #ffffff !important;
            font-weight: 700;
            padding: 0.6rem 2rem;
            border-radius: 6px;
            box-shadow: 0 4px 10px rgba(206, 168, 69, 0.25);
            transition: all 0.2s ease;
        }

        .btn-dorado:hover {
            background-color: var(--accent-hover);
            border-color: var(--accent-hover);
            transform: translateY(-1px);
        }

        .btn-cancelar {
            background-color: #6c757d;
            border-color: #6c757d;
            color: #ffffff !important;
            font-weight: 600;
            padding: 0.6rem 1.8rem;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .btn-cancelar:hover {
            background-color: #5a6268;
        }

        /* Loader Overlay */
        .loader {
            position: fixed;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            z-index: 9999;
            background: rgba(249, 249, 249, 0.85) url('{{ asset("assets/images/pageLoader.gif") }}') 50% 50% no-repeat;
            transition: opacity 0.3s ease;
        }

        .loader.hidden {
            display: none;
        }
    </style>
</head>
<body>

    <!-- Overlay Loader -->
    <div id="page-loader" class="loader hidden" aria-hidden="true"></div>

    <!-- Navegación Fija -->
    <nav class="navbar navbar-expand-lg navbar-institutional fixed-top py-2">
        <div class="container-fluid px-4">
            <a class="navbar-brand py-0" href="{{ route('login') }}">
                <img src="{{ asset('assets/images/Logos 2.png') }}" alt="Logo CCL Michoacán" height="60">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
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

    <!-- Separador para el Fijo del Navbar -->
    <div style="margin-top: 95px;"></div>

    <!-- Contenido Principal -->
    <main class="container my-4 flex-grow-1">
        <div id="app">
            <section class="section">
                <div class="main-card">

                    <!-- Encabezado de Solicitud -->
                    <div class="text-center mb-3">
                        <h2 class="title-header h3 mb-1">Solicitud de Conciliación</h2>
                    </div>

                    <div class="section-banner text-center shadow-sm">
                        <h4 class="mb-0"><i class="bi bi-folder2-open me-2"></i>Datos Generales de la Solicitud</h4>
                    </div>

                    <!-- Alertas de Éxito y Errores -->
                    @if(session()->has('success'))
                        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
                            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                            <div>
                                <strong>¡Registro correcto!</strong> {{ session()->get('success') }}
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Cuadro Informativo -->
                    <div class="info-requirements-box shadow-sm">
                        <h6><i class="bi bi-info-circle-fill me-1"></i> Requisitos indispensables para continuar tu solicitud:</h6>
                        <p>Contar con un número telefónico, correo electrónico e identificación oficial vigente (INE, Pasaporte, Licencia de Conducir o Cédula Profesional). En caso de ser menor de edad, presentar CURP o Acta de Nacimiento.</p>
                    </div>

                    <!-- Formulario Principal -->
                    <form id="form-parte1" class="needs-validation" novalidate method="POST" action="{{ route('parte1') }}">
                        @csrf
                        <input type="hidden" name="tipo_solicitud" value="{{ $tipo_solicitud }}">
                        <input type="hidden" name="draft_id" value="{{ $draftId ?? request('draft_id') }}">

                        <div class="row g-3">
                            <!-- Municipio de la Fuente de Empleo -->
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="dSolicitud" class="form-label">
                                        Municipio de la Fuente de Empleo <span class="text-required">(*)</span>
                                    </label>
                                    <select id="dSolicitud" class="form-select" name="dSolicitud" required>
                                        <option value="">Seleccione un municipio...</option>
                                        @foreach($municipios as $municipio)
                                            <option value="{{$municipio['id']}}" data-delegacion-id="{{ $municipio['delegacion_id'] }}">
                                                {{ $municipio['nombre'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">
                                        El municipio es obligatorio.
                                    </div>
                                </div>
                            </div>

                            <!-- Delegación asignada -->
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="delegacion" class="form-label">
                                        Delegación Asignada <span class="text-required">(*)</span>
                                    </label>
                                    <select class="form-select bg-light" id="delegacion" name="delegacion" required>
                                        <option value="">Seleccione municipio primero</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        La delegación es obligatoria.
                                    </div>
                                </div>
                            </div>

                            <!-- Objeto de la Solicitud -->
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="motivo_solicitud" class="form-label">
                                        Objeto de la Solicitud <span class="text-required">(*)</span>
                                    </label>
                                    <select class="form-select" id="motivo_solicitud">
                                        <option value="">Seleccione los motivos de su solicitud...</option>
                                        @foreach($mostrarMotivos as $motivo)
                                            <option value="{{$motivo['id']}}">{{$motivo['motivo']}}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">
                                        El objeto de la solicitud es obligatorio.
                                    </div>
                                </div>
                            </div>

                            <!-- Tabla Dinámica para Motivos Seleccionados -->
                            <div id="div1" class="col-12">
                                <div class="table-responsive">
                                    <table id="tabla" class="table table-custom table-hover align-middle mb-0 text-center">
                                        <thead>
                                            <tr>
                                                <th>Objeto de la Solicitud Seleccionado</th>
                                                <th style="width: 120px;">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Las filas agregadas dinámicamente aparecerán aquí -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Rama Industrial -->
                            <div class="col-12 col-md-5">
                                <div class="form-group">
                                    <label for="ramaIndustrial" class="form-label">
                                        Paso 1. Rama Industrial <span class="text-required">(*)</span>
                                    </label>
                                    <select id="ramaIndustrial" class="form-select" name="ramaIndustrial" required>
                                        <option value="">Seleccione una rama...</option>
                                        @foreach($ramas as $rama)
                                            <option value="{{$rama['id']}}">{{$rama['rama_industrial']}}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">
                                        El campo rama industrial es obligatorio.
                                    </div>
                                </div>
                            </div>

                            <!-- Actividad Económica -->
                            <div class="col-12 col-md-7">
                                <div class="form-group">
                                    <label for="actividad_economica" class="form-label">
                                        Paso 2. Actividad Económica del Patrón/Empresa <span class="text-required">(*)</span>
                                    </label>
                                    <input type="text" name="actividad_economica" id="actividad_economica" oninput="this.value = this.value.toUpperCase()" class="form-control" placeholder="Ej: Comercio de productos al por menor, construcción..." required>
                                    <div class="invalid-feedback">
                                        El campo actividad económica es obligatorio.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="d-flex justify-content-end align-items-center gap-3 mt-4 pt-3 border-top">
                            <a href="{{ route('publico') }}" class="btn btn-cancelar">
                                <i class="bi bi-arrow-left me-1"></i> Regresar
                            </a>
                            <button id="btn-guardar" type="submit" class="btn btn-dorado">
                                Guardar y Continuar <i class="bi bi-floppy-fill ms-1"></i>
                            </button>
                        </div>
                    </form>

                </div>
            </section>
        </div>
    </main>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            let motivosSeleccionados = [];

            function showLoader() {
                $('#page-loader').removeClass('hidden').attr('aria-hidden', 'false');
            }

            function hideLoader() {
                $('#page-loader').addClass('hidden').attr('aria-hidden', 'true');
            }

            // Interceptar submit: validar límite diario por delegación
            $('#form-parte1').on('submit', function(e) {
                // Dejar que el HTML5 validation marque los campos inválidos si corresponde
                if (this.checkValidity && this.checkValidity() === false) {
                    return;
                }

                e.preventDefault();

                const delegacion = ($('#delegacion').val() || '').trim();
                if (!delegacion) {
                    this.submit();
                    return;
                }

                // Consultar límite en backend
                $.ajax({
                    url: '{{ route('solicitudes.check_limite_diario') }}',
                    type: 'GET',
                    dataType: 'json',
                    data: { delegacion: delegacion },
                    success: (resp) => {
                        if (resp && resp.reached) {
                            // Mantiene el loader visible para bloquear la interfaz si se alcanza el límite
                            showLoader();
                            $('#btn-guardar').prop('disabled', true);
                            return;
                        }
                        showLoader();
                        this.submit();
                    },
                    error: () => {
                        showLoader();
                        this.submit();
                    }
                });
            });

            // Agregar Objeto de la Solicitud dinámicamente
            $('#motivo_solicitud').change(function() {
                var opcionSeleccionada = $(this).val();
                var opcionTexto = $("#motivo_solicitud option:selected").text();

                if (!opcionSeleccionada) return;

                // Verifica si ya fue agregado ese motivo
                if (motivosSeleccionados.includes(opcionSeleccionada)) {
                    alert('Este motivo ya ha sido seleccionado.');
                    $(this).val('');
                    return;
                }

                motivosSeleccionados.push(opcionSeleccionada);

                $('#tabla tbody').append(
                    '<tr data-id="' + opcionSeleccionada + '">' +
                        '<td class="text-start ps-3 fw-semibold">' + opcionTexto + '</td>' +
                        '<td><button type="button" class="eliminar btn btn-outline-danger btn-sm"><i class="bi bi-trash-fill me-1"></i> Eliminar</button></td>' +
                    '</tr>'
                );

                $('#div1').append(
                    '<input type="hidden" name="motivo_solicitud[]" value="' + opcionSeleccionada + '" id="input-motivo-' + opcionSeleccionada + '">'
                );

                // Reinicia el select
                $(this).val('');
            });

            // Eliminar fila de la tabla e input hidden correspondiente
            $(document).on('click', '.eliminar', function() {
                var fila = $(this).closest('tr');
                var idMotivo = fila.attr('data-id');

                $('#input-motivo-' + idMotivo).remove();
                fila.remove();

                motivosSeleccionados = motivosSeleccionados.filter(id => id !== idMotivo);
            });
        });

        // Carga dinámica de Delegaciones según el Municipio seleccionado
        document.addEventListener('DOMContentLoaded', function () {
            const delegacionSelect = document.getElementById('delegacion');
            const municipioSelect = document.getElementById('dSolicitud');

            const delegaciones = {
                1: ['Morelia'],
                2: ['Zitácuaro'],
                3: ['Uruapan'],
                4: ['Lázaro Cárdenas'],
                5: ['Zamora'],
                6: ['Sahuayo']
            };

            municipioSelect.addEventListener('change', function () {
                const selectedOption = municipioSelect.options[municipioSelect.selectedIndex];
                const delegacionId = selectedOption.getAttribute('data-delegacion-id');

                // Limpia el select de delegación
                delegacionSelect.innerHTML = '<option value="">Seleccione delegación...</option>';

                if (delegacionId && delegaciones[delegacionId]) {
                    delegaciones[delegacionId].forEach(delegacion => {
                        const option = document.createElement('option');
                        option.value = delegacion;
                        option.textContent = delegacion;
                        delegacionSelect.appendChild(option);
                    });
                }
            });
        });
    </script>
</body>
</html>