@extends('layouts.app')

    @section('content')
        <section class="section"> 
            <div class="section-body">
                <div class="row"> 
                    <div class="col-lg-12" >
                        <div class="card">
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
                                    @if (session()->has('error'))
                                        <div class="alert alert-dark alert-dismissible fade show" role="alert">
                                            <strong>¡Revise los campos!</strong>
                                            {{ session()->get('error') }}
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                    @endif
                                    <div style="background-color:#D2D3D5; width:100%; height:40px;">
                                        <h3 class="text-center" style="color:black">Genera tu turno</h3>
                                    </div>   

                            <!--Se realiza el envío de datos con formulario de Laravel Collective-->
                            <form class='needs-validation novalidate' id='form_roles' method='POST' action="{{route('turnos.store')}}">
                                @csrf
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label for="name">Nombre del solicitante</label>
                                            <input type="text" name="nombre" class="form-control" required> 
                                            <div class="invalid-feedback">
                                                El nombre es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-3">
                                        <div class="form-group">
                                            <label for="name">Tipo de Tramite</label>
                                            <select name="tipo" class="form-control" required>
                                                <option value="">Seleccione</option>
                                                <option value="Solicitud">Solicitud</option>
                                                <option value="Ratificación">Ratificación</option>
                                            </select>
                                            <div class="invalid-feedback">
                                                El tipo de solicitud es obligatoria.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-3">
                                        <div class="form-group">
                                            <label for="name">Edad</label>
                                            <input type="number" name="edad" class="form-control"> 
                                            <div class="invalid-feedback">
                                                El campo edad es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                        <div class="form-group">
                                            <label for="name">Sexo</label>
                                            <select name="sexo" class="form-control">
                                                <option value="">Seleccione</option>
                                                <option value="H">Hombre</option>
                                                <option value="M">Mujer</option>
                                                <option value="NB">No Binarios</option>
                                                <option value="Otros">Otros</option>
                                            </select>
                                            <div class="invalid-feedback">
                                                El campo sexo es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                        <div class="form-group">

                                            <label for="name">Posible caso de excepción 
                                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">
                                                    ?
                                                </button>
                                            </label>

                                            <select name="excepcion" class="form-control" onchange="cambiaExcepcion(this)">
                                                <option value="">Seleccione</option>
                                                <option value="Si">Si</option>
                                                <option value="No">No</option>
                                            </select>
                                            <div class="invalid-feedback">
                                                El campo es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    <div id="tipo_caso"  class="col-xs-12 col-sm-12 col-md-4">
                                        <div class="form-group">
                                            <label for="name">Tipo de caso de excepción</label>
                                            <select name="tipo_caso" class="form-control">
                                                <option value="">Seleccione</option>
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
                                                <option value="Adultos mayores">Adultos mayores</option>
                                                <option value="Personas con discapacidad">Personas con discapacidad</option>
                                                <option value="Población indígena">Población indígena</option>
                                                <option value="Personas Migrantes">Personas Migrantes</option>
                                                <option value="LGBTTTIQ">LGBTTTIQ+</option>
                                                <option value="No aplica">No aplica</option>
                                            </select>
                                            <div class="invalid-feedback">
                                                El campo es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                        <div class="form-group">
                                            <label for="name">Requiere Asesoria/Orientación Juridica</label>
                                            <select name="orientacion" class="form-control">
                                                <option value="">Seleccione</option>
                                                <option value="Si">Si</option>
                                                <option value="No">No</option>
                                            </select>
                                            <div class="invalid-feedback">
                                                El campo sexo es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    
                                     <div class="col-xs-12 col-sm-12 col-md-4">
                                        <div class="form-group">
                                            <label for="name">Delegación/Oficina</label>
                                            <select name="delegacion" class="form-control" required>
                                                <option value="">Seleccione</option>
                                                <option value="Morelia">Morelia</option>
                                                <option value="Zitácuaro">Zitácuaro</option>
                                                <option value="Uruapan">Uruapan</option>
                                                <option value="Lázaro Cárdenas">Lázaro Cárdenas</option>
                                                <option value="Zamora">Zamora</option>
                                                <option value="Sahuayo">Sahuayo</option>
                                            </select>
                                            <div class="invalid-feedback">
                                                El campo es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-12">
                                        <div class="form-group">
                                            <label for="name">Observaciones</label>
                                            <textarea name="conflicto" class="form-control"></textarea>
                                            <div class="invalid-feedback">
                                                El campo es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-xs-6 col-sm-6 col-md-6">
                                        <button type="submit" class="btn btn-primary">Guardar</button>
                                    </div>
                                </div>
                            </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Posibles Casos</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            La Ley Federal del Trabajo en el articulo 685-Ter establece que no estas obligado a agotar la etapa conciliatoria en estos supuestos<br>
                            -Discriminación<br>
                            -Acoso u hostigamiento sexual<br>
                            -Designación de beneficiarios<br>
                            -Prestaciones de Seguridad Social
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
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
    <script>
        document.getElementById("tipo_caso").style.display="none";
        
        function cambiaExcepcion(elemento){
            var valor = elemento.value;
            if(valor == "Si"){
                document.getElementById("tipo_caso").style.display="block";
            }
            else{
                document.getElementById("tipo_caso").style.display="none";
            }
        }

        function validarHora(input) {
            var horaInicio = input.value;
            console.log(horaInicio);
            if (horaInicio < "08:00:00") {
                alert("La hora debe ser mayor a las 09:00:00.");
                return false;
            }
            else if(horaInicio > "16:00:00") {
                alert("La hora debe ser menor a las 15:00:00");
                return false;
            }
        return true;
        }
    </script>

    @section('scripts')
        <script src="../public/assets/js/turnos/turnos.js"></script>
    @endsection