<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>Si concilio</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <!-- Bootstrap 5.3.3 -->
    <link href="../public/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <!-- Ionicons -->
    <link rel="icon" href="public/assets/images/ccl-r.png" type="image/x-icon">
    <link href="//fonts.googleapis.com/css?family=Lato&display=swap" rel="stylesheet">
    <link href="../public/assets/css/all.css" rel="stylesheet" type="text/css">
    <link href="../public/assets/css/iziToast.min.css" rel="stylesheet">
    <link href="../public/assets/css/sweetalert.css" rel="stylesheet" type="text/css"/>
    <link href="../public/assets/css/select2.min.css" rel="stylesheet" type="text/css"/>
    
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
            background: url('../public/assets/images/pageLoader.gif') 50% 50% no-repeat rgb(249,249,249);
           /* background-color: #6A0F49;/*<p style="color: #CEA845*/
            opacity: .8;
        }
    </style>
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
        <div class="">
            <img src="../public/assets/images/Logos 2.png" class="img" style="" width="250" height="90"></a>&nbsp;&nbsp;
        </div> 
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent" >
            <ul class="navbar-nav ml-auto">
                <li class="nav-item active">
                    <a class="nav-link" href="{{ route('publico') }}" style="color: black;">INICIO<span class="sr-only"></span></a>
                </li>
            </ul>
        </div>
    </nav>
    <div class="container">
        <br><br><br><br>
    </div>
