@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Cumplimientos</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="text-center">Registrar Cumplimiento</h3>
                            <h6 class="text-left">*En este aparado unicamente se registrarán los cumplimientos que no se visualicen en la agenda de complimientos(empalmados o no registrados).</h6>
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
                                <!--Se realiza el envío de datos con formulario de Laravel Collective-->
                                <form class="needs-validation novalidate" method="POST" action="{{route('guardar_cumplimiento_cumplimientos')}}" onsubmit="return validacionCamposInput()">
                                    @csrf
                                    <br><br>
                                    <div class="row">
                                        <div id="empresa" class="col-xs-12 col-sm-12 col-md-4">
                                            <div class="form-group">
                                                <label for="name">Número de identificación único<span style="color:red;">(*)</span></label>
                                                <input type="text" name="NUE" id="NUE" class="form-control" minlength="18" maxlength="18" oninput="this.value = this.value.toUpperCase()"> 
                                                <div class="invalid-feedback">
                                                    El Número de identificación es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                        <div id="empresa" class="col-xs-12 col-sm-12 col-md-4">
                                            <div class="form-group">
                                                <label for="name">Empresa/Patrón/Representante legal <span style="color:red;">(*)</span></label>
                                                <input type="text" name="empresa" id="empresa" class="form-control" oninput="this.value = this.value.toUpperCase()"> 
                                                <div class="invalid-feedback">
                                                    El nombre empresa/patrón/representante legal es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-4">
                                            <div class="form-group">
                                                <label for="name">Nombre(s) y apellidos trabajador <span style="color:red;">(*)</span></label>
                                                <input type="text" name="trabajador" class="form-control soloLetras" oninput="this.value = this.value.toUpperCase()" required> 
                                                <div class="invalid-feedback">
                                                    El nombre es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-2">
                                            <div class="form-group">
                                                <label for="name">Monto<span style="color:red;">(*)</span></label>
                                                <input type="text" name="monto" class="form-control soloMontos" required> 
                                                <div class="invalid-feedback">
                                                    El campo edad es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-3">
                                            <div class="form-group">
                                                <label for="name">Forma de pago<span style="color:red;">(*)</span></label>
                                                <input type="text" name="forma_pago" class="form-control soloLetras"required>
                                                <div class="invalid-feedback">
                                                    El campo forma de pago es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-7">
                                            <div class="form-group">
                                                <label for="name">Descripción<span style="color:red;">(*)</span></label>
                                                <input type="text" name="descripcion" class="form-control"required>
                                                <div class="invalid-feedback">
                                                    El campo es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-4">
                                            <div class="form-group">
                                                <label for="name">Sedes <span style="color:red;">(*)</span></label>
                                                <select name="sede" class="form-control" required>
                                                    <option value="">Seleccione la sede</option>
                                                    <option value="Morelia">Morelia</option>
                                                    <option value="Zitácuaro">Zitácuaro</option>
                                                    <option value="Uruapan">Uruapan</option>
                                                    <option value="Lázaro Cárdenas">Lázaro Cárdenas</option>
                                                    <option value="Zamora">Zamora</option>
                                                    <option value="Sahuayo">Sahuayo</option>
                                                </select>
                                                <div class="invalid-feedback">
                                                    La sede es obligatoria.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-4">
                                            <div class="form-group">
                                                <label for="name">Fecha<span style="color:red;">(*)</span></label>
                                                <input type="date" name="fecha" class="form-control">
                                                <div class="invalid-feedback">
                                                    El campo es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-4">
                                            <div class="form-group">
                                                <label for="name">Hora<span style="color:red;">(*)</span></label>
                                                <input type="time" name="hora" class="form-control">
                                                <div class="invalid-feedback">
                                                    El campo es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                    <div class="col-xs-12 col-sm-12 col-md-12">
                                        <div align="center">
                                            <button type="submit" class="btn btn-primary" style="background-color:#CEA845; border-color: #CEA845">Guardar</button> 
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
                             
    <div id="crear_poder" style ="display: none;">
        <div>.</div>
        <div class="loader"></div>
    </div>

    @section('scripts')
        <script>
        </script>
        <script src="../public/assets/js/poderes/general.js"></script>
    @endsection
