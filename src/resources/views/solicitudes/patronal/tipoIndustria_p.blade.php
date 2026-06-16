
<html lang="es_MX"> 
    <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
        <meta name="generator" content="Ing. ISBM">
        <link href="../public/assets/css/carousel.css" rel="stylesheet">
        <title>Si Concilio</title>
        <!-- Bootstrap core CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        <link rel="icon" href="../public/assets/images/logo-ccl.png" type="image/x-icon">

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- ================== LIBRERIAS AGREGADAS ================== -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        <!-- CSS de DataTables con Bootstrap 4 -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">

        <!-- CSS de DataTables Responsive con Bootstrap 4 -->
        <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">

        <!-- CSS de DataTables Scroller con Bootstrap 4 -->
        <link rel="stylesheet" href="https://cdn.datatables.net/scroller/2.0.0/css/scroller.bootstrap4.min.css">
        <!-- CSS de Bootstrap Datepicker -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/css/bootstrap-datepicker.min.css">

        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/css/bootstrap-datepicker.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/js/bootstrap-datepicker.min.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-timepicker/0.5.2/css/bootstrap-timepicker.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-timepicker/0.5.2/js/bootstrap-timepicker.min.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ekko-lightbox/5.3.0/ekko-lightbox.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/ekko-lightbox/5.3.0/ekko-lightbox.min.js"></script>
        <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/scroller/2.0.5/css/scroller.dataTables.min.css">
        <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/scroller/2.0.5/js/dataTables.scroller.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
        <style>.cke{visibility:hidden;}</style>

<style type="text/css">
    .d-flex {
        display: flex !important;
    }
    .div_gris {
        height: 34px;
        background-color: #D2D3D5;
    }
    *, ::after, ::before {
        box-sizing: border-box;
    }

    div {
        display: block;
        unicode-bidi: isolate;
    }
    .active {
        color: #1c1c28 !important;
    }
    body {
        height: 100%;
        letter-spacing: 0;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        margin: 0;
        font-family: "Montserrat", sans-serif;
        font-size: .75rem;
        font-weight: 400;
        line-height: 1.5;
        color: #333;
        text-align: left;
        background-color: #ffffff;
    }
    .pl-2, .px-2 {
        padding-left: 8px !important;
    }
    .pr-2, .px-2 {
        padding-right: 8px !important;
    }
    *, ::after, ::before {
        box-sizing: border-box;
    }
    .justify-content-between {
        justify-content: space-between !important;
    }
    .d-flex {
        display: flex !important
    ;
    }
    .div-info-blue {
        border-radius: 4px;
        border: solid 2px #F5C38E;
        background-color: #F5C38E;
        padding: 5px 13px 5px 10px;
        color: black;
        font-size: 11px;
    }
    *, ::after, ::before {
        box-sizing: border-box;
    }
    .default-container {
        border: solid 1px #c7c9d9;
        padding-bottom: 12px;
    }
    .pl-2, .px-2 {
        padding-left: 8px !important;
    }
    .pr-2, .px-2 {
        padding-right: 8px !important;
    }
    element.style {
        float: right;
        margin-top: 2%;
    }
    .btn:not(:disabled):not(.disabled) {
        cursor: pointer;
    }
    [type=reset], [type=submit], button, html [type=button] {
        -webkit-appearance: button;
    }
    .btn-primary {
        color: #fff;
        background-color: #CEA845;
        border-color: #CEA845;
    }
    .btn {
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
    }

    button, select {
        text-transform: none;
    }
    button, input {
        overflow: visible;
    }
    button, input, optgroup, select, textarea {
        margin: 0;
        font-family: inherit;
        font-size: inherit;
        line-height: inherit;
    }
    button {
        border-radius: 0;
    }
    .btn:not(:disabled):not(.disabled) {
        cursor: pointer;
    }
    .btn:not(:disabled):not(.disabled) {
        cursor: pointer;
    }
    [type=reset], [type=submit], button, html [type=button] {
        -webkit-appearance: button;
    }
    .modal .modal-header {
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        -webkit-box-flex: 1;
        -ms-flex: 1;
        flex: 1;
        -ms-flex-align: center;
        align-items: center;
    }
    .modal-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        padding: 15px 15px;
        border-bottom: 1px solid #A9ABAE;
        border-top-left-radius: 6px;
        border-top-right-radius: 6px;
    }
    .header-default {
        background-color: #A9ABAE;
        color: white;
        height: 38px !important;
        font-weight: bold;
    }
    .font-size-14 {
        font-size: 14px !important;
    }
    *, ::after, ::before {
        box-sizing: border-box;
    }
    .modal-content {
        position: relative;
        display: flex;
        flex-direction: column;
        width: 100%;
        pointer-events: auto;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid rgba(0, 0, 0, .2);
        border-radius: 6px;
        outline: 0;
    }
    .btn-success-custom {
        margin: 0 0 0 5px;
        font-family: Montserrat;
        font-size: 11px;
        font-weight: bold;
        font-stretch: normal;
        font-style: normal;
        line-height: normal;
        letter-spacing: normal;
        color: #05a660 !important;
        border: 2px solid #05a660 !important;
        box-shadow: 0 0 0 0 #05a660 !important;
    }
        .modal .modal-header {
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        -webkit-box-flex: 1;
        -ms-flex: 1;
        flex: 1;
        -ms-flex-align: center;
        align-items: center;
    }
    .modal-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        padding: 15px 15px;
        border-bottom: 1px solid #d5dbe0;
        border-top-left-radius: 6px;
        border-top-right-radius: 6px;
    }
    div {
        display: block;
        unicode-bidi: isolate;
    }
    .btn-primary {
        color: white !important;
        background-color: #CEA845 !important;
        border: 2px solid #CEA845 !important;
        box-shadow: 0 0 0 0 white !important;
    }
    .btn {
        display: inline-block;
        font-weight: 400;
        color: #05a660;
        text-align: center;
        vertical-align: middle;
        user-select: none;
        background-color: white;
        border: 1px solid transparent;
        padding: 7px .75rem;
        font-size: .75rem;
        line-height: 1.5;
        border-radius: 4px;
        transition: color .15s ease-in-out, background-color .15s ease-in-out, border-color .15s ease-in-out, box-shadow .15s ease-in-out;
    }
    p{
        text-align: justify;
    }
    li{
        text-align: justify;
    }
</style>

<body class="pace-top pace-done" style="">
    <div class="pace  pace-inactive">
        <div class="pace-progress" data-progress-text="100%" data-progress="99" style="transform: translate3d(100%, 0px, 0px);">
            <div class="pace-progress-inner"></div>
        </div>
        <div class="pace-activity"></div>
    </div>

