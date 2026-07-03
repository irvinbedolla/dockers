<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <meta name="csrf-token" content="{{ csrf_token() }}"/>
        <title>Sí Concilio</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>

        <!-- Bootstrap 5.3.3 -->
        <link href="{{ public_path('assets/css/bootstrap.min.css') }}" rel="stylesheet">
        
    
        <style>
            @page {
                margin: 0px 0px;
            }
            body{
                padding-top: 12%;
            }
            main{
                margin: 60px 0 40px 0; /*Para colocar el texto*/
            }
            header {
                position: fixed;
                top: -100px;
                left: 0;
                right: 0;
                height: 100px;
                text-align: center;
                font-size: 14px;
            }

            footer {
                position: fixed;
                bottom: -60px;
                left: 0;
                right: 0;
                height: 50px;
                text-align: center;
                font-size: 12px;
            }
            .content {
                font-family: sans-serif;
                font-size: 15px;
                text-align: justify;
                margin-left: 3cm;     
                margin-right: 3cm; 
                line-height: 1.3;
            }
            .fondo-membrete {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: -1;
            }
            .sangria {
                margin-left: 20px;
                text-indent: -15px; 
                padding-left: 15px;
            }
            .table-compacta td, 
            .table-compacta th {
                padding: 2px 5px !important; /* Reduce el espacio interno arriba y abajo */
                line-height: 1.1 !important;  /* Ajusta la altura del texto */
                vertical-align: middle;
            }
            .table-compacta {
                margin-bottom: 10px !important; /* Reduce espacio entre tablas */
            }
            /* Contenedor que agrupa las firmas */
            .salto-inteligente {
                display: block;
                height: 2cm;            
                margin-bottom: -2cm;   
                page-break-inside: avoid;
            }

            .contenedor-firmas {
                page-break-inside: avoid; 
            }
            .titulos{
                font-family: sans-serif;
                font-size: 17px;
                margin-left: 1cm;     
                margin-right: 1cm; 
                line-height: 1.2;
            }
            .datos{
                font-family: sans-serif;
                font-size: 15px;
                margin-left: 1cm;     
                margin-right: 1cm; 
                line-height: 1.2;
            }
            .contenedor-firmas {
                width: 100%;
                margin-top: 50px;
                font-family: Arial, sans-serif;
                color: #000;
            }
            .texto-centro { 
                text-align: center; 
            }
            .texto-negrita { 
                font-weight: bold; 
                margin: 0; 
                font-size: 14px;
            }
            
        </style>
    </head>
    <body>
        <img src="{{ public_path('assets/images/pdf_Siconcilio.jpg') }}" class="fondo-membrete">
        <footer>
            
        </footer>
        <main>
            <div class="content">
                <div class="titulos">
                    <p><center><b>
                        DEPARTMENTO DE CONCILIACIÓN, GÉNERO E IGUALDAD <br>ATENCIÓN PARA CASOS PREVISTOS EN EL ARTÍCULO 685 TER DE LA LEY FEDERAL DEL TRABAJO Y SITUACIONES DE VIOLENCIA LABORAL
                    </b></center></p>
                
                <p> <center> Fecha: {{$caso->fecha}}    Folio: {{$caso->expediente}}</center></p>
                
                <p><center><b>
                    AVISO DE PRIVACIDAD:
                </b></center></p><br>
                </div>
                <p>
                    <b>El Centro de Conciliación Laboral del Estado de Michoacán de Ocampo</b>, con domicilio en Boulevard García de León número 1575, Colonia Chapultepec Oriente, Código Postal 58260, en la ciudad de Morelia, Michoacán, en su carácter de sujeto obligado y 
                    responsable del tratamiento de los datos personales que se recaben a través del presente <b>Formato de Atención para Casos Previstos en el Artículo 685 Ter de la Ley Federal del Trabajo y Situaciones de Violencia Laboral</b>, se compromete a proteger dicha 
                    información conforme a lo dispuesto en los artículos 97 y 101 de la Ley de Transparencia, Acceso a la Información Pública y Protección de Datos Personales del Estado de Michoacán de Ocampo, así como demás normativa aplicable. 
                    <br>
                    <b>Datos personales recabados:</b> Los datos personales que se recaban de la persona solicitante incluyen: nombre completo, edad, sexo, puesto, área de trabajo, número telefónico, correo electrónico, así como datos relacionados con la empresa o patrón, nombre y cargo 
                    del jefe inmediato, y en su caso, cualquier otra información proporcionada de manera voluntaria para la atención del caso.<br>
                    <b> Finalidad del tratamiento:</b> Los datos personales recabados serán utilizados exclusivamente para: 

                    <ul style="margin-top: 0; padding-left: 20px; list-style-type: square;">
                        <li>Brindar atención, orientación y registro de casos relacionados con violencia laboral o situaciones previstas en el artículo 685 Ter de la Ley Federal del Trabajo.</li>
                        <li>Dar seguimiento a los hechos manifestados por la persona solicitante.</li>
                        <li>Canalizar, en su caso, a las áreas competentes o iniciar el procedimiento de conciliación correspondiente.</li>
                        <li>Integrar expedientes y generar estadísticas internas que permitan mejorar los servicios institucionales.</li>
                    </ul>
                    <b>Confidencialidad y transferencia de datos:</b> La información proporcionada será tratada de manera confidencial y no será compartida con terceros ajenos al procedimiento, salvo en los casos en que sea necesario atender requerimientos de autoridad competente, 
                    conforme a la legislación aplicable. En ningún caso se realizará la transferencia de datos personales sin el consentimiento del titular, salvo las excepciones previstas en la ley. <br>
                    <b>Consentimiento:</b> De conformidad con lo dispuesto en el artículo 101 de la Ley de Transparencia, el titular de los datos personales otorga su consentimiento para el tratamiento de los mismos al proporcionar su información a través del presente formato. 
                </p>
                <div class="titulos">
                <p><center><b>
                    DATOS DE LA PERSONA SOLICITANTE:
                </b></center></p><br>
                </div>
                <div class="datos">
                <p>
                    <b>Nombre completo:</b> {{$recepcion->solicitante}}.<br><br>
                    <b>Edad:</b> {{$recepcion->edad}} años.<br><br> 
                    <b>Género:</b> @if($recepcion->sexo == 'M') Femenino. @elseif($recepcion->sexo == 'H') Masculino.  @elseif($recepcion->sexo == 'NB') No binario. @else LGBTTTIQ. @endif<br><br>
                    <b>Teléfono de contacto:</b> {{$recepcion->telefono}} <br><br>
                    <b>Correo electrónico:</b> {{$recepcion->correo}}<br>
            
                </p>
                </div>
                <div class="titulos">
                <p><center><b>
                    DATOS DE LA FUENTE DE EMPLEO:
                </b></center></p><br>
                </div>
                <div class="datos">
                <p>
                    <b>Nombre de la empresa o persona empleadora:</b> {{$caso->empresa}}.<br><br>
                    <b>Área de adscripción:</b> {{$caso->area_adscripcion}}.<br><br>
                    <b>Puesto:</b> {{$caso->puesto}}.<br><br>
                    <b>¿Cuál es el nombre del jefe inmediato?</b> {{ $caso->jefe_inmediato }}. 
                    
                    <br><br><br><br>

                </p>
                <p>
                    <b>1. Tipo de situación que enfrenta:</b> 
                    @if($caso->tipo_caso==='Discriminación') Ha sido objeto de discriminación. {{ $caso->motivos }}.
                    @elseif ($caso->tipo_caso==='Acoso u hostigamiento sexual') Ha recibido acoso sexual por parte de un superior jerárquico o de un compañero de trabajo.
                    @elseif ($caso->tipo_caso==='Riesgo o accidente') Ha sufrido un riesgo o accidente de trabajo.
                    @elseif ($caso->tipo_caso==='Malos tratos o violencia') Ha sido objeto de malos tratos o violencia laboral.
                    @else No aplica.
                    @endif
                    <br><br>
                    <b>2. Frecuencia con la que han sucedido los hechos:</b> @if($caso->frecuencia === 'Continua') De manera continua, hasta la fecha atual. @else {{$caso->frecuencia}}. @endif<br><br>
                    <b>3. Cambios que se dieron en su situación laboral después de los hechos:</b> 
                    @if ($caso->situacion_laboral ==='Sigue igual' ) Sigue igual.
                    @elseif ($caso->situacion_laboral ==='Tension estres incomodidad' )Tension, estrés e incomodidad en el área de trabajo.
                    @elseif ($caso->situacion_laboral === 'Cambio area') Le cambiaron de área.
                    @else {{ $caso->situacion_laboral }}.
                        
                    @endif
                    <br><br>
                    <b>4. ¿La persona afectada comunicó los hechos a alguien de su ara de trabajo? </b> @if($caso->descripcion_persona === NULL) No. @else Sí. {{$caso->descripcion_persona}} @endif<br><br>
                    <b>5. Descripción de las conductas manifestadas:</b> {{$caso->descripcion_conductas}}<br><br>
                    <b>6. Observaciones y comentarios:</b> {{$caso->observaciones}}<br><br>
                    
                </p>
                </div>
                <p>
                    <b>Nota:</b> En caso de advertirse que la persona usuaria se encuentra dentro de alguno de los supuestos de excepción establecidos en el Artículo 685 Ter de la LFT, se le informará que no se encuentra obligada a agotar la instancia conciliatoria; asimismo, se hará constar 
                    que manifiesta no haber recibido orientación jurídica previa, que reconoce encontrarse en un caso de excepción y que, con pleno conocimiento de ello, expresa su voluntad de continuar con el procedimiento de conciliación; de igual forma, se asentará que le fue 
                    leído el Decálogo de Derechos y Obligaciones de las y los Usuarios, quedando debidamente enterada de su contenido.<br><br>

                    En caso de que la persona usuaria determine no continuar con el procedimiento, y una vez proporcionada la asesoría jurídica correspondiente, autoriza al Centro de Conciliación para ser canalizada ante el Departamento de la Procuraduría Local de la Defensa
                    del Trabajo, ubicado en {{ $caso->ubicacion }}, a efecto de que le brinden la representación legal que corresponda. Asimismo, en caso de advertirse la posible comisión de un delito, se procederá a su 
                    canalización ante las dependencias competentes, tales como la Comisión de Atención a Víctimas, la SEIMUJER y la COEPREDV con el propósito de garantizar una atención integral. 
                </p> 
                
                <div class="salto-inteligente"></div>
                <<div class="contenedor-firmas">
                    <h3 class="texto-centro texto-negrita" style="letter-spacing: 5px;">A C E P T O</h3><br><br><br><br>
                    <p><center><b>___________________________________<br> <br> {{$recepcion->solicitante}}</b></center> </p><br><br>
                    <div class="row">
                        <div class="col-12 text-center">
                            <div style="display: inline-block; margin-right: 30px;">
                                <p><center><b>___________________________________<br>  <br> {{$auxiliar->name}}<br></b></center></p>
                            </div>
                                    
                            <div style="display: inline-block;">
                                <p><center><b>Vo. Bo.<br><br><br></b></center></p>
                                <p><center><b>___________________________________<br><br>Lic. Mariam Samantha Cazarez Sánchez<br></b></center></p>
                            </div>
                        </div>
                    </div>
                    
                        
                </div>                
            </div>
            <script type="text/php">
                if (isset($pdf)) {
                    $font = $fontMetrics->get_font("Arial", "normal");
                    $size = 10;
                    $y = $pdf->get_height() - 44;
                    $x = ($pdf->get_width() / 2) - 50;
                    $text = "Página {PAGE_NUM} de {PAGE_COUNT}";
                    $pdf->page_text($x, $y, $text, $font, $size, array(0, 0, 0));
                }
            </script>
        </main>
    </body>