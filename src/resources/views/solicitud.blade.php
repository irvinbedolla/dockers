<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Sistema de Solicitud de Conciliación Laboral en Línea">
    <meta name="author" content="Centro de Conciliación Laboral">
    <link rel="icon" href="{{ asset('assets/images/logo-ccl.png') }}" type="image/x-icon">
    <title>Si Concilio - Centro de Conciliación Laboral</title>
    
    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root {
            --primary-guinda: #4A001F;
            --primary-guinda-hover: #360017;
            --accent-dorado: #CEA845;
            --accent-dorado-hover: #b59239;
            --bg-light: #F8F9FA;
            --text-dark: #2B2D42;
        }

        body {
            background-color: var(--bg-light);
            color: var(--text-dark);
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Navbar Institucional */
        .navbar-institutional {
            background-color: #FFFFFF;
            border-bottom: 3px solid var(--accent-dorado);
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        /* Banner y Encabezado */
        .main-header-title {
            color: var(--primary-guinda);
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .banner-container {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            max-width: 900px;
            margin: 0 auto;
        }

        .banner-container img {
            width: 100%;
            height: auto;
            display: block;
        }

        /* Tarjetas de Selección de Rol */
        .role-card {
            border: 1px solid #E2E8F0;
            border-radius: 10px;
            background: #FFFFFF;
            transition: all 0.25s ease-in-out;
            text-decoration: none;
            color: var(--text-dark);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.75rem 1rem;
            height: 100%;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }

        .role-card:not(.disabled):hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(74, 0, 31, 0.12);
            border-color: var(--accent-dorado);
            color: var(--primary-guinda);
        }

        .role-card .icon-wrapper {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: rgba(206, 168, 69, 0.15);
            color: var(--accent-dorado);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            margin-bottom: 1rem;
            transition: all 0.25s ease;
        }

        .role-card:not(.disabled):hover .icon-wrapper {
            background-color: var(--primary-guinda);
            color: #FFFFFF;
        }

        .role-card .role-title {
            font-weight: 700;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: center;
            margin: 0;
        }

        /* Estado Deshabilitado */
        .role-card.disabled {
            opacity: 0.55;
            background-color: #F1F5F9;
            cursor: not-allowed;
            border-color: #CBD5E1;
        }

        .role-card.disabled .icon-wrapper {
            background-color: #E2E8F0;
            color: #94A3B8;
        }

        /* Footer */
        footer {
            margin-top: auto;
            background-color: #FFFFFF;
            border-top: 1px solid #E2E8F0;
        }

        .social-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: #F1F5F9;
            color: var(--primary-guinda);
            text-decoration: none;
            transition: all 0.2s ease;
            font-size: 1.1rem;
        }

        .social-link:hover {
            background-color: var(--primary-guinda);
            color: #FFFFFF;
        }
    </style>
</head>
<body>

    <!-- Navegación Superior -->
    <nav class="navbar navbar-expand-lg navbar-institutional fixed-top py-2">
        <div class="container">
            <a class="navbar-brand py-0" href="#">
                <img src="{{ asset('assets/images/Logos 2.png') }}" alt="Logo Centro de Conciliación Laboral" height="65">
            </a>
        </div>
    </nav>

    <!-- Espaciador para la barra fija -->
    <div style="margin-top: 100px;"></div>

    <!-- Contenido Principal -->
    <main class="container my-4">
        
        <!-- Encabezado de Sección -->
        <div class="text-center mb-4">
            <h2 class="main-header-title h3 mb-2">Realiza tu solicitud en línea</h2>
            <p class="text-muted small">Selecciona el tipo de usuario para iniciar el trámite de conciliación</p>
        </div>

        <!-- Banner Informativo -->
        <div class="banner-container mb-4">
            <img src="{{ asset('assets/images/Baner.png') }}" class="img-fluid" alt="Banner Informativo Conciliación Laboral">
        </div>

        <!-- Alerta de Errores -->
        @if (session('error'))
            <div class="row justify-content-center mb-4">
                <div class="col-12 col-md-10 col-lg-8">
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-0" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Opciones de Perfil/Solicitud -->
        <div class="row justify-content-center g-3 my-2">
            
            <!-- Opción: Trabajador(a) -->
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <a href="{{ route('solicitud.industria', ['tipo_solicitud' => 1]) }}" class="role-card">
                    <div class="icon-wrapper">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <span class="role-title">Soy Trabajador(a)</span>
                </a>
            </div>

            <!-- Opción: Patronal Individual (Deshabilitado temporalmente) -->
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="role-card disabled" title="Opción no disponible momentáneamente">
                    <div class="icon-wrapper">
                        <i class="bi bi-building-lock"></i>
                    </div>
                    <span class="role-title">Patronal Individual</span>
                    <span class="badge bg-secondary mt-2 style-badge" style="font-size: 0.7rem;">Próximamente</span>
                </div>
            </div>

            <!-- Puedes descomentar estas opciones cuando estén activas -->
            <!--
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <a href="{{ route('solicitud.industria', ['tipo_solicitud' => 3]) }}" class="role-card">
                    <div class="icon-wrapper">
                        <i class="bi bi-buildings"></i>
                    </div>
                    <span class="role-title">Patronal Colectiva</span>
                </a>
            </div>

            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <a href="{{ route('solicitud.industria', ['tipo_solicitud' => 4]) }}" class="role-card">
                    <div class="icon-wrapper">
                        <i class="bi bi-people"></i>
                    </div>
                    <span class="role-title">Sindicato</span>
                </a>
            </div>
            -->

        </div>
    </main>

    <!-- Pie de Página Institucional -->
    <footer id="contacto" class="py-4">
        <div class="container">
            <div class="row align-items-center gy-3">
                <div class="col-12 col-md-6 text-center text-md-start">
                    <span class="text-secondary small">
                        <i class="bi bi-telephone-fill me-1 text-danger"></i> Teléfono de atención: <strong>(443) 688 6337</strong>
                    </span>
                </div>
                <div class="col-12 col-md-6">
                    <div class="d-flex justify-content-center justify-content-md-end gap-2">
                        <a href="https://x.com/cclmichoacan/status/1902452234568265892" target="_blank" class="social-link" title="X (Twitter)">
                            <i class="bi bi-twitter-x"></i>
                        </a>
                        <a href="https://www.instagram.com/cclmichoacan/" target="_blank" class="social-link" title="Instagram">
                            <i class="bi bi-instagram"></i>
                        </a>
                        <a href="https://www.facebook.com/conciliacionlaboralmich/?locale=es_LA" target="_blank" class="social-link" title="Facebook">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <a href="https://www.tiktok.com/@cclmichoacan0?_t=ZM-8uooi2eSI1V&_r=1" target="_blank" class="social-link" title="TikTok">
                            <i class="bi bi-tiktok"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="row mt-3 pt-3 border-top">
                <div class="col-12 text-center">
                    <p class="text-muted small mb-0">&copy; {{ date('Y') }} Centro de Conciliación Laboral. Todos los derechos reservados.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>