<!-- inicio Modal Aviso privacidad-->
<div class="modal fade" id="modal-aviso-privacidad" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header header-default font-size-14" style="height: 38px">
                <div class="col-md-12">
                    <center><h4>DATOS PERSONALES</h4></center>
                </div>
            </div>
            <div class="modal-body">
                <div class="col-md-12 gray-small-text">
                    <p>Los Datos Personales recabados por el Centro de Conciliación Laboral del Estado de Michoacán de Ocampo, servirán únicamente para realizar el Procedimiento de Conciliación Individual Prejudicial, 
                        serán tratados conforme lo dispuesto por la Ley General de Protección de Datos Personales en Posesión de Sujetos Obligados y demás normativa aplicable.</p>
                    <p>Los datos considerados sensibles no serán recabados, ni tratados, ni se realizaran transferencias de datos personales, salvo aquellos que no requieran el consentimiento de los titulares y que 
                        sean necesarios para atender su solicitud o requerimientos de información realizados por autoridad competente, siempre y cuando se encuentren debidamente fundados y motivados; lo anterior, de 
                        conformidad con los artículos 22, 66 y 70 de la Ley General de Protección de Datos Personales en Posesión de Sujetos Obligados.</p>
                    <p>Los datos personales que sean recabados serán empleados con los fines siguientes:</p>
                    <ul>
                        <li>Registrar al usuario en la plataforma digital denominada SICONCILIO (Sistema Integral para la Conciliación), dar seguimiento y trámite a su solicitud.</li><br>
                        <li>Administrar la información y datos del solicitante para efectuar el Procedimiento de Conciliación Prejudicial obligatorio y las notificaciones que deriven del mismo.</li><br>
                        <li>Generar información estadística y de control, en la que sus datos personales serán disociados de la información estadística para que no sea posible identificar a los titulares.</li><br>
                        <li>Establecer comunicación con los trabajadores y patrones por correo electrónico, por escrito, mediante correo ordinario o por teléfono, sobre aspectos relacionados con las 
                            fases y etapas del procedimiento de conciliación individual.</li>
                    </ul>
                </div>
                <div class="col-md-12 row">
                    <div class="col-md-12">
                        <hr>
                    </div>
                    <div class="row justify-content-center mb-3">
                        <div class="col-auto">
                            <div class="form-check form-check-inline">
                                <input type="radio" id="radioAviso1" name="radioAviso" value="1" class="form-check-input pointer" data-parsley-multiple="radioAviso" 
                                        style="width: 18px; height: 18px; border: 2px solid #CEA845; accent-color: #CEA845;">
                                <label class="form-check-label pointer" for="radioAviso1" style="font-size: 13px; color:black;">Sí acepto</label>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="form-check form-check-inline">
                                <input type="radio" id="radioAviso2" name="radioAviso" value="2" class="form-check-input pointer" data-parsley-multiple="radioAviso" 
                                        style="width: 18px; height: 18px; border: 2px solid #CEA845; accent-color: #CEA845;">
                                <label class="form-check-label pointer" for="radioAviso2" style="font-size: 13px; color:black;">No acepto</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <hr>
                    </div>
                    <div class="col-md-12 text-right">
                        <button class="btn btn-primary m-l-5" onclick="aceptarAviso()"> Aceptar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- PDF Derechos y Obligaciones -->
<div class="modal fade" id="modal-derechos-obligaciones" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Derechos y obligaciones</h5>
                <!--<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>-->
            </div>
            <div class="modal-body text-center">
                <div class="text-center mb-3">
                    <div style="display: inline-block; background-color: #CEA845; padding: 10px 40px; border-radius: 4px;">
                        <button onclick="zoomIn()" class="btn btn-lg btn-secondary mx-2" style="font-size: 18px; width: 30px; height: 30px; padding: 0; line-height: 30px; text-align: center;"><b>+</b></button>
                        <button onclick="zoomOut()" class="btn btn-lg btn-secondary mx-2" style="font-size: 18px; width: 30px; height: 30px; padding: 0; line-height: 30px; text-align: center;"><b>−</b></button>
                    </div>
                </div>
                <div id="pdfContainer" style="overflow: auto; max-height: 600px;">
                    <img id="pdfImg"
                        src="{{ asset('storage/app/public/pdf/terminos_condiciones.jpg') }}" alt="PDF como imagen"
                        style="width: 100%; max-width: none; transition: width 0.2s ease;" />
                </div>
                <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" id="aceptarCheck"  style="width: 18px; height: 18px; border: 2px solid #CEA845; accent-color: #CEA845; border-radius: 4px;">
                    <label class="form-check-label" for="aceptarCheck" style="font-size: 13px; color:black; margin-left: 8px;">
                        He leído y acepto mis derechos y obligaciones del procedimiento de conciliación.
                    </label>
                    <div id="mensaje-error" class="text-danger mt-2" style="display: none;" style="font-size: 13px;">
                        Debes aceptar los derechos y obligaciones para poder continuar con el proceso de conciliación.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button id="continuarBtn" class="btn btn-success">Continuar</button>
                <button type="button" class="btn btn-secondary" onclick="cancelarProceso()">Cancelar</button>
            </div>
        </div>
    </div>
</div>


<!-- begin #page-loader -->
<div id="page-loader" class="fade show d-none"><span class="spinner"></span></div>
<!-- end #page-loader -->
<section id="loading">
    <div id="loading-content"></div>
    <div id="progress-bar" style="display: none;" class="progress progress-content">
        <div class="progress-bar" id="progressbar-ajax" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"><span id="progressbar-ajax-value"></span></div>
    </div>
</section>
<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
    <div class="">
        <img src="../public/assets/images/Logos 2.png" class="img" style="" width="250" height="90"></a><br>&nbsp;&nbsp;
    </div>
    <div class=" navbar-collapse">
         <!-- begin breadcrumb -->
        <ul class="navbar-nav ml-auto">
            <li >
                <a class="text-right float-xl-right general-folio-span text-decoration-underline" href="#" 
                onclick="$('#modal-aviso-privacidad').modal('show')"><h6><b>Aviso de privacidad</b></h6></a><br>
            </li>
        </ul>
    </div>
</nav>
<div class="container">
    <br><br><br><br><br><br><br><br>
</div>
<div id="page-container" class="page-container page-without-sidebar fade page-sidebar-fixed page-header-fixed show">
    <form id="logout-formm" action="" method="POST" style="display: none;">
        <input type="hidden" name="_token" value="0l8yhzIG7ywVSd1eE3TP6ldlOPFNrqrT78i2Ezy6">
    </form>
    <div id="content" class="content "> 
        <center><h3 style="color:black"><b>Solicitud</b></h3></center>
        <!-- end breadcrumb -->
        <hr class="red">
        <div class="panel panel-inverse">
            <div class="panel-body row">
                <div class="tab-content col-md-2 col-sm-12" id="btn_cancelar"></div>
                <div class="col-md-8 mb-3" id="solicitud_container">
                    <div class="row row-offcanvas row-offcanvas-right">
                        <!-- begin container for wizard and form -->
                        <div id="wizard" class="col-md-12"> 
                            <!-- begin form -->
                            <div class="row">
                                <div class="col-12 tab-content" id="myTabContent">
                                    <!-- Aquí va el contenido del formulario -->
                                    <div class="tab-content" id="wizardSolicitud">
                                        <div class="tab-pane fade  step-content  show active" id="stepIndustria" role="tabpanel" aria-labelledby="stepIndustria-tab">
                                            <div id="form">
                                                <div class=" ">
                                                    <div class="default-container">
                                                        <div id="div-industria" class="div_gris d-flex">
                                                            <span class="default-form-label pl-2 align-content-center"><b>Industria o servicio</b></span>
                                                        </div>
                                                        <div class="px-2">
                                                            <div class="d-flex justify-content-between mt-3 mb-2">
                                                                <label class="gray-small-text ">¿La actividad principal del patrón es...?</label>
                                                            </div>
                                                            <div class="div-info-blue d-flex justify-content-between">
                                                                <div class="">
                                                                    <i class="bi bi-info-circle-fill mr-1"></i>
                                                                </div>
                                                                <div class="ml-2">
                                                                    (Nota: antes de seleccionar una industria, es necesario que hagas clic al botón “i” para obtener más información sobre la actividad económica. <strong>Si la descripción no coincide</strong> con la actividad principal del patrón, <strong>entonces selecciona la opción Ninguna de las anteriores</strong>)
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-md-6 content-vertical-center  border-r  row">
                                                                    <div class="col-1 mr-1">
                                                                        <input type="radio" class="radio_input industria" id="Aceites y grasas vegetales1" data-nombre="Aceites y grasas vegetales" data-descripcion="
                                                                            <strong>Si la empresa o patrón(a):</strong> 
                                                                            <p>Se dedica a la producción de aceites y grasas vegetales comestibles, extraídos de las oleaginosas, principalmente de soya, canola y cártamo.</p> 
                                                                            <br>
                                                                            De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.
                                                                            Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: 
                                                                            <p><a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a></p>
                                                                            <br>
                                                                            <p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p>" name="industria" onclick="mostrarDetalleIndustria(this)" value="1">
                                                                    </div>
                                                                    <div class="col-11 row ">
                                                                        <label class="bold-gray-label pl-2" for="Aceites y grasas vegetales1">Aceites y grasas vegetales
                                                                            <a style="cursor: pointer;" class="popover_default ml-2" data-trigger="focus" data-placement="top" role="button" id="popover1" data-id="1" data-html="true" data-toggle="popover" title="" data-content="<div class=&quot;gray-small-text&quot;>
                                                                                <strong>Si la empresa o patrón(a):</strong> 
                                                                                <p>Se dedica a la producción de aceites y grasas vegetales comestibles, extraídos de las oleaginosas, principalmente de soya, canola y cártamo.</p> 
                                                                                <br>
                                                                                De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.
                                                                                Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: 
                                                                                <p><a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a></p>
                                                                                <br>
