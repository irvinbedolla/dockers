@extends('layouts.app')
    <style>
        .loader {
            position: fixed;
            left: 0px;
            top: 0px;
            width: 100%;
            height: 100%;
            z-index: 9999;
            background: url('../public/assets/images/pageLoader.gif') 50% 50% no-repeat rgb(249,249,249);
           /* background-color: #6A0F49;/*<p style="color: #CEA845*/
            opacity: .8;
        }
        #resultado {
            background-color: red;
            color: white;
            font-weight: bold;
        }
        #resultado.ok {
            background-color: green;
        }

        select[name="vialidad"] option {
            text-transform: uppercase;
        }
        select[name="estado_citado"] option {
            text-transform: uppercase;
        }
        select[name="municipio_citado"] option {
            text-transform: uppercase;
        }
        .form-control {
            border: 1px solid #ced4da !important;
            box-shadow: none !important;
        }

        .form-control:focus {
            border-color: #80bdff !important;
            outline: 0 !important;
        }
        .btn-disabled {
            pointer-events: none;
            opacity: 0.65;
            cursor: not-allowed;
        }
    </style>
    @section('content')
        <section class="section">
            <div class="section-header">
                <h3 class="page__heading">Solicitud</h3>
            </div>
            <div class="section-body">
                <div class="row"> 
                    <div class="col-lg-12" >
                        <!--<div class="card">-->
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

                                <form class="needs-validation" novalidate id="form_concluir" method="POST" action="{{route('seer.citadosAuxP', ['id' => $id])}}" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $id }}">

                                    <div style="background-color:#D2D3D5; width:100%; height:30px;">
                                        <h4 class="text-center" style="color:black">Datos Personales del Citado</h4>
                                    </div>    
                                    <p><span style="color:red;"><!-- * --></span> <!-- Debes capturar al menos un citado --></p>

                                    <div class="row" id="div_datos_citado">
                                        <input type="hidden" name="tipo" value="Fisica">
                                        <input type="hidden" name="curp" value="">
                                        <input type="hidden" name="rfc" value="">
                                        <!-- <div class="col-xs-12 col-sm-12 col-md-2">
                                            <div class="form-group">
                                                <label for="name">Tipo de persona <span style="color:red;">(*)</span></label>
                                                <select name="tipo" id="tipo" class="form-control" required>
                                                    <option value="">Seleccione</option>
                                                    <option value="Fisica">Física</option>
                                                    <option value="Moral">Moral</option>
                                                </select>
                                                <div class="invalid-feedback">
                                                    El tipo de persona es obligatorio.
                                                </div>
                                            </div>
                                        </div> -->

                                        

                                        <div class="col-xs-12 col-sm-12 col-md-3" id="tipoPersona_razon" style="display:none;">
                                            <div class="form-group">
                                                <label for="name">Razón social <span style="color:red;">(*)</span></label>
                                                <input type="text" name="razon" id="razon" class="form-control" oninput="this.value = this.value.toUpperCase()"> 
                                                <div class="invalid-feedback">
                                                    La razón social es obligatorio.
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-xs-12 col-sm-12 col-md-12" id="tipoPersona_nombre" style="display:none;">
                                            <div class="row">
                                                <div class="col-xs-12 col-sm-12 col-md-4">
                                                    <div class="form-group">
                                                        <label for="name">Nombre(s) <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="nombre" id="nombre" class="form-control" oninput="this.value = this.value.toUpperCase()"> 
                                                        <div class="invalid-feedback">
                                                            El nombre es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-xs-12 col-sm-12 col-md-4">
                                                    <div class="form-group">
                                                        <label for="name">Primer apellido <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="primer_apellido" maxlength="50" class="form-control" oninput="this.value = this.value.toUpperCase()"> 
                                                        <div class="invalid-feedback">
                                                            El primer apellido es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-4">
                                                    <div class="form-group">
                                                        <label for="name">Segundo apellido</label>
                                                        <input type="text" name="segundo_apellido" maxlength="50" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                                        <!--<div class="invalid-feedback">
                                                            El segundo apellido es obligatorio.
                                                        </div>-->
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-4" id="campo_curp">
                                                    <div class="form-group">
                                                        <label for="name">CURP (Opcional)</label>
                                                        <input type="text" name="curp" maxlength="18" id="curp_input" oninput="validarInput(this)" class="form-control"> 
                                                        <pre id="resultado"></pre>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-4">
                                                    <div class="form-group">
                                                        <label for="name">RFC (Opcional)</label>
                                                        <input type="text" name="rfc" class="form-control" minlength="13" maxlength="13" oninput="this.value = this.value.toUpperCase()">   
                                                        <!--<div class="invalid-feedback">
                                                            Debes ingresar 13 caracteres.
                                                        </div>-->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <input type="hidden" name="fecha_nacimiento" value="2000-01-01">
                                        <input type="hidden" name="edad" value="0">
                                        <input type="hidden" name="nacionalidad" value="Mexicana">
                                        <!-- <div id="div1" class="col-xs-12 col-sm-12 col-md-4">
                                            <div class="form-group">
                                                <label for="name">Fecha de Nacimiento <span style="color:red;">(*)</span></label>
                                                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" onchange="validarfechaNacimiento(this)" class="form-control" required>
                                                <div class="invalid-feedback">
                                                    El campo fecha de nacimiento es obligatoria.
                                                </div>
                                            </div>
                                        </div>
                                        <div id="div1" class="col-xs-12 col-sm-12 col-md-4">
                                            <div class="form-group">
                                                <label for="name">Edad<span style="color:red;">(*)</span></label>
                                                <input type="number" min="0" name="edad" class="form-control" id="años_edad" required> 
                                                <div class="invalid-feedback">
                                                    El campo edad es obligatoria.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-4">
                                            <div class="form-group">
                                                <label for="name">Nacionalidad <span style="color:red;">(*)</span></label>
                                                <select name="nacionalidad" class="form-control" required>
                                                    <option value="">SELECCIONE</option>
                                                    <option value="Mexicana">MEXICANA</option>
                                                    <option value="Otra">OTRA</option>
                                                </select>
                                                <div class="invalid-feedback">
                                                    El campo nacionalidad es obligatoria.
                                                </div>
                                            </div>
                                        </div> -->
                                        <div class="col-xs-12 col-sm-12 col-md-3">
                                            <label for="btncheck1">¿Requiere Traductor?</label>
                                            <input type="checkbox" id="check_lenguaje" name="traductor" autocomplete="off">
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-4" id="lenguaje_señas" tyle="display:none">
                                            <div class="form-group">
                                                <label for="name">¿Qué tipo de lenguaje require?</label>
                                                <input type="text" name="lenguaje" class="form-control" id="lenguajeRequerido" oninput="this.value = this.value.toUpperCase()">
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-4">
                                            <label for="btncheck1">¿Tiene alguna discapacidad?</label>
                                            <input type="checkbox" id="check_discapacidad" name="discapacidad" autocomplete="off">
                                        </div>  
                                        <div class="col-xs-12 col-sm-12 col-md-4" id="discapacidad" style="display:none">
                                            <div class="form-group">
                                                <label for="name">¿Cuál es su discapacidad?</label>
                                                <input type="text" name="tipo_discapacidad" class="form-control" id="discapacidadRequerida" oninput="this.value = this.value.toUpperCase()">
                                                <div class="invalid-feedback">
                                                    Debe especificar la discapacidad.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                    
                                    <div class="row"> 
                                        <div class="col-xs-12 col-sm-12 col-md-12" style="background-color:#D2D3D5; width:100%; height:30px;">
                                            <div class="form-group">
                                                <h4 class="text-center" style="color:black">Dirección del Citado</h4>
                                            </div>
                                        </div>

                                        @if(!$session_notificacion)
                                        <!--div class="col-xs-12 col-sm-12 col-md-3">
                                            <div class="form-group">
                                                <label for="name">¿Quién entregará los citatorios? <span style="color:red;">(*)</span></label>
                                                <select name="notificacion" class="form-control" required>
                                                    <option value="">SELECCIONE</option>
                                                    <option value="Trabajador" {{ old('notificacion') == 'Trabajador' ? 'selected' : '' }}>Solicitante</option>
                                                    <option value="Centro" {{ old('notificacion') == 'Centro' ? 'selected' : '' }}>Centro de conciliación Laboral</option>
                                                </select>
                                                <div class="invalid-feedback">
                                                    El campo ¿quién entregará los citatorios? es obligatorio.
                                                </div>
                                            </div>
                                        </div-->
                                        @endif

                                        <input name="notificacion" type="hidden" value="Centro">

                                        <div class="col-xs-12 col-sm-12 col-md-3">
                                            <div class="form-group">
                                                <label for="name">Tipo de vialidad <span style="color:red;">(*)</span></label>
                                                <select name="vialidad" class="form-control" required>
                                                    <option value="">SELECCIONE</option>
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
                                                    <option value="PRIVADA">Privada</option>
                                                    <option value="RETORNO">Retorno</option>
                                                    <option value="VIADUCTO">Viaducto</option>
                                                </select>
                                            <div class="invalid-feedback">
                                                El campo vialidad es obligatorio.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label for="name">Nombre de la vialidad <span style="color:red;">(*)</span></label>
                                            <input type="text" name="calle" maxlength="100" class="form-control" oninput="this.value = this.value.toUpperCase()" required> 
                                            <div class="invalid-feedback">
                                                El campo nombre de la vialidad es obligatorio.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xs-12 col-sm-12 col-md-3">
                                        <div class="form-group">
                                            <label for="text">Núm. ext. <span style="color:red;">(*)</span></label>
                                            <input type="text" name="exterior" min="0" maxlength="50" class="form-control" oninput="this.value = this.value.toUpperCase()" required> 
                                            <div class="invalid-feedback">
                                                El núm. exterior es obligatorio.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xs-12 col-sm-12 col-md-3">
                                        <div class="form-group">
                                            <label for="name">Núm. int.</label>
                                            <input type="text" name="interior" min="0" maxlength="50" class="form-control"  oninput="this.value = this.value.toUpperCase()"> 
                                        </div>
                                    </div>

                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label for="name">Colonia <span style="color:red;">(*)</span></label>
                                            <input type="text" name="colonia" maxlength="100" class="form-control" oninput="this.value = this.value.toUpperCase()" required> 
                                            <div class="invalid-feedback">
                                                El campo colonia es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                        <div class="form-group">
                                            <label for="name">Estado <span style="color:red;">(*)</span></label>
                                            <select id="estado_citado" class="form-control" name="estado_citado" required>
                                                <option value="">Seleccione</option>
                                                @foreach($estados as $es)
                                                    <option value="{{$es['id']}}">{{$es['nombre']}}</option>
                                                @endforeach
                                            </select>
                                            <div class="invalid-feedback">
                                                El campo Estado es obligatorio.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                        <div class="form-group">
                                            <label for="name">Municipio o Alcaldía <span style="color:red;">(*)</span></label>
                                            <select id="municipio_citado" class="form-control" name="municipio_citado" required>
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

                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                        <div class="form-group">
                                            <label for="name">Código postal <span style="color:red;">(*)</span></label>
                                            <input type="text" name="cp" id="cp" class="form-control" maxlength="5">
                                            <div class="invalid-feedback">
                                                El campo Código Postal es obligatorio. Debes ingresar 5 caracteres.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label for="name">Entre calle (Opcional)</label>
                                            <input type="text" name="calle1" maxlength="50" class="form-control" oninput="this.value = this.value.toUpperCase()"> 
                                            <!--<div class="invalid-feedback">
                                                El campo entre calle es obligatorio.
                                            </div>-->
                                        </div>
                                    </div>

                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label for="name">y calle (Opcional)</label>
                                            <input type="text" name="calle2" maxlength="50" class="form-control" oninput="this.value = this.value.toUpperCase()"> 
                                            <!--<div class="invalid-feedback">
                                                El campo y calle es obligatorio.
                                            </div>-->
                                        </div>
                                    </div>

                                    <div class="col-xs-12 col-sm-12 col-md-12">
                                        <div class="form-group">
                                            <label for="floatingTextarea">Referencias del domicilio <span style="color:red;">(*)</span></label>
                                            <textarea class="form-control" placeholder="Ingresa alguna referencia de como llegar" name="referencia" style="height: 100px;" required></textarea>
                                            <div class="invalid-feedback">
                                                El campo referencias es obligatorio.
                                            </div>
                                        </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                            <label for="name">Ubica tu domicilio laboral y adjunta una captura.</label>
                                        </div>
                                        <div class="col-xs-12 col-sm-6 col-md-2"><br>
                                            <div class="form-group">
                                                <a class="btn btn-primary" 
                                                    style="background-color:blue; border-color:blue; display: flex; align-items: center; justify-content: center; gap: 8px;" 
                                                    href="https://www.google.com.mx/maps/@19.6837376,-101.1712,14z?entry=ttu&g_ep=EgoyMDI1MDgzMC4wIKXMDSoASAFQAw%3D%3D" 
                                                    target="_blank">
                                                    <img src="https://www.gstatic.com/images/branding/product/1x/maps_64dp.png" alt="Google Maps" style="width:20px; height:20px;">
                                                        Google Maps
                                                </a>
                                            </div>
                                        </div>                                     
                                        <div class="col-xs-12 col-sm-6 col-md-4">
                                            <div class="form-group">
                                                <label for="name">Referencia 1 <span style="color:red;">(*)</span></label>
                                                <input type="file" class="form-control" name="foto1" accept="image/*" required>
                                                <div class="invalid-feedback">
                                                    El campo Referencia 1 es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-6 col-md-4">
                                            <div class="form-group">
                                                <label for="name">Referencia 2 (Opcional)</label>
                                                <input type="file" class="form-control" name="foto2" accept="image/*">
                                            </div>
                                        </div>
                                        <!--div class="col-xs-12 col-sm-12 col-md-12">
                                            <div class="form-group">
                                                <h4 class="text-center">En caso de ser mayor de edad subir su identificación y en caso de ser menor su identificación es su Acta de Nacimiento</h4>
                                            </div>
                                        </div>
                                        <div-- class="col-xs-12 col-sm-12 col-md-4">
                                            <div class="form-group">
                                                <label>Subir Identificación oficial <span style="color:red;">(*)</span></label>
                                                <input type="file" id="documentoIdentificacion" name="documentoIdentificacion" class="form-control" accept=".pdf" required>
                                                <div class="invalid-feedback">
                                                    El documento con la identificación es obligatorio.
                                                </div>
                                            </div>
                                        </div-->
                                    </div>

                                    <div class="row" id="datos_laborales">
                                        <div class="col-xs-12 col-sm-12 col-md-12" style="background-color:#D2D3D5; width:100%; height:30px;">
                                            <h4 class="text-center" style="color:black">Datos Laborales del Citado</h4>
                                        </div>  
                                        <div class="col-xs-12 col-sm-12 col-md-3">
                                            <div class="form-group">
                                                <label for="name">Número de Seguro Social (Opcional)</label>
                                                <input type="number" name="seguro" minlength="11" maxlength="12" class="form-control soloNumeros"> 
                                                <div class="invalid-feedback">
                                                    Debe tener 12 dígitos su número de seguridad social
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-3">
                                            <div class="form-group">
                                                <label for="name">Puesto <span style="color:red;">(*)</span></label>
                                                <input type="text" name="puesto" maxlength="50" class="form-control" oninput="this.value = this.value.toUpperCase()" required> 
                                                <div class="invalid-feedback">
                                                    El campo puesto es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-3">
                                            <div class="form-group">
                                                <label for="name">Frecuencia de Pago <span style="color:red;">(*)</span></label>
                                                <select name="periodo_pago" class="form-control" required>
                                                    <option value="">SELECCIONE</option>
                                                    <option value="Diario">DIARIO</option>
                                                    <option value="Semanal">SEMANAL</option>
                                                    <option value="Quincenal">QUINCENAL</option>
                                                    <option value="Mensual">MENSUAL</option>
                                                </select>
                                                <div class="invalid-feedback">
                                                    El campo frecuencia de pago es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-3">
                                            <div class="form-group">
                                                <label for="name">Salario <span style="color:red;">(*)</span></label>
                                                <input type="number" step="0.001" name="pago" class="form-control soloMontos" required> 
                                                <div class="invalid-feedback">
                                                    El campo salario es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-3">
                                            <div class="form-group">
                                                <label for="name">Total de horas trabajadas por semana <span style="color:red;">(*)</span></label>
                                                <input type="number" name="horas" min="0" class="form-control" required> 
                                                <div class="invalid-feedback">
                                                    El campo cantidad de horas trabajadas es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-1">
                                            <div class="form-group">
                                                <label for="btncheck1">¿El citado labora actualmente?</label><br>
                                                <input name="labora" type="checkbox" id="check_fecha" autocomplete="off"/>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                            <div class="form-group">
                                                <label for="name">Horario laboral <span style="color:red;">(*)</span></label>
                                                <input type="text" name="jornada" maxlength="200" class="form-control" placeholder="Ejemplo: De lunes a viernes de 9:00 a.m. a 5:00 p.m. y Sábados de 9:00 a.m. a 2:00 p.m." required>
                                                <div class="invalid-feedback">
                                                    El campo horario laboral es obligatoria.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-2"> 
                                            <div class="form-group">
                                                <label for="name">Fecha de Ingreso <span style="color:red;">(*)</span></label>
                                                <input type="date" name="fecha_ingreso" class="form-control" required> 
                                                <div class="invalid-feedback">
                                                    El campo fecha de ingreso es obligatoria.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-2" id="fecha_fin">
                                            <div class="form-group">
                                                <label for="name">Fecha de Salida</label>
                                                <input type="date" name="fecha_salida" class="form-control"> 
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-10">
                                            <div class="form-group">
                                                <label for="name">Describe brevemente el motivo de tu solicitud <span style="color:red;">(*)</span></label>
                                                <textarea class="form-control" name="descripcionSolicitud" required></textarea>
                                                <div class="invalid-feedback">
                                                    El campo descripción del motivo de la solicitud es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xs-12 col-sm-12 col-md-12">
                                        <div style="display:flex; justify-content:flex-end; gap:12px; align-items:center; width:100%;">
                                            <!-- <div>
                                                <button type="submit" class="btn btn-primary" style="background-color:#CEA845; border-color:#CEA845;" id="btn-add-citado">Agregar Citado</button>
                                            </div> -->
                                            <div style="display:flex; flex-direction:column; align-items:flex-end;">
                                                <!-- @if($citados > 0)
                                                    <a href="{{ route('seer.finalizaAuxP',$id) }}" id="btn-conclude" class="btn btn-success" style=" background-color:#CEA845;border-color:#CEA845;">Concluir solicitud</a>
                                                    <div id="conclude-warning" class="text-danger" style="display:none; margin-top:6px;">Guarde el citado antes de concluir</div>
                                                @endif -->
                                                <button type="submit" id="btn-conclude" class="btn btn-primary" style=" background-color:#CEA845;border-color:#CEA845;">Concluir solicitud</button>
                                            </div>
                                       </div>
                                    </div>    
                                </form>
                            </div>
                        <!--</div>-->
                    </div>
                </div>
            </div>
        </section>
    <script>
        // Función genérica para convertir todo el texto a mayúsculas
        function convertirAMayusculas() {
            const elementos = document.querySelectorAll('input[type="text"], textarea');

            elementos.forEach(elemento => {
                elemento.addEventListener('input', function() {
                    this.value = this.value.toUpperCase();
                });
                if (elemento.value) {
                    elemento.value = elemento.value.toUpperCase();
                }
            });
        }

        // Ejecutar la función cuando el DOM esté completamente cargado
        document.addEventListener('DOMContentLoaded', (event) => {
            convertirAMayusculas();
            (function () {
                'use strict'
                var forms = document.querySelectorAll('.needs-validation')
                Array.prototype.slice.call(forms)
                    .forEach(function (form) {
                        form.addEventListener('submit', function (event) {
                            if (!form.checkValidity()) {
                                event.preventDefault()
                                event.stopPropagation()
                            }
                            form.classList.add('was-validated')
                        }, false)
                    })
            })()
        });

        //Validacion de documentos
        document.querySelector('input[name="foto1"]').addEventListener('change', function () {
            const file = this.files[0];
            if (file && !file.type.startsWith('image/')) {
                alert('Solo se permiten imágenes');
                this.value = '';
            }
        });
    </script>
    <script>
        // Deshabilitar/activar el botón "Concluir solicitud" si hay inputs con datos sin guardar
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('form.needs-validation');
            const conclude = document.getElementById('btn-conclude');
            const concludeWarning = document.getElementById('conclude-warning');
            if (!form || !conclude) return;

            function updateConcludeState() {
                try {
                    const elements = form.querySelectorAll('input:not([type=hidden]):not([type=submit]), textarea, select');
                    let hasValue = false;
                    elements.forEach(function (el) {
                        if (!el) return;
                        if (el.tagName.toLowerCase() === 'select') {
                            if (el.value && el.value !== '') hasValue = true;
                            return;
                        }
                        const t = (el.type || '').toLowerCase();
                        if (t === 'checkbox' || t === 'radio') {
                            if (el.checked) hasValue = true;
                        } else if (t === 'file') {
                            if (el.files && el.files.length) hasValue = true;
                        } else {
                            if (el.value && el.value.trim() !== '') hasValue = true;
                        }
                    });

                    if (hasValue) {
                        conclude.classList.add('btn-disabled');
                        conclude.setAttribute('aria-disabled', 'true');
                        if (concludeWarning) concludeWarning.style.display = '';
                    } else {
                        conclude.classList.remove('btn-disabled');
                        conclude.removeAttribute('aria-disabled');
                        if (concludeWarning) concludeWarning.style.display = 'none';
                    }
                } catch (err) { console.warn('updateConcludeState', err); }
            }

            updateConcludeState();

            form.addEventListener('input', updateConcludeState);
            form.addEventListener('change', updateConcludeState);

            conclude.addEventListener('click', function (e) {
                if (conclude.getAttribute('aria-disabled') === 'true') {
                    e.preventDefault();
                    return false;
                }
            });
        });
    </script>
    <script>
        // Carga dinámica de municipios según el estado seleccionado (citados)
        document.addEventListener('DOMContentLoaded', function () {
            function cargarMunicipiosCitado(estadoId) {
                var $municipio = $('#municipio_citado');
                if (!$municipio.length) return;
                $municipio.html('<option value="">Cargando...</option>');
                if (!estadoId) {
                    $municipio.html('<option value="">Seleccione</option>');
                    return;
                }
                // Intentar la ruta API primero (con base_url), si falla intentar la ruta web
                $.get(base_url + '/api/munCitado/' + estadoId, function (data) {
                    var html = '<option value="">Seleccione</option>';
                    data.forEach(function (m) {
                        html += '<option value="' + m.id + '">' + m.nombre + '</option>';
                    });
                    $municipio.html(html);
                }).fail(function (jqXHR, textStatus, errorThrown) {
                    // Intentar ruta sin prefijo /api
                    $.get(base_url + '/munCitado/' + estadoId, function (data) {
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

            var base_url = "{{ url('') }}";

            var $estadoCitado = $('#estado_citado');
            if ($estadoCitado.length) {
                $estadoCitado.on('change', function () {
                    cargarMunicipiosCitado(this.value);
                });
                // Si ya viene seleccionado (edición/old), cargar municipios al inicio
                var inicial = $estadoCitado.val();
                if (inicial) cargarMunicipiosCitado(inicial);
            }
        });
    </script>
    <div id="crear_poder" style ="display: none;">
        <div>.</div>
        <div class="loader"></div>
    </div>

    
    <div id="submit_loader" style="display:none;">
        <div>.</div>
        <div class="loader"></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const concludeBtn = document.getElementById('btn-conclude');
            if (!concludeBtn) return;

            concludeBtn.addEventListener('click', function (e) {
                if (concludeBtn.getAttribute('aria-disabled') === 'true') {
                    e.preventDefault();
                    return;
                }

                $('#submit_loader').show();
            });
        });
    </script>

@section('scripts')
    <!--<script src="../public/assets/js/poderes/general.js"></script>-->


    <script src="../public/assets/js/jquery.min.js"></script>
    <script src="../public/assets/js/popper.min.js"></script>
    <script src="../public/assets/js/bootstrap.min.js"></script>
    <script src="../public/assets/js/sweetalert.min.js"></script>
    <script src="../public/assets/js/select2.min.js"></script>
    <script src="../public/assets/js/jquery.nicescroll.js"></script>
    <script src="../public/assets/js/moment.js"></script>

    <!-- Template JS File -->
    <script src="../public/assets/js/stisla.js"></script>
    <script src="../public/assets/js/scripts.js"></script>
    <script src="../public/assets/js/profile.js"></script>
    <script src="../public/assets/js/custom.js"></script>

    <script src="https://cdn.datatables.net/2.1.5/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.1.5/js/dataTables.bootstrap4.js"></script>
    @yield('page_js')


    @yield('scripts')
    <script src="../public/assets/js/validaciones.js"></script> 
    <script>
       /* 
        $(function(){
            $('#check_datos').on('change', mostrarDatos);
            console.log("llego");
        })

        function mostrarDatos(){
            var check = document.getElementById("div_datos_citado").style.display;
            console.log(check);
            if (check == "none") {
                document.getElementById("div_datos_citado").style.display = "block";
            }
            else{
                document.getElementById("div_datos_citado").style.display = "none";
            }
        }*/
        document.addEventListener('DOMContentLoaded', function () {
            //const selectTipo = document.getElementById('tipo');
            const nombreDiv = document.getElementById('tipoPersona_nombre');
            //const razonDiv = document.getElementById('tipoPersona_razon');
            const curpDiv = document.getElementById('campo_curp');
            //const form = document.querySelector('form.needs-validation');

            //function actualizarTipoPersona() {
            //if (!selectTipo) return;
            //const valor = selectTipo.value;

            /* if (nombreDiv) nombreDiv.style.display = (valor === 'Fisica') ? 'block' : 'none';
            if (curpDiv) curpDiv.style.display = (valor === 'Fisica') ? 'block' : 'none'; */
            if (nombreDiv) nombreDiv.style.display = 'block';
            if (curpDiv) curpDiv.style.display = 'block';
            //if (razonDiv) razonDiv.style.display = (valor === 'Moral') ? 'block' : 'none';
            //}

            // Reset required state
            const inputNombre = document.querySelector('input[name="nombre"]');
            const inputPrimer = document.querySelector('input[name="primer_apellido"]');
            const inputSegundo = document.querySelector('input[name="segundo_apellido"]');
            const inputRazon = document.querySelector('input[name="razon"]');

            // Persona física: pedir nombre y primer apellido (segundo opcional), mostrar CURP
            if (inputNombre) inputNombre.required = true;
            if (inputPrimer) inputPrimer.required = true;
            if (inputSegundo) inputSegundo.required = false;
            if (inputRazon) inputRazon.required = false;

            // Se elimina el listener del select ya que solo existe Tipo de persona Física

            /* if (selectTipo) {
                selectTipo.addEventListener('change', actualizarTipoPersona);
                actualizarTipoPersona(); 
            } */

            const form = document.querySelector('form.needs-validation');
            form.addEventListener('submit', function(e) {
                const checkLanguage = document.getElementById('check_lenguaje');
                if (checkLanguage.checked) {
                    const languageRequired = document.getElementById('lenguajeRequerido');
                    languageRequired.required = true;
                }
                else {
                    const languageRequired = document.getElementById('lenguajeRequerido');
                    languageRequired.required = false;
                }
            });

            /*  if (form) {
                form.addEventListener('submit', function (e) {
                    let esValido = true;
                    const tipo = selectTipo ? selectTipo.value : '';

                    const inputNombre = document.getElementById('nombre');
                    const inputApellido = document.querySelector('input[name="primer_apellido"]');
                    const inputRazon = document.getElementById('razon');
                    const inputCP = document.getElementById('cp');

                    [inputNombre, inputApellido, inputRazon, inputCP].forEach(el => el?.classList.remove('is-invalid'));

                    if (inputCP) {
                        if (inputCP.value.length !== 5) {
                            console.log("Fallo en CP");
                            inputCP.classList.add('is-invalid');
                            swal("Error", "El Código Postal debe tener 5 dígitos", "error");
                            esValido = false;
                        }
                    }

                    if (tipo === 'Fisica') {
                        if (!inputNombre?.value.trim() || !inputApellido?.value.trim()) {
                            console.log("Fallo en campos de Persona Física");
                            if (!inputNombre?.value.trim()) inputNombre?.classList.add('is-invalid');
                            if (!inputApellido?.value.trim()) inputApellido?.classList.add('is-invalid');
                            swal("Error", "Nombre y Apellido son obligatorios", "warning");
                            esValido = false;
                        }
                    } else if (tipo === 'Moral') {
                        if (!inputRazon?.value.trim()) {
                            console.log("Fallo en Razón Social");
                            inputRazon?.classList.add('is-invalid');
                            swal("Error", "La Razón Social es obligatoria", "warning");
                            esValido = false;
                        }
                    }

                    if (!esValido) {
                        e.preventDefault();
                        e.stopPropagation();
                    } else {
                    
                        if (typeof loading === 'function') loading();
                    }
                }, false);    
            } */
        });
    </script>
    <script>
        // Deshabilitar el botón "Concluir solicitud" hasta que todos los campos obligatorios estén llenos
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('form.needs-validation');
            const conclude = document.getElementById('btn-conclude');
            //const concludeWarning = document.getElementById('conclude-warning');
            if (!form || !conclude) return;

            function updateConcludeState() {
                try {
                    // Obtener todos los campos obligatorios (required)
                    const requiredFields = form.querySelectorAll('[required]');
                    let allFilled = true;

                    requiredFields.forEach(function (field) {
                        if (!field) return;
                        
                        const fieldType = (field.type || '').toLowerCase();
                        const tagName = field.tagName.toLowerCase();

                        if (tagName === 'select') {
                            if (!field.value || field.value === '') {
                                allFilled = false;
                            }
                        } else if (fieldType === 'checkbox' || fieldType === 'radio') {
                            if (!field.checked) {
                                allFilled = false;
                            }
                        } else if (fieldType === 'file') {
                            if (!field.files || field.files.length === 0) {
                                allFilled = false;
                            }
                        } else if (tagName === 'textarea') {
                            if (!field.value || field.value.trim() === '') {
                                allFilled = false;
                            }
                        } else {
                            if (!field.value || field.value.trim() === '') {
                                allFilled = false;
                            }
                        }
                    });

                    // Si NO todos los campos están llenos, deshabilitar el botón
                    if (!allFilled) {
                        conclude.classList.add('btn-disabled');
                        conclude.setAttribute('aria-disabled', 'true');
                        //conclude.style.pointerEvents = 'none';
                        //conclude.style.opacity = '0.5';
                    } else {
                        conclude.classList.remove('btn-disabled');
                        conclude.removeAttribute('aria-disabled');
                        //conclude.style.pointerEvents = 'auto';
                        //conclude.style.opacity = '1';
                    }
                } catch (err) { console.warn('updateConcludeState', err); }
            }
            updateConcludeState();

            form.addEventListener('input', updateConcludeState);
            form.addEventListener('change', updateConcludeState);

            conclude.addEventListener('click', function (e) {
                if (conclude.getAttribute('aria-disabled') === 'true') {
                    e.preventDefault();
                    return false;
                }
            });
        });
    </script>
@endsection
@endsection