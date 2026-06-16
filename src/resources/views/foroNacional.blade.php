<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>Si concilio - Foro Nacional</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    
    <!-- Bootstrap 5.3.3 -->
    <link href="public/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    
    <!-- Fuentes y Estilos Base -->
    <link rel="icon" href="public/assets/images/ccl-r.png" type="image/x-icon">
    <link href="//fonts.googleapis.com/css?family=Lato&display=swap" rel="stylesheet">
    <link href="public/assets/css/all.css" rel="stylesheet" type="text/css">
    <link href="public/assets/css/iziToast.min.css" rel="stylesheet">
    <link href="public/assets/css/sweetalert.css" rel="stylesheet" type="text/css"/>
    <link href="public/assets/css/select2.min.css" rel="stylesheet" type="text/css"/>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <style>
        :root {
            --color-guinda: #6A0F49;
            --color-dorado: #CEA845;
            --color-fondo: #f9f1e7;
        }

        body {
            background-color: #f4f6f9;
            padding-top: 210px; /* Espacio para el navbar fijo */
        }

        /* Navbar Responsivo */
        .navbar-custom {
            background-color: var(--color-fondo) !important;
            border-bottom: 3px solid var(--color-dorado);
            padding: 10px 0;
        }

        .navbar-brand img {
            max-height: 180px;
            width: auto;
            transition: all 0.3s ease;
        }
        .navbar-brand {
            padding: 0;
            margin: 0;
        }

        /* Loader */
        .loader-container {
            position: fixed;
            left: 0; top: 0;
            width: 100%; height: 100%;
            z-index: 99999;
            background: rgba(255,255,255, 0.9);
            display: none; /* Oculto por defecto */
        }

        .loader-img {
            position: absolute;
            left: 50%; top: 50%;
            transform: translate(-50%, -50%);
            width: 80px;
        }

        /* Estilos de Card y Formulario */
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .header-registro {
            background-color: #D2D3D5;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 25px;
        }

        .form-label {
            font-weight: bold;
            font-size: 0.9rem;
        }

        /* Ajustes Móviles */
        @media (max-width: 768px) {
            body { padding-top: 105px; }
            .navbar-brand img { max-height: 75px; }
            h4 { font-size: 1.1rem; }
            .col-md-4 { margin-bottom: 15px; }
        }
    </style>
    @livewireStyles
</head>

