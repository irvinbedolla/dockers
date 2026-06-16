<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>Si concilio</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <!-- Bootstrap 4.1.1 -->
    <link href="public/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
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
    </style>
    @livewireStyles

    @yield('page_css')
    <link rel="stylesheet" href="public/assets/css/components.css">
    @yield('page_css')
</head>
<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top" style="background-color:#f8f9fa;">
    <div class="">
        <img src="public/assets/images/Logos 2.png" class="img" width="260" height="90">
    </div> 
</nav>
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
                                        {{ (session()->get('success')) }}
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                @endif

                                <!--Se realiza la validación de campos para ver si dejó alguno vacío-->
                                @if ($errors->any())
                                    <div class="alert alert-dark alert-dismissible fade show" role="alert">
                                        <strong>¡Revise los campos!</strong>
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                                <!--<span class="badge badge-danger">{{ $error }}</span>-->
                                            @endforeach
                                        </ul>
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                @endif
                                <div style="background-color:#D2D3D5">
                                    <h3 class="text-center" style="color:black">Registro de Representación Patronal/Legal(REPLE MICH)</h3>
                                </div>    
                                <br><br>
                                <div class="col-xs-12 col-sm-12 col-md-12">
                                    <div class="form-group">
                                        <h5 class="text-center">Bienvenido al Registro de Representación Patronal y Legal del Centro de Conciliación Laboral del 
                                            Estado de Michoacán de Ocampo, el cual tiene como objetivo agilizar el proceso de conciliación prejudicial dentro de las 
                                            audiencias de conciliación, así como para las ratificaciones de convenio. </h5><br><br>
                                            Antes de iniciar el registro, asegúrate de contar con los siguientes requisitos: <br><br>
                                            &nbsp;&nbsp;&nbsp;&nbsp;1)Poder Notarial/Acta Constitutiva.<span style="color:red;">* </span><br>
                                            &nbsp;&nbsp;&nbsp;&nbsp;2)Documento que acredite personalidad y representación del solicitante (poder general para pleitos y cobranzas).<span style="color:red;">* </span><br>
                                            &nbsp;&nbsp;&nbsp;&nbsp;3)Identificación Oficial Vigente del representante legal (INE, pasaporte, cédula profesional).<span style="color:red;">* </span><br>
                                            &nbsp;&nbsp;&nbsp;&nbsp;4)Datos de la Empresa como: Registro Federal de Contribuyentes (RFC) de la empresa o patrón, razón social y domicilio.<br>
                                            &nbsp;&nbsp;&nbsp;&nbsp;5)Datos de Contacto: Correo electrónico y número telefónico.<br>
                                            Será indispensable, que los requisitos con <span style="color:red;">*</span>, se tengan en documento PDF, no mayor a 10 MB.
                                        <br><br>
                                    </div>
                                </div>
                                <!--Se realiza el envío de datos con formulario de Laravel Collective-->
                                <form class="needs-validation novalidate" method="POST" action="{{route('poderes.publico')}}" enctype="multipart/form-data">
                                    @csrf

                                    @if (session('success'))
                                        <div class="alert alert-success">
                                            {{ session('success') }}
                                        </div>
                                    @endif

                                    @if (session('error'))
                                        <div class="alert alert-danger">
                                            {{ session('error') }}
                                        </div>
                                    @endif

                                    @if ($errors->any())
                                        <div class="alert alert-danger">
                                            <strong>Corrige lo siguiente:</strong>
                                            <ul class="mb-0">
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    <div class="col-xs-12 col-sm-12 col-md-12">
                                        <div class="form-group">
                                            <h4 class="text-center" style="color:#CEA845">Iniciar Registro</h4>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-xs-12 col-sm-12 col-md-4">
                                            <div class="form-group">
                                                <label for="name">Tipo de persona <span style="color:red;">(*)</span></label>
                                                <select name="tipoPersona" id="tipo_persona" class="form-control" required>
                                                    <option value="">Seleccione</option>
                                                    <option value="Fisica">Física</option>
                                                    <option value="Moral">Moral</option>
                                                </select>
                                                <div class="invalid-feedback">
                                                    El tipo de persona es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-2"><br>
                                            <a href="{{ route('publico'); }}" class="btn btn-primary" style=" background-color:#CEA845; border-color: #CEA845">Regresar</a>    
                                        </div>
                                    </div>

                                    <div class="col-xs-12 col-sm-12 col-md-12" id="persona_fisica" style="display:none;">
                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                            <div class="form-group">
                                                <h4 class="text-center" style="color:#CEA845">Información Patronal</h4>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                            <div class="form-group">
                                                <h5 class="text-center">Datos de identificación</h5>
                                            </div>
                                        </div>
                                        <div class="row">
                                                <div class="col-xs-12 col-sm-12 col-md-4">
                                                    <div class="form-group">
                                                        <label for="name">Nombre(s) del Empleador(a) <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="nombre_pF" id="nombre_pF" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                                        <div class="invalid-feedback">
                                                            El nombre es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-4">
                                                    <div class="form-group">
                                                        <label for="name">Primer apellido <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="primero_PF" id="primero_PF" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                                        <div class="invalid-feedback">
                                                            El primer apellido es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-4">
                                                    <div class="form-group">
                                                        <label for="name">Segundo apellido <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="segundo_Pf" id="segundo_Pf" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                                        <div class="invalid-feedback">
                                                            El segundo apellido es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                 <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="">CURP <span style="color:red;">(*)</span></label>
                                                        <input type="text" class="form-control" aria-label="CURP" name="curp_PF" id="curp_PF" minlength="18" maxlength="18" oninput="this.value = this.value.toUpperCase()" >
                                                        <div class="invalid-feedback">
                                                            La CURP es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="name">RFC <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="RFC_pF" id="RFC_pF" class="form-control" minlength="13" maxlength="13" oninput="this.value = this.value.toUpperCase()" > 
                                                        <div class="invalid-feedback">
                                                            El nombre es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="name">Sexo <span style="color:red;">(*)</span></label>
                                                        <select name="sexo_pf" id="sexo_pf" class="form-control">
                                                            <option value="">Seleccione</option>
                                                            <option value="Femenino">Femenino</option>
                                                            <option value="Masculino">Masculino</option>
                                                            <option value="Prefiero no responder">Prefiero no responder</option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El tipo de persona es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-9">
                                                    <div class="form-group">
                                                        <label for="name">Giro Comercial <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="giro_pF" id="giro_pF" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                                        <div class="invalid-feedback">
                                                            El nombre es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                   <div class="form-group">
                                                        <h5 class="text-center">Datos de contacto</h5>
                                                    </div>
                                                </div> 

                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="">Correo electrónico <span style="color:red;">(*)</span></label>
                                                        <input type="email" class="form-control"  name="correo_pF" id="electrónico_pF" >
                                                        <div class="invalid-feedback">
                                                            El Correo electrónico es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="">Teléfono <span style="color:red;">(*)</span></label>
                                                            <input type="text" class="form-control"  name="telefono_PF" id="telefono_PF" maxlength="10" pattern="[0-9]+" >
                                                        <div class="invalid-feedback">
                                                            El telefono es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <h5 class="text-center">Domicilio fiscal</h5>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="password">Entidad Federativa <span style="color:red;">(*)</span></label>
                                                        <select id="estado_pF" class="form-control" name="estado_pF" placeholder="*Entidad Federativa" >
                                                            <option value="">Seleccione</option>
                                                            @foreach($estados as $est)
                                                                <option value="{{$est['id']}}">{{$est['nombre']}}</option>
                                                            @endforeach
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El campo Estado es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="name">Nombre del Municipio o Alcaldía <span style="color:red;">(*)</span></label>
                                                        <select id="municipio_pF" class="form-control" name="municipio_pF" placeholder="*Municipio" >
                                                            <option value="">Seleccione</option>
                                                            @foreach($municipios as $mun)
                                                                <option value="{{$mun['id']}}">{{$mun['nombre']}}</option>
                                                            @endforeach
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El campo municipio o alcaldía es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="name">Tipo de Vialidad <span style="color:red;">(*)</span></label>
                                                        <select name="vialidad_pF" id="vialidad_pF" class="form-control" placeholder="*Vialidad" >
                                                            <option value="">Seleccione</option>
                                                            <option value="AMPLIACIÓN">Ampliación</option>
                                                            <option value="ANDADOR">Andador</option>
                                                            <option value="AUTOPISTA">Autopista</option>
                                                            <option value="AVENIDA">Avenida</option>
                                                            <option value="BOULEVARD">Boulevard</option>
                                                            <option value="CALLE">Calle</option>
                                                            <option value="CALLEJÓN">Callejón</option>
                                                            <option value="CALZADA">Calzada</option>
                                                            <option value="CARRETERA">Carretera</option>
                                                            <option value="CERRADA">Cerrada</option>
                                                            <option value="CIRCUITO">Circuito</option>
                                                            <option value="CIRCUNVALACIÓN">Circunvalación</option>
                                                            <option value="CONTINUACIÓN">Continuación</option>
                                                            <option value="CORREDOR">Corredor</option>
                                                            <option value="DIAGONAL">Diagonal</option>
                                                            <option value="EJE VIAL">Eje vial</option>
                                                            <option value="PERIFÉRICO">Periférico</option>
                                                            <option value="PRIVADA">Privada</option>
                                                            <option value="PROLONGACIÓN">Prolongación</option>
                                                            <option value="RETORNO">Retorno</option>
                                                            <option value="VIADUCTO">Viaducto</option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El campo vialidad es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="name">Nombre de la Vialidad <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="vialidad_calle_pF" id="vialidad_calle_pF" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                                        <div class="invalid-feedback">
                                                            El campo vialidad o calle es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="">Colonia <span style="color:red;">(*)</span></label>
                                                        <input type="text" class="form-control" name="colonia_pF" id="colonia_pF" oninput="this.value = this.value.toUpperCase()" >
                                                        <div class="invalid-feedback">
                                                            La colonia es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="">Núm. Ext. <span style="color:red;">(*)</span></label>
                                                        <input type="text" class="form-control" placeholder="*Número exterior" name="num_ext_pF" id="num_ext_pF" oninput="this.value = this.value.toUpperCase()" >
                                                        <div class="invalid-feedback">
                                                            El Núm. ext. es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="">Núm. Int.</label>
                                                        <input type="text" class="form-control" placeholder="Número interior" name="num_int_pF" data-no-required oninput="this.value = this.value.toUpperCase()">
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="">Código Postal <span style="color:red;">(*)</span></label>
                                                        <input type="text" class="form-control" placeholder="*Código Postal" name="cp_pF" id="cp_pF" minlength="5" maxlength="5" oninput="this.value = this.value.toUpperCase()" >
                                                        <div class="invalid-feedback">
                                                            El código postal es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="name">¿Desea registrar Representante Legal? <span style="color:red;">(*)</span></label>
                                                        <select name="representate" id="representate" class="form-control">
                                                            <option value="">Seleccione</option>
                                                            <option value="Si">Si</option>
                                                            <option value="No">No</option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El tipo de persona es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-12" id="Conrepresentante" style="display:none;">
                                            <div class="row">
                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <h5 class="text-center" style="color:#CEA845">Información del Representante Legal</h5>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <h5 class="text-center">Datos de identificación</h5>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="name">Nombre(s) del representante <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="nombre_representante_pF" id="nombre_representante_pF" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                                        <div class="invalid-feedback">
                                                            El nombre es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="name">Primer apellido <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="primer_representante_pF" id="primer_representante_pF" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                                        <div class="invalid-feedback">
                                                            El primer apellido es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="name">Segundo apellido <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="segundo_representante_pF" id="segundo_representante_pF" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                                        <div class="invalid-feedback">
                                                            El segundo apellido es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>  
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="">CURP<span style="color:red;"> (*)</span></label>
                                                        <input type="text" class="form-control"  aria-label="CURP" name="curp_representante_pF" id="curp_representante_pF" minlength="18" maxlength="18" oninput="this.value = this.value.toUpperCase()" >
                                                        <div class="invalid-feedback">
                                                            La CURP es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="name">Sexo <span style="color:red;">(*)</span></label>
                                                        <select name="sexo_representante_pF" id="sexo_representante_pF" class="form-control">
                                                            <option value="">Seleccione</option>
                                                            <option value="Femenino">Femenino</option>
                                                            <option value="Masculino">Masculino</option>
                                                            <option value="Prefiero no responder">Prefiero no responder</option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El tipo de persona es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                   <div class="form-group">
                                                        <h5 class="text-center">Datos de contacto</h5>
                                                    </div>
                                                </div> 

                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="">Correo electrónico <span style="color:red;">(*)</span></label>
                                                        <input type="email" class="form-control" name="correo_representante_pF" id="correo_representante_pF" >
                                                        <div class="invalid-feedback">
                                                            El Correo electrónico es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="">Teléfono <span style="color:red;">(*)</span></label>
                                                        <input type="text" class="form-control"  name="telefono_representante_pF" id="telefono_representante_pF" maxlength="10" pattern="[0-9]+" >
                                                        <div class="invalid-feedback">
                                                            El telefono es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <h5 class="text-center" style="color:#CEA845">Datos de la documentación que acredite la personeria</h5>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-4">  
                                                    <div class="form-group">
                                                        <label for="name">Tipo de documento <span style="color:red;">(*)</span></label>
                                                        <select name="tipo_documento_pF" id="tipo_documento_pF" class="form-control">
                                                            <option value="">Seleccione</option>
                                                            <option value="Carta Poder">Carta Poder</option>
                                                                <option value="Instrumento Notarial">Instrumento Notarial</option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El campo es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="">Fecha expedición <span style="color:red;">(*)</span></label>
                                                        <input type="date" class="form-control" aria-describedby="basic-addon1" name="fecha_expedicion_pF" id="fecha_expedicion_pF" >
                                                        <div class="invalid-feedback">
                                                            La fecha es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-2"><br><label for="btncheck1">Sin fecha de vigencia</label>
                                                    <input name="fecha_vigencia_pF" type="checkbox" class="btn-check" id="check_vigencia" data-no-required autocomplete="off"/>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3" id="fecha_vigencia_pF">
                                                    <div class="form-group">
                                                        <label for="fecha_vigencia_pF">Fecha vigencia</label>
                                                        <input type="date" class="form-control" aria-describedby="basic-addon1" name="fecha_vigencia_pF" id="fecha_vigencia_pF" data-no-required min "<?= date("Y-m-d") ?>" >
                                                        <div class="invalid-feedback">
                                                            La fecha es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>  
                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <label for="">Descripción del documento que acredite la personaria <span style="color:red;">(*)</span></label>
                                                        <textarea class="form-control" aria-describedby="basic-addon1" name="descripcion_pF" id="descripcion_pF" 
                                                        placeholder="Ejemplo: Carta poder simple de fecha___, firmada ante dos testigos, suscrita a favor del compareciente por el (C., Lic., Ing., etc.,)_____, en cuanto ___ de la moral citada, personalidad que acredite en terminos de___ número(45 Cuarenta y Cinco), de fecha___, pasada ante la fe del(Lic., Mtro., etc.,)___, Notario Público Número ___, del Estado de ____, y cuyas facultades no han sido revocadas ni mofificadas a la fecha."></textarea>
                                                        <div class="invalid-feedback">
                                                            La descripción es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="name">Identificación Oficial  <span style="color:red;">(*)</span></label>
                                                        <select id="tipo_identificacion_pFCR" name="tipo_identificacion_pFCR" class="form-control">
                                                            <option value="">Seleccione el tipo de indentificación</option>
                                                            <option value="Credencial de elector">Credencial de Elector</option>
                                                            <option value="Pasaporte">Pasaporte</option>
                                                            <option value="Cédula profesional">Cédula Profesional</option>
                                                            <option value="Licencia de conducir">Licencia de Conducir</option>
                                                            <option value="Credencial de inapam">Credencial de INAPAM</option>
                                                            <option value="Cartilla militar">Cartilla Militar</option>
                                                            <option value="Documento migratorio">Documento Migratorio</option>
                                                            <option value="Constancia de identidad">Constancia de Identidad</option>
                                                            <option value="Otro">Otros</option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            Este campo identificación es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6"> 
                                                    <div class="form-group">
                                                        <label for="name">Núm de identificación <span style="color:red;">(*)</span> <span data-bs-toggle="modal" data-bs-target="#helpModal" style="cursor: pointer;">❓</span></label>
                                                        <input type="text" name="num_identificacion_pFCR" id="num_identificacion_pFCR" class="form-control" oninput="this.value = this.value.toUpperCase()"> 
                                                        <div class="invalid-feedback">
                                                            El campo núm. de identificación es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <h5 class="text-center" style="color:#CEA845">Cargar Documentos</h5>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label><span style="color:red;">*</span>Identificación del Empleador</label><br>
                                                        <input type="file" name="documentoIne_pF" id="documentoIne_pF" class="form-control" accept=".pdf" >
                                                        <div class="invalid-feedback">
                                                            La Identificación es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label><span style="color:red;">*</span>Identificación del Representante Legal</label><br>
                                                        <input type="file" name="documentoRepresentacion_pF" id="documentoRepresentacion_pF" class="form-control" accept=".pdf" >
                                                        <div class="invalid-feedback">
                                                            El documento de representación es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label><span style="color:red;">*</span>Documento que acredite la personería</label><br>
                                                        <input type="file" name="documentoPoder_pF" id="documentoPoder_pF" class="form-control" accept=".pdf">
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label>Anexo (Documentos Complementarios)</label><br>
                                                        <input type="file" name="documentoAnexo_pF" id="documentoAnexo_pF" class="form-control" accept=".pdf" data-no-required>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div align="center">
                                                        <button type="submit" class="btn btn-primary" style="background-color:#CEA845; border-color:#CEA845;">Guardar</button>
                                                        <a href="{{ route('publico'); }}" class="btn btn-primary" style=" background-color:#CEA845; border-color:#CEA845;">Regresar</a>    
                                                    </div>
                                                </div> 
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-12" id="Sinrepresentante" style="display:none;">
                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div class="form-group">
                                                    <h5 class="text-center" style="color:#CEA845">Cargar Documentos</h5>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="name">Identificación Oficial <span style="color:red;">(*)</span></label>
                                                        <select id="tipo_identificacion_pF" name="tipo_identificacion_pF" class="form-control">
                                                            <option value="">Seleccione el tipo de indentificación</option>
                                                            <option value="Credencial de elector">Credencial de Elector</option>
                                                            <option value="Pasaporte">Pasaporte</option>
                                                            <option value="Cédula profesional">Cédula Profesional</option>
                                                            <option value="Licencia de conducir">Licencia de Conducir</option>
                                                            <option value="Credencial de inapam">Credencial de INAPAM</option>
                                                            <option value="Cartilla militar">Cartilla Militar</option>
                                                            <option value="Documento migratorio">Documento Migratorio</option>
                                                            <option value="Constancia de identidad">Constancia de Identidad</option>
                                                            <option value="Otro">Otros</option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            Este campo identificación es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6"> 
                                                    <div class="form-group">
                                                        <label for="name">Núm de identificación <span style="color:red;">(*)</span> <span data-bs-toggle="modal" data-bs-target="#helpModal" style="cursor: pointer;">❓</span></label>
                                                        <input type="text" name="num_identificacion_pF" id="num_identificacion_pF" class="form-control" oninput="this.value = this.value.toUpperCase()"> 
                                                        <div class="invalid-feedback">
                                                            El campo núm. de identificación es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label><span style="color:red;">*</span>Identificación Oficial</label><br>
                                                        <input type="file" name="documentoIne_pFSR" id="documentoIne_pFSR" class="form-control" accept=".pdf" >
                                                        <div class="invalid-feedback">
                                                            La Identificación es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label>Anexo (Documentos Complementarios)</label><br>
                                                        <input type="file" name="documentoAnexo_pFSR" id="documentoAnexo_pFSR" class="form-control" accept=".pdf" data-no-required>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div align="center">
                                                    <button type="submit" class="btn btn-primary" style="background-color:#CEA845; border-color:#CEA845;">Guardar</button>
                                                    <a href="{{ route('publico'); }}" class="btn btn-primary" style=" background-color:#CEA845; border-color:#CEA845;">Regresar</a>    
                                                </div>
                                            </div> 
                                        </div>
                                    </div>


                                    <div class="col-xs-12 col-sm-12 col-md-12" id="persona_moral" style="display:none;">
                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                            <div class="form-group">
                                                <h4 class="text-center" style="color:#CEA845">Información Patronal</h4>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                            <div class="form-group">
                                                <h5 class="text-center">Datos de identificación</h5>
                                            </div>
                                        </div>
                                            <div class="row">
                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <label for="name">Razón Social <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="razon" id="razon" placeholder="Ejemplo: Patos Asados S.A. de C.V." class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                                        <div class="invalid-feedback">
                                                            El nombre es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="name">RFC <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="rfc_moral" id="rfc_moral" class="form-control" minlength="13" maxlength="13" oninput="this.value = this.value.toUpperCase()" > 
                                                        <div class="invalid-feedback">
                                                            El nombre es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="name">Giro Comercial <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="giro_moral" id="giro_moral" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                                        <div class="invalid-feedback">
                                                            El nombre es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <h5 class="text-center">Domicilio fiscal</h5>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="password">Entidad Federativa <span style="color:red;">(*)</span></label>
                                                        <select id="estado_moral" class="form-control" name="estado_moral" placeholder="*Entidad Federativa" >
                                                            <option value="">Seleccione</option>
                                                            @foreach($estados as $est)
                                                                <option value="{{$est['id']}}">{{$est['nombre']}}</option>
                                                            @endforeach
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El campo Estado es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="name">Nombre del Municipio o Alcaldía <span style="color:red;">(*)</span></label>
                                                        <select id="municipio_moral" class="form-control" name="municipio_moral" placeholder="*Municipio" >
                                                            <option value="">Seleccione</option>
                                                            @foreach($municipios as $mun)
                                                                <option value="{{$mun['id']}}">{{$mun['nombre']}}</option>
                                                            @endforeach
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El campo municipio o alcaldía es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="name">Tipo de Vialidad <span style="color:red;">(*)</span></label>
                                                        <select name="vialidad_Moral" id="vialidad_Moral" class="form-control" placeholder="*Vialidad" >
                                                            <option value="">Seleccione</option>
                                                            <option value="AMPLIACIÓN">Ampliación</option>
                                                            <option value="ANDADOR">Andador</option>
                                                            <option value="AUTOPISTA">Autopista</option>
                                                            <option value="AVENIDA">Avenida</option>
                                                            <option value="BOULEVARD">Boulevard</option>
                                                            <option value="CALLE">Calle</option>
                                                            <option value="CALLEJÓN">Callejón</option>
                                                            <option value="CALZADA">Calzada</option>
                                                            <option value="CARRETERA">Carretera</option>
                                                            <option value="CERRADA">Cerrada</option>
                                                            <option value="CIRCUITO">Circuito</option>
                                                            <option value="CIRCUNVALACIÓN">Circunvalación</option>
                                                            <option value="CONTINUACIÓN">Continuación</option>
                                                            <option value="CORREDOR">Corredor</option>
                                                            <option value="DIAGONAL">Diagonal</option>
                                                            <option value="EJE VIAL">Eje vial</option>
                                                            <option value="PERIFÉRICO">Periférico</option>
                                                            <option value="PROLONGACIÓN">Prolongación</option>
                                                            <option value="RETORNO">Retorno</option>
                                                            <option value="VIADUCTO">Viaducto</option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El campo vialidad es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="name">Nombre de la Vialidad <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="vialidad_calleMoral" id="vialidad_calleMoral" class="form-control" placeholder="*Nombre vialidad" oninput="this.value = this.value.toUpperCase()" > 
                                                        <div class="invalid-feedback">
                                                            El campo vialidad o calle es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="">Colonia <span style="color:red;">(*)</span></label>
                                                        <input type="text" class="form-control" name="colonia_moral" id="colonia_moral" oninput="this.value = this.value.toUpperCase()" >
                                                        <div class="invalid-feedback">
                                                            La colonia es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="">Núm. Ext. <span style="color:red;">(*)</span></label>
                                                        <input type="text" class="form-control" maxlength="50" placeholder="*Número exterior" name="num_ext_moral" id="num_ext_moral" oninput="this.value = this.value.toUpperCase()" >
                                                        <div class="invalid-feedback">
                                                            El Núm. ext. es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="">Núm. Int.</label>
                                                        <input type="text" class="form-control" maxlength="30" placeholder="Número interior" name="num_int" data-no-required oninput="this.value = this.value.toUpperCase()">
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="">Código Postal <span style="color:red;">(*)</span></label>
                                                        <input type="text" class="form-control" name="cp_moral" id="cp_moral"  minlength="5" maxlength="5" oninput="this.value = this.value.toUpperCase()" >
                                                        <div class="invalid-feedback">
                                                            El código postal es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <h5 class="text-center" style="color:#CEA845">Información del Representante Legal</h5>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <h5 class="text-center">Datos de identificación</h5>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="name">Nombre(s) del Representante Legal<span style="color:red;">(*)</span></label>
                                                        <input type="text" name="nombre_representante_Moral" id="nombre_representante_Moral" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                                        <div class="invalid-feedback">
                                                            El nombre es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="name">Primer apellido <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="primer_Moral" maxlength="100" id="primer_Moral" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                                        <div class="invalid-feedback">
                                                            El primer apellido es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="name">Segundo apellido <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="segundo_Moral" maxlength="100" id="segundo_Moral" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                                        <div class="invalid-feedback">
                                                            El segundo apellido es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>  
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="">CURP<span style="color:red;"> (*)</span></label>
                                                        <input type="text" class="form-control"  aria-label="CURP" name="curp_moral" id="curp_moral" minlength="18" maxlength="18" oninput="this.value = this.value.toUpperCase()" >
                                                        <div class="invalid-feedback">
                                                            La CURP es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="name">Sexo <span style="color:red;">(*)</span></label>
                                                        <select name="sexo_Moral" id="sexo_Moral" class="form-control">
                                                            <option value="">Seleccione</option>
                                                            <option value="Femenino">Femenino</option>
                                                            <option value="Masculino">Masculino</option>
                                                            <option value="Prefiero no responder">Prefiero no responder</option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El tipo de persona es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                   <div class="form-group">
                                                        <h5 class="text-center">Datos de contacto</h5>
                                                    </div>
                                                </div> 

                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="">Correo electrónico <span style="color:red;">(*)</label>
                                                        <input type="email" class="form-control" name="correo_Moral" id="correo_Moral" >
                                                        <div class="invalid-feedback">
                                                            El Correo electrónico es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="">Teléfono <span style="color:red;">(*)</label>
                                                        <input type="text" class="form-control" placeholder="*Telefono"  name="telefono_Moral" id="telefono_Moral" maxlength="10" pattern="[0-9]+" >
                                                        <div class="invalid-feedback">
                                                            El telefono es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <h5 class="text-center" style="color:#CEA845">Datos de la documentación que acredite la personeria</h5>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-4">  
                                                    <div class="form-group">
                                                        <label for="name">Tipo de documento <span style="color:red;">(*)</span></label>
                                                        <select name="tipo_Moral" id="tipo_Moral" class="form-control">
                                                            <option value="">Seleccione</option>
                                                            <option value="Carta Poder">Carta Poder</option>
                                                                <option value="Instrumento Notarial">Instrumento Notarial</option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El campo es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="">Fecha expedición <span style="color:red;">(*)</span></label>
                                                        <input type="date" class="form-control" aria-describedby="basic-addon1" name="fecha_expedicicion_Moral" id="fecha_expedicicion_Moral" >
                                                        <div class="invalid-feedback">
                                                            La fecha es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-2"><br><label for="btncheck1">Sin fecha de vigencia</label>
                                                    <input name="fecha_vigencia_Moral" type="checkbox" class="btn-check" id="check_vigenciaM" data-no-required autocomplete="off"/>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3" id="fecha_vigencia_Moral">
                                                    <div class="form-group">
                                                        <label for="fecha_vigencia_Moral">Fecha vigencia</label>
                                                        <input type="date" class="form-control" aria-describedby="basic-addon1" name="fecha_vigencia_Moral" id="fecha_vigencia_Moral" data-no-required min "<?= date("Y-m-d") ?>" >
                                                        <div class="invalid-feedback">
                                                            La fecha es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>   
                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <label for="">Descripción del documento que acredite la personaria <span style="color:red;">(*)</span></label>
                                                        <textarea class="form-control" aria-describedby="basic-addon1" name="descripcion_Moral"  id="descripcion_Moral" 
                                                        placeholder="Ejemplo: Carta poder simple de fecha___, firmada ante dos testigos, suscrita a favor del compareciente por el (C., Lic., Ing., etc.,)_____, en cuanto ___ de la moral citada, personalidad que acredite en terminos de___ número(45 Cuarenta y Cinco), de fecha___, pasada ante la fe del(Lic., Mtro., etc.,)___, Notario Público Número ___, del Estado de ____, y cuyas facultades no han sido revocadas ni mofificadas a la fecha."></textarea>
                                                        <div class="invalid-feedback">
                                                            La descripción es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="name">Identificación Oficial  <span style="color:red;">(*)</span></label>
                                                        <select id="tipo_identificacion_Moral" name="tipo_identificacion_Moral" class="form-control">
                                                            <option value="">Seleccione el tipo de indentificación</option>
                                                            <option value="Credencial de elector">Credencial de Elector</option>
                                                            <option value="Pasaporte">Pasaporte</option>
                                                            <option value="Cédula profesional">Cédula Profesional</option>
                                                            <option value="Licencia de conducir">Licencia de Conducir</option>
                                                            <option value="Credencial de inapam">Credencial de INAPAM</option>
                                                            <option value="Cartilla militar">Cartilla Militar</option>
                                                            <option value="Documento migratorio">Documento Migratorio</option>
                                                            <option value="Constancia de identidad">Constancia de Identidad</option>
                                                            <option value="Otro">Otros</option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            Este campo identificación es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6"> 
                                                    <div class="form-group">
                                                        <label for="name">Núm de identificación <span style="color:red;">(*)</span> <span data-bs-toggle="modal" data-bs-target="#helpModal" style="cursor: pointer;">❓</span></label>
                                                        <input type="text" name="num_identificacion_Moral" id="num_identificacion_Moral" class="form-control" oninput="this.value = this.value.toUpperCase()"> 
                                                        <div class="invalid-feedback">
                                                            El campo núm. de identificación es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <h4 class="text-center" style="color:#CEA845">Documentos</h4>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label><span style="color:red;">*</span>Acta Constitutiva</label><br>
                                                        <input type="file" name="documentoIne_Moral" id="documentoIne_Moral" class="form-control" accept=".pdf" >
                                                        <div class="invalid-feedback">
                                                            La Identificación es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label><span style="color:red;">*</span>Identificación del Representante Legal</label><br>
                                                        <input type="file" name="documentoRepresentacion_Moral" id="documentoRepresentacion_Moral" class="form-control" accept=".pdf" >
                                                        <div class="invalid-feedback">
                                                            El documento de representación es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label><span style="color:red;">*</span>Documento que acredite la personería</label><br>
                                                        <input type="file" name="documentoPoder" id="documentoPoder" class="form-control" accept=".pdf">
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label>Anexo (Documentos Complementarios)</label><br>
                                                        <input type="file" name="documentoAnexo" id="documentoAnexo" class="form-control" accept=".pdf" data-no-required>
                                                    </div>
                                                </div>

                                    
                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div align="center">
                                                        <button type="submit" class="btn btn-primary" style="background-color:#CEA845; border-color:#CEA845;">Guardar</button>
                                                        <a href="{{ route('publico'); }}" class="btn btn-primary" style=" background-color:#CEA845; border-color:#CEA845;">Regresar</a>    
                                                    </div>
                                                </div> 
                                            </div>
                                    </div>   
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>


    <div id="crear_poder" style ="display: none;">
        <div>.</div>
        <div class="loader"></div>
    </div>

