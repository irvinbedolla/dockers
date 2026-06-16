@extends('layouts.app_1')
    <style>
            .card {
                border: none;
                margin: 30px 30px; /* posición de los botones en la vista */
            }
            .card-group {
                display: flex;
                justify-content: center;
                align-items: center;
                flex-wrap: wrap;
                width: 600px;
                gap: 24px; /* Define un espacio entre los botones */
            }
            /*Estilos boton*/
            .button-link {
                display: flex;
                justify-content: center; 
                align-items: center; 
                padding: 0; 
                font-size: 16px;
                text-align: center;
                width: 130px;   
                height: 65px;   
                background-color: #CEA845; 
                color: white; 
                text-decoration: none; 
                border-radius: 5px; /* Bordes redondeados */
                cursor: pointer;
                transition: transform 0.3s ease; /* Transición suave para el zoom */
                line-height: 1.2;
            }

            /* Efecto de zoom al pasar el ratón */
            .button-link:hover {
                transform: scale(1.2); /* Aumenta el tamaño del botón en un 20% */
            }

            /* Efecto de zoom al hacer clic en el botón */
            .button-link:active {
                transform: scale(1); /* Vuelve al tamaño original */
            }

            /* Efecto de cambio de color al pasar el ratón */
            .button-link:hover {
                background-color:#FFC3D0; /* Cambia el color de fondo al pasar el ratón */
                border-radius: 5px; /* Bordes redondeados */
                color: white; 
            } 
            .button-link:hover,
            .button-link:focus,
            .button-link:active {
                text-decoration: none !important;
                box-shadow: none !important; 
            }
            .responsive-img{
                max-width: 100%;
                height: auto;
            }
    </style>
@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Solicitud</h3>
        </div>
        <div class="section-body">
            <?php $fecha_actual = date('d-m-Y');?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">

                            <div class="card-body">
                                <center><img src="public/assets/images/Baner.png" class="responsive-img" width="750" height="350" alt="Descripción de la imagen"></center><br>
                                <center><div class="card-group">
                                    <div class="card">   <!-- Inicio Seer -->
                                        <div style="display: block; text-align: center;">
                                            <a href="{{ route('solicitud.industriaAuxiliar', ['tipo_solicitud' =>1]) }}" class="button-link">
                                                SOY <br>TRABAJADOR(A)
                                            </a> 
                                        </div> 
                                    </div>   <!-- fin Seer -->
                                    <div class="card">   <!-- Inicio Seer -->
                                        <div style="display: block; text-align: center;">
                                            <a href="{{ route('solicitud.industriaAuxiliarP', ['tipo_solicitud' =>2]) }}" class="button-link">
                                                SOY <br>PATRONAL INDIVIDUAL   
                                            </a>
                                        </div>  
                                    </div>
                                    <div class="card">  
                                        <div style="display: block; text-align: center;">
                                            <a href="{{ route('solicitud.industriaAuxiliar', ['tipo_solicitud' =>3]) }}" class="button-link">
                                                SOY <br>PATRONAL COLECTIVA    
                                            </a>
                                        </div>  
                                    </div> 
                                    <div class="card"> 
                                        <div style="display: block; text-align: center;">
                                            <a href="{{ route('solicitud.industriaAuxiliar', ['tipo_solicitud' =>4]) }}" class="button-link">
                                                SOY<br> SINDICATO
                                            </a>
                                        </div>  
                                    </div>
                                </center><br><br><br>
                            </div>
          
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
