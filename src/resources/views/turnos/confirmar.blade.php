@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Caso de Excepcíon</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="text-center">Confirmar</h3>
                            
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
                            <form class='needs-validation novalidate' id='form_roles' method='POST' action="{{route('turnos.edit')}}">
                                @csrf
                                <input type="hidden" name="id" value="{{ $turno->id }}">
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label for="name">Nombre del solicitante</label>
                                            <input type="text" name="nombre" class="form-control" value="{{ $turno->solicitante }}"> 
                                            <div class="invalid-feedback">
                                                El nombre es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label for="name">Motivo/Causa de la Atención</label>
                                            <input type="text" name="motivo" class="form-control" required>
                                            <div class="invalid-feedback">
                                                El campo es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                        <div class="form-group">
                                            <label for="name">Posible caso de excepción</label>
                                            <select name="excepcion" class="form-control" required>
                                                <option value="">Seleccione</option>
                                                <option value="Si">Si</option>
                                                <option value="No">No</option>
                                            </select>
                                            <div class="invalid-feedback">
                                                El campo es obligatorio.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                        <div class="form-group">
                                            <label for="name">Tipo de caso</label>
                                            <select name="tipo_caso" class="form-control" required>
                                                <option value="">Seleccione</option>
                                                <option value="No aplica">No aplica</option>
                                                <option value="Discriminación">Discriminación</option>
                                                <option value="Acoso u hostigamiento sexual">Acoso u hostigamiento sexual</option>
                                                <option value="Discriminación">Designación de beneficiarios</option>
                                                <option value="Discriminación">Prestaciones de Seguridad Social</option>
                                                <option value="Discriminación">Maternidad</option>
                                                <option value="Acoso u hostigamiento sexual">Riesgos de trabajo</option>
                                                <option value="Discriminación">Accidentes de Trabajo</option>
                                                <option value="Discriminación">Invalidez</option>
                                                <option value="Discriminación">Seguros de Vida</option>
                                                <option value="Discriminación">Otras</option>
                                                <option value="Discriminación">Libertad y Asociación Sindical</option>
                                                <option value="Discriminación">Trata Laboral y Trabajo Forzoso</option>
                                                <option value="Discriminación">Trabajo Infantil</option>
                                                <option value="Discriminación">Disputa de titularidad de Contrato Coletivo y Contrato Ley</option>
                                                <option value="Discriminación">Impugnación de estatutos de Sindicato y su Modificación</option>
                                            </select>
                                            <div class="invalid-feedback">
                                                El campo es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                        <div class="form-group">
                                            <label for="name">Grupos vulnerables</label>
                                            <select name="vulnerables" class="form-control" required>
                                                <option value="">Seleccione</option>
                                                <option value="Menores de edad">Menores de edad</option>
                                                <option value="Mayores">Adultos mayores</option>
                                                <option value="Discapacidad">Personas con discapacidad</option>
                                                <option value="Indigena">Población indígena</option>
                                                <option value="Personas Migrantes">Personas Migrantes</option>
                                                <option value="LGBTTTIQ">LGBTTTIQ+</option>
                                                <option value="Ninguno">No aplica</option>
                                            </select>
                                            <div class="invalid-feedback">
                                                El campo es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                        <div class="form-group">
                                            <label for="name">Resultado  de Atencíon</label>
                                            <select id="resultado" name="resultado" class="form-control"  onchange="cambiaEstatus(this)" required>
                                                <option value="">Seleccione</option>
                                                <option value="Solicitud">Solicitud</option>
                                                <option value="Canaliza">Canaliza</option>
                                                <option value="Asesoria">Asesoria</option>
                                            </select>
                                            <div class="invalid-feedback">
                                                El campo es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    <div id="link" class="col-xs-12 col-sm-12 col-md-4">
                                        <div class="form-group">
                                            <label for="name">SINACOL</label><br>
                                            <a type="button" href="https://michoacan.cencolab.mx/solicitudes/create-public" target="_blank">Levantar Solicitud</a>
                                        </div>
                                    </div>
                                    <div id="folio" class="col-xs-12 col-sm-12 col-md-4">
                                        <div class="form-group">
                                            <label for="name">Folio de solicitud</label>
                                            <input type="text" name="folio" class="form-control">
                                            <div class="invalid-feedback">
                                                El campo  es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    <div id="canaliza" class="col-xs-12 col-sm-12 col-md-4">
                                        <div class="form-group">
                                            <label for="name">Institución a la que se canalizá</label>
                                            <select name="INS" class="form-control">
                                                <option value="">Seleccione</option>
                                                <option value="CEEADV">CEEADV</option>
                                                <option value="COEPRED">COEPRED</option>
                                                <option value="SEIMUJER">SEIMUJER</option>
                                                <option value="PRODET">PRODET</option>
                                                <option value="OTRO">OTRO</option>
                                            </select>
                                            <div class="invalid-feedback">
                                                El campo  es obligatorio.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xs-12 col-sm-12 col-md-6"><br>
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
@endsection

<div id="nuevo_turno" style="display: none;">
    <div>.</div>
    <div class="loader"></div>
</div>


@section('scripts')
    <script src="../../public/assets/js/turnos/turnos.js"></script>
    <script>
        document.getElementById("folio").style.display="none";
        document.getElementById("link").style.display="none";
        document.getElementById("canaliza").style.display="none";
        
        function cambiaEstatus(elemento){
            var valor = elemento.value;
            if(valor == "Solicitud"){
                document.getElementById("folio").style.display="block";
                document.getElementById("link").style.display="block";
                document.getElementById("canaliza").style.display="none";
            }
            else if(valor == "Canaliza"){
                document.getElementById("folio").style.display="none";
                document.getElementById("link").style.display="none";
                document.getElementById("canaliza").style.display="block";
            }
            else{
                document.getElementById("folio").style.display="none";
                document.getElementById("link").style.display="none";
                document.getElementById("canaliza").style.display="none";
            }
        }
    </script>
@endsection