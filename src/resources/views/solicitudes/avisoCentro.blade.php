<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
        <meta name="generator" content="Ing. ISBM">
        <link href="public/assets/css/carousel.css" rel="stylesheet">
        <title>Si Concilio</title>
        <!-- Bootstrap core CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        
        <link rel="icon" href="public/assets/images/logo-ccl.png" type="image/x-icon">
    
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
        
    </style>   
</head>
@php     
    $direccion_sede='';
    if($delegacion->delegacion === 'Morelia'){
        $direccion_sede='BLVD. GARCÍA DE LEÓN NO. 1575, COL. CHAPULTEPEC ORIENTE, C.P. 58260 MORELIA, MICHOACÁN DE OCAMPO, con un horario de atención Lunes a Jueves de 9:00 am a 4:00 pm. Y viernes de 9:00 am a 3:00 pm.';
    }    
    if($delegacion->delegacion === 'Uruapan'){
        $direccion_sede='NUEVO PARICUTÍN NO. 308, COL. JARDINES DE SAN RAFAEL, C.P. 30136 URUAPAN, MICHOACÁN DE OCAMPO. SE ENCUENTRA DENTRO DEL RECINTÓ DONDE ESTA RENTAS DEL
            ESTADO, POR LA CLÍNICA DEL IMSS NO.76, con un horario de atención Lunes a Viernes de 9:00 am a 4:00 pm.';
    }
    if($delegacion->delegacion === 'Zamora') {
        $direccion_sede='JUSTO SIERRA PONIENTE NO. 290, COL. JARDINES DE CATEDRAL, C.P. 59600 ZAMORA, MICHOACÁN DE OCAMPO, con un horario de atención Lunes a Viernes de 9:00 am a 3:00 pm.';
    }  
    if($delegacion->delegacion === 'Zitácuaro') {
        $direccion_sede='5 DE MAYO NORTE NO. 03, PISO 3 COL. CENTRO, C.P. 61500 ZITÁCUARO, MICHOACÁN DE OCAMPO, con un horario de atención Lunes a Viernes de 9:00 am a 3:00 pm.';
    } 
    if($delegacion->delegacion === 'Lázaro Cárdenas') {
        $direccion_sede='PARACHO NO. 26, COL. 600 CASAS, C.P. 60950 LÁZARO CÁRDENAS, MICHOACÁN DE OCAMPO, con un horario de atención Lunes a Viernes de 9:00 am a 3:00 pm.';
    }  
    if($delegacion->delegacion === 'Sahuayo') {
        $direccion_sede='AV. UNIVERSIDAD SUR NO. 3000, COL. LOMAS DE UNIVERSIDAD, C.P. 59103 SAHUAYO DE MORELOS, MICHOACÁN DE OCAMPO, con un horario de atención Lunes a Viernes de 9:00 am a 3:00 pm.';
    } 
@endphp

<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
    <div class="">
        <mg src="public/assets/images/Logos 2.png" class="img" style="" width="250" height="90"></a>&nbsp;&nbsp;
    </div> 
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent" >
        <ul class="navbar-nav ml-auto">
            <li class="nav-item active">
                <a class="nav-link" href="{{ route('publico') }}" style="color: black;">INICIO<span class="sr-only"></span></a>
             </li>
        </ul>
    </div>
</nav>
<div class="container">
    <br><br><br>
</div>
    <div id="app">  
        <section class="section">
            <div class="section-body">
                <div class="row"> 
                    <div class="col-lg-12" >
                        <div class="card">
                            <div class="card-body">
                                <div class="alert alert-success alert-dismissible fade show" role="alert" style="text-align: justify;">
                                    <input type="hidden" name="id" value="{{ $id }}">
                                    <strong>¡Registro completo!</strong><br>
                                    <label>Tu solicitud fue capturada correctamente. 
                                    <br><br>  
                                        <b>POR FAVOR, ACUDE CON EL PERSONAL DEL CENTRO DE CONCILIACIÓN PARA PROSEGUIR CON LA CONFIRMACIÓN DE TU SOLICITUD</b>
                                    <br><br>
    
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <a href="{{ route('solicitudEnLineaCentro'); }}" class="btn btn-primary" style=" background-color:#CEA845;border-color:#CEA845;">Terminar</a>   
                            </div>     
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script src="./public/assets/js/estadistica/estadistica.js"></script>
    <script src="https://cdn.datatables.net/2.1.5/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.1.5/js/dataTables.bootstrap4.js"></script>
       