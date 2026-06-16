@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Poderes</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="text-center">Editar representante legal</h3>

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

                            <!--Se realiza el envío de datos con formulario de Laravel Collective-->
                            <form class='needs-validation novalidate' id='form_roles' method='POST' action="{{route('poderes.update' ,$poder->idAbogado)}}" enctype='multipart/form-data'>
                                <input type="hidden" name="_method" value="PATCH">
                                <input type="hidden" name="id" value="{{ Auth::id() }}">
                                @csrf
                                    @if($poder->tipo == "Moral")
                                        <input type="hidden" name="tipoPersona" value="Moral">
                                        <div class="row">
                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <label for="name">Razón Social <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="razon" value="{{$poder->nombres_patronal}}" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                                        <div class="invalid-feedback">
                                                            El nombre es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="name">RFC <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="rfc_moral" value="{{$poder->rfc_patronal}}" class="form-control" minlength="13" maxlength="13" oninput="this.value = this.value.toUpperCase()" > 
                                                        <div class="invalid-feedback">
                                                            El nombre es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="name">Giro Comercial <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="giro_moral" value="{{$poder->giroComercial}}" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                                        <div class="invalid-feedback">
                                                            El nombre es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <h5 class="text-center">Domicilio laboral</h5>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="password">Entidad Federativa</label>
                                                        <select id="estado_moral" class="form-control" name="estado_moral" placeholder="*Entidad Federativa" >
                                                            <option value="">Seleccione</option>
                                                            @foreach($estados as $est)
                                                                <option value="{{$est['id']}}" {{ $poder["estado_patronal"] == $est["id"] ? "selected" : '' }}>{{$est['nombre']}}</option>
                                                            @endforeach
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El campo Estado es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="name">Nombre del Municipio o Alcaldía (*)</label>
                                                        <select id="municipio_moral" class="form-control" name="municipio_moral" placeholder="*Municipio" >
                                                            <option value="">Seleccione</option>
                                                            @foreach($municipios as $mun)
                                                                <option value="{{$mun['id']}}" {{ $poder["municipio_patronal"] == $mun["id"] ? "selected" : '' }}>{{$mun['nombre']}}</option>
                                                            @endforeach
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El campo municipio o alcaldía es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="name">Tipo de Vialidad (*)</label>
                                                        <select name="vialidad_Moral" id="vialidad_Moral" class="form-control" placeholder="*Vialidad" >
                                                            <option value="">SELECCIONE</option>
                                                            <option value="AMPLIACIÓN"  {{ $poder["tipo_vialidad_patronal"] == "AMPLIACIÓN" ? "selected" : '' }}>Ampliación</option>
                                                            <option value="ANDADOR"     {{ $poder["tipo_vialidad_patronal"] == "ANDADOR" ? "selected" : '' }}>Andador</option>
                                                            <option value="AUTOPISTA"   {{ $poder["tipo_vialidad_patronal"] == "AUTOPISTA" ? "selected" : '' }}>Autopista</option>
                                                            <option value="AVENIDA"     {{ $poder["tipo_vialidad_patronal"] == "AVENIDA" ? "selected" : '' }}>Avenida</option>
                                                            <option value="BOULEVARD"   {{ $poder["tipo_vialidad_patronal"] == "BOULEVARD" ? "selected" : '' }}>Boulevard</option>
                                                            <option value="CALLE"       {{ $poder["tipo_vialidad_patronal"] == "CALLE" ? "selected" : '' }}>Calle</option>
                                                            <option value="CALLEJÓN"    {{ $poder["tipo_vialidad_patronal"] == "CALLEJÓN" ? "selected" : '' }}>Callejón</option>
                                                            <option value="CALZADA"     {{ $poder["tipo_vialidad_patronal"] == "CALZADA" ? "selected" : '' }}>Calzada</option>
                                                            <option value="CARRETERA"   {{ $poder["tipo_vialidad_patronal"] == "CARRETERA" ? "selected" : '' }}>Carretera</option>
                                                            <option value="CERRADA"     {{ $poder["tipo_vialidad_patronal"] == "CERRADA" ? "selected" : '' }}>Cerrada</option>
                                                            <option value="CIRCUITO"    {{ $poder["tipo_vialidad_patronal"] == "CIRCUITO" ? "selected" : '' }}>Circuito</option>
                                                            <option value="CIRCUNVALACIÓN"  {{ $poder["tipo_vialidad_patronal"] == "CIRCUNVALACIÓN" ? "selected" : '' }}>Circunvalación</option>
                                                            <option value="CONTINUACIÓN"    {{ $poder["tipo_vialidad_patronal"] == "CONTINUACIÓN" ? "selected" : '' }}>Continuación</option>
                                                            <option value="CORREDOR"    {{ $poder["tipo_vialidad_patronal"] == "CORREDOR" ? "selected" : '' }}>Corredor</option>
                                                            <option value="DIAGONAL"    {{ $poder["tipo_vialidad_patronal"] == "DIAGONAL" ? "selected" : '' }}>Diagonal</option>
                                                            <option value="EJE VIAL"    {{ $poder["tipo_vialidad_patronal"] == "EJE VIAL" ? "selected" : '' }}>Eje vial</option>
                                                            <option value="PERIFÉRICO"  {{ $poder["tipo_vialidad_patronal"] == "PERIFÉRICO" ? "selected" : '' }}>Periférico</option>
                                                            <option value="PROLONGACIÓN" {{ $poder["tipo_vialidad_patronal"] == "PROLONGACIÓN" ? "selected" : '' }}>Prolongación</option>
                                                            <option value="RETORNO"     {{ $poder["tipo_vialidad_patronal"] == "RETORNO" ? "selected" : '' }}>Retorno</option>
                                                            <option value="VIADUCTO"    {{ $poder["tipo_vialidad_patronal"] == "VIADUCTO" ? "selected" : '' }}>Viaducto</option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El campo vialidad es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="name">Nombre de la Vialidad (*)</label>
                                                        <input type="text" name="vialidad_calleMoral" value="{{$poder->vialidad_patronal}}" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                                        <div class="invalid-feedback">
                                                            El campo vialidad o calle es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="">Colonia <span style="color:red;">(*)</span></label>
                                                        <input type="text" class="form-control" name="colonia_moral" value="{{$poder->colonia_patronal}}" oninput="this.value = this.value.toUpperCase()" >
                                                        <div class="invalid-feedback">
                                                            El domicilio es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="">Núm. Ext. <span style="color:red;">(*)</span></label>
                                                        <input type="text" class="form-control" value="{{$poder->num_ext_patronal}}" name="num_ext_moral"  oninput="this.value = this.value.toUpperCase()" >
                                                        <div class="invalid-feedback">
                                                            El domicilio es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="">Núm. Int.</label>
                                                        <input type="text" class="form-control" value="{{$poder->mun_int_patronal}}" name="num_int" oninput="this.value = this.value.toUpperCase()">
                                                        <div class="invalid-feedback">
                                                            El domicilio es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="">Código Postal <span style="color:red;">(*)</span></label>
                                                        <input type="text" class="form-control" name="cp_moral" value="{{$poder->cp_patronal}}"  minlength="5" maxlength="5" oninput="this.value = this.value.toUpperCase()" >
                                                        <div class="invalid-feedback">
                                                            El domicilio es obligatoria.
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
                                                        <input type="text" name="nombre_representante_Moral" value="{{$poder->nombre_representante}}" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                                        <div class="invalid-feedback">
                                                            El nombre es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="name">Primer apellido <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="primer_Moral" value="{{$poder->primer_apellido_representante}}" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                                        <div class="invalid-feedback">
                                                            El primer apellido es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="name">Segundo apellido <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="segundo_Moral" value="{{$poder->segundo_apellido_representante}}" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                                        <div class="invalid-feedback">
                                                            El segundo apellido es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>  
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="">CURP</label>
                                                        <input type="text" class="form-control" name="curp_moral" value="{{$poder->curp_representante}}" minlength="18" maxlength="18" oninput="this.value = this.value.toUpperCase()" >
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
                                                            <option value="Femenino"    {{ $poder["sexo_representante"] == "Femenino" ? "selected" : '' }}>Femenino</option>
                                                            <option value="Masculino"   {{ $poder["sexo_representante"] == "Masculino" ? "selected" : '' }}>Masculino</option>
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
                                                        <input type="email" class="form-control" name="correo_Moral" value="{{$poder->correo_representante}}" >
                                                        <div class="invalid-feedback">
                                                            El Correo electrónico es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="">Teléfono <span style="color:red;">(*)</label>
                                                        <input type="text" class="form-control" name="telefono_Moral" value="{{$poder->numero_representante}}" maxlength="10" pattern="[0-9]+" >
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
                                                            <option value="Carta Poder"                 {{ $poder["tipo_documento_representante"] == "Carta Poder" ? "selected" : '' }}>Carta Poder</option>
                                                                <option value="Instrumento Notarial"    {{ $poder["tipo_documento_representante"] == "Instrumento Notarial" ? "selected" : '' }}>Instrumento Notarial</option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El campo es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="">Fecha expedición <span style="color:red;">(*)</span></label>
                                                        <input type="date" class="form-control" aria-describedby="basic-addon1" name="fecha_expedicicion_Moral" value="{{$poder->fechaRegistro}}">
                                                        <div class="invalid-feedback">
                                                            La fecha es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="">Fecha vigencia</label>
                                                        <input type="date" class="form-control" aria-describedby="basic-addon1" name="fecha_vigencia_Moral" value="{{$poder->fechaVigencia}}">
                                                        <div class="invalid-feedback">
                                                            La fecha es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>  
                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <label for="">Descripción del documento que acredite la personaria</label>
                                                        <textarea class="form-control" aria-describedby="basic-addon1" name="descripcion_Moral">{{$poder->descipcion_poder}}</textarea>
                                                        <div class="invalid-feedback">
                                                            La descripción es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="name">Identificación Oficial  <span style="color:red;">(*)</span></label>
                                                        <select name="tipo_identificacion_Moral" class="form-control">
                                                            <option value="">Seleccione el tipo de indentificación</option>
                                                            <option value="Credencial de elector" {{ $poder["tipo_identificacion"] == "Credencial de elector" ? "selected" : '' }} >Credencial de Elector</option>
                                                            <option value="Pasaporte" {{ $poder["tipo_identificacion"] == "Pasaporte" ? "selected" : '' }}>Pasaporte</option>
                                                            <option value="Cédula profesional" {{ $poder["tipo_identificacion"] == "Cédula profesional" ? "selected" : '' }}>Cédula Profesional</option>
                                                            <option value="Licencia de conducir" {{ $poder["tipo_identificacion"] == "Licencia de conducir" ? "selected" : '' }}>Licencia de Conducir</option>
                                                            <option value="Credencial de inapam" {{ $poder["tipo_identificacion"] == "Credencial de inapam" ? "selected" : '' }}>Credencial de INAPAM</option>
                                                            <option value="Cartilla militar" {{ $poder["tipo_identificacion"] == "Cartilla militar" ? "selected" : '' }}>Cartilla Militar</option>
                                                            <option value="Documento migratorio" {{ $poder["tipo_identificacion"] == "Documento migratorio" ? "selected" : '' }}>Documento Migratorio</option>
                                                            <option value="Constancia de identidad" {{ $poder["tipo_identificacion"] == "Constancia de identidad" ? "selected" : '' }}>Constancia de Identidad</option>
                                                            <option value="Otro" {{ $poder["tipo_identificacion"] == "Otro" ? "selected" : '' }}>Otros</option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            Este campo identificación es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6"> 
                                                    <div class="form-group">
                                                        <label for="name">Núm de identificación <span style="color:red;">(*)</span> <span data-bs-toggle="modal" data-bs-target="#helpModal" style="cursor: pointer;">❓</span></label>
                                                        <input type="text" name="num_identificacion_Moral" class="form-control" value="{{$poder->num_identificacion}}" oninput="this.value = this.value.toUpperCase()"> 
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
                                                        <label>*Acta Constitutiva</label><br>
                                                        <input type="file" name="documentoIne_Moral" id="documentoIne_Moral" class="form-control" accept=".pdf" >
                                                        <a target="_blank" class="btn btn-primary" href="../../storage/app/documentos_abogados/{{$poder->ineDocumento}}">Existente</a>
                                                        <div class="invalid-feedback">
                                                            La Identificación es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label>*Identificación del Representante Legal</label><br>
                                                        <input type="file" name="documentoRepresentacion_Moral" id="documentoRepresentacion_Moral" class="form-control" accept=".pdf" >
                                                        @if($poder->representacionDocumento != NULL)
                                                            <a target="_blank" class="btn btn-primary" href="../../storage/app/documentos_abogados/{{$poder->representacionDocumento}}">Existente</a>
                                                        @endif
                                                        <div class="invalid-feedback">
                                                            El documento de representación es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label>*Documento que acredite la personería</label><br>
                                                        <input type="file" name="documentoPoder" id="documentoPoder" class="form-control" accept=".pdf">
                                                        @if($poder->cedulaDocumento != NULL)
                                                            <a target="_blank" class="btn btn-primary" href="../../storage/app/documentos_abogados/{{$poder->cedulaDocumento}}">Existente</a>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label>Anexo (Documentos Complementarios)</label><br>
                                                        <input type="file" name="documentoAnexo" class="form-control" accept=".pdf">
                                                        @if($poder->anexo_documeto != "Sin anexo")
                                                            <a target="_blank" class="btn btn-primary" href="../../storage/app/documentos_abogados/{{$poder->anexo_documeto}}">Existente</a>
                                                        @endif
                                                        
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="name">Validación</label>
                                                        <select name="validacion" class="form-control">
                                                            <option value="">Seleccionar</option>
                                                            <option value="Validado"    {{ $poder["estatus"] == "Validado" ? "selected" : '' }}>Validar</option>
                                                            <option value="Pendiente"  >Rechazar</option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El campo es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>  
                                        </div>
                                    @elseif($poder->tipo == "Fisica")
                                        <input type="hidden" name="tipoPersona" value="Fisica">
                                            <div class="row">
                                                <div class="col-xs-12 col-sm-12 col-md-4">
                                                    <div class="form-group">
                                                        <label for="name">Nombre(s) del Empleador<span style="color:red;">(*)</span></label>
                                                        <input type="text" name="nombre_pF" value="{{$poder->nombres_patronal}}" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                                        <div class="invalid-feedback">
                                                            El nombre es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-4">
                                                    <div class="form-group">
                                                        <label for="name">Primer apellido <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="primero_PF" value="{{$poder->primer_apellido_patronal}}" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                                        <div class="invalid-feedback">
                                                            El primer apellido es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-4">
                                                    <div class="form-group">
                                                        <label for="name">Segundo apellido <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="segundo_Pf" value="{{$poder->segundo_apellido_patronal}}"  class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                                        <div class="invalid-feedback">
                                                            El segundo apellido es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                 <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="">CURP <span style="color:red;">(*)</span></label>
                                                        <input type="text" class="form-control" name="curp_PF" value="{{$poder->curp_patronal}}" minlength="18" maxlength="18" oninput="this.value = this.value.toUpperCase()" >
                                                        <div class="invalid-feedback">
                                                            La CURP es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="name">RFC <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="RFC_pF" value="{{$poder->rfc_patronal}}" class="form-control" minlength="13" maxlength="13" oninput="this.value = this.value.toUpperCase()" > 
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
                                                            <option value="Femenino"  {{ $poder["sexo_patronal"] == "Femenino" ? "selected" : '' }}>Femenino</option>
                                                            <option value="Masculino" {{ $poder["sexo_patronal"] == "Masculino" ? "selected" : '' }}>Masculino</option>
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
                                                        <input type="text" name="giro_pF" value="{{$poder->giroComercial}}" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
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
                                                        <label for="">Correo electrónico</label>
                                                        <input type="email" class="form-control" value="{{$poder->email_patronal}}" name="correo_pF" id="electrónico_pF" >
                                                        <div class="invalid-feedback">
                                                            El Correo electrónico es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="">Teléfono</label>
                                                            <input type="text" class="form-control"  name="telefono_PF" value="{{$poder->telefono_patronal}}" maxlength="10" pattern="[0-9]+" >
                                                        <div class="invalid-feedback">
                                                            El telefono es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <h5 class="text-center">Domicilio laboral</h5>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="password">Entidad Federativa</label>
                                                        <select id="estado_pF" class="form-control" name="estado_pF" placeholder="*Entidad Federativa" >
                                                            <option value="">Seleccione</option>
                                                            @foreach($estados as $est)
                                                                <option value="{{$est['id']}}" {{ $est["id"] == $poder["estado_patronal"] ? "selected" : '' }}>{{$est['nombre']}}</option>
                                                            @endforeach
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El campo Estado es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="name">Nombre del Municipio o Alcaldía (*)</label>
                                                        <select id="municipio_pF" class="form-control" name="municipio_pF" placeholder="*Municipio" >
                                                            <option value="">Seleccione</option>
                                                            @foreach($municipios as $mun)
                                                                <option value="{{$mun['id']}}" {{ $mun["id"] == $poder["municipio_patronal"] ? "selected" : '' }}>{{$mun['nombre']}}</option>
                                                            @endforeach
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El campo municipio o alcaldía es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="name">Tipo de Vialidad (*)</label>
                                                        <select name="vialidad_pF" id="vialidad_pF" class="form-control" placeholder="*Vialidad" >
                                                            <option value="">SELECCIONE</option>
                                                            <option value="AMPLIACIÓN"  {{ $poder["tipo_vialidad_patronal"] == "AMPLIACIÓN" ? "selected" : '' }}>Ampliación</option>
                                                            <option value="ANDADOR"     {{ $poder["tipo_vialidad_patronal"] == "ANDADOR" ? "selected" : '' }}>Andador</option>
                                                            <option value="AUTOPISTA"   {{ $poder["tipo_vialidad_patronal"] == "AUTOPISTA" ? "selected" : '' }}>Autopista</option>
                                                            <option value="AVENIDA"     {{ $poder["tipo_vialidad_patronal"] == "AVENIDA" ? "selected" : '' }}>Avenida</option>
                                                            <option value="BOULEVARD"   {{ $poder["tipo_vialidad_patronal"] == "BOULEVARD" ? "selected" : '' }}>Boulevard</option>
                                                            <option value="CALLE"       {{ $poder["tipo_vialidad_patronal"] == "CALLE" ? "selected" : '' }}>Calle</option>
                                                            <option value="CALLEJÓN"    {{ $poder["tipo_vialidad_patronal"] == "CALLEJÓN" ? "selected" : '' }}>Callejón</option>
                                                            <option value="CALZADA"     {{ $poder["tipo_vialidad_patronal"] == "CALZADA" ? "selected" : '' }}>Calzada</option>
                                                            <option value="CARRETERA"   {{ $poder["tipo_vialidad_patronal"] == "CARRETERA" ? "selected" : '' }}>Carretera</option>
                                                            <option value="CERRADA"     {{ $poder["tipo_vialidad_patronal"] == "CERRADA" ? "selected" : '' }}>Cerrada</option>
                                                            <option value="CIRCUITO"    {{ $poder["tipo_vialidad_patronal"] == "CIRCUITO" ? "selected" : '' }}>Circuito</option>
                                                            <option value="CIRCUNVALACIÓN"  {{ $poder["tipo_vialidad_patronal"] == "CIRCUNVALACIÓN" ? "selected" : '' }}>Circunvalación</option>
                                                            <option value="CONTINUACIÓN"    {{ $poder["tipo_vialidad_patronal"] == "CONTINUACIÓN" ? "selected" : '' }}>Continuación</option>
                                                            <option value="CORREDOR"    {{ $poder["tipo_vialidad_patronal"] == "CORREDOR" ? "selected" : '' }}>Corredor</option>
                                                            <option value="DIAGONAL"    {{ $poder["tipo_vialidad_patronal"] == "DIAGONAL" ? "selected" : '' }}>Diagonal</option>
                                                            <option value="EJE VIAL"    {{ $poder["tipo_vialidad_patronal"] == "EJE VIAL" ? "selected" : '' }}>Eje vial</option>
                                                            <option value="PERIFÉRICO"  {{ $poder["tipo_vialidad_patronal"] == "PERIFÉRICO" ? "selected" : '' }}>Periférico</option>
                                                            <option value="PROLONGACIÓN" {{ $poder["tipo_vialidad_patronal"] == "PROLONGACIÓN" ? "selected" : '' }}>Prolongación</option>
                                                            <option value="RETORNO"     {{ $poder["tipo_vialidad_patronal"] == "RETORNO" ? "selected" : '' }}>Retorno</option>
                                                            <option value="VIADUCTO"    {{ $poder["tipo_vialidad_patronal"] == "VIADUCTO" ? "selected" : '' }}>Viaducto</option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El campo vialidad es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="name">Nombre de la Vialidad <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="vialidad_calle_pF" value="{{$poder->vialidad_patronal}}" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                                        <div class="invalid-feedback">
                                                            El campo vialidad o calle es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="">Colonia <span style="color:red;">(*)</span></label>
                                                        <input type="text" class="form-control" name="colonia_pF" value="{{$poder->colonia_patronal}}" oninput="this.value = this.value.toUpperCase()" >
                                                        <div class="invalid-feedback">
                                                            El domicilio es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="">Núm. Ext. <span style="color:red;">(*)</span></label>
                                                        <input type="text" class="form-control" name="num_ext_pF" value="{{$poder->num_ext_patronal	}}" oninput="this.value = this.value.toUpperCase()" >
                                                        <div class="invalid-feedback">
                                                            El domicilio es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="">Núm. Int.</label>
                                                        <input type="text" class="form-control" name="num_int_pF" value="{{$poder->mun_int_patronal	}}"  oninput="this.value = this.value.toUpperCase()">
                                                        <div class="invalid-feedback">
                                                            El domicilio es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="">Código Postal <span style="color:red;">(*)</span></label>
                                                        <input type="text" class="form-control"  name="cp_pF" value="{{$poder->cp_patronal	}}" minlength="5" maxlength="5" oninput="this.value = this.value.toUpperCase()" >
                                                        <div class="invalid-feedback">
                                                            El domicilio es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>

                                            @if($poder->reprecentante == "Si")
                                                <input type="hidden" name="representate" value="Si">
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
                                                        <label for="name">Nombre(s) del representante<span style="color:red;">(*)</span></label>
                                                        <input type="text" name="nombre_representante_pF" value="{{$poder->nombre_representante	}}" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                                        <div class="invalid-feedback">
                                                            El nombre es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="name">Primer apellido <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="primer_representante_pF" value="{{$poder->primer_apellido_representante	}}" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                                        <div class="invalid-feedback">
                                                            El primer apellido es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="name">Segundo apellido <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="segundo_representante_pF" value="{{$poder->segundo_apellido_representante	}}" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                                        <div class="invalid-feedback">
                                                            El segundo apellido es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>  
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="">CURP</label>
                                                        <input type="text" class="form-control" name="curp_representante_pF" value="{{$poder->curp_representante }}" minlength="18" maxlength="18" oninput="this.value = this.value.toUpperCase()" >
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
                                                            <option value="Femenino"    {{ $poder["sexo_representante"] == "Femenino" ? "selected" : '' }}>Femenino</option>
                                                            <option value="Masculino"   {{ $poder["sexo_representante"] == "Masculino" ? "selected" : '' }}>Masculino</option>
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
                                                        <input type="email" class="form-control" name="correo_representante_pF" value="{{$poder->correo_representante }}">
                                                        <div class="invalid-feedback">
                                                            El Correo electrónico es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="">Teléfono <span style="color:red;">(*)</span></label>
                                                        <input type="text" class="form-control"  name="telefono_representante_pF" value="{{$poder->numero_representante }}" maxlength="10" pattern="[0-9]+" >
                                                        <div class="invalid-feedback">
                                                            El telefono es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                        <div class="form-group">
                                                            <label for="name">Identificación Oficial<span style="color:red;">(*)</span></label>
                                                            <select  name="tipo_identificacion_pFCR" class="form-control">
                                                                <option value="">Seleccione el tipo de indentificación</option>
                                                                <option value="Credencial de elector" {{ $poder["tipo_identificacion"] == "Credencial de elector" ? "selected" : '' }} >Credencial de Elector</option>
                                                                <option value="Pasaporte" {{ $poder["tipo_identificacion"] == "Pasaporte" ? "selected" : '' }}>Pasaporte</option>
                                                                <option value="Cédula profesional" {{ $poder["tipo_identificacion"] == "Cédula profesional" ? "selected" : '' }}>Cédula Profesional</option>
                                                                <option value="Licencia de conducir" {{ $poder["tipo_identificacion"] == "Licencia de conducir" ? "selected" : '' }}>Licencia de Conducir</option>
                                                                <option value="Credencial de inapam" {{ $poder["tipo_identificacion"] == "Credencial de inapam" ? "selected" : '' }}>Credencial de INAPAM</option>
                                                                <option value="Cartilla militar" {{ $poder["tipo_identificacion"] == "Cartilla militar" ? "selected" : '' }}>Cartilla Militar</option>
                                                                <option value="Documento migratorio" {{ $poder["tipo_identificacion"] == "Documento migratorio" ? "selected" : '' }}>Documento Migratorio</option>
                                                                <option value="Constancia de identidad" {{ $poder["tipo_identificacion"] == "Constancia de identidad" ? "selected" : '' }}>Constancia de Identidad</option>
                                                                <option value="Otro" {{ $poder["tipo_identificacion"] == "Otro" ? "selected" : '' }}>Otros</option>
                                                            </select>
                                                            <div class="invalid-feedback">
                                                                Este campo identificación es obligatorio.
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-12 col-sm-12 col-md-6"> 
                                                        <div class="form-group">
                                                            <label for="name">Núm de identificación <span style="color:red;">(*)</span> <span data-bs-toggle="modal" data-bs-target="#helpModal" style="cursor: pointer;">❓</span></label>
                                                            <input type="text" name="num_identificacion_pFCR" class="form-control" oninput="this.value = this.value.toUpperCase()"  value="{{$poder->num_identificacion }}"> 
                                                            <div class="invalid-feedback">
                                                                El campo núm. de identificación es obligatorio.
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
                                                            <option value="Carta Poder" {{ $poder["tipo_documento_representante"] == "Carta Poder" ? "selected" : '' }}>Carta Poder</option>
                                                            <option value="Instrumento Notarial" {{ $poder["tipo_documento_representante"] == "Instrumento Notarial" ? "selected" : '' }}>Instrumento Notarial</option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El campo es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-4">
                                                    <div class="form-group">
                                                        <label for="">Fecha expedición <span style="color:red;">(*)</span></label>
                                                        <input type="date" class="form-control" name="fecha_expedicion_pF" value="{{$poder->fechaRegistro }}">
                                                        <div class="invalid-feedback">
                                                            La fecha es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-4">
                                                    <div class="form-group">
                                                        <label for="">Fecha vigencia</label>
                                                        <input type="date" class="form-control" name="fecha_vigencia_pF" value="{{$poder->fechaVigencia }}">
                                                        <div class="invalid-feedback">
                                                            La fecha es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>  
                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <label for="">Descripción del documento que acredite la personaria</label>
                                                        <textarea class="form-control" aria-describedby="basic-addon1" name="descripcion_pF">{{$poder->descipcion_poder}}</textarea>
                                                        <div class="invalid-feedback">
                                                            La descripción es obligatoria.
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
                                                        <label>*Identificación del Empleador</label><br>
                                                        <input type="file" name="documentoIne_pF"  class="form-control" accept=".pdf" >
                                                        <a target="_blank" class="btn btn-primary" href="../../storage/app/documentos_abogados/{{$poder->ineDocumento}}">Existente</a>
                                                        <div class="invalid-feedback">
                                                            La Identificación es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label>*Identificación del Representante Legal</label><br>
                                                        <input type="file" name="documentoRepresentacion_pF"  class="form-control" accept=".pdf" >
                                                        @if($poder->representacionDocumento != NULL)
                                                            <a target="_blank" class="btn btn-primary" href="../../storage/app/documentos_abogados/{{$poder->representacionDocumento}}">Existente</a>
                                                        @endif
                                                        <div class="invalid-feedback">
                                                            El documento de representación es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label>*Documento que acredite la personería</label><br>
                                                        <input type="file" name="documentoPoder_pF"class="form-control" accept=".pdf">
                                                        @if($poder->cedulaDocumento != NULL)
                                                            <a target="_blank" class="btn btn-primary" href="../../storage/app/documentos_abogados/{{$poder->cedulaDocumento}}">Existente</a>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label>Anexo (Documentos Complementarios)</label><br>
                                                        <input type="file" name="documentoAnexo_pF" class="form-control" accept=".pdf">
                                                        @if($poder->anexo_documeto != "Sin anexo")
                                                            <a target="_blank" class="btn btn-primary" href="../../storage/app/documentos_abogados/{{$poder->anexo_documeto}}">Existente</a>
                                                        @endif
                                                        
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="name">Validación</label>
                                                        <select name="validacion" class="form-control">
                                                            <option value="">Seleccionar</option>
                                                            <option value="Validado"    {{ $poder["estatus"] == "Validado" ? "selected" : '' }}>Validar</option>
                                                            <option value="Pendiente"  >Rechazar</option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El campo es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <input type="hidden" name="representate" value="No">
                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <h5 class="text-center" style="color:#CEA845">Cargar Documentos</h5>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                                        <div class="form-group">
                                                            <label for="name">Identificación Oficial <span style="color:red;">(*)</span></label>
                                                            <select  name="tipo_identificacion_pF" class="form-control">
                                                                <option value="">Seleccione el tipo de indentificación</option>
                                                                <option value="Credencial de elector" {{ $poder["tipo_identificacion"] == "Credencial de elector" ? "selected" : '' }} >Credencial de Elector</option>
                                                                <option value="Pasaporte" {{ $poder["tipo_identificacion"] == "Pasaporte" ? "selected" : '' }}>Pasaporte</option>
                                                                <option value="Cédula profesional" {{ $poder["tipo_identificacion"] == "Cédula profesional" ? "selected" : '' }}>Cédula Profesional</option>
                                                                <option value="Licencia de conducir" {{ $poder["tipo_identificacion"] == "Licencia de conducir" ? "selected" : '' }}>Licencia de Conducir</option>
                                                                <option value="Credencial de inapam" {{ $poder["tipo_identificacion"] == "Credencial de inapam" ? "selected" : '' }}>Credencial de INAPAM</option>
                                                                <option value="Cartilla militar" {{ $poder["tipo_identificacion"] == "Cartilla militar" ? "selected" : '' }}>Cartilla Militar</option>
                                                                <option value="Documento migratorio" {{ $poder["tipo_identificacion"] == "Documento migratorio" ? "selected" : '' }}>Documento Migratorio</option>
                                                                <option value="Constancia de identidad" {{ $poder["tipo_identificacion"] == "Constancia de identidad" ? "selected" : '' }}>Constancia de Identidad</option>
                                                                <option value="Otro" {{ $poder["tipo_identificacion"] == "Otro" ? "selected" : '' }}>Otros</option>
                                                            </select>
                                                            <div class="invalid-feedback">
                                                                Este campo identificación es obligatorio.
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-12 col-sm-12 col-md-6"> 
                                                        <div class="form-group">
                                                            <label for="name">Núm de identificación <span style="color:red;">(*)</span> <span data-bs-toggle="modal" data-bs-target="#helpModal" style="cursor: pointer;">❓</span></label>
                                                            <input type="text" name="num_identificacion_pF" class="form-control" oninput="this.value = this.value.toUpperCase()" value="{{$poder->num_identificacion }}"> 
                                                            <div class="invalid-feedback">
                                                                El campo núm. de identificación es obligatorio.
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                                        <div class="form-group">
                                                            <label>*Identificación Oficial</label><br>
                                                            <input type="file" name="documentoIne_pFSR" id="documentoIne_pFSR" class="form-control" accept=".pdf" >
                                                            <a target="_blank" class="btn btn-primary" href="../../storage/app/documentos_abogados/{{$poder->ineDocumento}}">Existente</a>

                                                            <div class="invalid-feedback">
                                                                La Identificación es obligatoria.
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                                        <div class="form-group">
                                                            <label>Anexo (Documentos Complementarios)</label><br>
                                                            <input type="file" name="documentoAnexo_pFSR" class="form-control" accept=".pdf">
                                                            @if($poder->anexo_documeto != "Sin anexo")
                                                                <a target="_blank" class="btn btn-primary" href="../../storage/app/documentos_abogados/{{$poder->anexo_documeto}}">Existente</a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-12 col-sm-12 col-md-3">
                                                        <div class="form-group">
                                                            <label for="name">Validación</label>
                                                            <select name="validacion" class="form-control">
                                                                <option value="">Seleccionar</option>
                                                                <option value="Validado"    {{ $poder["estatus"] == "Validado" ? "selected" : '' }}>Validar</option>
                                                                <option value="Pendiente"  >Rechazar</option>
                                                            </select>
                                                            <div class="invalid-feedback">
                                                                El campo es obligatorio.
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                </div>
                                            @endif

                                        </div>
                                    @endif
                                    
                                <button type="submit" class="btn btn-success">Guardar</button>
                                
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

<div id="crear_poder" style ="display: none;">
    <div>.</div>
    <div class="loader"></div>
</div>

@section('scripts')
    <script src="../public/assets/js/poderes/general.js"></script>
@endsection