</head>

    <div id="app">  
        <section class="section">
            <div class="section-body">
                <div class="row"> 
                    <div class="col-lg-12" >
                        <div class="card">
                            <div class="card-body">
                                    @if(isset($success))
                                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                                            <strong>¡Registro correcto!</strong>
                                            {{ $success }}
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
                                    <div style="background-color:#D2D3D5; width:100%; height:40px;">
                                        <h3 class="text-center" style="color:black">Datos de identificación</h3>
                                    </div>    
                                    <!--Se realiza el envío de datos con formulario-->
                                    <form class="needs-validation novalidate" method="POST" action="{{route('parte2')}}">
                                        @csrf
                                        <input type="hidden" name="id" value="{{$id}}">
                                        <div class="row">
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <label for="name">Tipo persona</label>
                                                <select name="tipo" class="form-control" required>
                                                    <option value="">Seleccione</option>
                                                    <option value="Fisica">Fisica</option>
                                                    <option value="Moral">Moral</option>
                                                </select>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-6">
                                                <div class="form-group">
                                                    <label for="name">Curp del solicitante (*)</label>
                                                    <input type="text" name="curp" class="form-control" required> 
                                                    <div class="invalid-feedback">
                                                        El curp es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Nombre(s) del solicitante (*) </label>
                                                    <input type="text" name="nombre" class="form-control" required> 
                                                    <div class="invalid-feedback">
                                                        El nombre es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Fecha de nacimiento (*)</label>
                                                    <input type="date" name="fecha_nacimiento" class="form-control" required> 
                                                    <div class="invalid-feedback">
                                                        La fecha de nacimiento es obligatoria.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Edad del solicitante (*)</label>
                                                    <input type="number" name="edad" class="form-control" required> 
                                                    <div class="invalid-feedback">
                                                        La edad es obligatoria.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">RFC del solicitante</label>
                                                    <input type="text" name="rfc" class="form-control" minlength="13" maxlength="13"> 
                                                    <div class="invalid-feedback">
                                                        El RFC es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Genero (*)</label>
                                                    <select name="genero" class="form-control" required>
                                                        <option value="">Seleccione</option>
                                                        <option value="H">Hombre</option>
                                                        <option value="M">Mujer</option>
                                                        <option value="NB">No Binarios</option>
                                                        <option value="LGBTTTIQ">LGBTTTIQ+</option>
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        El genero es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Nacionalidad (*)</label>
                                                    <select name="nacionalidad" class="form-control" required>
                                                        <option value="">Seleccione</option>
                                                        <option value="Mexicana">Mexicana</option>
                                                        <option value="Otra">Otra</option>
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        La nacionalidad es obligatoria.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Estado de nacimiento (*)</label>
                                                    <select id="estado_nacimiento" name="estado_nacimiento" class="form-control" required>
                                                        @foreach($estados as $est)
                                                            <option value="{{$est['id']}}">{{$est['nombre']}}</option>
                                                        @endforeach
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        El estado es obligatoria.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-6 col-sm-12 col-md-3"><br>
                                                <spam for="btncheck1">¿Requiere traductor?</spam>
                                                <input type="checkbox" class="btn-check" id="check_lenguaje" name="traductor" autocomplete="off">
                                            </div>
                                            <div class="col-xs-6 col-sm-12 col-md-3" id="lenguaje_señas">
                                                <div class="form-group">
                                                    <label for="name">¿Qué tipo de lenguaje require?</label>
                                                    <input type="text" name="lenguaje" class="form-control">
                                                </div>
                                            </div>    
                                            <div class="col-xs-12 col-sm-12 col-md-12" style="background-color:#D2D3D5; width:100%; height:40px;">
                                                <h3 class="text-center" style="color:black">Contacto</h3>
                                            </div>  
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Teléfono (*)</label>
                                                    <input type="number" name="telefono" minlength="10" maxlength="10" class="form-control"  required> 
                                                    <div class="invalid-feedback">
                                                        El teléfono  es obligatorio.
                                                    </div>
                                                </div>   
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Email (*)</label>
                                                    <input type="mail" name="correo" class="form-control" required> 
                                                    <div class="invalid-feedback">
                                                        El Email es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-12" style="background-color:#D2D3D5; width:100%; height:40px;">
                                                <h3 class="text-center" style="color:black">Domicilio</h3>
                                            </div>
                                            <div class="col-xs-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label for="password">Estado del solicitante (*)</label>
                                                    <select id="estado_solicitante" class="form-control" name="estado_solicitante" required>
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
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Tipo de vialidad (*)</label>
                                                    <input type="text" name="vialidad" class="form-control" required> 
                                                    <div class="invalid-feedback">
                                                        El campo vialidad es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Nombre de la vialidad o calle (*)</label>
                                                    <input type="text" name="vialidad_calle" class="form-control" required> 
                                                    <div class="invalid-feedback">
                                                        El campo vialidad o calle es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Número exterior (*)</label>
                                                    <input type="text" name="numExt" class="form-control" required> 
                                                    <div class="invalid-feedback">
                                                        El campo número es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Número interior</label>
                                                    <input type="text" name="numInt" class="form-control"> 
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Colonia (*)</label>
                                                    <input type="text" name="colonia_solicitante" class="form-control" required> 
                                                    <div class="invalid-feedback">
                                                        El campo colonia es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Nombre del municipio o alcaldía (*)</label>
                                                    <select id="municipio_solicitante" class="form-control" name="municipio_solicitante" required>
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
                                            <div id="div1"  class="col-xs-12 col-sm-12 col-md-3">
                                                <div class="form-group">
                                                    <label for="name">Código postal (*)</label>
                                                    <input type="number" name="codigo_solicitante" class="form-control" required> 
                                                    <div class="invalid-feedback">
                                                        El campo código postal es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Referencias (*)</label>
                                                    <input type="text" name="referencias" class="form-control" required> 
                                                    <div class="invalid-feedback">
                                                        El campo referencia es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Entre calle (*)</label>
                                                    <input type="text" name="calle1" class="form-control" required> 
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">y calle (*)</label>
                                                    <input type="text" name="calle2" class="form-control" required>                                     
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-12" style="background-color:#D2D3D5; width:100%; height:40px;">
                                                <h3 class="text-center" style="color:black">Datos laborales</h3>
                                            </div>  
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Número de seguro social (*)</label>
                                                    <input type="text" name="seguro" class="form-control" required> 
                                                    <div class="invalid-feedback">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Puesto (*)</label>
                                                    <input type="text" name="puesto" class="form-control" required> 
                                                    <div class="invalid-feedback">
                                                        El puesto es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Oficio</label>
                                                    <input type="text" name="oficio" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">¿Cada cuándo te pagan?</label>
                                                    <select name="tiempo_pago" class="form-control" required>
                                                        <option value="">Seleccione</option>
                                                        <option value="Semanal">Semanal</option>
                                                        <option value="Quincenal">Quincenal</option>
                                                        <option value="Mensual">Mensual</option>
                                                        <option value="Diario">Diario</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Salario (*)</label>
                                                    <input type="number" name="pago" class="form-control" required> 
                                                    <div class="invalid-feedback">
                                                        Es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Cantidad de horas trabajadas por semana</label>
                                                    <input type="number" name="horas" class="form-control" required> 
                                                    <div class="invalid-feedback">
                                                        Es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div class="form-group">
                                                    <label for="btncheck1">¿Labora actualmente?</label><br>
                                                    <input name="labora" type="checkbox" class="btn-check" id="check_lenguaje" autocomplete="off"/>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Fecha de ingreso</label>
                                                    <input type="date" name="fecha_ingreso" class="form-control" required> 
                                                    <div class="invalid-feedback">
                                                        La fecha de ingreso es obligatoria.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Fecha de salida</label>
                                                    <input type="date" name="fecha_salida" class="form-control" required> 
                                                    <div class="invalid-feedback">
                                                        La fecha de salida es obligatoria.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Jornada</label>
                                                    <select name="jornada" class="form-control" required>
                                                        <option value="">Seleccione</option>
                                                        <option value="Diurna">Diurna</option>
                                                        <option value="Nocturna">Nocturna</option>
                                                        <option value="Mixta">Mixta</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div align="center">
                                                    <button type="submit" class="btn btn-primary" style="background-color:#CEA845; border-color:#CEA845;">Agregar</button>
                                                    <a href="{{ route('agregar_citado', $id); }}" class="btn btn-primary" style=" background-color:#CEA845;border-color:#CEA845;">Continuar</a>    
                                                </div>
                                            </div>     
                                    </form>
                                </div>
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
    <script src="../public/assets/js/poderes/general.js"></script>
@endsection



    <script src="../public/assets/js/jquery.min.js"></script>
    <script src="../public/assets/js/popper.min.js"></script>
    <script src="../public/assets/js/bootstrap.min.js"></script>
    <script src="../public/assets/js/sweetalert.min.js"></script>
    <script src="../public/assets/js/select2.min.js"></script>
    <script src="../public/assets/js/jquery.nicescroll.js"></script>

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
        function sedes(){
            document.getElementById("fecha").removeAttribute("disabled");
        }
        function diaSemana() {
            var dia_semana  = document.getElementById("fecha").value;
            var sede        = document.getElementById("sede").value;

            $.get('api/obtenerHorario/'+dia_semana+'/'+sede, function (data){
                var html_select = '<option value="">--Seleccione un horario --</option>';  
                for(var i=0; i<data.length; ++i)
                    html_select += '<option value= "'+data[i].hora+'">'+data[i].hora+'</option>';
                    $('#horarios').html(html_select);

            });
        }
    </script>
<div id="crear_poder" style ="display: none;">
    <div>.</div>
    <div class="loader"></div>
</div>

@section('scripts')
    <script src="../public/js/poderes/general.js"></script>
    
@endsection
