<!doctype html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="csrf-token" content="{{ csrf_token() }}"/>
        <title>Si concilio</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>

        <!-- Bootstrap 5.3.3 -->
        <link href="../public/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
       
        <!-- Ionicons -->
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
            body {
                display: flex;
                justify-content: center;
                align-items: center;
                font-family: Arial, sans-serif;
                background-color: #f4f4f9;
            }
            .chat-box {
                background-color: white;
                padding: 10px;
                box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
                max-width: 500px;
                max-height: 600px;
                margin: 0 auto;
            }
            .form-container h2 {
                margin-bottom: 10px;
                text-align: center;
            }
            h2 {
                text-align: center;
                color: #CEA845;
            }
            h1 {
                text-align: center;
                color:  #496163;
            }
            .preg{
                color: #CEA845;
                font-weight:bold;
            }
            .response {
                margin-top: 20px;
                padding: 10px;
                background-color: #f1f1f1;
                border-radius: 5px;
            }
            .btn {
                background-color: #CEA845;
                color: white;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                justify-content:center;
                align-items:center;
            }
            .btn:hover {
                background-color: #CEA845;
            }
            .needs-validation
            {
                background-color: #f8f9fa;
                height: 100px;
            }
            
        </style>

    </head>
    <main>
        <div id="app">
            <!-- Formulario -->
            <form class="needs-validation novalidate" method="POST" style="position: relative; top:10px;" action="{{route('RespuestasChat.store')}}">
                &nbsp;&nbsp;<img src="public/assets/images/Logos 2.png" class="img" width="240" height="90">
                @csrf
                <div class="chat-box" style="position: relative; top:10px; right:0px; left:0px;">
                    <h1>Asistente Centro de Conciliación</h1>
                    <p align="center"><b>¿Cómo te podemos ayudar?</b></p>
                    <div class="form-container">
                        <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-6">
                                <div class="form-group">
                                    <p for="nombre_completo" class="preg">Nombre completo</p>
                                    <input type="text" class="form-control" placeholder="*Nombre(s)" name="nombre_completo" oninput="this.value = this.value.toUpperCase()" required>
                                    <div class="invalid-feedback">
                                        El nombre es obligatorio.
                                    </div>
                                </div> <br>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6">
                                <div class="form-group">
                                    <div class="form-group">
                                        <p for="ciudad" class="preg">Ciudad</p>
                                        <input type="text" class="form-control" placeholder="*Ciudad" name="ciudad" oninput="this.value = this.value.toUpperCase()" required>
                                        <div class="invalid-feedback">
                                            La ciudad es obligatoria.
                                        </div>
                                    </div>
                                </div>  <br> 
                            </div>
                            <div class="form-group">
                                <div class="form-group">
                                    <!-- muestra las preguntas guardadas -->
                                    <label class="preg" for="pregunta">Selecciona una pregunta:</label><br>
                                    <select class="form-control" name="idPregunta" id="preguntasChat" required>
                                        @foreach($preguntasChats as $preguntasChat)
                                            <div>
                                                <option value="{{ $preguntasChat->id }}"> {{ $preguntasChat->pregunta }} </option>
                                            </div>
                                        @endforeach 
                                    </select>
                                </div>
                            </div><br>            
                        </div><br>
                        <button type="submit" class="btn" style="position: relative; top:0px; right:0px; left:225px;">
                            Enviar
                        </button>     
                    </div>
                </div>
            </form>
        </div>    
    </main>
</html>