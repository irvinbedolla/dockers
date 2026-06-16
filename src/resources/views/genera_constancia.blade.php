<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>Si concilio</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <!-- Bootstrap 5.3.3 -->
    <link href="public/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>

    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js'></script>

    <!-- Ionicons -->
    <link rel="icon" href="public/assets/images/ccl-r.png" type="image/x-icon">
    <link href="//fonts.googleapis.com/css?family=Lato&display=swap" rel="stylesheet">
    <link href="public/assets/css/all.css" rel="stylesheet" type="text/css">
    <link href="public/assets/css/iziToast.min.css" rel="stylesheet">
    <link href="public/assets/css/sweetalert.css" rel="stylesheet" type="text/css"/>
    <link href="public/assets/css/select2.min.css" rel="stylesheet" type="text/css"/>
    
    <!-- Agregados para los Select del Formulario Personas-->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <style>
        .loader {
            position: fixed;
            left: 0px;
            top: 0px;
            width: 100%;
            height: 100%;
            z-index: 9999;
            background: url('public/assets/images/pageLoader.gif') 50% 50% no-repeat rgb(249,249,249);
           /* background-color: #6A0F49;/*<p style="color: #CEA845*/
            opacity: .8;
        }
        .resultado {
            background-color: red;
            color: white;
            font-weight: bold;
        }
        .resultado.ok {
            background-color: green;
        }

        .fc-event {
            padding: 3px 6px !important;
            border-radius: 4px !important;
            font-size: 12px !important;
            cursor: pointer;
        }

        #calendar {
            width: 100%;
            min-height: 500px;
        }

        .fc-event-disponible {
            background-color: #00CE1C !important;
            border-color: #00CE1C !important;
            cursor: pointer;
        }

        .fc-event-ocupado {
            background-color: #DA0909 !important;
            border-color: #DA0909 !important;
            cursor: not-allowed;
        }

        .fc-event-selected {
            border: 2px solid #FFD700 !important;
            box-shadow: 0 0 8px #FFD700;
        }

        .modal-xl {
            max-width: 95% !important;
        }

        .modal-content {
            height: 90vh;
        }

        .modal-body {
            overflow-y: auto;
        }

        .btn-custom-morado {
            height: 50px;
            font-size: 12px;
            padding: 5px 10px;
            background-color: #6A0F49 !important;
            color: #fff !important;
            border: none;
        }
        .btn-custom-morado:hover, .btn-custom-morado:focus {
            background-color: #530c3a !important;
            color: #fff !important;
        }
         body {
            background-color: #f4f6f9;
            padding-top: 100px; /* Espacio para el navbar fijo */
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

    @yield('page_css')
    <!-- Template CSS <img src="public/assets_seer/images/ccl.png" width="180" height="90" style="position: absolute; left: 100px; top: 10px; right:0px;"/>  -->
    <link rel="stylesheet" href="public/assets/css/components.css">
    @yield('page_css')
</head>
<nav class="navbar navbar-light fixed-top navbar-custom">
    <div class="container justify-content-center">
        <a class="navbar-brand m-0" href="https://foro-nacional.cclmichoacan.gob.mx/">
            <img src="public/assets/images/registro-foro-nacional-consolidacion-justicia-laboral.png" alt="Logo Foro Nacional">
        </a>
    </div> 
</nav>

<body onload="validarcheckfolio()">
    <main>
        <div class="container">
            <br><br><br><br>
        </div>
        <div id="app">  
        <section class="section"> 
            <div class="section-body">
                <div class="row"> 
                    <div class="col-lg-12" >
                        <div class="card">
                            <div class="card-body">
                                @if(session()->has('success'))
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <strong>¡Registro correcto!</strong>
                                        {{ session()->get('success') }}
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                @endif
                                @if (session()->has('error'))
                                    <div class="alert alert-dark alert-dismissible fade show" role="alert">
                                        <strong>¡Revise los campos!</strong>
                                        {{ session()->get('error') }}
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                @endif
                                <div style="background-color:#D2D3D5; width:100%; height:40px;">
                                    <h3 class="text-center" style="color:black">Genera tu constancia</h3> 
                                </div>
                                <form method="POST" action="{{ route('generaConstancia') }}" id="formConstancia">
                                    @csrf
                                    <div class="row align-items-end">
                                        <div class="col-md-4">
                                            <label for="folio" class="form-label fw-bold">Folio</label>
                                            <input type="number" name="folio" id="folio" class="form-control" value="{{ old('folio', $id ?? '') }}">
                                        </div>
                                        <button type="submit" class="btn btn-success">Buscar</button>
                                        <div class="col-md-6">
                                            <label for="folio" class="form-label fw-bold">Conferencia/Conversatorio</label>
                                            <select name="constancia" id="constancia" class="form-control">
                                                <option value="">Seleccione la constancia a generar</option>
                                                <option value="Genera">Generar Constancia</option>
                                            </select>
                                        </div>
                                        <center><p>Si no recibiste un <b>folio</b> en el correo eléctronico que ingresaste en tu registro, envia un correo con tu nombre completo a la siguiente dirección <b>aleal@cclmichoacan.gob.mx</b> solicitando tu folio.</p></center>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const folioInput = document.getElementById('folio');
                const constanciaSelect = document.getElementById('constancia');

                constanciaSelect.addEventListener('change', function() {
                    const folio = folioInput.value.trim();
                    const conferencia = constanciaSelect.value;

                    if (folio !== '' && conferencia !== '') {
                        const url = `{{ url('crear_constancia') }}`;
                        fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ folio: folio, constancia: conferencia })
                        })
                        .then(response => response.blob())
                        .then(blob => {
                            const fileURL = URL.createObjectURL(blob);
                            window.open(fileURL, '_blank');
                        })
                        .catch(error => console.error('Error al generar PDF:', error));
                    }
                });
            });
        </script>
    </main>
</body>
@section('scripts')
    <script src="public/assets/js/validaciones-ratificacion.js"></script> 
    <script src="public/assets/js/poderes/general.js"></script>
@endsection

    <script src="public/assets/js/jquery.min.js"></script>
    <script src="public/assets/js/popper.min.js"></script>
    <script src="public/assets/js/bootstrap.min.js"></script>
    <script src="public/assets/js/sweetalert.min.js"></script>
    <script src="public/assets/js/select2.min.js"></script>
    <script src="public/assets/js/jquery.nicescroll.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/locales-all.min.js"></script>

    <!-- Template JS File -->
    <script src="public/assets/js/stisla.js"></script>
    <script src="public/assets/js/scripts.js"></script>
    <script src="public/assets/js/profile.js"></script>
    <script src="public/assets/js/custom.js"></script>

    <script src="https://cdn.datatables.net/2.1.5/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.1.5/js/dataTables.bootstrap4.js"></script>