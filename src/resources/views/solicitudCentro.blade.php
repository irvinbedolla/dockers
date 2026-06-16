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
        </nav>
        <div class="container">
            <br><br><br><br><br>
        </div>
        <main>
            <div>
                <h2 style="color: #4A001F; text-align: center;">Realiza tu solicitud en línea</h2>
                <center><img src="public/assets/images/Baner.png" class="responsive-img" width="750" height="350" alt="Descripción de la imagen"></center>
            </div>
            <br>
            <center><div class="card-group">
                <div class="card">   <!-- Inicio Seer -->
                    <div style="display: block; text-align: center;">
                        <a href="{{ route('solicitud.industriaCentro', ['tipo_solicitud' =>1]) }}" class="button-link">
                            SOY <br>TRABAJADOR(A)
                        </a> 
                    </div> 
                </div>   <!-- fin Seer -->
                <div class="card">   <!-- Inicio Seer -->
                    <div style="display: block; text-align: center;">
                        <a href="{{ route('solicitud.industriaCentro', ['tipo_solicitud' =>2]) }}" class="button-link">
                            SOY <br>PATRONAL INDIVIDUAL   
                        </a>
                    </div>  
                </div>   <!-- 
                <div class="card">  
                    <div style="display: block; text-align: center;">
                        <a href="{{ route('solicitud.industria', ['tipo_solicitud' =>3]) }}" class="button-link">
                            SOY <br>PATRONAL COLECTIVA    
                        </a>
                    </div>  
                </div> 
                <div class="card"> 
                    <div style="display: block; text-align: center;">
                        <a href="{{ route('solicitud.industria', ['tipo_solicitud' =>4]) }}" class="button-link">
                            SOY<br> SINDICATO
                        </a>
                    </div>  
                </div> -->
            </div></center><br><br><br>
        </main>
   
        <footer id="contacto" class="d-flex flex-wrap justify-content-between align-items-center py-3 my-4 border-top">
            <div class="col-md-4 d-flex align-items-center">
              <a href="/" class="mb-3 me-2 mb-md-0 text-body-secondary text-decoration-none lh-1">
                <svg class="bi" width="30" height="24"><use xlink:href="#bootstrap"></use></svg>
              </a>
                <span class="mb-3 mb-md-0 text-body-secondary">Teléfono de contacto: <b>(443) 688 6337</b></span>
            </div>
            <div class="col-md-4 d-flex align-items-center">
                <div class="col-md-3, mx-auto, my-auto">
                    <a href="https://x.com/cclmichoacan/status/1902452234568265892"><img src="public/assets/images/X.png" style="width: 8%"></a>
                    &ensp;<a href="https://www.instagram.com/cclmichoacan/"><img src="public/assets/images/IG.png" style="width:10%"></a>
                    &ensp;<a href="https://www.facebook.com/conciliacionlaboralmich/?locale=es_LA"><img src="public/assets/images/FC.png" style="width:8%"></a>
                    &ensp;<a href="https://www.tiktok.com/@cclmichoacan0?_t=ZM-8uooi2eSI1V&_r=1"><img src="public/assets/images/TK.png" style="width:8%"></a>
                </div>
            </div>
        </footer>
      
            <!--<script src="public/assets_seer/assets/dist/js/bootstrap.bundle.min.js"></script>-->
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
      
</body>
</html>