@section('scripts')
    <script src="public/assets/js/poderes/general.js"></script>
@endsection



    <script src="public/assets/js/jquery.min.js"></script>
    <script src="public/assets/js/popper.min.js"></script>
    <script src="public/assets/js/bootstrap.min.js"></script>
    <script src="public/assets/js/sweetalert.min.js"></script>
    <script src="public/assets/js/select2.min.js"></script>
    <script src="public/assets/js/jquery.nicescroll.js"></script>

    <!-- Template JS File -->
    <script src="public/assets/js/stisla.js"></script>
    <script src="public/assets/js/scripts.js"></script>
    <script src="public/assets/js/profile.js"></script>
    <script src="public/assets/js/custom.js"></script>

    <script src="https://cdn.datatables.net/2.1.5/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.1.5/js/dataTables.bootstrap4.js"></script>
    @yield('page_js')


    @yield('scripts')


<div id="crear_poder" style ="display: none;">
    <div>.</div>
    <div class="loader"></div>
</div>
        <!-- Modal para la captura de la ine-->
            <div class="modal fade" id="helpModal" aria-labelledby="helpModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" style="max-height: 80vh;">
                  <div class="modal-content" style="height: 100%;">
                    <div class="modal-header">
                      <h5 class="modal-title" id="helpModalLabel">Ubicación de núm. de identificación</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body text-center">
                      <img src="./public/assets/images/capturaIne.png" alt="Instrucciones" class="img-fluid">
                    </div>
                  </div>
                </div>
            </div>
            
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('form');
            const selectTipoPersona = document.getElementById('tipo_persona');
            const selectRepresentante = document.getElementById('representate');

            const seccionFisica = document.getElementById('persona_fisica');
            const seccionMoral = document.getElementById('persona_moral');
            const seccionConRepresentante = document.getElementById('Conrepresentante');
            const seccionSinRepresentante = document.getElementById('Sinrepresentante');

            // En este formulario muchos campos no traen el atributo required en el HTML.
            // Por eso NO podemos basarnos en “required original” para aplicarlo dinámicamente.
            // Regla: todo campo visible dentro de una sección activa (excepto hidden y botones)
            // se vuelve required, salvo que se marque explícitamente con `data-no-required`.

            // Limpia valores para que al cambiar de flujo no se queden datos “fantasma”.
            function clearSectionValues(sectionEl) {
                if (!sectionEl) return;
                sectionEl.querySelectorAll('input, select, textarea').forEach(function (el) {
                    const tag = el.tagName.toLowerCase();
                    const type = (el.type || '').toLowerCase();

                    if (type === 'hidden') return; // no tocar los hidden

                    if (type === 'checkbox' || type === 'radio') {
                        el.checked = false;
                        return;
                    }

                    if (type === 'file') {
                        el.value = '';
                        return;
                    }

                    if (tag === 'select') {
                        el.selectedIndex = 0;
                        // Si es select2, intentamos refrescar sin romper si no existe.
                        if (window.jQuery && jQuery(el).data('select2')) {
                            jQuery(el).val('').trigger('change');
                        }
                        return;
                    }

                    el.value = '';
                });
            }

            // Activa/desactiva una sección:
            // - disabled controla si se manda en submit
            // - required se aplica solo si está visible, y NO es hidden, y NO está opt-out
            function setSectionActive(sectionEl, active) {
                if (!sectionEl) return;

                sectionEl.style.display = active ? 'block' : 'none';

                sectionEl.querySelectorAll('input, select, textarea, button').forEach(function (el) {
                    el.disabled = !active;

                    if (!(el instanceof HTMLInputElement || el instanceof HTMLSelectElement || el instanceof HTMLTextAreaElement)) {
                        return;
                    }

                    const type = (el.type || '').toLowerCase();
                    const shouldSkipRequired =
                        type === 'hidden' ||
                        el.disabled ||
                        el.hasAttribute('data-no-required') ||
                        el.getAttribute('aria-hidden') === 'true';

                    if (active && !shouldSkipRequired) {
                        el.setAttribute('required', 'required');
                    } else {
                        el.removeAttribute('required');
                    }
                });

                // Si una sección se va a ocultar, limpiamos cualquier estado de validación visual
                if (!active) {
                    sectionEl.querySelectorAll('.is-invalid').forEach(function (el) {
                        el.classList.remove('is-invalid');
                    });
                }
            }

            function applyRepresentanteFlow() {
                if (!selectRepresentante) return;
                const val = selectRepresentante.value;

                if (val === 'Si') {
                    setSectionActive(seccionConRepresentante, true);
                    clearSectionValues(seccionSinRepresentante);
                    setSectionActive(seccionSinRepresentante, false);
                } else if (val === 'No') {
                    setSectionActive(seccionSinRepresentante, true);
                    clearSectionValues(seccionConRepresentante);
                    setSectionActive(seccionConRepresentante, false);
                } else {
                    clearSectionValues(seccionConRepresentante);
                    clearSectionValues(seccionSinRepresentante);
                    setSectionActive(seccionConRepresentante, false);
                    setSectionActive(seccionSinRepresentante, false);
                }
            }

            function applyTipoPersonaFlow() {
                if (!selectTipoPersona) return;
                const val = selectTipoPersona.value;

                if (val === 'Fisica') {
                    setSectionActive(seccionFisica, true);
                    setSectionActive(seccionMoral, false);
                    clearSectionValues(seccionMoral);

                    applyRepresentanteFlow();
                } else if (val === 'Moral') {
                    // Moral activa
                    setSectionActive(seccionMoral, true);
                    setSectionActive(seccionFisica, false);
                    clearSectionValues(seccionFisica);

                    //En moral no aplica el flujo de representante de física
                    if (selectRepresentante) {
                        selectRepresentante.value = '';
                    }
                    clearSectionValues(seccionConRepresentante);
                    clearSectionValues(seccionSinRepresentante);
                    setSectionActive(seccionConRepresentante, false);
                    setSectionActive(seccionSinRepresentante, false);
                } else {
                    //Ninguno seleccionado aún
                    clearSectionValues(seccionFisica);
                    clearSectionValues(seccionMoral);
                    clearSectionValues(seccionConRepresentante);
                    clearSectionValues(seccionSinRepresentante);
                    setSectionActive(seccionFisica, false);
                    setSectionActive(seccionMoral, false);
                    setSectionActive(seccionConRepresentante, false);
                    setSectionActive(seccionSinRepresentante, false);
                }
            }

            //Eventos
            if (selectTipoPersona) {
                selectTipoPersona.addEventListener('change', applyTipoPersonaFlow);
                applyTipoPersonaFlow();
            }
            if (selectRepresentante) {
                selectRepresentante.addEventListener('change', applyRepresentanteFlow);
            }

            //En submit nos aseguramos de deshabilitar lo que esté oculto
            if (form) {
                form.addEventListener('submit', function (event) {
                    [seccionFisica, seccionMoral, seccionConRepresentante, seccionSinRepresentante].forEach(function (sec) {
                        if (!sec) return;
                        const visible = sec.style.display !== 'none';
                        sec.querySelectorAll('input, select, textarea, button').forEach(function (el) {
                            el.disabled = !visible;
                        });
                    });

                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                });
            }
        });

         //PERSONA FÍSICA
        document.getElementById("fecha_vigencia_pF").style.display = "block";
        $(function(){
            $('#check_vigencia').on('change', validarcheckvigencia);
        })
        function validarcheckvigencia(){
            vigencia = document.getElementById("fecha_vigencia_pF").style.display;
            if (vigencia == "none") {
                document.getElementById("fecha_vigencia_pF").style.display = "block";
            }
            else{
                document.getElementById("fecha_vigencia_pF").style.display = "none";
            }
        }

        //PERSONA MORAL
        document.getElementById("fecha_vigencia_Moral").style.display = "block";
        $(function(){
            $('#check_vigenciaM').on('change', validarcheckvigenciaM);
        })
        function validarcheckvigenciaM(){
            vigenciaM = document.getElementById("fecha_vigencia_Moral").style.display;
            if (vigenciaM == "none") {
                document.getElementById("fecha_vigencia_Moral").style.display = "block";
            }
            else{
                document.getElementById("fecha_vigencia_Moral").style.display = "none";
            }
        }

    </script>
    <script>
        // Esperamos a que el DOM esté listo para evitar el error "Cannot read properties of null"
        document.addEventListener('DOMContentLoaded', function() {
            const inputDocumento = document.querySelector('input[name="documentoIne_pFSR"]');
            if (inputDocumento) {
                inputDocumento.addEventListener('change', function(e) {
                    // Accedemos al archivo cargado
                    const archivo = e.target.files[0];

                    if (archivo) {
                        // Aquí puedes ejecutar tu validación de 10MB
                        const limite = 10 * 1024 * 1024;
                        if (archivo.size > limite) {
                            alert("El archivo no puede pasar de 10 Megas");
                            this.value = ""; // Limpiar el input
                        }
                    }
                });
            }
        });
        document.addEventListener('DOMContentLoaded', function() {
            const inputDocumento = document.querySelector('input[name="documentoAnexo_pFSR"]');
            if (inputDocumento) {
                inputDocumento.addEventListener('change', function(e) {
                    // Accedemos al archivo cargado
                    const archivo = e.target.files[0];

                    if (archivo) {
                        // Aquí puedes ejecutar tu validación de 10MB
                        const limite = 10 * 1024 * 1024;
                        if (archivo.size > limite) {
                            alert("El archivo no puede pasar de 10 Megas");
                            this.value = ""; // Limpiar el input
                        }
                    }
                });
            }
        });
        document.addEventListener('DOMContentLoaded', function() {
            const inputDocumento = document.querySelector('input[name="documentoRepresentacion_pF"]');
            if (inputDocumento) {
                inputDocumento.addEventListener('change', function(e) {
                    // Accedemos al archivo cargado
                    const archivo = e.target.files[0];

                    if (archivo) {
                        // Aquí puedes ejecutar tu validación de 10MB
                        const limite = 10 * 1024 * 1024;
                        if (archivo.size > limite) {
                            alert("El archivo no puede pasar de 10 Megas");
                            this.value = ""; // Limpiar el input
                        }
                    }
                });
            }
        });
        document.addEventListener('DOMContentLoaded', function() {
            const inputDocumento = document.querySelector('input[name="documentoIne_pF"]');
            if (inputDocumento) {
                inputDocumento.addEventListener('change', function(e) {
                    // Accedemos al archivo cargado
                    const archivo = e.target.files[0];

                    if (archivo) {
                        // Aquí puedes ejecutar tu validación de 10MB
                        const limite = 10 * 1024 * 1024;
                        if (archivo.size > limite) {
                            alert("El archivo no puede pasar de 10 Megas");
                            this.value = ""; // Limpiar el input
                        }
                    }
                });
            }
        });
        document.addEventListener('DOMContentLoaded', function() {
            const inputDocumento = document.querySelector('input[name="documentoPoder_pF"]');
            if (inputDocumento) {
                inputDocumento.addEventListener('change', function(e) {
                    // Accedemos al archivo cargado
                    const archivo = e.target.files[0];

                    if (archivo) {
                        // Aquí puedes ejecutar tu validación de 10MB
                        const limite = 10 * 1024 * 1024;
                        if (archivo.size > limite) {
                            alert("El archivo no puede pasar de 10 Megas");
                            this.value = ""; // Limpiar el input
                        }
                    }
                });
            }
        });
        document.addEventListener('DOMContentLoaded', function() {
            const inputDocumento = document.querySelector('input[name="documentoAnexo_pF"]');
            if (inputDocumento) {
                inputDocumento.addEventListener('change', function(e) {
                    // Accedemos al archivo cargado
                    const archivo = e.target.files[0];

                    if (archivo) {
                        // Aquí puedes ejecutar tu validación de 10MB
                        const limite = 10 * 1024 * 1024;
                        if (archivo.size > limite) {
                            alert("El archivo no puede pasar de 10 Megas");
                            this.value = ""; // Limpiar el input
                        }
                    }
                });
            }
        });
        document.addEventListener('DOMContentLoaded', function() {
            const inputDocumento = document.querySelector('input[name="documentoIne_Moral"]');
            if (inputDocumento) {
                inputDocumento.addEventListener('change', function(e) {
                    // Accedemos al archivo cargado
                    const archivo = e.target.files[0];

                    if (archivo) {
                        // Aquí puedes ejecutar tu validación de 10MB
                        const limite = 10 * 1024 * 1024;
                        if (archivo.size > limite) {
                            alert("El archivo no puede pasar de 10 Megas");
                            this.value = ""; // Limpiar el input
                        }
                    }
                });
            }
        });
        document.addEventListener('DOMContentLoaded', function() {
            const inputDocumento = document.querySelector('input[name="documentoRepresentacion_Moral"]');
            if (inputDocumento) {
                inputDocumento.addEventListener('change', function(e) {
                    // Accedemos al archivo cargado
                    const archivo = e.target.files[0];

                    if (archivo) {
                        // Aquí puedes ejecutar tu validación de 10MB
                        const limite = 10 * 1024 * 1024;
                        if (archivo.size > limite) {
                            alert("El archivo no puede pasar de 10 Megas");
                            this.value = ""; // Limpiar el input
                        }
                    }
                });
            }
        });
        document.addEventListener('DOMContentLoaded', function() {
            const inputDocumento = document.querySelector('input[name="documentoPoder"]');
            if (inputDocumento) {
                inputDocumento.addEventListener('change', function(e) {
                    // Accedemos al archivo cargado
                    const archivo = e.target.files[0];

                    if (archivo) {
                        // Aquí puedes ejecutar tu validación de 10MB
                        const limite = 10 * 1024 * 1024;
                        if (archivo.size > limite) {
                            alert("El archivo no puede pasar de 10 Megas");
                            this.value = ""; // Limpiar el input
                        }
                    }
                });
            }
        });
        document.addEventListener('DOMContentLoaded', function() {
            const inputDocumento = document.querySelector('input[name="documentoAnexo"]');
            if (inputDocumento) {
                inputDocumento.addEventListener('change', function(e) {
                    // Accedemos al archivo cargado
                    const archivo = e.target.files[0];

                    if (archivo) {
                        // Aquí puedes ejecutar tu validación de 10MB
                        const limite = 10 * 1024 * 1024;
                        if (archivo.size > limite) {
                            alert("El archivo no puede pasar de 10 Megas");
                            this.value = ""; // Limpiar el input
                        }
                    }
                });
            }
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function cargarMunicipiosSolicitante(estadoId) {
                var $municipio = $('#municipio_pF');
                if (!$municipio.length) return;
                $municipio.html('<option value="">Cargando...</option>');
                if (!estadoId) {
                    $municipio.html('<option value="">Seleccione</option>');
                    return;
                }
                $.get(base_url + '/api/munSolicitante/' + estadoId, function (data) {
                    var html = '<option value="">Seleccione</option>';
                    data.forEach(function (m) {
                        html += '<option value="' + m.id + '">' + m.nombre + '</option>';
                    });
                    $municipio.html(html);
                }).fail(function (jqXHR, textStatus, errorThrown) {
                    $.get(base_url + '/munSolicitante/' + estadoId, function (data) {
                        var html = '<option value="">Seleccione</option>';
                        data.forEach(function (m) {
                            html += '<option value="' + m.id + '">' + m.nombre + '</option>';
                        });
                        $municipio.html(html);
                    }).fail(function (jq2, t2, e2) {
                        $municipio.html('<option value="">Error cargando municipios</option>');
                        if (typeof iziToast !== 'undefined') {
                            iziToast.error({
                                title: 'Error',
                                message: 'No se pudieron cargar los municipios. HTTP: ' + (jqXHR.status || jq2.status || 'N/A') + ' - ' + (errorThrown || e2 || textStatus),
                                position: 'topRight'
                            });
                        } else {
                            alert('No se pudieron cargar los municipios.');
                        }
                    });
                });
            }

            var $estadoSolicitante = $('#estado_pF');
            var base_url = "{{ url('') }}";

            if ($estadoSolicitante.length) {
                $estadoSolicitante.on('change', function () {
                    cargarMunicipiosSolicitante(this.value);
                });
                var inicial = $estadoSolicitante.val();
                if (inicial) cargarMunicipiosSolicitante(inicial);
            }
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function cargarMunicipiosSolicitante(estadoId) {
                var $municipio = $('#municipio_moral');
                if (!$municipio.length) return;
                $municipio.html('<option value="">Cargando...</option>');
                if (!estadoId) {
                    $municipio.html('<option value="">Seleccione</option>');
                    return;
                }
                $.get(base_url + '/api/munSolicitante/' + estadoId, function (data) {
                    var html = '<option value="">Seleccione</option>';
                    data.forEach(function (m) {
                        html += '<option value="' + m.id + '">' + m.nombre + '</option>';
                    });
                    $municipio.html(html);
                }).fail(function (jqXHR, textStatus, errorThrown) {
                    $.get(base_url + '/munSolicitante/' + estadoId, function (data) {
                        var html = '<option value="">Seleccione</option>';
                        data.forEach(function (m) {
                            html += '<option value="' + m.id + '">' + m.nombre + '</option>';
                        });
                        $municipio.html(html);
                    }).fail(function (jq2, t2, e2) {
                        $municipio.html('<option value="">Error cargando municipios</option>');
                        if (typeof iziToast !== 'undefined') {
                            iziToast.error({
                                title: 'Error',
                                message: 'No se pudieron cargar los municipios. HTTP: ' + (jqXHR.status || jq2.status || 'N/A') + ' - ' + (errorThrown || e2 || textStatus),
                                position: 'topRight'
                            });
                        } else {
                            alert('No se pudieron cargar los municipios.');
                        }
                    });
                });
            }

            var $estadoSolicitante = $('#estado_moral');
            var base_url = "{{ url('') }}";

            if ($estadoSolicitante.length) {
                $estadoSolicitante.on('change', function () {
                    cargarMunicipiosSolicitante(this.value);
                });
                var inicial = $estadoSolicitante.val();
                if (inicial) cargarMunicipiosSolicitante(inicial);
            }
        });
    </script>

@section('scripts')
    <script src="public/js/poderes/general.js"></script>
@endsection