<br><p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p></div>" data-original-title="Cerrar X"><i class="bi bi-info-circle-fill -Icon-16"></i></a><br>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6 content-vertical-center  pl-md-3  row">
                                                                    <div class="col-1 mr-1">
                                                                        <input type="radio" class="radio_input industria" id="AUTOMOTRIZ3" data-nombre="Automotriz" data-descripcion="
                                                                            <strong>Si la empresa o patrón(a):</strong> 
                                                                            <p>Se dedica a la fabricación de automóviles, incluyendo autopartes mecánicas o eléctricas. En general, cualquier manufacturera de autopartes es probable que sea de competencia federal.</p> 
                                                                            <br>
                                                                            De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.
Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: <a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a><br>
<br><p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p>" name="industria" onclick="mostrarDetalleIndustria(this)" value="3">
                                                                    </div>
                                                                    <div class="col-11 row ">
                                                                        <label class="bold-gray-label pl-2" for="AUTOMOTRIZ3">Automotriz
                                                                            <a style="cursor: pointer;" class="popover_default ml-2" data-trigger="focus" data-placement="top" role="button" id="popover3" data-id="3" data-html="true" data-toggle="popover" title="" data-content="<div class=&quot;gray-small-text&quot;>
                                                                                <strong>Si la empresa o patrón(a):</strong> 
                                                                                <p>Se dedica a la fabricación de automóviles, incluyendo autopartes mecánicas o eléctricas. En general, cualquier manufacturera de autopartes es probable que sea de competencia federal.</p> 
                                                                                <br>
                                                                                De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.
Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: <a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a><br>
<br><p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p></div>" data-original-title="Cerrar X"><i class="bi bi-info-circle-fill -Icon-16"></i></a><br>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6 content-vertical-center  border-r  row">
                                                                    <div class="col-1 mr-1">
                                                                        <input type="radio" class="radio_input industria" id="AZUCARERA4" data-nombre="Azucarera" data-descripcion="
                                                                            <strong>Si la empresa o patrón(a):</strong> 
                                                                            <p>Se dedica a la producción de azúcar en ingenios azucareros.</p> 
                                                                            <br>
                                                                            De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.
Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: <a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a><br>
<br><p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p>" name="industria" onclick="mostrarDetalleIndustria(this)" value="4">
                                                                    </div>
                                                                    <div class="col-11 row ">
                                                                        <label class="bold-gray-label pl-2" for="AZUCARERA4">Azucarera
                                                                            <a style="cursor: pointer;" class="popover_default ml-2" data-trigger="focus" data-placement="top" role="button" id="popover4" data-id="4" data-html="true" data-toggle="popover" title="" data-content="<div class=&quot;gray-small-text&quot;>
                                                                                <strong>Si la empresa o patrón(a):</strong> 
                                                                                <p>Se dedica a la producción de azúcar en ingenios azucareros.</p> 
                                                                                <br>
                                                                                De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.
Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: <a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a><br>
<br><p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p></div>" data-original-title="Cerrar X"><i class="bi bi-info-circle-fill -Icon-16"></i></a><br>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6 content-vertical-center  pl-md-3  row">
                                                                    <div class="col-1 mr-1">
                                                                        <input type="radio" class="radio_input industria" id="CALERA6" data-nombre="Calera" data-descripcion="
                                                                            <strong>Si la empresa o patrón(a):</strong> 
                                                                            <p>Se dedica a la calcinación de piedra caliza para producir cal.</p> 
                                                                            <br>
                                                                            De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.
Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: <a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a><br>
<br><p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p>" name="industria" onclick="mostrarDetalleIndustria(this)" value="6">
                                                                    </div>
                                                                    <div class="col-11 row ">
                                                                        <label class="bold-gray-label pl-2" for="CALERA6">Calera
                                                                            <a style="cursor: pointer;" class="popover_default ml-2" data-trigger="focus" data-placement="top" role="button" id="popover6" data-id="6" data-html="true" data-toggle="popover" title="" data-content="<div class=&quot;gray-small-text&quot;>
                                                                                <strong>Si la empresa o patrón(a):</strong> 
                                                                                <p>Se dedica a la calcinación de piedra caliza para producir cal.</p> 
                                                                                <br>
                                                                                De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.
Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: <a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a><br>
<br><p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p></div>" data-original-title="Cerrar X"><i class="bi bi-info-circle-fill -Icon-16"></i></a><br>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6 content-vertical-center  border-r  row">
                                                                    <div class="col-1 mr-1">
                                                                        <input type="radio" class="radio_input industria" id="Celulosa y papel7" data-nombre="Celulosa y papel" data-descripcion="
                                                                            <strong>Si la empresa o patrón(a):</strong> 
                                                                            <p>Se dedica a la producción de celulosa y papel, que producen pulpa, papel, cartón y otros productos a base de celulosa.</p> 
                                                                            <br>
                                                                            De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.
Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: <a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a><br>
<br><p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p>" name="industria" onclick="mostrarDetalleIndustria(this)" value="7">
                                                                    </div>
                                                                    <div class="col-11 row ">
                                                                        <label class="bold-gray-label pl-2" for="Celulosa y papel7">Celulosa y papel
                                                                            <a style="cursor: pointer;" class="popover_default ml-2" data-trigger="focus" data-placement="top" role="button" id="popover7" data-id="7" data-html="true" data-toggle="popover" title="" data-content="<div class=&quot;gray-small-text&quot;>
                                                                                <strong>Si la empresa o patrón(a):</strong> 
                                                                                <p>Se dedica a la producción de celulosa y papel, que producen pulpa, papel, cartón y otros productos a base de celulosa.</p> 
                                                                                <br>
                                                                                De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.
Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: <a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a><br>
<br><p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p></div>" data-original-title="Cerrar X"><i class="bi bi-info-circle-fill -Icon-16"></i></a><br>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6 content-vertical-center  pl-md-3  row">
                                                                    <div class="col-1 mr-1">
                                                                        <input type="radio" class="radio_input industria" id="CEMENTERA8" data-nombre="Cementera" data-descripcion="
                                                                            <strong>Si la empresa o patrón(a):</strong> 
                                                                            <p>Se dedica a la fabricación de la mezcla de caliza y arcilla calcinada y molida, como Cemex y Holcim-Apasco.</p> 
                                                                            <br>
                                                                            De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.
Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: <a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a><br>
<br><p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p>" name="industria" onclick="mostrarDetalleIndustria(this)" value="8">
                                                                    </div>
                                                                    <div class="col-11 row ">
                                                                        <label class="bold-gray-label pl-2" for="CEMENTERA8">Cementera
                                                                            <a style="cursor: pointer;" class="popover_default ml-2" data-trigger="focus" data-placement="top" role="button" id="popover8" data-id="8" data-html="true" data-toggle="popover" title="" data-content="<div class=&quot;gray-small-text&quot;>
                                                                                <strong>Si la empresa o patrón(a):</strong> 
                                                                                <p>Se dedica a la fabricación de la mezcla de caliza y arcilla calcinada y molida, como Cemex y Holcim-Apasco.</p> 
                                                                                <br>
                                                                                De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.
Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: <a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a><br>
<br><p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p></div>" data-original-title="Cerrar X"><i class="bi bi-info-circle-fill -Icon-16"></i></a><br>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6 content-vertical-center  border-r  row">
                                                                    <div class="col-1 mr-1">
                                                                        <input type="radio" class="radio_input industria" id="CINEMATOGRÁFICA9" data-nombre="Cinematográfica" data-descripcion="
                                                                            <strong>Si la empresa o patrón(a):</strong> 
                                                                            <p>Se dedica a la producción, distribución y proyección de películas, como Cinépolis y Cinemex</p> 
                                                                            <br>
                                                                            De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.
Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: <a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a><br>
<br><p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p>" name="industria" onclick="mostrarDetalleIndustria(this)" value="9">
                                                                    </div>
                                                                    <div class="col-11 row ">
                                                                        <label class="bold-gray-label pl-2" for="CINEMATOGRÁFICA9">Cinematográfica
                                                                            <a style="cursor: pointer;" class="popover_default ml-2" data-trigger="focus" data-placement="top" role="button" id="popover9" data-id="9" data-html="true" data-toggle="popover" title="" data-content="<div class=&quot;gray-small-text&quot;>
                                                                                <strong>Si la empresa o patrón(a):</strong> 
                                                                                <p>Se dedica a la producción, distribución y proyección de películas, como Cinépolis y Cinemex</p> 
                                                                                <br>
                                                                                De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.
Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: <a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a><br>
<br><p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p></div>" data-original-title="Cerrar X"><i class="bi bi-info-circle-fill -Icon-16"></i></a><br>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6 content-vertical-center  pl-md-3  row">
                                                                    <div class="col-1 mr-1">
                                                                        <input type="radio" class="radio_input industria" id="ELABORADORA DE BEBIDAS11" data-nombre="Elaboradora de bebidas" data-descripcion="
                                                                            <strong>Si la empresa o patrón(a):</strong> 
                                                                            <p>Se dedica a la elaboración de bebidas que sean envasadas o enlatadas al alto vacío o que se destinen a ello, como Coca Cola y Jumex o que purifiquen agua para envasarla.</p> 
                                                                            <br>
                                                                            De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.
Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: <a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a><br>
<br><p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p>" name="industria" onclick="mostrarDetalleIndustria(this)" value="11">
                                                                    </div>
                                                                    <div class="col-11 row ">
                                                                        <label class="bold-gray-label pl-2" for="ELABORADORA DE BEBIDAS11">Elaboradora de bebidas
                                                                            <a style="cursor: pointer;" class="popover_default ml-2" data-trigger="focus" data-placement="top" role="button" id="popover11" data-id="11" data-html="true" data-toggle="popover" title="" data-content="<div class=&quot;gray-small-text&quot;>
                                                                                <strong>Si la empresa o patrón(a):</strong> 
                                                                                <p>Se dedica a la elaboración de bebidas que sean envasadas o enlatadas al alto vacío o que se destinen a ello, como Coca Cola y Jumex o que purifiquen agua para envasarla.</p> 
                                                                                <br>
                                                                                De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.
Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: <a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a><br>
<br><p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p></div>" data-original-title="Cerrar X"><i class="bi bi-info-circle-fill -Icon-16"></i></a><br>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6 content-vertical-center  border-r  row">
                                                                    <div class="col-1 mr-1">
                                                                        <input type="radio" class="radio_input industria" id="ELÉCTRICA12" data-nombre="Eléctrica" data-descripcion="
                                                                            <strong>Selecciona si la empresa de tu patrón es:</strong> 
                                                                            <p>Se dedicada principalmente a la generación y distribución de energía eléctrica.</p> 
                                                                            <br>
                                                                            De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.
Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: <a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a><br>
<br><p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p>" name="industria" onclick="mostrarDetalleIndustria(this)" value="12">
                                                                    </div>
                                                                    <div class="col-11 row ">
                                                                        <label class="bold-gray-label pl-2" for="ELÉCTRICA12">Eléctrica
                                                                            <a style="cursor: pointer;" class="popover_default ml-2" data-trigger="focus" data-placement="top" role="button" id="popover12" data-id="12" data-html="true" data-toggle="popover" title="" data-content="<div class=&quot;gray-small-text&quot;>
                                                                                <strong>Selecciona si la empresa de tu patrón es:</strong> 
                                                                                <p>Se dedicada principalmente a la generación y distribución de energía eléctrica.</p> 
                                                                                <br>
                                                                                De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.
Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: <a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a><br>
<br><p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p></div>" data-original-title="Cerrar X"><i class="bi bi-info-circle-fill -Icon-16"></i></a><br>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6 content-vertical-center  pl-md-3  row">
                                                                    <div class="col-1 mr-1">
                                                                        <input type="radio" class="radio_input industria" id="Empresas administradas en forma directa o descentralizada del Gobierno Federal2" data-nombre="Empresas administradas en forma directa o descentralizada del gobierno federal" data-descripcion="
                                                                            <strong>Selecciona si el patrón es:</strong>
                                                                            <p>ISSSTE, IMSS, Comisión Nacional para la Prevención y Eliminación de la Discriminación (CONAPRED), Instituto Nacional de Lenguas Indígenas, etc.</p> 
                                                                            <br>
                                                                            De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.
Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: <a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a><br>
<br><p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p>" name="industria" onclick="mostrarDetalleIndustria(this)" value="2">
                                                                    </div>
                                                                    <div class="col-11 row ">
                                                                        <label class="bold-gray-label pl-2" for="Empresas administradas en forma directa o descentralizada del Gobierno Federal2">Empresas administradas en forma directa o descentralizada del gobierno federal
                                                                            <a style="cursor: pointer;" class="popover_default ml-2" data-trigger="focus" data-placement="top" role="button" id="popover2" data-id="2" data-html="true" data-toggle="popover" title="" data-content="<div class=&quot;gray-small-text&quot;>
                                                                                <strong>Selecciona si el patrón es:</strong>
                                                                                <p>ISSSTE, IMSS, Comisión Nacional para la Prevención y Eliminación de la Discriminación (CONAPRED), Instituto Nacional de Lenguas Indígenas, etc.</p> 
                                                                                <br>
                                                                                De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.
Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: <a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a><br>
<br><p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p></div>" data-original-title="Cerrar X"><i class="bi bi-info-circle-fill -Icon-16"></i></a><br>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6 content-vertical-center  border-r  row">
                                                                    <div class="col-1 mr-1">
                                                                        <input type="radio" class="radio_input industria" id="Empresas que actúen en virtud de un contrato o concesión federal10" data-nombre="Empresas que actúen en virtud de un contrato o concesión federal" data-descripcion="
                                                                            <strong>Selecciona si el patrón es:</strong> 
                                                                            <p>Televisa, Tv Azteca, Telmex, AeroMéxico, Iberia, Air France, etc. o si es una empresa de autotransportes de mercancías y pasajeros, empresas con concesión o permiso federal, como ETN Turistar, ADO, Primera Plus y Estrella Roja</p> 
                                                                            <br>
                                                                            De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.
Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: <a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a><br>
<br><p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p>" name="industria" onclick="mostrarDetalleIndustria(this)" value="10">
                                                                    </div>
                                                                    <div class="col-11 row ">
                                                                        <label class="bold-gray-label pl-2" for="Empresas que actúen en virtud de un contrato o concesión federal10">Empresas que actúen en virtud de un contrato o concesión federal
                                                                            <a style="cursor: pointer;" class="popover_default ml-2" data-trigger="focus" data-placement="top" role="button" id="popover10" data-id="10" data-html="true" data-toggle="popover" title="" data-content="<div class=&quot;gray-small-text&quot;>
                                                                                <strong>Selecciona si el patrón es:</strong> 
                                                                                <p>Televisa, Tv Azteca, Telmex, AeroMéxico, Iberia, Air France, etc. o si es una empresa de autotransportes de mercancías y pasajeros, empresas con concesión o permiso federal, como ETN Turistar, ADO, Primera Plus y Estrella Roja</p> 
                                                                                <br>
                                                                                De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.
Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: <a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a><br>
<br><p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p></div>" data-original-title="Cerrar X"><i class="bi bi-info-circle-fill -Icon-16"></i></a><br>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6 content-vertical-center  pl-md-3  row">
                                                                    <div class="col-1 mr-1">
                                                                        <input type="radio" class="radio_input industria" id="Empresas que ejecuten trabajos en zonas federales o jurisdicción federal29" data-nombre="Empresas que ejecuten trabajos en zonas federales o jurisdicción federal" data-descripcion="
                                                                            <strong>Si la empresa o patrón(a):</strong> 
                                                                            <p>Ejecuta trabajos en zonas federales, como aguas marítimas, playas, fronteras, aeropuertos internacionales y otros..</p> 
                                                                            <br>
                                                                            De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.
Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: <a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a><br>
<br><p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p>" name="industria" onclick="mostrarDetalleIndustria(this)" value="29">
                                                                    </div>
                                                                    <div class="col-11 row ">
                                                                        <label class="bold-gray-label pl-2" for="Empresas que ejecuten trabajos en zonas federales o jurisdicción federal29">Empresas que ejecuten trabajos en zonas federales o jurisdicción federal
                                                                            <a style="cursor: pointer;" class="popover_default ml-2" data-trigger="focus" data-placement="top" role="button" id="popover29" data-id="29" data-html="true" data-toggle="popover" title="" data-content="<div class=&quot;gray-small-text&quot;>
                                                                                <strong>Si la empresa o patrón(a):</strong> 
                                                                                <p>Ejecuta trabajos en zonas federales, como aguas marítimas, playas, fronteras, aeropuertos internacionales y otros..</p> 
                                                                                <br>
                                                                                De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.
Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: <a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a><br>
<br><p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p></div>" data-original-title="Cerrar X"><i class="bi bi-info-circle-fill -Icon-16"></i></a><br>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6 content-vertical-center  border-r  row">
                                                                    <div class="col-1 mr-1">
                                                                        <input type="radio" class="radio_input industria" id="FERROCARRILERA13" data-nombre="Ferrocarrilera" data-descripcion="
                                                                            <strong>Selecciona si el patrón es:</strong> 
                                                                            <p>Alguna empresa ferrocarrilera, dedicadasa la industria ferroviaria, incluyendo las actividades de infraestructura, material rodante, señalización, control de tráfico, etc.</p> 
                                                                            <br>
                                                                            De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.
Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: <a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a><br>
<br><p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p>" name="industria" onclick="mostrarDetalleIndustria(this)" value="13">
                                                                    </div>
                                                                    <div class="col-11 row ">
                                                                        <label class="bold-gray-label pl-2" for="FERROCARRILERA13">Ferrocarrilera
                                                                            <a style="cursor: pointer;" class="popover_default ml-2" data-trigger="focus" data-placement="top" role="button" id="popover13" data-id="13" data-html="true" data-toggle="popover" title="" data-content="<div class=&quot;gray-small-text&quot;>
                                                                                <strong>Selecciona si el patrón es:</strong> 
                                                                                <p>Alguna empresa ferrocarrilera, dedicadasa la industria ferroviaria, incluyendo las actividades de infraestructura, material rodante, señalización, control de tráfico, etc.</p> 
                                                                                <br>
                                                                                De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.
Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: <a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a><br>
<br><p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p></div>" data-original-title="Cerrar X"><i class="bi bi-info-circle-fill -Icon-16"></i></a><br>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6 content-vertical-center  pl-md-3  row">
                                                                    <div class="col-1 mr-1">
                                                                        <input type="radio" class="radio_input industria" id="HIDROCARBUROS14" data-nombre="Hidrocarburos" data-descripcion="
                                                                            <strong>Si la empresa o patrón(a):</strong> 
                                                                            <p>Produce gasolina, diésel y gas por medio de la extracción en pozos de explotación, plataformas marinas y refinerías como Pemex.</p> 
                                                                            <br>
                                                                            De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.
Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: <a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a><br>
<br><p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p>" name="industria" onclick="mostrarDetalleIndustria(this)" value="14">
                                                                    </div>
                                                                    <div class="col-11 row ">
                                                                        <label class="bold-gray-label pl-2" for="HIDROCARBUROS14">Hidrocarburos
                                                                            <a style="cursor: pointer;" class="popover_default ml-2" data-trigger="focus" data-placement="top" role="button" id="popover14" data-id="14" data-html="true" data-toggle="popover" title="" data-content="<div class=&quot;gray-small-text&quot;>
                                                                                <strong>Si la empresa o patrón(a):</strong> 
                                                                                <p>Produce gasolina, diésel y gas por medio de la extracción en pozos de explotación, plataformas marinas y refinerías como Pemex.</p> 
                                                                                <br>
                                                                                De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.
Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: <a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a><br>
<br><p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p></div>" data-original-title="Cerrar X"><i class="bi bi-info-circle-fill -Icon-16"></i></a><br>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6 content-vertical-center  border-r  row">
                                                                    <div class="col-1 mr-1">
                                                                        <input type="radio" class="radio_input industria" id="HULERA15" data-nombre="Hulera" data-descripcion="
                                                                            <strong>Si la empresa o patrón(a):</strong> 
                                                                            <p>Se dedica al cultivo de la planta de guayule y a la extracción del hule de la planta y a la fabricación de llantas.</p> 
                                                                            <br>
                                                                            De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.
Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: <a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a><br>
<br><p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p>" name="industria" onclick="mostrarDetalleIndustria(this)" value="15">
                                                                    </div>
                                                                    <div class="col-11 row ">
                                                                        <label class="bold-gray-label pl-2" for="HULERA15">Hulera
                                                                            <a style="cursor: pointer;" class="popover_default ml-2" data-trigger="focus" data-placement="top" role="button" id="popover15" data-id="15" data-html="true" data-toggle="popover" title="" data-content="<div class=&quot;gray-small-text&quot;>
                                                                                <strong>Si la empresa o patrón(a):</strong> 
                                                                                <p>Se dedica al cultivo de la planta de guayule y a la extracción del hule de la planta y a la fabricación de llantas.</p> 
                                                                                <br>
                                                                                De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.
Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: <a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a><br>
<br><p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p></div>" data-original-title="Cerrar X"><i class="bi bi-info-circle-fill -Icon-16"></i></a><br>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6 content-vertical-center  pl-md-3  row">
                                                                    <div class="col-1 mr-1">
                                                                        <input type="radio" class="radio_input industria" id="Maderera17" data-nombre="Maderera" data-descripcion="
                                                                            <strong>Si la empresa o patrón(a):</strong> 
                                                                            <p>Se dedica a la madera básica, que comprende la explotación, extracción, corte y procesado de las maderas, para la producción de aserradero y la fabricación de triplay o aglutinados de madera.</p> 
                                                                            <br>
                                                                            De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.
Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: <a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a><br>
<br><p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p>" name="industria" onclick="mostrarDetalleIndustria(this)" value="17">
                                                                    </div>
                                                                    <div class="col-11 row ">
                                                                        <label class="bold-gray-label pl-2" for="Maderera17">Maderera
                                                                            <a style="cursor: pointer;" class="popover_default ml-2" data-trigger="focus" data-placement="top" role="button" id="popover17" data-id="17" data-html="true" data-toggle="popover" title="" data-content="<div class=&quot;gray-small-text&quot;>
                                                                                <strong>Si la empresa o patrón(a):</strong> 
                                                                                <p>Se dedica a la madera básica, que comprende la explotación, extracción, corte y procesado de las maderas, para la producción de aserradero y la fabricación de triplay o aglutinados de madera.</p> 
                                                                                <br>
                                                                                De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.
Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: <a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a><br>
<br><p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p></div>" data-original-title="Cerrar X"><i class="bi bi-info-circle-fill -Icon-16"></i></a><br>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6 content-vertical-center  border-r  row">
                                                                    <div class="col-1 mr-1">
                                                                        <input type="radio" class="radio_input industria" id="Metalúrgica y siderúrgica18" data-nombre="Metalúrgica y siderúrgica" data-descripcion="
                                                                            <strong>Si la empresa o patrón(a):</strong> 
                                                                            <p>Se dedica a la explotación de los minerales básicos, el beneficio y la fundición de los mismos, así como la obtención de hierro metálico y acero a todas sus formas y ligas y los productos laminados de los mismos, son de competencia federal.</p> 
                                                                            <br>
                                                                            De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.
Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: <a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a><br>
<br><p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p>" name="industria" onclick="mostrarDetalleIndustria(this)" value="18">
                                                                    </div>
                                                                    <div class="col-11 row ">
                                                                        <label class="bold-gray-label pl-2" for="Metalúrgica y siderúrgica18">Metalúrgica y siderúrgica
                                                                            <a style="cursor: pointer;" class="popover_default ml-2" data-trigger="focus" data-placement="top" role="button" id="popover18" data-id="18" data-html="true" data-toggle="popover" title="" data-content="<div class=&quot;gray-small-text&quot;>
                                                                                <strong>Si la empresa o patrón(a):</strong> 
                                                                                <p>Se dedica a la explotación de los minerales básicos, el beneficio y la fundición de los mismos, así como la obtención de hierro metálico y acero a todas sus formas y ligas y los productos laminados de los mismos, son de competencia federal.</p> 
                                                                                <br>
                                                                                De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.
Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: <a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a><br>
<br><p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p></div>" data-original-title="Cerrar X"><i class="bi bi-info-circle-fill -Icon-16"></i></a><br>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6 content-vertical-center  pl-md-3  row">
                                                                    <div class="col-1 mr-1">
                                                                        <input type="radio" class="radio_input industria" id="MINERA19" data-nombre="Minera" data-descripcion="
                                                                            <strong>Si la empresa o patrón(a):</strong> 
                                                                            <p>Se dedica a la explotación de minerales metálicos y no metálicos, así como a la extracción de gas y petróleo en minas, canteras y bancos de materiales; así como operaciones en pozos.</p> 
                                                                            <br>
                                                                            De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.
Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: <a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a><br>
<br><p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p>" name="industria" onclick="mostrarDetalleIndustria(this)" value="19">
                                                                    </div>
                                                                    <div class="col-11 row ">
                                                                        <label class="bold-gray-label pl-2" for="MINERA19">Minera
                                                                            <a style="cursor: pointer;" class="popover_default ml-2" data-trigger="focus" data-placement="top" role="button" id="popover19" data-id="19" data-html="true" data-toggle="popover" title="" data-content="<div class=&quot;gray-small-text&quot;>
                                                                                <strong>Si la empresa o patrón(a):</strong> 
                                                                                <p>Se dedica a la explotación de minerales metálicos y no metálicos, así como a la extracción de gas y petróleo en minas, canteras y bancos de materiales; así como operaciones en pozos.</p> 
                                                                                <br>
                                                                                De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.
Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: <a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a><br>
<br><p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p></div>" data-original-title="Cerrar X"><i class="bi bi-info-circle-fill -Icon-16"></i></a><br>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6 content-vertical-center  border-r  row">
                                                                    <div class="col-1 mr-1">
                                                                        <input type="radio" class="radio_input industria" id="PETROQUÍMICA20" data-nombre="Petroquímica" data-descripcion="
                                                                            <strong>Si la empresa o patrón(a):</strong> 
                                                                            <p>Se dedica a la extracción de combustibles fósiles para su transformación del gas natural y los derivados del petróleo en materias primas.</p> 
                                                                            <br>
                                                                            De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.
Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: <a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a><br>
<br><p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p>" name="industria" onclick="mostrarDetalleIndustria(this)" value="20">
                                                                    </div>
                                                                    <div class="col-11 row ">
                                                                        <label class="bold-gray-label pl-2" for="PETROQUÍMICA20">Petroquímica
                                                                            <a style="cursor: pointer;" class="popover_default ml-2" data-trigger="focus" data-placement="top" role="button" id="popover20" data-id="20" data-html="true" data-toggle="popover" title="" data-content="<div class=&quot;gray-small-text&quot;>
                                                                                <strong>Si la empresa o patrón(a):</strong> 
                                                                                <p>Se dedica a la extracción de combustibles fósiles para su transformación del gas natural y los derivados del petróleo en materias primas.</p> 
                                                                                <br>
                                                                                De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.
Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: <a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a><br>
<br><p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p></div>" data-original-title="Cerrar X"><i class="bi bi-info-circle-fill -Icon-16"></i></a><br>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6 content-vertical-center  pl-md-3  row">
                                                                    <div class="col-1 mr-1">
                                                                        <input type="radio" class="radio_input industria" id="PRODUCTORA DE ALIMENTOS21" data-nombre="Productora de alimentos" data-descripcion="
                                                                            <strong>Si la empresa o patrón(a):</strong> 
                                                                            <p>Se dedica a la producción de alimentos, abarcando exclusivamente la fabricación de los que sean empacados, enlatados o envasados al alto vacío o que se destinen a ello.</p> 
                                                                            <br>
                                                                            De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.
Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: <a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a><br>
<br><p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p>" name="industria" onclick="mostrarDetalleIndustria(this)" value="21">
                                                                    </div>
                                                                    <div class="col-11 row ">
                                                                        <label class="bold-gray-label pl-2" for="PRODUCTORA DE ALIMENTOS21">Productora de alimentos
                                                                            <a style="cursor: pointer;" class="popover_default ml-2" data-trigger="focus" data-placement="top" role="button" id="popover21" data-id="21" data-html="true" data-toggle="popover" title="" data-content="<div class=&quot;gray-small-text&quot;>
                                                                                <strong>Si la empresa o patrón(a):</strong> 
                                                                                <p>Se dedica a la producción de alimentos, abarcando exclusivamente la fabricación de los que sean empacados, enlatados o envasados al alto vacío o que se destinen a ello.</p> 
                                                                                <br>
                                                                                De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.
Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: <a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a><br>
<br><p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p></div>" data-original-title="Cerrar X"><i class="bi bi-info-circle-fill -Icon-16"></i></a><br>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6 content-vertical-center  border-r  row">
                                                                    <div class="col-1 mr-1">
                                                                        <input type="radio" class="radio_input industria" id="QUÍMICA22" data-nombre="Química" data-descripcion="
                                                                            <strong>Si la empresa o patrón(a):</strong> 
                                                                            <p>Se dedica a la fabricación de productos químicos, incluyendo la química farmacéutica y medicamentos.</p> 
                                                                            <br>
                                                                            De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.
Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: <a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a><br>
<br><p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p>" name="industria" onclick="mostrarDetalleIndustria(this)" value="22">
                                                                    </div>
                                                                    <div class="col-11 row ">
                                                                        <label class="bold-gray-label pl-2" for="QUÍMICA22">Química
                                                                            <a style="cursor: pointer;" class="popover_default ml-2" data-trigger="focus" data-placement="top" role="button" id="popover22" data-id="22" data-html="true" data-toggle="popover" title="" data-content="<div class=&quot;gray-small-text&quot;>
                                                                                <strong>Si la empresa o patrón(a):</strong> 
                                                                                <p>Se dedica a la fabricación de productos químicos, incluyendo la química farmacéutica y medicamentos.</p> 
                                                                                <br>
                                                                                De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.
Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: <a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a><br>
<br><p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p></div>" data-original-title="Cerrar X"><i class="bi bi-info-circle-fill -Icon-16"></i></a><br>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6 content-vertical-center  pl-md-3  row">
                                                                    <div class="col-1 mr-1">
                                                                        <input type="radio" class="radio_input industria" id="Servicios de banca y crédito5" data-nombre="Servicios de banca y crédito" data-descripcion="
                                                                            <strong>Si la empresa o patrón(a):</strong> 
                                                                            <p>Se dedica a la Banca comercial, que ofrecen productos financieros como tarjetas bancarias, créditos bancarios, servicios de cuentas bancarias, créditos prendarios (como Nacional Monte de Piedad) etc.</p> 
                                                                            <br>
                                                                            De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.
Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: <a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a><br>
<br><p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p>" name="industria" onclick="mostrarDetalleIndustria(this)" value="5">
                                                                    </div>
                                                                    <div class="col-11 row ">
                                                                        <label class="bold-gray-label pl-2" for="Servicios de banca y crédito5">Servicios de banca y crédito
                                                                            <a style="cursor: pointer;" class="popover_default ml-2" data-trigger="focus" data-placement="top" role="button" id="popover5" data-id="5" data-html="true" data-toggle="popover" title="" data-content="<div class=&quot;gray-small-text&quot;>
                                                                                <strong>Si la empresa o patrón(a):</strong> 
                                                                                <p>Se dedica a la Banca comercial, que ofrecen productos financieros como tarjetas bancarias, créditos bancarios, servicios de cuentas bancarias, créditos prendarios (como Nacional Monte de Piedad) etc.</p> 
                                                                                <br>
                                                                                De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.
Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: <a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a><br>
<br><p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p></div>" data-original-title="Cerrar X"><i class="bi bi-info-circle-fill -Icon-16"></i></a><br>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6 content-vertical-center  border-r  row">
                                                                    <div class="col-1 mr-1">
                                                                        <input type="radio" class="radio_input industria" id="TABACALERA25" data-nombre="Tabacalera" data-descripcion="
                                                                            <strong>Si la empresa o patrón(a):</strong> 
                                                                            <p>Comprenden el beneficio o fabricación de productos de tabaco como Philip Morris o British American Tobacco México.</p> 
                                                                            <br>
                                                                            De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.
Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: <a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a><br>
<br><p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p>" name="industria" onclick="mostrarDetalleIndustria(this)" value="25">
                                                                    </div>
                                                                    <div class="col-11 row ">
                                                                        <label class="bold-gray-label pl-2" for="TABACALERA25">Tabacalera
                                                                            <a style="cursor: pointer;" class="popover_default ml-2" data-trigger="focus" data-placement="top" role="button" id="popover25" data-id="25" data-html="true" data-toggle="popover" title="" data-content="<div class=&quot;gray-small-text&quot;>
                                                                                <strong>Si la empresa o patrón(a):</strong> 
                                                                                <p>Comprenden el beneficio o fabricación de productos de tabaco como Philip Morris o British American Tobacco México.</p> 
                                                                                <br>
                                                                                De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.
Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: <a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a><br>
<br><p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p></div>" data-original-title="Cerrar X"><i class="bi bi-info-circle-fill -Icon-16"></i></a><br>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6 content-vertical-center  pl-md-3  row">
                                                                    <div class="col-1 mr-1">
                                                                        <input type="radio" class="radio_input industria" id="TEXTIL26" data-nombre="Textil" data-descripcion="
                                                                            <strong>Si la empresa o patrón(a):</strong> 
                                                                            <p>Se dedica a la fabricación de hilo y tela son de competencia federal.</p> 
                                                                            <br>
                                                                            De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.
Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: <a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a><br>
<br><p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p>" name="industria" onclick="mostrarDetalleIndustria(this)" value="26">
                                                                    </div>
                                                                    <div class="col-11 row ">
                                                                        <label class="bold-gray-label pl-2" for="TEXTIL26">Textil
                                                                            <a style="cursor: pointer;" class="popover_default ml-2" data-trigger="focus" data-placement="top" role="button" id="popover26" data-id="26" data-html="true" data-toggle="popover" title="" data-content="<div class=&quot;gray-small-text&quot;>
                                                                                <strong>Si la empresa o patrón(a):</strong> 
                                                                                <p>Se dedica a la fabricación de hilo y tela son de competencia federal.</p> 
                                                                                <br>
                                                                                De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.
Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: <a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a><br>
<br><p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p></div>" data-original-title="Cerrar X"><i class="bi bi-info-circle-fill -Icon-16"></i></a><br>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6 content-vertical-center  border-r  row">
                                                                    <div class="col-1 mr-1">
                                                                        <input type="radio" class="radio_input industria" id="VIDRIERA27" data-nombre="Vidriera" data-descripcion="
                                                                            <strong>Si la empresa o patrón(a):</strong> 
                                                                            <p>Se dedica a la fabricación de vidrio plano, liso o labrado o envases de vidrio, como Vitro.</p> 
                                                                            <br>
                                                                            De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.
Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: <a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a><br>
<br><p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p>" name="industria" onclick="mostrarDetalleIndustria(this)" value="27">
                                                                    </div>
                                                                    <div class="col-11 row ">
                                                                        <label class="bold-gray-label pl-2" for="VIDRIERA27">Vidriera
                                                                            <a style="cursor: pointer;" class="popover_default ml-2" data-trigger="focus" data-placement="top" role="button" id="popover27" data-id="27" data-html="true" data-toggle="popover" title="" data-content="<div class=&quot;gray-small-text&quot;>
                                                                                <strong>Si la empresa o patrón(a):</strong> 
                                                                                <p>Se dedica a la fabricación de vidrio plano, liso o labrado o envases de vidrio, como Vitro.</p> 
                                                                                <br>
                                                                                De conformidad con el artículo 527 de la Ley Federal del Trabajo, la industria o servicio que seleccionaste es de Competencia Federal.
Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: <a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a><br>
<br><p>En caso contrario selecciona la opción &quot;Ninguna de las anteriores&quot;.</p></div>" data-original-title="Cerrar X"><i class="bi bi-info-circle-fill -Icon-16"></i></a><br>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6 content-vertical-center  pl-md-3  row">
                                                                    <div class="col-1 mr-1">
                                                                        <input type="radio" class="radio_input industria" id="Ningunadelasanteriores28" data-nombre="Ninguna de las anteriores" data-descripcion="" name="industria" value="28">
                                                                    </div>
                                                                    <div class="col-11 row ">
                                                                        <label for="Ningunadelasanteriores28" class="bold-gray-label pl-2">Ninguna de las anteriores</label>
                                                                    </div>
                                                                </div>  
                                                            </div> 
                                                        </div>
                                                    </div> 
                                                </div>
                                            </div>
                                            <div>
                                                <button type="button" class="btn btn-primary" onclick="validarIndustria()" style="float: right; margin-top: 2%;"> Validar y Continuar <i class="fa fa-arrow-right"></i></button>
                                                <button type="button" onclick="window.location.href='{{ route('solicitud') }}'" class="btn" style="float: right; margin-top: 2%;">Cancelar Solicitud</button>
                                            </div>
                                        </div>
                                    </div> 
                                </div>
                            </div>                                   
                        </div>
                    </div>
                </div>        
            </div>
        </div>
    </div>
