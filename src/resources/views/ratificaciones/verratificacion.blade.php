@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Ratificación</h3>
        </div>
        <div class="section-body">
            <?php $fecha_actual = date('d-m-Y');?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            
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
                            <form class='needs-validation novalidate' id='form_roles' method='POST' action="{{route('editar_ratificacion')}}" enctype='multipart/form-data'>
                                @csrf    
                                <input type="hidden" name="id" value="{{ $folio->id }}">
                                <div class="row">
                                    <div class="col-xs-12 col-sm-6 col-md-4">
                                        <div class="form-group">
                                            <label for="name">Folio de solicitud</label>
                                            <input type="text" class="form-control" value="<?=$folio["id"];?>"readonly>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-12">
                                        <div class="form-group">
                                            <h4 class="text-center">Datos de identificación Empleador(a)</h4>
                                        </div>
                                    </div>



                                    <div class="col-xs-12 col-sm-6 col-md-4">
                                        <div class="form-group">
                                            <label for="email">Nombre Empleador</label>
                                            <input type="text" class="form-control" name="empresa" value="<?=$representante["nombres_patronal"];?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-6 col-md-4">
                                        <div class="form-group">
                                            <label for="email">Primer apellido</label>
                                            <input type="text" class="form-control" name="primero_empresa" value="<?=$representante["primer_apellido_patronal"];?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-6 col-md-4">
                                        <div class="form-group">
                                            <label for="email">Segundo apellido</label>
                                            <input type="text" class="form-control" name="segundo_empresa" value="<?=$representante["segundo_apellido_patronal"];?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-6 col-md-4">
                                        <div class="form-group">
                                            <label for="email">Email</label>
                                            <input type="text" class="form-control" value="<?=$representante["email_patronal"];?>"readonly>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-6 col-md-4">
                                        <div class="form-group">
                                            <label for="email">Teléfono</label>
                                            <input type="text" class="form-control" value="<?=$representante["telefono_patronal"];?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-6 col-md-4">
                                        <div class="form-group">
                                            <label for="email">Curp</label>
                                            <input type="text" class="form-control" name="curp_solicitante" value="<?=$representante["curp_patronal"];?>" readonly>
                                        </div>
                                    </div>


                                    <div class="col-xs-12 col-sm-12 col-md-12">
                                        <div class="form-group">
                                            <h4 class="text-center">Datos del trabajador</h4>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-6 col-md-4">
                                        <div class="form-group">
                                            <label for="email">Nombre</label>
                                            <input type="text" class="form-control" name="nombre_trabajador" value="<?=$folio["trabajador"];?>">
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-6 col-md-4">
                                        <div class="form-group">
                                            <label for="email">Primer apellido</label>
                                            <input type="text" class="form-control" name="primer_apellidot" value="<?=$folio["primero_trabajador"];?>">
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-6 col-md-4">
                                        <div class="form-group">
                                            <label for="email">Segundo apellido</label>
                                            <input type="text" class="form-control" name="segundo_apellidot" value="<?=$folio["segundo_trabajador"];?>">
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-6 col-md-4">
                                        <div class="form-group">
                                            <label for="email">Edad</label>
                                            <input type="text" class="form-control" name="edad" value="<?=$folio["edad"];?>">
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-6 col-md-4">
                                        <div class="form-group">
                                            <label for="email">Sexo</label>
                                            <select class="form-control" name="sexo" required>
                                                <option value="H" @php if($folio->sexo === "H") echo "selected"  @endphp>Hombre</option>
                                                <option value="M" @php if($folio->sexo === "M") echo "selected"  @endphp>Mujer</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-6 col-md-4">
                                        <div class="form-group">
                                            <label for="email">Curp trabajador</label>
                                            <input type="text" class="form-control" name="trabajador_curp" value="<?=$folio["trabajador_curp"];?>">
                                        </div>
                                    </div>
                                    
                                    <div class="col-xs-12 col-sm-6 col-md-4">
                                        <div class="form-group">
                                            <label for="email">Email</label>
                                            <input type="text" class="form-control" name="email_trabajador" value="<?=$folio["email"];?>">
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-6 col-md-4">
                                        <div class="form-group">
                                            <label for="email">Teléfono</label>
                                            <input type="text" class="form-control" name="telefono" value="<?=$folio["telefono"];?>">
                                        </div>
                                    </div>
                                    
                                    <div class="col-xs-12 col-sm-12 col-md-12">
                                        <div class="form-group">
                                            <h4 class="text-center">Datos de la relación laboral</h4>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-6 col-md-4">
                                        <div class="form-group">
                                            <label for="email">Fecha de inicio de la relación laboral</label>
                                            <input type="date" class="form-control" name="fecha_inicio" value="<?=$folio["fecha_inicio"];?>">
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-6 col-md-4">
                                        <div class="form-group">
                                            <label for="email">Fecha de término de la relación laboral</label>
                                            <input type="date" class="form-control" name="fecha_termino" value="<?=$folio["fecha_termino"];?>">
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-6 col-md-4">
                                        <div class="form-group">
                                            <label for="email">Categoría o puesto que desempeña</label>
                                            <input type="text" class="form-control" name="categoria" value="<?=$folio["categoria"];?>">
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-6 col-md-4">
                                        <div class="form-group">
                                            <label for="email">Frecuencia de pago</label>
                                            <select class="form-control" name="frecuencia" required>
                                                <option value="Diario" @php if($folio->frecuencia === "Diario") echo "selected"  @endphp>Diario</option>
                                                <option value="Semanal" @php if($folio->frecuencia === "Semanal") echo "selected"  @endphp>Semanal</option>
                                                <option value="Quincenal" @php if($folio->frecuencia === "Quincenal") echo "selected"  @endphp>Quincenal</option>
                                                <option value="Mensual" @php if($folio->frecuencia === "Mensual") echo "selected"  @endphp>Mensual</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-6 col-md-4">
                                        <div class="form-group">
                                            <label for="email">Salario</label>
                                            <input type="text" class="form-control" name="salario" value="<?=$folio["salario"];?>">
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-6 col-md-4">
                                        <div class="form-group">
                                            <label for="email">Días a la semana trabajados </label>
                                            <input type="text" class="form-control" name="dias" value="<?=$folio["dias"];?>">
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-6 col-md-4">
                                        <div class="form-group">
                                            <label for="email">Motivo de la conciliación</label>
                                            <select class="form-control" id="motivo" name="motivo">
                                                <option value="">Seleccione</option>
                                                <option value="Pago de prestaciones" @php if($folio->motivo === "Pago de prestaciones") echo "selected"  @endphp>Pago de prestaciones</option>
                                                <option value="Terminación voluntaria de la relación de trabajo" @php if($folio->motivo === "Terminación voluntaria de la relación de trabajo") echo "selected"  @endphp>Terminación voluntaria de la relación de trabajo</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div id="motivo_pago" class="col-xs-12 col-sm-12 col-md-2">
                                        <div class="form-group">
                                            <label for="name">* Selecciona las casillas correspondientes
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="Aguinaldo" @php if($folio->Aguinaldo == 1) echo "checked" @endphp >
                                                    <label class="form-check-label" for="flexCheckDefault">
                                                    Aguinaldo
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="Vacaciones" @php if($folio->Vacaciones == 1) echo "checked" @endphp>
                                                    <label class="form-check-label" for="flexCheckDefault">
                                                    Vacaciones
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="PrimaVacacional" @php if($folio->PrimaVacacional == 1) echo "checked" @endphp>
                                                    <label class="form-check-label" for="flexCheckDefault">
                                                    Prima Vacacional
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="PagoPTU" @php if($folio->PagoPTU == 1) echo "checked" @endphp>
                                                    <label class="form-check-label" for="flexCheckDefault">
                                                        Pago de PTU
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="Gratificación" @php if($folio->Gratificación == 1) echo "checked" @endphp>
                                                    <label class="form-check-label" for="flexCheckDefault">
                                                    Gratificación
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="PrimaAntigüedad" @php if($folio->PrimaAntigüedad == 1) echo "checked" @endphp>
                                                    <label class="form-check-label" for="flexCheckDefault">
                                                    Prima de Antigüedad
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="Otras" id="otras" @php if($folio->Otras == 1) echo "checked" @endphp>
                                                    <label class="form-check-label" for="flexCheckDefault">
                                                    Otras
                                                    </label>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    <div id="div_otras" class="col-xs-12 col-sm-12 col-md-3" style="display:none">
                                        <div class="form-group">
                                            <label for="name">Especifique
                                                <input type="text" name="Especifique" class="form-control" > 
                                            </label>
                                       </div>
                                    </div>        
                                    <div class="col-xs-12 col-sm-6 col-md-4">
                                        <div class="form-group">
                                            <label for="email">Monto total del convenio a pagar</label>
                                            <input type="text" class="form-control" name="monto" value="<?=$folio["monto"];?>">
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-6 col-md-4">
                                        <div class="form-group">
                                            <label for="email">Tipo pago</label>
                                            <input type="text" class="form-control" name="tipo_pago" value="<?=$folio["tipo_pago"];?>">
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-6 col-md-4">
                                        <div class="form-group">
                                            <label for="email">Tipo de Identificación</label>
                                            <select class="form-control" name="tipo_identificacion" required>
                                                <option value="Credencial de elector" @php if($folio->tipo_identificacion === "Credencial de elector") echo "selected"  @endphp>Credencial de elector</option>
                                                <option value="Pasaporte" @php if($folio->tipo_identificacion === "Pasaporte") echo "selected"  @endphp>Pasaporte</option>
                                                <option value="Cédula profesional" @php if($folio->tipo_identificacion === "Cédula Profesional") echo "selected"  @endphp>Cédula Profesional</option>
                                                <option value="Licencia para conducir" @php if($folio->tipo_identificacion === "Licencia para Conducir") echo "selected"  @endphp>Licencia para conducir</option>
                                                <option value="Credencial de inapam" @php if($folio->tipo_identificacion === "Credencial de inapam") echo "selected"  @endphp>Credencial de INAPAM</option>
                                                <option value="Cartilla militar" @php if($folio->tipo_identificacion === "Cartilla militar") echo "selected"  @endphp>Cartilla Militar</option>
                                                <option value="Documento migratorio" @php if($folio->tipo_identificacion === "Documento migratorio") echo "selected"  @endphp>Documento Migratorio</option>
                                                <option value="Constancia de identidad" @php if($folio->tipo_identificacion === "Constancia de identidad") echo "selected"  @endphp>Constancia de Identidad</option>
                                                <option value="Otros" @php if($folio->tipo_identificacion === "Otros") echo "selected"  @endphp>Otros</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-12">
                                        <div class="form-group">
                                            <h4 class="text-center">Documentos</h4>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <label for="email">*INE</label><br>
                                            <a target="_blank" class="btn btn-primary" href="../../storage/app/{{$ruta_abogado}}/{{$folio->ine}}">Existente</a>
                                            <input type="file" name="documentoIne" class="form-control-file" accept=".pdf">        
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <label>*Documento que acredite la representación</label><br>
                                            <a target="_blank" class="btn btn-primary" href="../../storage/app/{{$ruta_abogado}}/{{$folio->representacion}}">Existente</a>
                                            <input type="file" name="documentoRepresentacion" class="form-control-file" accept=".pdf">
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <label for="email">*Documento curp</label><br>
                                            <a target="_blank" class="btn btn-primary" href="../../storage/app/documentos_ratificacion/{{$folio->documentoCurp}}">Existente</a>
                                            <input type="file" name="documentoCurp" class="form-control-file" accept=".pdf">        
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <label for="email">*Documento identificación</label><br>
                                            <a target="_blank" class="btn btn-primary" href="../../storage/app/documentos_ratificacion/{{$folio->documentoidentificacion}}">Existente</a>
                                            <input type="file" name="documentoidentificacion" class="form-control-file" accept=".pdf">        
                                        </div>
                                    </div>

                                    @if($userRole[0] == "Auxiliar" || $userRole[0] == "Administrador Solicitante")
                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                            <div class="form-group">
                                                <h4 class="text-center">Datos de la fecha</h4>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-6 col-md-4">
                                            <div class="form-group">
                                                <label for="email">Sede</label>
                                                <input type="text" class="form-control" name="delegacion" value="<?=$folio["delegacion"];?>" readonly>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-6 col-md-4">
                                            <div class="form-group">
                                                <label for="email">Fecha</label>
                                                <input type="text" class="form-control" name="fecha_pago" value="<?=$folio["fecha"];?>" readonly>
                                        </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-6 col-md-4">
                                            <div class="form-group">
                                                <label for="email">Hora inicio</label>
                                                <input type="text" class="form-control" name="hora_pago" value="<?=$folio["hora"];?>" readonly>
                                            </div>
                                        </div> 
                                    @endif
                                    <div class="col-xs-12 col-sm-6 col-md-12">
                                        <div class="form-group">
                                            <label for="email">Observaciones</label>
                                            <input type="text" class="form-control" name="observaciones" value="<?=$folio["observaciones"];?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-12">
                                        @if($userRole[0] == "Auxiliar")
                                            <a class="btn btn-primary" href="{{ route('Ratificacion') }}">Regresar</a>
                                        @elseif($userRole[0] == "Solicitante")
                                            <a class="btn btn-primary" href="{{ route('ratificacion') }}">Regresar</a>
                                        @elseif($userRole[0] == "Administrador Solicitante")
                                        <a class="btn btn-primary" href="{{ route('Ratificacion') }}">Regresar</a>
                                        @endif
                                        
                                        @if($userRole[0] == "Auxiliar")
                                            @if($folio->estatus == "Confirmado" || $folio->estatus == "Pendiente")
                                                <button type="submit" class="btn btn-primary">Guardar</button>
                                            @endif
                                        @elseif($userRole[0] == "Solicitante")
                                            @if($folio->estatus == "Prevencion")
                                                <button type="submit" class="btn btn-primary">Guardar</button>
                                            @endif
                                        @endif
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


