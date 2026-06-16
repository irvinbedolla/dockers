<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
        <meta name="generator" content="Ing. ISBM">
        <title>Si Concilio</title>
        
        <!-- Bootstrap 5.3.3 -->
       
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">

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
            p{
                text-align: justify;
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
            <form class="needs-validation novalidate" method="POST" style="position: relative; top:10px;" action="{{route('RespuestasChat.storeUno')}}">   
                &nbsp;&nbsp;<img src="../public/assets/images/Logos 2.png" class="img" width="240" height="90">
                @csrf
                <div class="chat-box" style="position: relative; top:10px; right:0px; left:0px;">
                    <h1>Asistente Centro de Conciliación</h1>
                    <div class="form-container">
                        <div class="row">
                            <h2>Hola {{ $registro->nombre_completo }}</h2>
                            @if($registro)
                                @if(isset($idPregunta))
                                    @foreach($res as $re)
                                        <p><b>{{ $re->pregunta }}</b></p>                   
                                        <p>{{ $re->respuesta }}</p>      
                                        <br>
                                    @endforeach  
                                @endif
                            @endif
                        </div>
                        <div class="row"> 
                            <div class="form-group">
                                <label class="preg" for="pregunta">Selecciona una pregunta:</label>
                                <select  class="form-control" name="idPregunta" id="preguntasChat" required autofocus>
                                    @foreach($preguntasChats as $preguntasChat)
                                        <div>
                                            <option value="{{ $preguntasChat->id }}"> {{ $preguntasChat->pregunta }} </option>
                                        </div>
                                    @endforeach 
                                </select>  
                            </div>
                            <input type="hidden" class="form-control" name="id" value="{{ $id }}">       
                        </div>
                        <br>
                        <button type="submit" style="position: relative; top:0px; right:0px; left:225px;" class="btn">Enviar</button>
                        <br><br>
                    </div>
                </div>       
            </form>
        </main>
    <script>
        window.onload = function() {
            document.LoginForm.occupationSelect.focus();
        }
    </script>

</html>