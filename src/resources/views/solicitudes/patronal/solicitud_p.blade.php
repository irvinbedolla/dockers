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
                                    <h3 class="text-center" style="color:#CEA845;">Solicitud</h3>
                                    <div style="background-color:#D2D3D5; width:100%; height:40px;">
                                        <h3 class="text-center" style="color:black;">Datos generales de la solicitud</h3>
                                    </div>   
                                    <h6 class="text-center" style="color: #828282"><b>Requisitos para realizar tu solicitud:</b></h6> 
                                    <h6 class="text-center" style="color: #828282"><b>*Teléfono, correo electrónico, identificación Oficial del empleador, identificación Oficial del Representante Legal (en los casos que así se requiera), Documento que acredite la personería 
                                        (Carta Poder, Instrumento Notarial), Constancia de Situación Fiscal (obligatorio en caso de ser Persona Moral), Domicilio de la fuente laboral.</b></h6> 
                                    <!--Se realiza el envío de datos con formulario de Laravel Collective-->
                                    <form class="needs-validation" novalidate method="POST" action="{{route('parte1Patronal')}}">
                                        @csrf
                                        <input type="hidden" name="tipo_solicitud" value="{{ $tipo_solicitud }}">
                                        <div class="row">
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Municipio de la Fuente de Empleo <span style="color:red;">(*)</span></label>
                                                    <select id="dSolicitud" class="form-control" name="dSolicitud" required>
                                                        <option value="">Seleccione</option>
                                                        @foreach($municipios as $municipio)
                                                            <option value="{{$municipio['id']}}" data-delegacion-id="{{ $municipio['delegacion_id'] }}">
                                                                {{ $municipio['nombre'] }}</option>
                                                        @endforeach
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        El municipio es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <!--<div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Delegación <span style="color:red;">(*)</span></label>
                                                    <select class="form-control" id="delegacion" name="delegacion">
                                                        <option value="">Seleccione</option>
                                                        <option value="Morelia">Morelia</option>
                                                        <option value="Uruapan">Uruapan</option>
                                                        <option value="Zamora">Zamora</option>
                                                        <option value="Lázaro Cárdenas">Lázaro Cárdenas</option>
                                                        <option value="Zitácuaro">Zitácuaro</option>
                                                        <option value="Sahuayo ">Sahuayo </option>
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        La delegación es obligatoria.
                                                    </div>
                                                </div>
                                            </div>-->
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="delegacion">Delegación <span style="color:red;">(*)</span></label>
                                                    <select class="form-control" id="delegacion" name="delegacion" required>
                                                        <option value="">Seleccione</option>
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        La delegación es obligatoria.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-6 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Objeto de la solicitud <span style="color:red;">(*)</span></label>
                                                    <select  class="form-control" id="motivo_solicitud">
                                                        <option value="">Seleccione</option>
                                                        @foreach($mostrarMotivos as $motivo)
                                                            <option value="{{$motivo['id']}}">{{$motivo['motivo']}}</option>
                                                        @endforeach
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        El objeto de solicitud es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="div1"  class="col-xs-12 col-sm-12 col-md-12"><br>
                                                <table id="tabla" name="motivo_solicitud[]" class="table table-striped mt-1" style="margin: 0 center; text-align:center;">
                                                    <thead style="background-color: #D2D3D5;">
                                                        <th style="color: black;">Objeto de la Solicitud</th>
                                                        <th style="color: black;">Acción</th>
                                                    </thead>
                                                    <tbody></tbody>
                                                </table>
                                            </div>
                                            <div id="div1"  class="col-xs-12 col-sm-12 col-md-5">
                                                <p>Rama Industrial del Negocio</p>
                                                <div class="form-group">
                                                    <label for="name">Paso 1. Rama Industrial <span style="color:red;">(*)</span></label>
                                                    <select id="ramaIndustrial" class="form-control" name="ramaIndustrial" required>
                                                        <option value="">Seleccione</option>
                                                        @foreach($ramas as $rama)
                                                            <option value="{{$rama['id']}}">{{$rama['rama_industrial']}}</option>
                                                        @endforeach
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        El campo rama industrial es obligatorio.
                                                    </div>
                                                </div>
                                            </div>                  
                                            <div id="div2"  class="col-xs-12 col-sm-12 col-md-7">
                                                <p style="color: white">.</p>
                                                <div class="form-group">  
                                                    <label for="name">Paso 2: Actividad Económica del Patrón(a)/Empresa <span style="color:red;">(*)</span>   <em>Ejemplos: comercio de productos al por menor, construcción, servicios médicos...</em></label>
                                                    <input type="text" name="actividad_economica" id="actividad_economica" oninput="this.value = this.value.toUpperCase()" class="form-control" required> 
                                                <!--<select id="actividad_economica" name="actividad_economica" class="form-control" disabled>
                                                        <option value=""> --Primero selecciona una rama industrial --</option>  
                                                    </select>-->
                                                    <div class="invalid-feedback">
                                                        El campo actividad económica del patrón es obligatorio.
                                                    </div>
                                                </div>
                                            </div>  
                                        </div>
                                        
                                        <div align="center">
                                            <button type="submit" class="btn btn-primary" style="background-color:#CEA845; border-color: #CEA845">Guardar</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
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
       
    <script>
        //Fecha conflicto limitada a la fecha actual
        //fechaConflicto.max = new Date().toISOString().split("T")[0];
        //Solicitud en línea trabajador parte 1
        $(document).ready(function() {
            let motivosSeleccionados = [];

            $('#motivo_solicitud').change(function() {
                var opcionSeleccionada = $(this).val();
                var opcionTexto = $("#motivo_solicitud option:selected").text();

                // Verifica si ya fue agregado ese motivo
                if (motivosSeleccionados.includes(opcionSeleccionada)) {
                    alert('Este motivo ya ha sido seleccionado.');
                    $(this).val('');
                    return;
                }

                motivosSeleccionados.push(opcionSeleccionada);

                $('#tabla tbody').append(
                    '<tr data-id="' + opcionSeleccionada + '">' +
                        '<td>' + opcionTexto + '</td>' +
                        '<td><button type="button" class="eliminar btn btn-danger btn-sm">Eliminar</button></td>' +
                    '</tr>'
                );

                $('#div1').append(
                    '<input type="hidden" name="motivo_solicitud[]" value="' + opcionSeleccionada + '" id="input-motivo-' + opcionSeleccionada + '">'
                );

                // Reinicia el select
                $(this).val('');
            });

            // Eliminar fila e input hidden
            $(document).on('click', '.eliminar', function() {
                var fila = $(this).closest('tr');
                var idMotivo = fila.attr('data-id');

                // Elimina input y fila
                $('#input-motivo-' + idMotivo).remove();
                fila.remove();

                // Actualiza la lista de los motivos seleccionados
                motivosSeleccionados = motivosSeleccionados.filter(id => id !== idMotivo);
            });
        });

        //Dependiendo del Municipio seleccionado muestra la delegación y oficina de apoyo que le corresponde
        document.addEventListener('DOMContentLoaded', function () {
            const delegacionSelect = document.getElementById('delegacion');
            const municipioSelect = document.getElementById('dSolicitud');

            const delegaciones = {
                1: ['Morelia'],
                2: ['Zitácuaro'],
                3: ['Uruapan'],
                4: ['Lázaro Cárdenas'],
                5: ['Zamora'],
                6: ['Sahuayo']
            };

            municipioSelect.addEventListener('change', function () {
                const selectedOption = municipioSelect.options[municipioSelect.selectedIndex];
                const delegacionId = selectedOption.getAttribute('data-delegacion-id');

                // Limpia el select de delegación
                delegacionSelect.innerHTML = '<option value="">Seleccione</option>';

                if (delegacionId && delegaciones[delegacionId]) {
                    delegaciones[delegacionId].forEach(delegacion => {
                        const option = document.createElement('option');
                        option.value = delegacion;
                        option.textContent = delegacion;
                        delegacionSelect.appendChild(option);
                    });
                }
            });
        });
    </script>