<div id="menu_carga" style ="display: none;">
    <div>.</div>
    <div class="loader"></div>
</div>

@section('scripts')
    <script src="../../public/assets/js/poderes/general.js"></script>
@endsection

    <script src="../../public/assets/js/jquery.min.js"></script>
    <script src="../../public/assets/js/popper.min.js"></script>
    <script src="../../public/assets/js/bootstrap.min.js"></script>
    <script src="../../public/assets/js/sweetalert.min.js"></script>
    <script src="../../public/assets/js/select2.min.js"></script>
    <script src="../../public/assets/js/jquery.nicescroll.js"></script>

    <!-- Template JS File -->
    <script src="../../public/assets/js/stisla.js"></script>
    <script src="../../public/assets/js/scripts.js"></script>
    <script src="../../public/assets/js/profile.js"></script>
    <script src="../../public/assets/js/custom.js"></script>

    <script src="https://cdn.datatables.net/2.1.5/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.1.5/js/dataTables.bootstrap4.js"></script>
    @yield('page_js')


    @yield('scripts')
    <script>

        $(function(){
            const motivo = document.getElementById('motivo');
            motivo.addEventListener('change', function() {
                const valorSeleccionado = this.value;
                // Realiza la validación o acciones necesarias
                if (valorSeleccionado === 'Pago de prestaciones') {
                    document.getElementById('motivo_pago').style.display = "block";
                } else {
                    document.getElementById('motivo_pago').style.display = "none";
                }
            });        
            
            const otras = document.getElementById('otras');
            otras.addEventListener('click', function() {
                const valorSeleccionado = this.value;
                    document.getElementById('div_otras').style.display = "block";
            });
        })
    </script>

@section('scripts')
    <script src="../../public/js/estadistica/estadistica.js"></script>
@endsection