<body onload="validarcheckfolio()">

    <!-- Loader (Se activa solo al confirmar) -->
    <div id="crear_poder" class="loader-container">
        <img src="public/assets/images/pageLoader.gif" class="loader-img" alt="Cargando...">
    </div>

    <!-- Navbar -->
    <nav class="navbar navbar-light fixed-top navbar-custom">
        <div class="container justify-content-center">
            <a class="navbar-brand m-0" href="https://foro-nacional.cclmichoacan.gob.mx/">
                <img src="public/assets/images/registro-foro-nacional-consolidacion-justicia-laboral.png" alt="Logo Foro Nacional">
            </a>
        </div> 
    </nav>

    <main class="container">
        <div id="app">  
            <section class="section"> 
                <div class="row justify-content-center"> 
                    <div class="col-lg-11">
                        <div class="card">
                            <div class="card-body p-4">
                                
                                <!-- Mensajes de Sesión -->
                                @if(session()->has('success'))
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <strong>¡Registro correcto!</strong> {{ session()->get('success') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif

                                @if (session()->has('error'))
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <strong>¡Revise los campos!</strong> {{ session()->get('error') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif

                                <!-- Formulario -->
                                <form id="miFormulario" class="needs-validation" novalidate method="POST" action="{{route('foroNacionalregistro')}}">
                                    @csrf
                                    
                                    <div class="header-registro text-center">
                                        <h4 class="m-0">Registro al Foro Nacional por la Consolidación de la Justicia Laboral en México</h4>
                                    </div>

                                    <div class="row g-3">
                                        <!-- Nombre -->
                                        <div class="col-12 col-md-4">
                                            <div class="form-group">
                                                <label class="form-label">Nombre(s) <span class="text-danger">(*)</span></label>
                                                <input type="text" name="trabajador" class="form-control" oninput="this.value = this.value.toUpperCase()" required> 
                                                <div class="invalid-feedback">El nombre es obligatorio.</div>
                                            </div>
                                        </div>

                                        <!-- Apellido 1 -->
                                        <div class="col-12 col-md-4">
                                            <div class="form-group">
                                                <label class="form-label">Primer apellido <span class="text-danger">(*)</span></label>
                                                <input type="text" name="primero_trabajador" class="form-control" oninput="this.value = this.value.toUpperCase()" required> 
                                                <div class="invalid-feedback">Requerido.</div>
                                            </div>
                                        </div>

                                        <!-- Apellido 2 -->
                                        <div class="col-12 col-md-4">
                                            <div class="form-group">
                                                <label class="form-label">Segundo apellido <span class="text-danger">(*)</span></label>
                                                <input type="text" name="segundo_trabajador" class="form-control" oninput="this.value = this.value.toUpperCase()" required> 
                                                <div class="invalid-feedback">Requerido.</div>
                                            </div>
                                        </div>

                                        <!-- Email -->
                                        <div class="col-12 col-md-4">
                                            <div class="form-group">
                                                <label class="form-label">Email <span class="text-danger">(*)</span></label>
                                                <input type="email" name="email" class="form-control" required> 
                                                <div class="invalid-feedback">Ingrese un email válido.</div>
                                            </div>
                                        </div>

                                        <!-- Teléfono -->
                                        <div class="col-12 col-md-4">
                                            <div class="form-group">
                                                <label class="form-label">Teléfono <span class="text-danger">(*)</span></label>
                                                <input type="number" name="telefono" class="form-control" required> 
                                                <div class="invalid-feedback">Teléfono requerido.</div>
                                            </div>
                                        </div>

                                        <!-- Sexo -->
                                        <div class="col-12 col-md-4">
                                            <div class="form-group">
                                                <label class="form-label">Sexo <span class="text-danger">(*)</span></label>
                                                <select name="trabajador_sexo" class="form-control" required>
                                                    <option value="">Seleccione</option>
                                                    <option value="H">Hombre</option>
                                                    <option value="M">Mujer</option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Procedencia -->
                                        <div class="col-12 col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">¿De dónde nos visitas? <span class="text-danger">(*)</span></label>
                                                <input type="text" name="lugar" class="form-control" placeholder="Estado / Ciudad" required> 
                                            </div>
                                        </div>

                                        <!-- Ocupación -->
                                        <div class="col-12 col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">¿Asistes como? <span class="text-danger">(*)</span></label>
                                                <select name="ocupacion" class="form-control" required>
                                                    <option value="">Seleccione</option>
                                                    <option value="Público general">Público general</option>
                                                    <option value="Estudiante">Estudiante</option>
                                                    <option value="Académico">Académico</option>
                                                    <option value="Servidor público">Servidor público</option>
                                                    <option value="Barra de Abogados">Barra de Abogados</option>
                                                    <option value="Sindicato">Sindicato</option>
                                                    <option value="Ponente">Ponente</option>
                                                    <option value="Ponente">Acompañante CONACENTROS</option>
                                                    <option value="Ponente">Titular de CONACENTROS</option>
                                                    <option value="Ponente">Prensa</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-5 text-center">
                                        <button type="submit" class="btn btn-lg px-5 text-white" style="background-color: var(--color-dorado); border-radius: 30px;">
                                            Enviar Registro
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Scripts -->
    <script src="public/assets/js/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/assets/js/sweetalert.min.js"></script>

    <script>
        // Manejo del envío con Alerta y Loader
        document.getElementById('miFormulario').addEventListener('submit', function(e) {
            var form = this;

            // 1. Prevenir envío para validar
            e.preventDefault();
            e.stopPropagation();

            // 2. Validar con Bootstrap
            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                // Scroll al primer error para móvil
                document.querySelector('.invalid-feedback:visible')?.parentElement.scrollIntoView();
                return;
            }

            // 3. Confirmación SweetAlert
            swal({
                title: "¿Tus datos son correctos?",
                text: "Le solicitamos validar su información, ya que así se imprimirán en el documento oficial de Reconocimiento.",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#CEA845",
                confirmButtonText: "Sí, enviar",
                cancelButtonText: "Cancelar",
                closeOnConfirm: true 
            }, function(isConfirm) {
                if (isConfirm) {
                    // MOSTRAR LOADER SOLO AQUÍ
                    document.getElementById('crear_poder').style.display = 'block';
                    
                    // ENVIAR
                    form.submit();
                }
            });
        });
    </script>

    @yield('scripts')
</body>
</html>