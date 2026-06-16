@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Información del citado</h3>
        </div>
        <div class="section-body">
            <?php $fecha_actual = date('d-m-Y');?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="text-center"></h3>
                            
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
                            <form class="needs-validation novalidate" method="POST" action="{{route('actualizar_enlace')}}" enctype="multipart/form-data">
                                @csrf    
                                <input type="hidden" name="id" value="{{ $folio->id }}">
                                
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-3">
                                        <div class="form-group">
                                            <label for="name">Tipo de persona</label>
                                            <select name="tipo" class="form-control">
                                                <option value="">Seleccione</option>
                                                <option value="Fisica" @php if($folio->tipo_persona === "Fisica") echo "selected"  @endphp>Física</option>
                                                <option value="Moral" @php if($folio->tipo_persona === "Moral") echo "selected"  @endphp>Moral</option>
                                            </select>
                                            <div class="invalid-feedback">
                                                El tipo de persona es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label for="name">Nombre(s)</label>
                                            <input type="text" name="nombre" class="form-control" value="<?=$folio["nombre"];?>" oninput="this.value = this.value.toUpperCase()" > 
                                            <div class="invalid-feedback">
                                                El nombre es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    @if(!empty($folio['primer_apellido']))         
                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                            <div class="form-group">
                                                <label for="name">Primer apellido</label>
                                                <input type="text" name="primer_apellido" class="form-control" value="<?=$folio["primer_apellido"];?>" oninput="this.value = this.value.toUpperCase()" > 
                                                <div class="invalid-feedback">
                                                    El primer apellido es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    @if(!empty($folio['segundo_apellido']))    
                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                            <div class="form-group">
                                                <label for="name">Segundo apellido</label>
                                                <input type="text" name="segundo_apellido" class="form-control" value="<?=$folio["segundo_apellido"];?>"oninput="this.value = this.value.toUpperCase()" > 
                                                <div class="invalid-feedback">
                                                    El segundo apellido es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="col-xs-12 col-sm-12 col-md-3">
                                        <div class="form-group">
                                            <label for="name">RFC</label>
                                            <input type="text" name="rfc" class="form-control" value="<?=$folio["rfc"];?>"minlength="13" maxlength="13" > 
                                            <div class="invalid-feedback">
                                                El campo RFC es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    @if(!empty($folio['curp'])) 
                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                            <div class="form-group">
                                                <label for="name">CURP</label>
                                                <input type="text" name="curp" id="curp_input" oninput="validarInput(this)" class="form-control" value="<?=$folio["curp"];?>"> 
                                                <pre id="resultado"></pre>
                                                <div class="invalid-feedback">
                                                    El CURP es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="col-xs-12 col-sm-6 col-md-3">
                                        <div class="form-group">
                                            <label for="password">Estado</label>
                                            <select class="form-control" name="estado_citado" id="estado_citado">
                                                @foreach($estados as $est)
                                                    <option value="{{$est['id']}}" {{ $folio['estado_citado'] == $est['id'] ? "selected" : '' }}>{{$est['nombre']}}</option>
                                                @endforeach
                                            </select>
                                            <div class="invalid-feedback">
                                                El Estado es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-6 col-md-3">
                                        <div class="form-group">
                                            <label for="password">Municipio</label>
                                            <select class="form-control" name="municipio_citado" id="municipio_citado">
                                                @foreach($municipios as $mun)
                                                    <option value="{{$mun['id']}}" {{ $folio['municipio_citado'] == $mun['id'] ? "selected" : '' }}>{{$mun['nombre']}}</option>
                                                @endforeach
                                            </select>
                                            <div class="invalid-feedback">
                                                El Municipio es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-3">
                                        <div class="form-group">
                                            <label for="name">Tipo de Vialidad del Citado</label>
                                            <select name="vialidad" class="form-control" required>
                                                <option value="">SELECCIONE</option>
                                                <option value="CALLE"          @php if($folio->tipo_vialidad === "CALLE") echo "selected"  @endphp>Calle</option>
                                                <option value="AVENIDA"        @php if($folio->tipo_vialidad === "AVENIDA") echo "selected"  @endphp>Avenida</option>
                                                <option value="CALZADA"        @php if($folio->tipo_vialidad === "CALZADA") echo "selected"  @endphp>Calzada</option>
                                                <option value="BOULEVARD"      @php if($folio->tipo_vialidad === "BOULEVARD") echo "selected"  @endphp>Boulevard</option>
                                                <option value="AMPLIACIÓN"     @php if($folio->tipo_vialidad === 'AMPLIACIÓN') echo "selected"  @endphp >Ampliación</option>
                                                <option value="ANDADOR"        @php if($folio->tipo_vialidad === 'ANDADOR') echo "selected"  @endphp >Andador</option>
                                                <option value="AUTOPISTA"      @php if($folio->tipo_vialidad === 'AUTOPISTA') echo "selected"  @endphp >Autopista</option>
                                                <option value="CALLEJÓN"       @php if($folio->tipo_vialidad === 'CALLEJÓN') echo "selected"  @endphp>Callejón</option>
                                                <option value="CARRETERA"      @php if($folio->tipo_vialidad === 'CARRETERA') echo "selected"  @endphp>Carretera</option>
                                                <option value="CERRADA"        @php if($folio->tipo_vialidad === 'CERRADA') echo "selected"  @endphp>Cerrada</option>
                                                <option value="CIRCUITO"       @php if($folio->tipo_vialidad === 'CIRCUITO') echo "selected"  @endphp>Circuito</option>
                                                <option value="CIRCUNVALACIÓN" @php if($folio->tipo_vialidad === 'CIRCUNVALACIÓN') echo "selected"  @endphp>Circunvalación</option>
                                                <option value="CONTINUACIÓN"   @php if($folio->tipo_vialidad === 'CONTINUACIÓN') echo "selected"  @endphp>Continuación</option>
                                                <option value="CORREDOR"       @php if($folio->tipo_vialidad === 'CORREDOR') echo "selected"  @endphp>Corredor</option>
                                                <option value="DIAGONAL"       @php if($folio->tipo_vialidad === 'DIAGONAL') echo "selected"  @endphp>Diagonal</option>
                                                <option value="EJE VIAL"       @php if($folio->tipo_vialidad === 'EJE VIAL') echo "selected"  @endphp>Eje vial</option>
                                                <option value="PERIFÉRICO"     @php if($folio->tipo_vialidad === 'PERIFÉRICO') echo "selected"  @endphp>Periférico</option>
                                                <option value="PROLONGACIÓN"   @php if($folio->tipo_vialidad === 'PROLONGACIÓN') echo "selected"  @endphp>Prolongación</option>
                                                <option value="RETORNO"        @php if($folio->tipo_vialidad === 'RETORNO') echo "selected"  @endphp>Retorno</option>
                                                <option value="VIADUCTO"       @php if($folio->tipo_vialidad === 'VIADUCTO') echo "selected"  @endphp>Viaducto</option>
                                                </select>
                                            <div class="invalid-feedback">
                                                El campo vialidad es obligatorio.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xs-12 col-sm-12 col-md-3">
                                        <div class="form-group">
                                            <label for="name">Nombre de la vialidad</label>
                                            <input type="text" name="calle" class="form-control" value="<?=$folio["calle"];?>"required> 
                                            <div class="invalid-feedback">
                                                El campo calle es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-3">
                                        <div class="form-group">
                                            <label for="name">Núm. Ext.</label>
                                            <input type="text" name="exterior" class="form-control" value="<?=$folio["n_ext"];?>" required> 
                                            <div class="invalid-feedback">
                                                El campo núm. ext. es obligatorio.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xs-12 col-sm-12 col-md-3">
                                        <div class="form-group">
                                            <label for="name">Núm. Int.</label>
                                            <input type="text" name="interior" class="form-control" value="<?=$folio["n_int"];?>" > 
                                            <div class="invalid-feedback">
                                                El campo núm. int. es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-3">
                                        <div class="form-group">
                                            <label for="name">Colonia</label>
                                            <input type="text" name="colonia" class="form-control" value="<?=$folio["colonia"];?>"required> 
                                            <div class="invalid-feedback">
                                                El campo colonia es obligatorio.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xs-12 col-sm-12 col-md-3">
                                        <div class="form-group">
                                            <label for="name">Código Postal</label>
                                            <input type="text" name="cp" class="form-control" value="<?=$folio["cp"];?>" minlength="5" maxlength="5" required> 
                                            <div class="invalid-feedback">
                                                El campo Código Postal es obligatorio.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xs-12 col-sm-12 col-md-3">
                                        <div class="form-group">
                                            <label for="name">Entre calle</label>
                                            <input type="text" name="calle1" class="form-control" value="<?=$folio["calle1"];?>">
                                            <div class="invalid-feedback">
                                                El campo calle es obligatorio.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xs-12 col-sm-12 col-md-3">
                                        <div class="form-group">
                                            <label for="name">Y calle</label>
                                            <input type="text" name="calle2" class="form-control" value="<?=$folio["calle2"];?>"> 
                                            <div class="invalid-feedback">
                                                El campo calle es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-12">
                                        <div class="form-group">
                                            <label for="floatingTextarea">Referencias del domicilio</label>
                                            <textarea class="form-control" placeholder="" name="referencia"><?=$folio["referencia"];?></textarea>
                                            <div class="invalid-feedback">
                                                El campo referencias es obligatorio.
                                            </div>
                                        </div>
                                    </div> 
                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                        <label for="password">Referencia 1</label><br>
                                        @if (!empty($folio->imagen_domicilio1) && $folio->imagen_domicilio1 !== 'Sin documento')
                                            <a target='_blank' href="{{ asset('storage/app/documentosSolicitud/'.$folio->imagen_domicilio1) }}">VER IMAGEN</a>
                                        @else
                                            <span class="text-muted">No se subió imagen</span>
                                        @endif
                                        <input type="hidden" name="imagen_domicilio1" value="{{ $folio->imagen_domicilio1 }}">
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                        <label for="password">Referencia 2</label><br>
                                        @if (!empty($folio->imagen_domicilio2) && $folio->imagen_domicilio2 !== 'Sin documento')
                                            <a target='_blank' href="{{ asset('storage/app/documentosSolicitud/'.$folio->imagen_domicilio2) }}">VER IMAGEN</a><br>
                                        @else
                                            <span class="text-muted">No se subió imagen</span>
                                        @endif
                                        <input type="hidden" name="imagen_domicilio2" value="{{ $folio->imagen_domicilio2 }}">
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-12"><br></div>
                                    <div class="col-xs-12 col-sm-12 col-md-12">
                                        <a class="btn btn-info" href="{{ route('seer')}}" onclick=consultar_estadistica();>Regresar</a>
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
    <script src="../../public/assets/js/validaciones.js"></script>
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
