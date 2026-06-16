<!doctype html>
<html class="wide wow-animation" lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
        <meta name="generator" content="Ing. ISBM">
        <link href="public/assets/css/carousel.css" rel="stylesheet">
        <title>Si Concilio</title>
        <!-- Bootstrap core CSS -->
        

        <!-- CARRUSEL -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        <link rel="stylesheet" href="public/assets/css/owl.carousel.css">
        <link rel="stylesheet" href="public/assets/css/owl.carousel.min.css">
        <link rel="stylesheet" href="public/assets/css/owl.theme.default.min.css">
        <script src="public/assets/js/owl.carousel.min.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        
        <link rel="icon" href="public/assets/images/logo-ccl.png" type="image/x-icon">
        <style>
          /*CARRUSEL*/
          .owl-carousel.owl-loaded{
            display:block;
          }
          .owl-carousel {
            display: none;
            width: 100%;
            -webkit-tap-highlight-color: transparent;
            position: relative;
            z-index: 1;
          }
          .owl-carousel .owl-item img {
            display: block;
            width: 100%;
           
          }
          .owl-carousel.owl-drag .owl-item {
            touch-action: pan-y;
            -webkit-user-select: none;
            -moz-user-select: none;
            user-select: none;
          }
          .owl-carousel .owl-item {
            -webkit-backface-visibility: hidden;
            float: left;
            min-height: 1px;
            position: relative;
            -webkit-tap-highlight-color: transparent;
            -webkit-touch-callout: none;
          }
          /*FIN CARRUSEL*/
          body{
            margin: 0;
            padding: 0;
          }
          .boton {
            display: inline-block;
            font-weight: 400;
            text-align: center; 
            white-space: nowrap;
            vertical-align: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            border: 1px solid transparent;
            padding: .375rem .75rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: .25rem;
            transition: color .15s ease-in-out, background-color .15s ease-in-out, border-color .15s ease-in-out, box-shadow .15s ease-in-out;
            text-align: center;
            padding: 0.375rem 0.75rem; /* Tamaño del botón */
            font-weight: 400;
            background-color: #4A001F; /* Color de fondo inicial */
            color: white; /* Color del texto */
          }

          /* Estilo al pasar el mouse */
          .boton:hover {
              color: #4A001F;
              background-color: white; /* Nuevo color de fondo cuando el mouse está sobre el botón */
              text-decoration: none;/* Elimina el subrayado del enlace */
              border-radius: 5px; /* Bordes redondeados */
          } 
          .flip-box{
            border: none;
          }
          .flip-box-back{
            padding: .50rem .30rem;
            text-align: center;
          }
          .card{
            border: none;
            background-color: rgba(255, 195, 208, 0);
          }
          #servicios {
            background-image: url('./public/assets/images/Degradado.png') !important;
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;    
          }
          .flip-box-front{
            background-color: rgba(255, 195, 208, 0);
          }

        </style>
        <!-- Custom styles for this template -->
    </head>
    <body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
        <div class="">
          <a href="#"><img src="public/assets/images/Logos 2.png" class="img" width="250" height="90"></a>&nbsp;&nbsp;
        </div>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent" >
            <ul class="navbar-nav ml-auto">
                <li class="nav-item active">
                    <a class="nav-link" href="#" style="color: black;">INICIO<span class="sr-only"></span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#servicios" style="color: black;">SERVICIOS</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#contacto"style="color: black;">CONTACTO</a>
                </li>
            </ul>
        </div>
    </nav>

  <main>
    <div class="container">
      <br><br><br><br><br>
    </div>
  <!-- CARRUSEL-->
  <div class="owl-carousel owl-theme">
    <img src="public/assets/images/Baner.png" class="d-block w-100" alt="" loading="lazy">
    <!--<img src="public/assets/images/carusel/carrusel_2.png" class="d-block w-100" alt="" loading="lazy">-->
  </div>
  <!-- FIN CARRUSEL-->	
  <div class="container">
      <br><br><br><br><br>
    </div>
  <!-- INICIANDO SERVICIOS -->
  <section id="servicios"><br>
    <h3 class="wow fadeInLeft" style="text-align: center ; font-family:Gibson, font-weight: 600;">Trámites y servicios digitales</h3> <br><br><br>
    <div>
    <div class="card-group">
      <div class="card">   <!-- Inicio asesoria en línea -->
        <div style="display: block; text-align: center;">
          <div class="flip-box">
            <div class="flip-box-inner">
              <div class="flip-box-front" >
                <h2 style="font-size: 22px; font-family: Arial; color:#000000;">Asesoría virtual<br><br></h2>
                <img src="public/assets/images/ASESORIA.png" alt="Descripción de la imagen"
                  style="width: 120px; height:120px; position: absolute; top: 60%; left: 50%; transform: translate(-50%, -50%);">
              </div>
              <div class="flip-box-back">
                 <h2 style="font-size: 21px; font-family: Arial; color:#ffff;  text-align: center"><br>Es un servicio informático que podrás consultar con la finalidad de atender y resolver preguntas acerca de nuestros servicios.</h2>
              </div>
            </div> 
          </div>
            <p><a class="boton" href="{{ route('chat') }}" onclick="window.open(this.href, 'mywin', 'left=' + (window.innerWidth - 500) + ',top=' + (window.innerHeight - 550) + ',width=500,height=550,toolbar=1,resizable=0'); return false;" target="_self">Ver más</a></p>
        </div>  
      </div>   <!-- fin asesoria en línea -->
      <div class="card" > <!-- Inicio calculadora de prestaciones -->
        <div style="display: block; text-align: center;">
          <div class="flip-box">
            <div class="flip-box-inner">
              <div class="flip-box-front">
                <h2 style="font-size: 22px; font-family: Arial; color:#000000">Calculadora de <br>prestaciones</h2>
                <img src="public/assets/images/CALCULADORA.png" alt="Descripción de la imagen"
                    style="width: 120px; height:120px; position: absolute; top: 60%; left: 50%; transform: translate(-50%, -50%);">
              </div>
              <div class="flip-box-back">
                <h2 style="font-size: 21px; font-family: Arial; color:#ffff;">Es una herramienta digital que permite a las personas, conocer los cálculos aproximados de las prestaciones laborales, que serán consideradas dentro de la audiencia de conciliación.</h2>
              </div>
            </div>   
          </div>
            <p><a class="boton" href="https://cclmichoacan.gob.mx/Calculadora.html" target="_blank">Ver más</a></p>
        </div>    
      </div> <!-- Fin calculadora de prestaciones -->
      <div class="card"> <!-- Inicio solicitud en línea -->
        <div style="display: block; text-align: center;">
          <div class="flip-box">
            <div class="flip-box-inner">
              <div class="flip-box-front">
                <h2 style="font-size: 22px; font-family: Arial; color: #000000">Solicitud en línea<br></h2> <img
                  src="public/assets/images/SOLICITUD.png" alt="Descripción de la imagen"
                  style="width: 120px; height:120px; position: absolute; top: 60%; left: 50%; transform: translate(-50%, -50%);">
              </div>
              <div class="flip-box-back">
                  <h2 style="font-size: 21px; font-family: Arial; color:#ffffff">Es un servicio rápido, eficiente que permite a las personas, tanto trabajadoras como empleadoras iniciar su solicitud para conciliar de forma digital a través de la página: siconcilio.cclmichoacan.gob.mx.</h2>
              </div>
            </div> 
          </div> 
          <p><a class="boton" href="{{ route('solicitudEnLinea') }}">Ver más</a></p> 
        </div> 
      </div><!-- Inicio solicitud en línea -->
    </div>
  
  <!-- SEGUNDO BLOQUE DE SERVICIOS-->
    <div class="card-group">
      <div class="card">   <!-- Inicio citas de ratificación -->
        <div style="display: block; text-align: center;">
          <div class="flip-box">
            <div class="flip-box-inner">
              <div class="flip-box-front">
                <h2 style="font-size: 22px; font-family: Arial; color:#000000">Citas de ratificación de convenios</h2> <img
                  src="public/assets/images/RATIFICACION.png" alt="Descripción de la imagen"
                  style="width: 120px; height:120px; position: absolute; top: 60%; left: 50%; transform: translate(-50%, -50%);">
              </div>
              <div class="flip-box-back">
                <h2 style="font-size: 21px; font-family: Arial; color:#ffff">Es un servicio que permite a las partes, que terminan su relación laboral, acudir con previa cita ante el Centro de Concicliación Laboral a ratificar su acuerdo, con el fin de brindar seguridad jurídica.</h2>
              </div>
            </div>
          </div>
          <p><a class="boton" href="{{ route('create_cita') }}">Ver más</a></p>   
        </div>  
      </div>   <!-- fin citas de ratificación -->
      <div class="card"> <!-- Inicio registro de rep. legales -->
        <div style="display: block; text-align: center;">
          <div class="flip-box">
            <div class="flip-box-inner">
              <div class="flip-box-front">
                <h2 style="font-size: 22px; font-family: Arial; color:#000000">Registro de <br>representación legal patronal</h2> <img
                  src="public/assets/images/REGISTROPA.png" alt="Descripción de la imagen"
                  style="width: 120px; height:120px; position: absolute; top: 60%; left: 50%; transform: translate(-50%, -50%);">
              </div>
              <div class="flip-box-back">
                <h2 style="font-size: 21px; font-family: Arial; color:#ffff"><br>Es una plataforma digital, que permite a las personas empleadoras registrar a sus representantes legales, con la finalidad de agilizar el procedimiento de conciliación.</h2>
              </div>
            </div>
          </div>
          <p><a class="boton" href="{{ route('poder-crear') }}">Ver más</a></p>
        </div>    
      </div> <!-- Fin registro de rep. legales -->
      <div class="card"> <!-- Inicio cursos y capacitaciones -->
        <div style="display: block; text-align: center;">
          <div class="flip-box">
            <div class="flip-box-inner">
              <div class="flip-box-front">
                <h2 style="font-size: 22px; font-family: Arial; color:#000000">Cursos y capacitaciones<br><br></h2>
                <img src="public/assets/images/CURSOSYCA.png" alt="Descripción de la imagen"
                  style="width: 120px; height:120px; position: absolute; top: 60%; left: 50%; transform: translate(-50%, -50%);">
              </div>
              <div class="flip-box-back">
                <h2 style="font-size: 21px; font-family: Arial; color:#ffff"><br><br>&ensp;&ensp;&ensp;Ingresar a los cursos.</h2>
              </div>
            </div>
          </div>
          <p><a class="boton" href="{{ route('login') }}">Ver más</a></p> 
        </div> 
      </div><!-- Inicio  cursos y capacitaciones -->
    </div>
  
    <!-- TERCER BLOQUE DE SERVICIOS-->
    <div class="card-group">
      <div class="card"> 
        <div style="display: block; text-align: center;">
          <div class="flip-box">
            <div class="flip-box-inner">
              <div class="flip-box-front">
                <h2 style="font-size: 22px; font-family: Arial; color:#000000">Buzón electrónico</h2>
                <img src="public/assets/images/Audiencias e.png" alt="Descripción de la imagen"
                    style="width: 120px; height: 120px; position: absolute; top: 60%; left: 50%; transform: translate(-50%, -50%);">
              </div>
              <div class="flip-box-back">
                 <h2 style="font-size: 21px; font-family: Arial; color:#ffff"><br><br>Buzón Electrónico.</h2>
              </div>
            </div>
          </div>
          <p><a class="boton" href="{{ route('login') }}">Ingresa aquí</a></p>   
        </div>
      </div>
      <div class="card">   <!-- Inicio Seer -->
        <div style="display: block; text-align: center;">
          <div class="flip-box">
            <div class="flip-box-inner">
              <div class="flip-box-front">
                <h2 style="font-size: 22px; font-family: Arial; color:#000000">Seer</h2>
                <img src="public/assets/images/ccl-r.png" alt="Descripción de la imagen"
                    style="width: 180px; height: auto; position: absolute; top: 60%; left: 50%; transform: translate(-50%, -50%);">
              </div>
              <div class="flip-box-back">
                 <h2 style="font-size: 21px; font-family: Arial; color:#ffff"><br><br>Sistema Integral de Conciliación.</h2>
              </div>
            </div>
          </div>
          <p><a class="boton" href="{{ route('login') }}">Ver más</a></p>   
        </div>  
      </div>   <!-- fin Seer -->
      <div class="card" > </div>
    </div>
  </div>
  </section> 
  </main> 
      <!-- CARRUSEL-->
      <div class="owl-carousel owl-theme">
        <img src="public/assets/images/carusel/carrusel_1.png" class="d-block w-100" alt="" loading="lazy">
        <img src="public/assets/images/carusel/carrusel_2.png" class="d-block w-100" alt="" loading="lazy">
        <img src="public/assets/images/carusel/carrusel_3.png" class="d-block w-100" alt="" loading="lazy">
        <img src="public/assets/images/carusel/carrusel_4.png" class="d-block w-100" alt="" loading="lazy">
        <img src="public/assets/images/carusel/carrusel_5.png" class="d-block w-100" alt="" loading="lazy">
        <img src="public/assets/images/carusel/carrusel_6.png" class="d-block w-100" alt="" loading="lazy">
        <img src="public/assets/images/carusel/carrusel_7.png" class="d-block w-100" alt="" loading="lazy">
        <img src="public/assets/images/carusel/carrusel_8.png" class="d-block w-100" alt="" loading="lazy">
        <img src="public/assets/images/carusel/carrusel_9.png" class="d-block w-100" alt="" loading="lazy">
        
      </div>
      <!-- FIN CARRUSEL-->	
    <footer id="contacto" class="d-flex flex-wrap justify-content-between align-items-center py-3 my-4 border-top">
      <div class="col-md-4 d-flex align-items-center">
        <a href="/" class="mb-3 me-2 mb-md-0 text-body-secondary text-decoration-none lh-1">
          <svg class="bi" width="30" height="24"><use xlink:href="#bootstrap"></use></svg>
        </a>
          <span class="mb-3 mb-md-0 text-body-secondary">Teléfono de contacto:<b> (443) 688 6337</b></span>
      </div>
      <div class="col-md-4 d-flex align-items-center">
        <div class="col-md-3, mx-auto, my-auto">
          <a href="https://x.com/cclmichoacan?s=21"><img src="public/assets/images/X.png" style="width: 8%"></a>
          &ensp;<a href="https://www.instagram.com/cclmichoacan/"><img src="public/assets/images/IG.png" style="width:10%"></a>
          &ensp;<a href="https://www.facebook.com/conciliacionlaboralmich/?locale=es_LA"><img src="public/assets/images/FC.png" style="width:8%"></a>
          &ensp;<a href="https://www.tiktok.com/@cclmichoacan0?_t=ZM-8uooi2eSI1V&_r=1"><img src="public/assets/images/TK.png" style="width:8%"></a>
        </div>
      </div>
    </footer> 
    <script>
      $(document).ready(function(){
        $(".owl-carousel").owlCarousel();
      });
    
      var owl = $('.owl-carousel');
      owl.owlCarousel({
          items:1,
          loop:true,
          margin:10,
          autoplay:true,
          autoplayTimeout:5000,
          autoplayHoverPause:true
      });
    
      $('.play').on('click',function(){
          owl.trigger('play.owl.autoplay',[5000])
      })
      $('.stop').on('click',function(){
          owl.trigger('stop.owl.autoplay')
      })
    
    </script>
      <!--<script src="public/assets_seer/assets/dist/js/bootstrap.bundle.min.js"></script>-->
      <!--<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>-->
    </body>
</html>



