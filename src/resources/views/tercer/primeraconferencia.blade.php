<!doctype html>
<html class="wide wow-animation" lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
        <meta name="generator" content="Ing. ISBM">
        <link rel="icon" href="public/assets/images/logo-ccl.png" type="image/x-icon">
        <title>Si Concilio</title>
        <!-- Bootstrap core CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <style>
            .card {
                border: none;
                margin: 30px 30px; /* posición de los botones en la vista */
            }
            .card-group {
                display: flex;
                justify-content: center;
                align-items: center;
                flex-wrap: wrap;
                width: 600px;
                gap: 24px; /* Define un espacio entre los botones */
            }
            /*Estilos boton*/
            .button-link {
                display: flex;
                justify-content: center; 
                align-items: center; 
                padding: 0; 
                font-size: 16px;
                text-align: center;
                width: 130px;   
                height: 65px;   
                background-color: #CEA845; 
                color: white; 
                text-decoration: none; 
                border-radius: 5px; /* Bordes redondeados */
                cursor: pointer;
                transition: transform 0.3s ease; /* Transición suave para el zoom */
                line-height: 1.2;
            }

            /* Efecto de zoom al pasar el ratón */
            .button-link:hover {
                transform: scale(1.2); /* Aumenta el tamaño del botón en un 20% */
            }

            /* Efecto de zoom al hacer clic en el botón */
            .button-link:active {
                transform: scale(1); /* Vuelve al tamaño original */
            }

            /* Efecto de cambio de color al pasar el ratón */
            .button-link:hover {
                background-color:#FFC3D0; /* Cambia el color de fondo al pasar el ratón */
                border-radius: 5px; /* Bordes redondeados */
            } 
           /* .card{
                border:none;
            }
            .card-group{
                width: 500px;   /* Establece el ancho fijo del botón */
                /*height: 100px;
            }*/
            .responsive-img{
                max-width: 100%;
                height: auto;
            }
           
        </style>
        <!-- Custom styles for this template -->
    </head>
    <body>
        <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
            <div class="">
                &nbsp;&nbsp;<img src="public/assets/images/Logos 2.png" class="img" width="250" height="90">
            </div> 
        </nav><br><br><br><br><br>
        <main>
            @if(session()->has('success'))
                <div class="alert alert-success">
                    <strong> {{ session()->get('success') }}</strong>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form class='needs-validation novalidate' id='form_roles' method='POST' action="{{route('guardar_asistencia')}}">
                @csrf
                <div class="container">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-6">
                            <div class="form-group">
                                <label for="name">Ingresa tu folio</label>
                                <input type="number" name="folio" class="form-control">                                             
                            </div>
                        </div>
                        <div class="col-xs-6 col-sm-6 col-md-6"><br>
                            <button type="submit" class="btn btn-primary">Confirmar</button>
                        </div>
                    </div>
                </div>
            </form>    
        </main>
            <!--<script src="public/assets_seer/assets/dist/js/bootstrap.bundle.min.js"></script>-->
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>  
    </body>
</html>