</div><!-- Fin principal-->
<!-- inicio Modal Competencia Federal-->
<div class="modal" id="modal-competencia" data-backdrop="static" data-keyboard="false" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header header-default font-size-14" style="height: 38px">
                <div class="col-md-12">
                    <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true"> <i class="bi bi-x-circle-fill Iconclose-circle-fill ml-2 btn-close cursor-pointer" data-dismiss="modal" aria-label="Close"></i></button>
                    <span>Advertencia - competencia federal</span>
                </div>
            </div>
            <div class="modal-body">
                <div class="col-md-12 gray-small-text ">
                    <p><strong>La industria o servicio que seleccionaste es de competencia federal, no local.</strong></p>
                    <p>Acude a la Oficina Estatal del Centro Federal de Conciliación y Registro Laboral (CFCRL) de tu entidad para realizar la solicitud.: <a href='https://www.gob.mx/cfcrl/articulos/conciliacion-laboral'>O da clic en el siguiente enlace</a><br></p>
                    <p>
                        Si no tienes la posibilidad de realizar a tiempo tu solicitud en el CFCRL, puedes continuar la solicitud en el Centro de Conciliación Local y al momento de confirmar tu solicitud, esta será revisada por un funcionario, quien determinará la corrección de la industria o servicio, o la emisión de una constancia de incompetencia y el envío de tu solicitud al CFRL.</p>
                </div>
                <div class="mt-3">
                    <button type="button" class="btn btn-success-custom" style="float: right;" data-dismiss="modal" onclick="sendIndustria()"> <i class="bi bi-check-lg iconedit-fill"></i> Entendido </button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal para mostrar los detalles de la industria-->
