@extends('layouts.app')
@section('title', 'Caso de Excepción')

@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Caso de Excepción</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="text-center">Atender excepciones</h3>
                             
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
                            <form class='needs-validation novalidate' id='form_roles' method='POST' action="{{route('guardar_excepcion')}}">
                                <input type="hidden" name="id" value="{{$recepcion->id}}">
                                @csrf
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label for="name">Tipo de situación que enfrenta<span style="color:red;">(*)</span></label>
                                            <select id="situacion" name= 'tipo_caso' class="form-control" required>
                                                <option value="">Selecciona</option>
                                                <option value="Discriminación" {{ $recepcion->tipo_caso == 'Discriminación' ? 'selected' : '' }}>He sido objeto de discriminación</option>
                                                <option value="Acoso u hostigamiento sexual" {{ $recepcion->tipo_caso == 'Acoso u hostigamiento sexual' ? 'selected' : '' }}>He recibido acoso sexual por parte de un superior jerárquico o de un compañero de trabajo</option>
                                                <option value="Riesgo o accidente">He sufrido un riesgo o accidente de trabajo.</option>
                                                <option value="Malos tratos o violencia">He sido objeto de malos tratos o violencia laboral</option>
                                                <option value="No aplica" {{ $recepcion->tipo_caso == 'No aplica' ? 'selected' : '' }}>No aplica</option>
                                            </select>
                                        </div>
                                        <div class="invalid-feedback">
                                            El campo es obligatorio.
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-6" id="motivo" style="display:none;">
                                        <div class="form-group">
                                            <label for="name">Señale el motivo:</label>
                                            <input id="motivo" name="motivo" type="text" oninput="this.value = this.value.toUpperCase()" class="form-control"">
                                        </div>
                                    </div>
                                    

                                    
                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label for="name">Grupos vulnerables<span style="color:red;">(*)</span></label>
                                            <select name="vulnerables" class="form-control" required>
                                                <option value="">Seleccione</option>
                                                <option value="Menores de edad" {{ $recepcion->vulnerables == 'Menores de edad' ? 'selected' : '' }}>Menores de edad</option>
                                                <option value="Adultos mayores" {{ $recepcion->vulnerables == 'Adultos mayores' ? 'selected' : '' }}>Adultos mayores</option>
                                                <option value="Discapacidad" {{ $recepcion->vulnerables == 'Discapacidad' ? 'selected' : '' }}>Personas con discapacidad</option>
                                                <option value="Población indígena" {{ $recepcion->vulnerables == 'Población indígena' ? 'selected' : '' }}>Población indígena</option>
                                                <option value="Personas Migrantes" {{ $recepcion->vulnerables == 'Personas Migrantes' ? 'selected' : '' }}>Personas Migrantes</option>
                                                <option value='LGBTTTIQ' {{ $recepcion->vulnerables == 'LGBTTTIQ' ? 'selected' : '' }}>LGBTTTIQ+</option>
                                                <option value="No aplica" {{ $recepcion->vulnerables == 'No aplica' ? 'selected' : '' }}>No aplica</option>
                                            </select>
                                            <div class="invalid-feedback">
                                                El campo es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label for="name">Frecuencia con la que han sucedido los hechos<span style="color:red;">(*)</span></label>
                                            <select name="frecuencia" class="form-control" required>
                                                <option value="">Selecciona</option>
                                                <option value="Una vez">Una vez</option>
                                                <option value="Varias veces">Varias veces</option>
                                                <option value="Continua">De manera continua, hasta la fecha atual</option>
                                            </select>
                                            <div class="invalid-feedback">
                                                El campo es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                        <label for="name">Cambios que se dieron en su situación laboral despues de los hechos<span style="color:red;">(*)</span></label>
                                        <select id='cambios' class="form-control" required>
                                            <option value="">Selecciona</option>
                                            <option value="Sigue igual">Sigue igual</option>
                                            <option value="Tension estres incomodidad">Tension, estrés e incomodidad en el área de trabajo</option>
                                            <option value="Cambio area">Le cambiaron de área</option>
                                            <option value="otro">Otro</option>

                                        </select>
                                        <div class="invalid-feedback">
                                            El campo es obligatorio.
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-6" id="otro_cambio" style="display:none;">
                                        <div class="form-group">
                                            <label for="name">Especifique el cambio en la situación laboral</label>
                                            <input id="tipo_cambio" type="text" oninput="this.value = this.value.toUpperCase()" class="form-control">
                                        </div>
                                    </div>
                                    <input type="hidden" name="situacion_laboral" id="situacion_laboral">

                                    
                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                        <label for="name">Dependencia a la que se canalizo<span style="color:red;">(*)</span></label>
                                        <select id="canalizar" class="form-control" required>
                                            <option value="">Selecciona</option>
                                            <option value="Primera dependencia">Primera dependencia</option>
                                            <option value="Segunda dependencia">Segunda dependencia</option>
                                            <option value="Tercera dependencia">Tercera dependencia</option>
                                            <option value="Cuarta dependencia">Cuarta dependencia</option>
                                            <option value="otro">Otro</option>
                                                
                                        </select>
                                        <div class="invalid-feedback">
                                            El campo es obligatorio.
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-6" id="otra_dependencia" style="display:none;">
                                        <div class="form-group">
                                            <label for="name">Especificar dependencia</label>
                                            <input id="nombre_dependencia" type="text" oninput=" this.value = this.value.toUpperCase()" class="form-control" >
                                            <!--textarea class="form-control" name="dependencia" rows="4"style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()">{{ old('especificar', $citado->especificar ?? '') }}</textarea-->
                                        </div>
                                    </div>
                                    <input type="hidden" name="dependencia" id="dependencia">

                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                        <label for="name">¿Se conoce el nombre del Jefe inmediato?<span style="color:red;">(*)</span></label>
                                        <select id="se_conoce" class="form-control" required >
                                            <option value="">Selecciona</option>
                                            <option value="si">Sí</option>
                                            <option value="No se conoce">No</option>
                                                
                                        </select>
                                        <div class="invalid-feedback">
                                            El campo es obligatorio.
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-6" id="si_conoce" style="display:none;">
                                        <div class="form-group">
                                            <label for="name">Especificar el nombre del Jefe inmediato</label>
                                            <input id="nombre_jefe" type="text" oninput="this.value = this.value.toUpperCase()" class="form-control" >
                                        </div>
                                    </div>
                                    <input type="hidden" name="jefe_inmediato" id="jefe_inmediato">
                                    

                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label for="name">Expediente<span style="color:red;">(*)</span></label>
                                            <input type="text" class="form-control" name="expediente" maxlength="18" required>
                                            <div class="invalid-feedback">
                                                El número de expediente es obligatorio.
                                            </div>
                                            
                                            <a href="{{ route('solicitud') }}" class="btn btn-primary"  target="_blank">Nueva Solicitud</a>
                                            
                                        </div>
                                    </div>

                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label for="name">¿La persona afectada comunicó los hechos a alguien más de su área de trabajo? <span style="color:rgb(102, 102, 102);">(Decriba a quién o a quiénes)</span></label>
                                            <textarea name="descripcion_persona" oninput="this.value = this.value.toUpperCase()" class="form-control"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label for="name">Nombre de la Empresa</label>
                                            <input type="text" name="empresa" maxlength="50" class="form-control" style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase();" required> 
                                            <div class="invalid-feedback">
                                                El campo empresa es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label for="name">Puesto:<span style="color:red;">(*)</span></label>
                                            <input type=text name="puesto" oninput="this.value = this.value.toUpperCase()" class="form-control"  required>
                                            <div class="invalid-feedback">
                                                El puesto es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label for="name">Área de adscripción:<span style="color:red;">(*)</span></label>
                                            <input type=text name="area_adscripcion" oninput="this.value = this.value.toUpperCase()" class="form-control"  required>
                                            <div class="invalid-feedback">
                                                El área de adscripción es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label for="name">Descripción de las conductas manifestadas<span style="color:red;">(*)</span></label>
                                            <textarea name="descripcion_conductas" oninput="this.value = this.value.toUpperCase()" class="form-control" required></textarea>
                                            <div class="invalid-feedback">
                                                La descripción es oligatoria.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label for="name">Ubicación:<span style="color:red;">(*)</span></label>
                                            <textarea name="ubicacion" oninput="this.value = this.value.toUpperCase()" class="form-control"  required></textarea>
                                            <div class="invalid-feedback">
                                                La ubicación son obligatoria.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label for="name">Observaciones<span style="color:red;">(*)</span></label>
                                            <textarea name="observaciones" oninput="this.value = this.value.toUpperCase()" class="form-control"  required></textarea>
                                            <div class="invalid-feedback">
                                                Las observaciones son obligatorias.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    
                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                        <button type="submit" class="btn btn-primary">Guardar</button>
                                    </div>
                                    
                                </div>
                            </form>       
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script>
       
        document.addEventListener('DOMContentLoaded', function () {

            const dependencia = document.getElementById('canalizar');
            const otraOpcionDiv = document.getElementById('otra_dependencia');
            const nombreDependencia = document.getElementById('nombre_dependencia');
            const dependenciaFinal = document.getElementById('dependencia');

            const cambio = document.getElementById('cambios');
            const cambioOpcionDiv = document.getElementById('otro_cambio');
            const tipoCambio = document.getElementById('tipo_cambio');
            const situacionFinal = document.getElementById('situacion_laboral');

            const situacion = document.getElementById('situacion');
            const motivoOpcionDiv = document.getElementById('motivo');
            const tipoDiscriminacion = document.getElementById('tipo_discriminacion');
            
            const se_conoce = document.getElementById('se_conoce');
            const si_conoce = document.getElementById('si_conoce');
            const nombre_jefe = document.getElementById('nombre_jefe');
            const jefe_inmediato = document.getElementById('jefe_inmediato');
           

            function otraDependencia() {

                const valor = dependencia.value;

                if (valor === 'otro') {

                    otraOpcionDiv.style.display = 'block';
                    nombreDependencia.disabled = false;
                    dependenciaFinal.value = nombreDependencia.value;

                } else {

                    otraOpcionDiv.style.display = 'none';
                    nombreDependencia.disabled = true;
                    nombreDependencia.value = '';

                    dependenciaFinal.value = valor;
                }
            }
            function otroCambio() {

                const valor = cambio.value;

                if (valor === 'otro') {

                    cambioOpcionDiv.style.display = 'block';
                    tipoCambio.disabled = false;
                    situacionFinal.value = tipoCambio.value;

                } else {

                    cambioOpcionDiv.style.display = 'none';
                    tipoCambio.disabled = true;
                    tipoCambio.value = '';

                    situacionFinal.value = valor;
                }
            }
            function motivoDiscriminacion() {

                const valor = situacion.value;

                if (valor === 'Discriminación') {

                    motivoOpcionDiv.style.display = 'block';
                    tipoDiscriminacion.disabled = false;

                } else {

                    motivoOpcionDiv.style.display = 'none';
                    tipoDiscriminacion.disabled = true;
                    tipoDiscriminacion.value = '';

    
                }
            }
           
            function nombreJefe() {

                const valor = se_conoce.value;

                if (valor === 'si') {

                    si_conoce.style.display = 'block';
                    nombre_jefe.disabled = false;
                    jefe_inmediato.value = nombre_jefe.value;

                } else {

                    si_conoce.style.display = 'none';
                    nombre_jefe.disabled = true;
                    nombre_jefe.value = '';

                    jefe_inmediato.value = valor;
                }
            }
            

            dependencia.addEventListener('change', otraDependencia);

            nombreDependencia.addEventListener('input', function () {
                dependenciaFinal.value = this.value;
            });
            otraDependencia();


            cambio.addEventListener('change', otroCambio);

            tipoCambio.addEventListener('input', function () {
                situacionFinal.value = this.value;
            });
            otroCambio();

            se_conoce.addEventListener('change', nombreJefe);

            nombre_jefe.addEventListener('input', function () {
                jefe_inmediato.value = this.value;
            });
            nombreJefe();

            situacion.addEventListener('change', motivoDiscriminacion);
            motivoDiscriminacion();

            
           
        });
        

    </script>
@endsection

@push('body_end')
<div id="menu_carga" style ="display: none;">
    <div>.</div>
    <div class="loader"></div>
</div>
@endpush


@section('scripts')
    <script src="../public/js/usuarios/usuarios.js"></script>
@endsection