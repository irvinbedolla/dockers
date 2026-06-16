<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>Si concilio</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <!-- Bootstrap 5.3.3 -->
    <link href="public/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <!-- Ionicons -->
    <link rel="icon" href="public/assets/images/ccl-r.png" type="image/x-icon">
    <link href="//fonts.googleapis.com/css?family=Lato&display=swap" rel="stylesheet">
    <link href="public/assets/css/all.css" rel="stylesheet" type="text/css">
    <link href="public/assets/css/iziToast.min.css" rel="stylesheet">
    <link href="public/assets/css/sweetalert.css" rel="stylesheet" type="text/css"/>
    <link href="public/assets/css/select2.min.css" rel="stylesheet" type="text/css"/>
    
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
            background: url('public/assets/images/pageLoader.gif') 50% 50% no-repeat rgb(249,249,249);
           /* background-color: #6A0F49;/*<p style="color: #CEA845*/
            opacity: .8;
        }
        
    </style>

    @livewireStyles

    @yield('page_css')
    <!-- Template CSS <img src="public/assets_seer/images/ccl.png" width="180" height="90" style="position: absolute; left: 100px; top: 10px; right:0px;"/>  -->
    <link rel="stylesheet" href="public/assets/css/style.css">
    <link rel="stylesheet" href="public/assets/css/components.css">
    @yield('page_css')
</head>
    <div id="app">  
        <section class="section">
            <div class="col-lg-12" >
                <div style="background-color:#6A0F49">
                    <div align="right"><br>
                        <img src="public/assets/images/ccl-r.png" style="max-width: 10%" class="text-center">
                    </div>
                    <h3 class="text-center" style="color:#CEA845">Solicitud de trabajador</h3>    
                </div>
            </div>
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
                                   
                                    <h5 class="text-center" >Giro Comercial</h5>
                                    <h5 class="text-center" >Actividades legislativas, gubernamentales y de impartición de justicia</h5>
                                    <h5 class="text-center" >Objeto de la solicitud</h5>

                                    <!--Se realiza el envío de datos con formulario de Laravel Collective-->
                                    <form class="needs-validation novalidate" method="POST" action="{{route('solicitud_trabajador')}}">
                                        @csrf
                                        
                                        <div >
                                            <div id="div1"  class="col-xs-12 col-sm-12 col-md-6">
                                                <div class="form-group">
                                                    <table id="tabla" class="table table-striped mt-1" style="margin: 0 50%; text-align:center;">
                                                        <thead style="background-color: #4A001F;">
                                                            <th style="color: #fff;">Objeto de la solicitud</th>
                                                            <th style="color: #fff;">Acción</th>
                                                        </thead>
                                                        <tbody></tbody>
                                                    </table>
                                                    
                                                </div>
                                                <a href="{{ route('solicitud_trabajador'); }}" class="btn btn-primary" style=" background-color:#CEA845; border-color: #CEA845;">Editar datos de la solicitud</a> 
                                            </div>    
                                        </div>
                                        <div >
                                            <div id="div1"  class="col-xs-12 col-sm-12 col-md-6">
                                                <div class="form-group">
                                                    <h4 style="text-align: center">Solicitantes</h4> 
                                                    <table id="tabla" class="table table-striped mt-1" style="margin: 0 50%; text-align:center;">
                                                        <thead style="background-color: #4A001F;">
                                                            <th style="color: #fff;">Nombre</th>
                                                            <th style="color: #fff;">Curp</th>
                                                            <th style="color: #fff;">RFC</th>
                                                            <th style="color: #fff;">Acción</th>
                                                        </thead>
                                                        <tbody></tbody>
                                                    </table> 
                                                    <a href="{{ route('solicitud_trabajador'); }}" class="btn btn-primary" style=" background-color:#CEA845; border-color: #CEA845;">Agregar solicitante</a>    
                                                </div>   
                                            </div>
                                        </div>
                                        <div>
                                            <div id="div2"  class="col-xs-12 col-sm-12 col-md-6">
                                                <div class="form-group">
                                                    <h4 style="text-align: center">Citados</h4>   
                                                    <table id="tabla" class="table table-striped mt-1" style="margin: 0 50%; text-align:center;">
                                                        <thead style="background-color: #4A001F;">
                                                            <th style="color: #fff;">Nombre</th>
                                                            <th style="color: #fff;">Curp</th>
                                                            <th style="color: #fff;">RFC</th>
                                                            <th style="color: #fff;">Acción</th>
                                                        </thead>
                                                        <tbody></tbody>
                                                    </table>
                                                    <a href="{{ route('solicitud_trabajador'); }}" class="btn btn-primary" style=" background-color:#CEA845; border-color: #CEA845;">Agregar citado</a> 
                                                </div>
                                            </div>
                                        </div>    
                                        <div>
                                            <div id="div2"  class="col-xs-12 col-sm-12 col-md-6">
                                                <div class="form-group">
                                                    <input type="text" class="form-control" placeholder="descripcion" name="descripcion" oninput="this.value = this.value.toUpperCase()" required>
                                                    <p>Descripción de los hechos, motivo de la solicitud.</p> 
                                                    <div class="invalid-feedback">
                                                        La descripción es obligatoria.
                                                    </div>
                                                </div>    
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                            <div align="center">
                                                <a href="{{ route('publico'); }}" class="btn btn-primary" style=" background-color:#CEA845; border-color: #CEA845;">Guardar</a>    
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

    <script src="./public/assets/js/estadistica/estadistica.js"></script>
    <script src="https://cdn.datatables.net/2.1.5/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.1.5/js/dataTables.bootstrap4.js"></script>
       
    <script>
        fechaConflicto.max = new Date().toISOString().split("T")[0];

        $(document).ready(function() {
            $('#motivo_solicitud').change(function() {
                var opcionSeleccionada = $(this).val();
                var opcionTexto = $("#motivo_solicitud option:selected").text();
                if (opcionSeleccionada && opcionSeleccionada !== "") {
                    $('#tabla tbody').append(
                        '<tr><td>' + opcionTexto + '</td><td><button class="eliminar">Eliminar</button></td></tr>'
                    );
                }
            });

            $(document).on('click', '.eliminar', function() {
                $(this).closest('tr').remove();
            });
        });
    </script>