<div class="modal" id="modal-industria-detalle" data-backdrop="static" data-keyboard="false" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header header-default font-size-14">
                <div class="col">
                    <span id="nombre_industria"></span>
                </div>
            </div>
            <div class="modal-body">
                <div class="col-md-12 gray-small-text ">
                    <p id="detalle_industria"></p>
                </div>
                <div class="mt-3">
                    <button type="button" class="btn btn-success-custom" style="float: right;" data-bs-dismiss="modal"> <i class="bi bi-check-lg iconedit-fill"></i> Entendido </button>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    //Para realizar zoom en el modal con la imagen
    let zoomLevel = 100; // porcentaje inicial
    function zoomIn() {
        if (zoomLevel < 300) { 
            zoomLevel += 10;
            document.getElementById("pdfImg").style.width = zoomLevel + "%";
        }
    }
    function zoomOut() {
        if (zoomLevel > 50) { 
            zoomLevel -= 10;
            document.getElementById("pdfImg").style.width = zoomLevel + "%";
        }
    }

    /*para mostrar los detalles de la industria*/
    document.addEventListener('DOMContentLoaded', function () {
        $('[data-toggle="popover"]').popover();
        window.mostrarDetalleIndustria = function (element) {
            const nombre = element.getAttribute('data-nombre');
            const descripcionHTML = element.getAttribute('data-descripcion');

            document.getElementById('nombre_industria').innerText = nombre;
            document.getElementById('detalle_industria').innerHTML = descripcionHTML;
            $('#modal-industria-detalle').modal('show');
        };
    });

    /* Aviso de privacidad*/
    $(document).ready(function () {
        $('#modal-aviso-privacidad').modal('show');
    });

    function aceptarAviso() {
        const aceptado = document.getElementById('radioAviso1').checked;
        if (!aceptado) {
            alert("Debes aceptar el aviso para poder continuar.");
            return;
        }
        $('#modal-aviso-privacidad').modal('hide'); 
        
        setTimeout(function () {
            $('#modal-derechos-obligaciones').modal('show');
        }, 500); 
    }

    /* Derechos y obligaciones */
    document.addEventListener('DOMContentLoaded', function () {
        const check = document.getElementById('aceptarCheck');
        const continuarBtn = document.getElementById('continuarBtn');
        const mensajeError = document.getElementById('mensaje-error');

        continuarBtn.addEventListener('click', function () {
            if (check.checked) {
                mensajeError.style.display = 'none';
                const modal = bootstrap.Modal.getInstance(document.getElementById('modal-derechos-obligaciones'));
                modal.hide();
            } else {
                mensajeError.style.display = 'block';
            }
        });
        check.addEventListener('change', function () {
            if (this.checked) {
                mensajeError.style.display = 'none';
            }
        });
        window.cancelarProceso = function () {
            window.location.href = "{{ route('publico') }}";
        };
        let zoomLevel = 1;
    function zoomIn() {
        zoomLevel += 0.1;
        document.getElementById('pdfImg').style.transform = `scale(${zoomLevel})`;
    }

    function zoomOut() {
        zoomLevel = Math.max(0.5, zoomLevel - 0.1);
        document.getElementById('pdfImg').style.transform = `scale(${zoomLevel})`;
    }
    });

    /*Competencia Federal*/
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
            $('#modal-competencia').modal('show');
            return;
        }else{
            window.location.href = "{{ route('solicitud_patron', [$tipo_solicitud]) }}";
        }
    }
    function sendIndustria() {
        window.location.href = "{{ route('solicitud_patron', [$tipo_solicitud]) }}";
    }
</script>
</body></html>