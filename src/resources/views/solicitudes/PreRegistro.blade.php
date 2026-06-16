<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
        <meta name="generator" content="Ing. ISBM">
        <link href="../public/assets/css/carousel.css" rel="stylesheet">
        <title>Si Concilio</title>
        <!-- Bootstrap core CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        
        <link rel="icon" href="../public/assets/images/logo-ccl.png" type="image/x-icon">
    
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
</head>

<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
    <div class="">
        <img src="public/assets/images/Logos 2.png" class="img" style="" width="250" height="90"></a>&nbsp;&nbsp;
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
    <br><br><br>
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
                                    <div>
                                    <br></div>
                                    <h3 class="text-center" style="color:#CEA845;">Registro</h3> 
                                    <!--Se realiza el envío de datos con formulario de Laravel Collective-->
                                    <form class="needs-validation novalidate" method="POST" action="{{route('guardar_registro_solicitud')}}">
                                        @csrf
                                       
                                        <div class="row">
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Nombre(s) y Apellidos (*) </label>
                                                    <input type="text" name="nombre" class="form-control" oninput="this.value = this.value.toUpperCase()" required> 
                                                    <div class="invalid-feedback">
                                                        El campo nombre es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">RFC (*)</label>
                                                    <input type="text" name="rfc" class="form-control" minlength="13" maxlength="13" oninput="this.value = this.value.toUpperCase()" required> 
                                                    <div class="invalid-feedback">
                                                        El campo RFC es obligatorio.
                                                    </div>
                                                </div>
                                            </div> 
                                            <div class="col-xs-12 col-sm-12 col-md-3">
                                                <div class="form-group">
                                                    <label for="name">Teléfono Celular (*)</label>
                                                    <input type="text" name="telefono" minlength="10" maxlength="10" class="form-control" required> 
                                                    <div class="invalid-feedback">
                                                        El campo teléfono es obligatorio.
                                                    </div>
                                                </div>   
                                            </div>
                                        </div>
                                        <div align="center">
                                            <button type="submit" class="btn btn-primary" style="background-color:#CEA845; border-color: #CEA845">
                                                Continuar
                                            </button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                            <a href="{{ route('publico'); }}" class="btn btn-primary" style=" background-color:#CEA845;border-color: #CEA845">Regresar</a>    
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

    <script src="../public/assets/js/estadistica/estadistica.js"></script>
    <script src="https://cdn.datatables.net/2.1.5/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.1.5/js/dataTables.bootstrap4.js"></script>

    <script src="../public/assets/js/validaciones.js"></script> 
       
   
