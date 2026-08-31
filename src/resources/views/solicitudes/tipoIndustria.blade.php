<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    @include('partials.favicon')
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="SiConcilio - Competencia por Actividad del Empleador">
    <meta name="author" content="Centro de Conciliación Laboral de Michoacán">
    <title>Si Concilio - Competencia por Actividad</title>

    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Librerías de Apoyo (jQuery, SweetAlert, Popovers) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

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

        .link-privacidad {
            color: var(--primary-guinda);
            font-weight: 700;
            text-decoration: underline;
            transition: color 0.2s ease;
        }

        .link-privacidad:hover {
            color: var(--primary-hover);
        }

        /* Envoltura de Contenido */
        .main-card {
            background: #ffffff;
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            padding: 1.5rem;
        }

        .title-header {
            color: var(--primary-guinda);
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Caja de Información Relevante */
        .info-notice-box {
            background-color: #fff8eb;
            border-left: 4px solid var(--accent-dorado);
            border-radius: 6px;
            padding: 1rem;
            font-size: 0.85rem;
            color: #5a4300;
        }

        /* Grid de Items de Industrias */
        .industry-item-card {
            border: 1px solid #e3e8ee;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            background: #ffffff;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 100%;
        }

        .industry-item-card:hover {
            border-color: var(--accent-dorado);
            box-shadow: 0 3px 10px rgba(206, 168, 69, 0.15);
            background-color: #fffdf9;
        }

        .form-check-input:checked {
            background-color: var(--primary-guinda);
            border-color: var(--primary-guinda);
        }

        .industry-label {
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--text-dark);
            cursor: pointer;
            margin-bottom: 0;
        }

        .btn-info-popover {
            color: var(--accent-dorado);
            font-size: 1.1rem;
            padding: 0 4px;
            transition: color 0.2s ease;
        }

        .btn-info-popover:hover {
            color: var(--primary-guinda);
        }

        /* Botones de Acción */
        .btn-guinda {
            background-color: var(--primary-guinda);
            border-color: var(--primary-guinda);
            color: #ffffff !important;
            font-weight: 700;
            padding: 0.6rem 1.8rem;
            border-radius: 6px;
            box-shadow: 0 4px 10px rgba(106, 15, 73, 0.2);
            transition: all 0.2s ease;
        }

        .btn-guinda:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
            transform: translateY(-1px);
        }

        .btn-cancelar {
            background-color: #6c757d;
            border-color: #6c757d;
            color: #ffffff !important;
            font-weight: 600;
            padding: 0.6rem 1.5rem;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .btn-cancelar:hover {
            background-color: #5a6268;
        }

        /* Estilos Modales */
        .modal-header-custom {
            background: linear-gradient(135deg, var(--primary-guinda), var(--primary-hover));
            color: #ffffff;
            border-bottom: none;
        }

        .modal-header-custom .btn-close {
            filter: invert(1);
        }

        .pdf-viewer-container {
            overflow: auto;
            max-height: 550px;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            background: #e9ecef;
            padding: 10px;
        }

        .pdf-viewer-container img {
            transition: transform 0.2s ease;
            max-width: 100%;
        }
    </style>
</head>
<body>

    <!-- Navegación Fija -->
    <nav class="navbar navbar-expand-lg navbar-institutional fixed-top py-2">
        <div class="container-fluid px-4">
            <a class="navbar-brand py-0" href="{{ route('login') }}">
                <img src="{{ asset('assets/images/Logos 2.png') }}" alt="Logo CCL Michoacán" height="60">
            </a>
            <div class="ms-auto">
                <a class="link-privacidad d-flex align-items-center gap-1" href="#" onclick="$('#modal-aviso-privacidad').modal('show'); return false;">
                    <i class="bi bi-shield-check"></i>
                    <span>Aviso de Privacidad</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Separador -->
    <div style="margin-top: 95px;"></div>

    <!-- Contenedor Principal -->
    <main class="container my-4 flex-grow-1">
        <div class="main-card">
            
            <div class="text-center mb-3">
                <h3 class="title-header h4 mb-1">Competencia por Actividad del Empleador</h3>
                <p class="text-muted small mb-0">Selecciona la actividad económica principal que corresponde a tu patrón o empresa</p>
            </div>
            
            <hr class="text-secondary opacity-25 my-3">

            <!-- Cuadro de Aviso / Instrucciones -->
            <div class="info-notice-box d-flex align-items-start gap-2 mb-4">
                <i class="bi bi-info-circle-fill fs-5 flex-shrink-0 mt-1"></i>
                <div>
                    <strong>Nota importante:</strong> Antes de seleccionar una industria, es recomendable hacer clic en el botón de información <i class="bi bi-info-circle-fill"></i> para verificar detalles. 
                    <strong>Si la descripción no coincide exactamente</strong> con la actividad principal del patrón, deberás seleccionar la opción <strong>"Ninguna de las anteriores"</strong>.
                </div>
            </div>

            <!-- Formulario con Selección de Industria -->
            <form id="formIndustria">
                <div class="row g-3">

                    <!-- Aceites y grasas -->
                    <div class="col-12 col-md-6">
                        <div class="industry-item-card">
                            <div class="form-check m-0">
                                <input type="radio" class="form-check-input industria" id="Aceites1" name="industria" value="1"
                                    data-nombre="Aceites y grasas vegetales "
                                    data-descripcion="<strong>Si la empresa o patrón(a):</strong><p>Se dedica a la producción de aceites y grasas vegetales comestibles, extraídos de las oleaginosas, principalmente de soya, canola y cártamo.</p><br>De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.<br>Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud: <p><a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral' target='_blank'>O da clic en el siguiente enlace</a></p><br><p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p>"
                                    onclick="mostrarDetalleIndustria(this)">
                                <label class="form-check-label industry-label ms-1" for="Aceites1">Aceites y grasas vegetales</label>
                            </div>
                            <a href="javascript:void(0)" class="btn-info-popover" onclick="triggerInfo('Aceites1')"><i class="bi bi-info-circle-fill"></i></a>
                        </div>
                    </div>

                    <!-- Automotriz -->
                    <div class="col-12 col-md-6">
                        <div class="industry-item-card">
                            <div class="form-check m-0">
                                <input type="radio" class="form-check-input industria" id="AUTOMOTRIZ3" name="industria" value="3"
                                    data-nombre="Automotriz"
                                    data-descripcion="<strong>Si la empresa o patrón(a):</strong><p>Se dedica a la fabricación de automóviles, incluyendo autopartes mecánicas o eléctricas.</p><br>De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria es de Competencia Federal.<br>Acude al CFCRL de tu entidad para realizar la solicitud: <a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral' target='_blank'>Ir al enlace</a><br><p>En caso contrario selecciona &quot;Ninguna de las anteriores&quot;.</p>"
                                    onclick="mostrarDetalleIndustria(this)">
                                <label class="form-check-label industry-label ms-1" for="AUTOMOTRIZ3">Automotriz</label>
                            </div>
                            <a href="javascript:void(0)" class="btn-info-popover" onclick="triggerInfo('AUTOMOTRIZ3')"><i class="bi bi-info-circle-fill"></i></a>
                        </div>
                    </div>

                    <!-- Azucarera -->
                    <div class="col-12 col-md-6">
                        <div class="industry-item-card">
                            <div class="form-check m-0">
                                <input type="radio" class="form-check-input industria" id="AZUCARERA4" name="industria" value="4"
                                    data-nombre="Azucarera"
                                    data-descripcion="<strong>Si la empresa o patrón(a):</strong><p>Se dedica a la producción de azúcar en ingenios azucareros.</p><br>Es de Competencia Federal. Acude al CFCRL de tu entidad."
                                    onclick="mostrarDetalleIndustria(this)">
                                <label class="form-check-label industry-label ms-1" for="AZUCARERA4">Azucarera</label>
                            </div>
                            <a href="javascript:void(0)" class="btn-info-popover" onclick="triggerInfo('AZUCARERA4')"><i class="bi bi-info-circle-fill"></i></a>
                        </div>
                    </div>

                    <!-- Calera -->
                    <div class="col-12 col-md-6">
                        <div class="industry-item-card">
                            <div class="form-check m-0">
                                <input type="radio" class="form-check-input industria" id="CALERA6" name="industria" value="6"
                                    data-nombre="Calera"
                                    data-descripcion="<strong>Si la empresa o patrón(a):</strong><p>Se dedica a la calcinación de piedra caliza para producir cal.</p><br>Es de Competencia Federal."
                                    onclick="mostrarDetalleIndustria(this)">
                                <label class="form-check-label industry-label ms-1" for="CALERA6">Calera</label>
                            </div>
                            <a href="javascript:void(0)" class="btn-info-popover" onclick="triggerInfo('CALERA6')"><i class="bi bi-info-circle-fill"></i></a>
                        </div>
                    </div>

                    <!-- Celulosa y papel -->
                    <div class="col-12 col-md-6">
                        <div class="industry-item-card">
                            <div class="form-check m-0">
                                <input type="radio" class="form-check-input industria" id="Celulosa7" name="industria" value="7"
                                    data-nombre="Celulosa y papel"
                                    data-descripcion="<strong>Si la empresa o patrón(a):</strong><p>Se dedica a la producción de celulosa y papel, pulpa, cartón y derivados.</p>"
                                    onclick="mostrarDetalleIndustria(this)">
                                <label class="form-check-label industry-label ms-1" for="Celulosa7">Celulosa y papel</label>
                            </div>
                            <a href="javascript:void(0)" class="btn-info-popover" onclick="triggerInfo('Celulosa7')"><i class="bi bi-info-circle-fill"></i></a>
                        </div>
                    </div>

                    <!-- Cementera -->
                    <div class="col-12 col-md-6">
                        <div class="industry-item-card">
                            <div class="form-check m-0">
                                <input type="radio" class="form-check-input industria" id="CEMENTERA8" name="industria" value="8"
                                    data-nombre="Cementera"
                                    data-descripcion="<strong>Si la empresa o patrón(a):</strong><p>Se dedica a la fabricación de la mezcla de caliza y arcilla calcinada (Ej. Cemex, Holcim).</p>"
                                    onclick="mostrarDetalleIndustria(this)">
                                <label class="form-check-label industry-label ms-1" for="CEMENTERA8">Cementera</label>
                            </div>
                            <a href="javascript:void(0)" class="btn-info-popover" onclick="triggerInfo('CEMENTERA8')"><i class="bi bi-info-circle-fill"></i></a>
                        </div>
                    </div>

                    <!-- Cinematográfica -->
                    <div class="col-12 col-md-6">
                        <div class="industry-item-card">
                            <div class="form-check m-0">
                                <input type="radio" class="form-check-input industria" id="CINEMATOGRAFICA9" name="industria" value="9"
                                    data-nombre="Cinematográfica"
                                    data-descripcion="<strong>Si la empresa o patrón(a):</strong><p>Se dedica a la producción, distribución y proyección de películas (Ej. Cinépolis, Cinemex).</p>"
                                    onclick="mostrarDetalleIndustria(this)">
                                <label class="form-check-label industry-label ms-1" for="CINEMATOGRAFICA9">Cinematográfica</label>
                            </div>
                            <a href="javascript:void(0)" class="btn-info-popover" onclick="triggerInfo('CINEMATOGRAFICA9')"><i class="bi bi-info-circle-fill"></i></a>
                        </div>
                    </div>

                    <!-- Elaboradora de bebidas -->
                    <div class="col-12 col-md-6">
                        <div class="industry-item-card">
                            <div class="form-check m-0">
                                <input type="radio" class="form-check-input industria" id="BEBIDAS11" name="industria" value="11"
                                    data-nombre="Elaboradora de bebidas"
                                    data-descripcion="<strong>Si la empresa o patrón(a):</strong><p>Se dedica a la elaboración de bebidas envasadas o enlatadas al alto vacío (Ej. Coca Cola, Jumex, embotelladoras de agua).</p>"
                                    onclick="mostrarDetalleIndustria(this)">
                                <label class="form-check-label industry-label ms-1" for="BEBIDAS11">Elaboradora de bebidas</label>
                            </div>
                            <a href="javascript:void(0)" class="btn-info-popover" onclick="triggerInfo('BEBIDAS11')"><i class="bi bi-info-circle-fill"></i></a>
                        </div>
                    </div>

                    <!-- Eléctrica -->
                    <div class="col-12 col-md-6">
                        <div class="industry-item-card">
                            <div class="form-check m-0">
                                <input type="radio" class="form-check-input industria" id="ELECTRICA12" name="industria" value="12"
                                    data-nombre="Eléctrica"
                                    data-descripcion="<strong>Si la empresa o patrón(a):</strong><p>Se dedica principalmente a la generación y distribución de energía eléctrica.</p>"
                                    onclick="mostrarDetalleIndustria(this)">
                                <label class="form-check-label industry-label ms-1" for="ELECTRICA12">Eléctrica</label>
                            </div>
                            <a href="javascript:void(0)" class="btn-info-popover" onclick="triggerInfo('ELECTRICA12')"><i class="bi bi-info-circle-fill"></i></a>
                        </div>
                    </div>

                    <!-- Empresas Gobierno Federal -->
                    <div class="col-12 col-md-6">
                        <div class="industry-item-card">
                            <div class="form-check m-0">
                                <input type="radio" class="form-check-input industria" id="GobFederal2" name="industria" value="2"
                                    data-nombre="Empresas administradas en forma directa o descentralizada del gobierno federal"
                                    data-descripcion="<strong>Si el patrón es:</strong><p>ISSSTE, IMSS, CONAPRED, Instituto Nacional de Lenguas Indígenas, etc.</p>"
                                    onclick="mostrarDetalleIndustria(this)">
                                <label class="form-check-label industry-label ms-1" for="GobFederal2">Empresas descentralizadas del Gob. Federal</label>
                            </div>
                            <a href="javascript:void(0)" class="btn-info-popover" onclick="triggerInfo('GobFederal2')"><i class="bi bi-info-circle-fill"></i></a>
                        </div>
                    </div>

                    <!-- Concesión Federal -->
                    <div class="col-12 col-md-6">
                        <div class="industry-item-card">
                            <div class="form-check m-0">
                                <input type="radio" class="form-check-input industria" id="Concesion10" name="industria" value="10"
                                    data-nombre="Empresas que actúen en virtud de un contrato o concesión federal"
                                    data-descripcion="<strong>Si el patrón es:</strong><p>Televisa, TV Azteca, Telmex, Aerolíneas, Autotransportes federales (ETN, ADO, Primera Plus).</p>"
                                    onclick="mostrarDetalleIndustria(this)">
                                <label class="form-check-label industry-label ms-1" for="Concesion10">Empresas con concesión federal</label>
                            </div>
                            <a href="javascript:void(0)" class="btn-info-popover" onclick="triggerInfo('Concesion10')"><i class="bi bi-info-circle-fill"></i></a>
                        </div>
                    </div>

                    <!-- Zonas Federales -->
                    <div class="col-12 col-md-6">
                        <div class="industry-item-card">
                            <div class="form-check m-0">
                                <input type="radio" class="form-check-input industria" id="ZonasFed29" name="industria" value="29"
                                    data-nombre="Empresas que ejecuten trabajos en zonas federales o jurisdicción federal"
                                    data-descripcion="<strong>Si la empresa o patrón(a):</strong><p>Ejecuta trabajos en zonas federales (marítimas, playas, fronteras, aeropuertos internacionales).</p>"
                                    onclick="mostrarDetalleIndustria(this)">
                                <label class="form-check-label industry-label ms-1" for="ZonasFed29">Trabajos en zonas federales</label>
                            </div>
                            <a href="javascript:void(0)" class="btn-info-popover" onclick="triggerInfo('ZonasFed29')"><i class="bi bi-info-circle-fill"></i></a>
                        </div>
                    </div>

                    <!-- Ferrocarrilera -->
                    <div class="col-12 col-md-6">
                        <div class="industry-item-card">
                            <div class="form-check m-0">
                                <input type="radio" class="form-check-input industria" id="FERROCARRILERA13" name="industria" value="13"
                                    data-nombre="Ferrocarrilera"
                                    data-descripcion="<strong>Si el patrón es:</strong><p>Empresa ferrocarrilera dedicada a la infraestructura, transporte rodante o control ferroviario.</p>"
                                    onclick="mostrarDetalleIndustria(this)">
                                <label class="form-check-label industry-label ms-1" for="FERROCARRILERA13">Ferrocarrilera</label>
                            </div>
                            <a href="javascript:void(0)" class="btn-info-popover" onclick="triggerInfo('FERROCARRILERA13')"><i class="bi bi-info-circle-fill"></i></a>
                        </div>
                    </div>

                    <!-- Hidrocarburos -->
                    <div class="col-12 col-md-6">
                        <div class="industry-item-card">
                            <div class="form-check m-0">
                                <input type="radio" class="form-check-input industria" id="HIDROCARBUROS14" name="industria" value="14"
                                    data-nombre="Hidrocarburos"
                                    data-descripcion="<strong>Si la empresa o patrón(a):</strong><p>Produce gasolina, diésel o gas por extracción en pozos, plataformas o refinerías (Ej. Pemex).</p>"
                                    onclick="mostrarDetalleIndustria(this)">
                                <label class="form-check-label industry-label ms-1" for="HIDROCARBUROS14">Hidrocarburos</label>
                            </div>
                            <a href="javascript:void(0)" class="btn-info-popover" onclick="triggerInfo('HIDROCARBUROS14')"><i class="bi bi-info-circle-fill"></i></a>
                        </div>
                    </div>

                    <!-- Hulera -->
                    <div class="col-12 col-md-6">
                        <div class="industry-item-card">
                            <div class="form-check m-0">
                                <input type="radio" class="form-check-input industria" id="HULERA15" name="industria" value="15"
                                    data-nombre="Hulera"
                                    data-descripcion="<strong>Si la empresa o patrón(a):</strong><p>Se dedica a la extracción del hule o fabricación de llantas.</p>"
                                    onclick="mostrarDetalleIndustria(this)">
                                <label class="form-check-label industry-label ms-1" for="HULERA15">Hulera</label>
                            </div>
                            <a href="javascript:void(0)" class="btn-info-popover" onclick="triggerInfo('HULERA15')"><i class="bi bi-info-circle-fill"></i></a>
                        </div>
                    </div>

                    <!-- Maderera -->
                    <div class="col-12 col-md-6">
                        <div class="industry-item-card">
                            <div class="form-check m-0">
                                <input type="radio" class="form-check-input industria" id="Maderera17" name="industria" value="17"
                                    data-nombre="Maderera"
                                    data-descripcion="<strong>Si la empresa o patrón(a):</strong><p>Se dedica a la extracción, corte y procesamiento de madera básica (aserraderos, triplay).</p>"
                                    onclick="mostrarDetalleIndustria(this)">
                                <label class="form-check-label industry-label ms-1" for="Maderera17">Maderera</label>
                            </div>
                            <a href="javascript:void(0)" class="btn-info-popover" onclick="triggerInfo('Maderera17')"><i class="bi bi-info-circle-fill"></i></a>
                        </div>
                    </div>

                    <!-- Metalúrgica -->
                    <div class="col-12 col-md-6">
                        <div class="industry-item-card">
                            <div class="form-check m-0">
                                <input type="radio" class="form-check-input industria" id="Metalurgica18" name="industria" value="18"
                                    data-nombre="Metalúrgica y siderúrgica"
                                    data-descripcion="<strong>Si la empresa o patrón(a):</strong><p>Se dedica a la obtención de hierro, acero, laminados y fundición de minerales básicos.</p>"
                                    onclick="mostrarDetalleIndustria(this)">
                                <label class="form-check-label industry-label ms-1" for="Metalurgica18">Metalúrgica y siderúrgica</label>
                            </div>
                            <a href="javascript:void(0)" class="btn-info-popover" onclick="triggerInfo('Metalurgica18')"><i class="bi bi-info-circle-fill"></i></a>
                        </div>
                    </div>

                    <!-- Minera -->
                    <div class="col-12 col-md-6">
                        <div class="industry-item-card">
                            <div class="form-check m-0">
                                <input type="radio" class="form-check-input industria" id="MINERA19" name="industria" value="19"
                                    data-nombre="Minera"
                                    data-descripcion="<strong>Si la empresa o patrón(a):</strong><p>Se dedica a la explotación de minerales metálicos y no metálicos en canteras o minas.</p>"
                                    onclick="mostrarDetalleIndustria(this)">
                                <label class="form-check-label industry-label ms-1" for="MINERA19">Minera</label>
                            </div>
                            <a href="javascript:void(0)" class="btn-info-popover" onclick="triggerInfo('MINERA19')"><i class="bi bi-info-circle-fill"></i></a>
                        </div>
                    </div>

                    <!-- Petroquímica -->
                    <div class="col-12 col-md-6">
                        <div class="industry-item-card">
                            <div class="form-check m-0">
                                <input type="radio" class="form-check-input industria" id="PETROQUIMICA20" name="industria" value="20"
                                    data-nombre="Petroquímica"
                                    data-descripcion="<strong>Si la empresa o patrón(a):</strong><p>Se dedica a la transformación del gas natural y derivados del petróleo en materias primas.</p>"
                                    onclick="mostrarDetalleIndustria(this)">
                                <label class="form-check-label industry-label ms-1" for="PETROQUIMICA20">Petroquímica</label>
                            </div>
                            <a href="javascript:void(0)" class="btn-info-popover" onclick="triggerInfo('PETROQUIMICA20')"><i class="bi bi-info-circle-fill"></i></a>
                        </div>
                    </div>

                    <!-- Productora de alimentos -->
                    <div class="col-12 col-md-6">
                        <div class="industry-item-card">
                            <div class="form-check m-0">
                                <input type="radio" class="form-check-input industria" id="ALIMENTOS21" name="industria" value="21"
                                    data-nombre="Productora de alimentos"
                                    data-descripcion="<strong>Si la empresa o patrón(a):</strong><p>Se dedica a la fabricación exclusiva de alimentos empacados, enlatados o envasados al alto vacío.</p>"
                                    onclick="mostrarDetalleIndustria(this)">
                                <label class="form-check-label industry-label ms-1" for="ALIMENTOS21">Productora de alimentos</label>
                            </div>
                            <a href="javascript:void(0)" class="btn-info-popover" onclick="triggerInfo('ALIMENTOS21')"><i class="bi bi-info-circle-fill"></i></a>
                        </div>
                    </div>

                    <!-- Química -->
                    <div class="col-12 col-md-6">
                        <div class="industry-item-card">
                            <div class="form-check m-0">
                                <input type="radio" class="form-check-input industria" id="QUIMICA22" name="industria" value="22"
                                    data-nombre="Química"
                                    data-descripcion="<strong>Si la empresa o patrón(a):</strong><p>Se dedica a la fabricación de productos químicos, farmacéuticos y medicamentos.</p>"
                                    onclick="mostrarDetalleIndustria(this)">
                                <label class="form-check-label industry-label ms-1" for="QUIMICA22">Química</label>
                            </div>
                            <a href="javascript:void(0)" class="btn-info-popover" onclick="triggerInfo('QUIMICA22')"><i class="bi bi-info-circle-fill"></i></a>
                        </div>
                    </div>

                    <!-- Banca y crédito -->
                    <div class="col-12 col-md-6">
                        <div class="industry-item-card">
                            <div class="form-check m-0">
                                <input type="radio" class="form-check-input industria" id="Banca5" name="industria" value="5"
                                    data-nombre="Servicios de banca y crédito"
                                    data-descripcion="<strong>Si la empresa o patrón(a):</strong><p>Se dedica a Banca comercial, créditos bancarios, prendarios (Ej. Monte de Piedad).</p>"
                                    onclick="mostrarDetalleIndustria(this)">
                                <label class="form-check-label industry-label ms-1" for="Banca5">Servicios de banca y crédito</label>
                            </div>
                            <a href="javascript:void(0)" class="btn-info-popover" onclick="triggerInfo('Banca5')"><i class="bi bi-info-circle-fill"></i></a>
                        </div>
                    </div>

                    <!-- Tabacalera -->
                    <div class="col-12 col-md-6">
                        <div class="industry-item-card">
                            <div class="form-check m-0">
                                <input type="radio" class="form-check-input industria" id="TABACALERA25" name="industria" value="25"
                                    data-nombre="Tabacalera"
                                    data-descripcion="<strong>Si la empresa o patrón(a):</strong><p>Se dedica a la fabricación de productos de tabaco (Ej. Philip Morris).</p>"
                                    onclick="mostrarDetalleIndustria(this)">
                                <label class="form-check-label industry-label ms-1" for="TABACALERA25">Tabacalera</label>
                            </div>
                            <a href="javascript:void(0)" class="btn-info-popover" onclick="triggerInfo('TABACALERA25')"><i class="bi bi-info-circle-fill"></i></a>
                        </div>
                    </div>

                    <!-- Textil -->
                    <div class="col-12 col-md-6">
                        <div class="industry-item-card">
                            <div class="form-check m-0">
                                <input type="radio" class="form-check-input industria" id="TEXTIL26" name="industria" value="26"
                                    data-nombre="Textil"
                                    data-descripcion="<strong>Si la empresa o patrón(a):</strong><p>Se dedica a la fabricación industrial de hilo y tela.</p>"
                                    onclick="mostrarDetalleIndustria(this)">
                                <label class="form-check-label industry-label ms-1" for="TEXTIL26">Textil</label>
                            </div>
                            <a href="javascript:void(0)" class="btn-info-popover" onclick="triggerInfo('TEXTIL26')"><i class="bi bi-info-circle-fill"></i></a>
                        </div>
                    </div>

                    <!-- Vidriera -->
                    <div class="col-12 col-md-6">
                        <div class="industry-item-card">
                            <div class="form-check m-0">
                                <input type="radio" class="form-check-input industria" id="VIDRIERA27" name="industria" value="27"
                                    data-nombre="Vidriera"
                                    data-descripcion="<strong>Si la empresa o patrón(a):</strong><p>Se dedica a la fabricación de vidrio plano, envases de vidrio (Ej. Vitro).</p>"
                                    onclick="mostrarDetalleIndustria(this)">
                                <label class="form-check-label industry-label ms-1" for="VIDRIERA27">Vidriera</label>
                            </div>
                            <a href="javascript:void(0)" class="btn-info-popover" onclick="triggerInfo('VIDRIERA27')"><i class="bi bi-info-circle-fill"></i></a>
                        </div>
                    </div>

                    <!-- Ninguna de las anteriores -->
                    <div class="col-12 col-md-6">
                        <div class="industry-item-card bg-light border-secondary-subtle">
                            <div class="form-check m-0">
                                <input type="radio" class="form-check-input industria" id="Ninguna28" name="industria" value="28" data-nombre="Ninguna de las anteriores" data-descripcion="">
                                <label class="form-check-label industry-label ms-1 fw-bold text-dark" for="Ninguna28">Ninguna de las anteriores</label>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Botones de Acción -->
                <div class="d-flex justify-content-end align-items-center gap-2 mt-4 pt-3 border-top">
                    <button type="button" onclick="window.location.href='{{ route('solicitud') }}'" class="btn btn-cancelar">
                        Cancelar Solicitud
                    </button>
                    <button type="button" class="btn btn-guinda" onclick="validarIndustria()">
                        Validar y Continuar <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                </div>
            </form>

        </div>
    </main>

    <!-- MODAL 1: AVISO DE PRIVACIDAD -->
    <div class="modal fade" id="modal-aviso-privacidad" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title font-weight-bold"><i class="bi bi-shield-lock me-2"></i>AVISO DE PRIVACIDAD</h5>
                </div>
                <div class="modal-body text-justify small">
                    <p>Los Datos Personales recabados por el Centro de Conciliación Laboral del Estado de Michoacán de Ocampo, servirán únicamente para realizar el Procedimiento de Conciliación Individual Prejudicial, serán tratados conforme lo dispuesto por la Ley General de Protección de Datos Personales en Posesión de Sujetos Obligados y demás normativa aplicable.</p>
                    <p>Los datos considerados sensibles no serán recabados, ni tratados, ni se realizaran transferencias de datos personales, salvo aquellos que no requieran el consentimiento de los titulares y que sean necesarios para atender su solicitud o requerimientos de información realizados por autoridad competente, siempre y cuando se encuentren debidamente fundados y motivados.</p>
                    <p><strong>Los datos personales recabados serán empleados para:</strong></p>
                    <ul>
                        <li>Registrar al usuario en la plataforma digital SICONCILIO (Sistema Integral para la Conciliación), dar seguimiento y trámite a su solicitud.</li>
                        <li>Administrar la información del solicitante para efectuar el Procedimiento de Conciliación Prejudicial obligatorio y notificaciones.</li>
                        <li>Generar información estadística desasociada.</li>
                        <li>Establecer comunicación con los trabajadores y patrones por correo electrónico o teléfono sobre su procedimiento.</li>
                    </ul>
                    
                    <hr class="my-3">
                    
                    <div class="d-flex justify-content-center gap-4 my-2">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" id="radioAviso1" name="radioAviso" value="1">
                            <label class="form-check-label fw-bold" for="radioAviso1">Sí, acepto.</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" id="radioAviso2" name="radioAviso" value="2">
                            <label class="form-check-label fw-bold" for="radioAviso2">No acepto.</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-guinda w-100" onclick="aceptarAviso()">Aceptar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 2: DERECHOS Y OBLIGACIONES -->
    <div class="modal fade" id="modal-derechos-obligaciones" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title"><i class="bi bi-file-earmark-text me-2"></i>Derechos y Obligaciones</h5>
                </div>
                <div class="modal-body text-center">
                    
                    <!-- Controles de Zoom -->
                    <div class="mb-3">
                        <div class="btn-group shadow-sm" role="group">
                            <button onclick="zoomIn()" type="button" class="btn btn-sm btn-outline-secondary"><i class="bi bi-zoom-in"></i> Zoom +</button>
                            <button onclick="zoomOut()" type="button" class="btn btn-sm btn-outline-secondary"><i class="bi bi-zoom-out"></i> Zoom -</button>
                        </div>
                    </div>

                    <div id="pdfContainer" class="pdf-viewer-container">
                        <img id="pdfImg" src="{{ asset('storage/app/public/pdf/terminos_condiciones.jpg') }}" alt="Términos y Condiciones">
                    </div>

                    <div class="form-check mt-3 text-start d-inline-block">
                        <input class="form-check-input" type="checkbox" id="aceptarCheck">
                        <label class="form-check-label small fw-bold" for="aceptarCheck">
                            He leído y acepto mis derechos y obligaciones del procedimiento de conciliación.
                        </label>
                    </div>
                    
                    <div id="mensaje-error" class="text-danger small mt-1" style="display: none;">
                        Debes aceptar los derechos y obligaciones para poder continuar con el proceso.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="cancelarProceso()">Cancelar</button>
                    <button type="button" id="continuarBtn" class="btn btn-guinda">Continuar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 3: COMPETENCIA FEDERAL (ADVERTENCIA) -->
    <div class="modal fade" id="modal-competencia" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title font-weight-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>Advertencia - Competencia Federal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body small">
                    <p><strong>La industria o servicio que seleccionaste es de competencia federal, no local.</strong></p>
                    <p>Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud: <a href="https://www.gob.mx/cfcrl/articulos/conciliacion-laboral" target="_blank">O da clic en el siguiente enlace</a>.</p>
                    <p class="text-muted">Si no tienes la posibilidad de realizar a tiempo tu solicitud en el CFCRL, puedes continuar la solicitud en el Centro de Conciliación Local y al momento de confirmar tu solicitud, esta será revisada por un funcionario, quien determinará la competencia o la emisión de la constancia de incompetencia.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-guinda" data-bs-dismiss="modal" onclick="sendIndustria()"><i class="bi bi-check-lg me-1"></i> Entendido</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 4: DETALLE DE INDUSTRIA -->
    <div class="modal fade" id="modal-industria-detalle" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title" id="nombre_industria">Detalle de Industria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body small">
                    <div id="detalle_industria"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-guinda" data-bs-dismiss="modal"><i class="bi bi-check-lg me-1"></i> Entendido</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Declaración de Modales
        let modalAvisoPrivacidad, modalDerechos, modalCompetencia, modalDetalle;
        let zoomLevel = 100;

        document.addEventListener('DOMContentLoaded', function () {
            modalAvisoPrivacidad = new bootstrap.Modal(document.getElementById('modal-aviso-privacidad'));
            modalDerechos = new bootstrap.Modal(document.getElementById('modal-derechos-obligaciones'));
            modalCompetencia = new bootstrap.Modal(document.getElementById('modal-competencia'));
            modalDetalle = new bootstrap.Modal(document.getElementById('modal-industria-detalle'));

            // Mostrar el Aviso de Privacidad al cargar la página
            modalAvisoPrivacidad.show();

            // Evento para botón continuar de Derechos y Obligaciones
            const check = document.getElementById('aceptarCheck');
            const continuarBtn = document.getElementById('continuarBtn');
            const mensajeError = document.getElementById('mensaje-error');

            continuarBtn.addEventListener('click', function () {
                if (check.checked) {
                    mensajeError.style.display = 'none';
                    modalDerechos.hide();
                } else {
                    mensajeError.style.display = 'block';
                }
            });

            check.addEventListener('change', function () {
                if (this.checked) mensajeError.style.display = 'none';
            });
        });

        /* Funciones de Zoom */
        function zoomIn() {
            if (zoomLevel < 250) {
                zoomLevel += 15;
                document.getElementById("pdfImg").style.width = zoomLevel + "%";
            }
        }

        function zoomOut() {
            if (zoomLevel > 60) {
                zoomLevel -= 15;
                document.getElementById("pdfImg").style.width = zoomLevel + "%";
            }
        }

        /* Detalle de Industria Modal */
        window.mostrarDetalleIndustria = function (element) {
            const nombre = element.getAttribute('data-nombre');
            const descripcionHTML = element.getAttribute('data-descripcion');

            if (!descripcionHTML) return; // Caso "Ninguna de las anteriores"

            document.getElementById('nombre_industria').innerText = nombre;
            document.getElementById('detalle_industria').innerHTML = descripcionHTML;
            modalDetalle.show();
        };

        function triggerInfo(idInput) {
            const el = document.getElementById(idInput);
            if (el) mostrarDetalleIndustria(el);
        }

        /* Aceptar Aviso de Privacidad */
        function aceptarAviso() {
            const aceptado = document.getElementById('radioAviso1').checked;
            if (!aceptado) {
                alert("Debes aceptar el aviso para poder continuar.");
                return;
            }
            modalAvisoPrivacidad.hide();
            setTimeout(function () {
                modalDerechos.show();
            }, 400);
        }

        function cancelarProceso() {
            window.location.href = "{{ route('publico') }}";
        }

        /* Validación de Competencia Federal / Local */
        function validarIndustria() {
            var industria = $("input[name='industria']:checked");

            if (!industria.length) {
                alert("Debes seleccionar una industria.");
                return;
            }

            var nombreIndustria = industria.data('nombre');
            var industriasFederales = [
                "Aceites y grasas vegetales ",
                "Azucarera",
                "Celulosa y papel",
                "Cinematográfica",
                "Eléctrica",
                "Empresas que actúen en virtud de un contrato o concesión federal",
                "Ferrocarrilera",
                "Hulera",
                "Metalúrgica y siderúrgica",
                "Petroquímica",
                "Química",
                "Tabacalera",
                "Vidriera",
                "Automotriz",
                "Calera",
                "Cementera",
                "Elaboradora de bebidas",
                "Empresas administradas en forma directa o descentralizada del gobierno federal",
                "Empresas que ejecuten trabajos en zonas federales o jurisdicción federal",
                "Hidrocarburos",
                "Maderera",
                "Minera",
                "Productora de alimentos",
                "Servicios de banca y crédito",
                "Textil"
            ];

            if (industriasFederales.includes(nombreIndustria)) {
                modalCompetencia.show();
            } else {
                //window.location.href = "{{ route('solicitud_trabajador', [$tipo_solicitud]) }}";
            }
        }

        function sendIndustria() {
            window.location.href = "{{ route('solicitud_trabajador', [$tipo_solicitud]) }}";
        }
    </script>
</body>
</html>