<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Sistema Integral Concilio">
    <meta name="generator" content="Ing. ISBM">
    <title>Si Concilio - Solicitud Registrada</title>

    <!-- Bootstrap 5.3.3 CSS -->
    <link href="public/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link rel="icon" href="public/assets/images/ccl-r.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="public/assets/css/all.css" rel="stylesheet" type="text/css">

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

        /* Navbar Custom */
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

        /* Main Card */
        .main-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.06);
            background: #ffffff;
            margin-top: 110px;
            margin-bottom: 40px;
            overflow: hidden;
        }

        .card-header-custom {
            background-color: var(--primary-color);
            color: #ffffff;
            padding: 22px;
            text-align: center;
        }

        .card-header-custom h3 {
            margin: 0;
            font-weight: 600;
            font-size: 1.4rem;
        }

        /* Badges & Info Boxes */
        .folio-badge {
            background-color: rgba(106, 15, 73, 0.06);
            border: 2px dashed var(--primary-color);
            color: var(--primary-color);
            border-radius: 8px;
            padding: 16px;
            text-align: center;
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 25px;
        }

        .info-box {
            background-color: #fcf8f2;
            border-left: 4px solid var(--secondary-color);
            border-radius: 6px;
            padding: 16px 20px;
            margin-bottom: 20px;
        }

        .location-box {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
        }

        /* Buttons */
        .btn-gold {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            color: #ffffff;
            font-weight: 600;
            padding: 11px 28px;
            border-radius: 6px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .btn-gold:hover {
            background-color: var(--secondary-hover);
            border-color: var(--secondary-hover);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(206, 168, 69, 0.3);
        }
    </style>
</head>

<body>
    @php     
        $nombreDelegacion = is_object($delegacion) ? ($delegacion->delegacion ?? '') : $delegacion;

        $direccion_sede = '';
        if ($nombreDelegacion === 'Morelia') {
            $direccion_sede = 'BLVD. GARCÍA DE LEÓN NO. 1575, COL. CHAPULTEPEC ORIENTE, C.P. 58260 MORELIA, MICHOACÁN DE OCAMPO, con un horario de atención Lunes a Viernes de 9:00 am a 5:00 pm.';
        } elseif ($nombreDelegacion === 'Uruapan') {
            $direccion_sede = 'NUEVO PARICUTÍN NO. 308, COL. JARDINES DE SAN RAFAEL, C.P. 30136 URUAPAN, MICHOACÁN DE OCAMPO (Dentro del recinto de Rentas del Estado, por la Clínica del IMSS No. 76), con un horario de atención Lunes a Viernes de 9:00 am a 4:00 pm.';
        } elseif ($nombreDelegacion === 'Zamora') {
            $direccion_sede = 'JUSTO SIERRA PONIENTE NO. 290, COL. JARDINES DE CATEDRAL, C.P. 59600 ZAMORA, MICHOACÁN DE OCAMPO, con un horario de atención Lunes a Viernes de 9:00 am a 3:00 pm.';
        } elseif ($nombreDelegacion === 'Zitácuaro') {
            $direccion_sede = '5 DE MAYO NORTE NO. 03, PISO 3 COL. CENTRO, C.P. 61500 ZITÁCUARO, MICHOACÁN DE OCAMPO, con un horario de atención Lunes a Viernes de 9:00 am a 3:00 pm.';
        } elseif ($nombreDelegacion === 'Lázaro Cárdenas') {
            $direccion_sede = 'PARACHO NO. 26, COL. 600 CASAS, C.P. 60950 LÁZARO CÁRDENAS, MICHOACÁN DE OCAMPO, con un horario de atención Lunes a Viernes de 9:00 am a 3:00 pm.';
        } elseif ($nombreDelegacion === 'Sahuayo') {
            $direccion_sede = 'AV. UNIVERSIDAD SUR NO. 3000, COL. LOMAS DE UNIVERSIDAD, C.P. 59103 SAHUAYO DE MORELOS, MICHOACÁN DE OCAMPO, con un horario de atención Lunes a Viernes de 9:00 am a 3:00 pm.';
        }
    @endphp

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light navbar-custom fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('publico') }}">
                <img src="public/assets/images/Logos 2.png" alt="Logo Si Concilio">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <div id="app" class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-9">
                <div class="card main-card">
                    <div class="card-header-custom">
                        <h3><i class="fas fa-check-circle me-2"></i> ¡Registro Completo!</h3>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        <input type="hidden" name="id" value="{{ $id }}">
                        
                        <!-- Main Message -->
                        <div class="alert alert-success border-0 shadow-sm mb-4 p-4" style="text-align: justify; background-color: #eafaf1; color: #1e7e34;">
                            <p class="mb-3 fs-6">
                                Tu solicitud fue capturada correctamente. Para consultar el estado de tu trámite, debes ingresar a:
                                <a href="http://siconcilio.cclmichoacan.gob.mx/" target="_blank" class="fw-bold text-decoration-underline" style="color: #1e7e34;">
                                    http://siconcilio.cclmichoacan.gob.mx/
                                </a> 
                                en el apartado de <strong>Buzón Electrónico</strong> con la siguiente información:
                            </p>
                            <div class="bg-white p-3 rounded text-center border fw-bold text-dark fs-5 shadow-sm">
                                {{ $mensaje }}
                            </div>
                        </div>

                        <!-- Important Note -->
                        <div class="info-box">
                            <h6 class="fw-bold mb-1" style="color: #8a6d3b;">
                                <i class="fas fa-info-circle me-1"></i> NOTA IMPORTANTE:
                            </h6>
                            <p class="mb-0 text-muted small" style="text-align: justify;">
                                En caso de detectar algún error u omisión en los datos proporcionados, el personal del Centro de Conciliación Laboral se pondrá en contacto contigo a la brevedad.
                            </p>
                        </div>

                        <!-- Location Information -->
                        <div class="location-box mt-4">
                            <h5 class="fw-bold mb-2" style="color: var(--primary-color);">
                                <i class="fas fa-building me-2"></i> Atentamente: Delegación u Oficina de Apoyo
                            </h5>
                            <p class="mb-1 fw-semibold text-dark">
                                Centro de Conciliación Laboral en {{ $nombreDelegacion }}
                            </p>
                            <p class="text-muted small mb-0">
                                <i class="fas fa-map-marker-alt me-1 text-danger"></i> 
                                {{ $direccion_sede ?: 'Consulte la ubicación directa en las oficinas del Centro de Conciliación Laboral.' }}
                            </p>
                        </div>

                        <!-- Footer Action -->
                        <div class="text-center mt-5">
                            <a href="{{ route('publico') }}" class="btn btn-gold">
                                <i class="fas fa-home me-1"></i> Volver al Inicio
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts Bootstrap 5 -->
    <script src="../public/assets/js/jquery.min.js"></script>
    <script src="../public/assets/js/bootstrap.min.js"></script>
</body>
</html>