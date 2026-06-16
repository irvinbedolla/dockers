@extends('layouts.app')
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
@php     
    $direccion_sede='';
    if($delegacion->delegacion === 'Morelia'){
        $direccion_sede='BLVD. GARCÍA DE LEÓN NO. 1575, COL. CHAPULTEPEC ORIENTE, C.P. 58260 MORELIA, MICHOACÁN DE OCAMPO, con un horario de atención Lunes a Viernes de 9:00 am a 5:00 pm.';
    }    
    if($delegacion->delegacion === 'Uruapan'){
        $direccion_sede='NUEVO PARICUTÍN NO. 308, COL. JARDINES DE SAN RAFAEL, C.P. 30136 URUAPAN, MICHOACÁN DE OCAMPO. SE ENCUENTRA DENTRO DEL RECINTÓ DONDE ESTA RENTAS DEL
            ESTADO, POR LA CLÍNICA DEL IMSS NO.76, con un horario de atención Lunes a Viernes de 9:00 am a 4:00 pm.';
    }
    if($delegacion->delegacion === 'Zamora') {
        $direccion_sede='JUSTO SIERRA PONIENTE NO. 290, COL. JARDINES DE CATEDRAL, C.P. 59600 ZAMORA, MICHOACÁN DE OCAMPO, con un horario de atención Lunes a Viernes de 9:00 am a 3:00 pm.';
    }  
    if($delegacion->delegacion === 'Zitácuaro') {
        $direccion_sede='5 DE MAYO NORTE NO. 03, PISO 3 COL. CENTRO, C.P. 61500 ZITÁCUARO, MICHOACÁN DE OCAMPO, con un horario de atención Lunes a Viernes de 9:00 am a 3:00 pm.';
    } 
    if($delegacion->delegacion === 'Lázaro Cárdenas') {
        $direccion_sede='PARACHO NO. 26, COL. 600 CASAS, C.P. 60950 LÁZARO CÁRDENAS, MICHOACÁN DE OCAMPO, con un horario de atención Lunes a Viernes de 9:00 am a 3:00 pm.';
    }  
    if($delegacion->delegacion === 'Sahuayo') {
        $direccion_sede='AV. UNIVERSIDAD SUR NO. 3000, COL. LOMAS DE UNIVERSIDAD, C.P. 59103 SAHUAYO DE MORELOS, MICHOACÁN DE OCAMPO, con un horario de atención Lunes a Viernes de 9:00 am a 3:00 pm.';
    } 
@endphp
@section('content')
        <section class="section">
            <div class="section-header">
                <h3 class="page__heading">Solicitud</h3>
            </div>
            <div class="section-body">
                <div class="row"> 
                    <div class="col-lg-12" >
                        <!--<div class="card">-->
                            <div class="card-body">                                <div class="alert alert-success alert-dismissible fade show" role="alert" style="text-align: justify;">
                                <input type="hidden" name="id" value="{{ $id }}">
                                <strong>¡Registro completo!</strong><br>
                                <label>Tu solicitud fue capturada correctamente, tu número de folio es: "{{$id}}", Debes ingresar a 
                                http://siconcilio.cclmichoacan.gob.mx/ en el apartado de buzón electrónico con: "{{$mensaje}}"
                                <br><br>

                                NOTA: En caso de detectar algún error en los datos proporcionados, el personal del centro se pondrá en contacto contigo.<br><br>
                                Para dudas acude a tu Delegación u Oficina de Apoyo del Centro de Conciliación Laboral en {{$delegacion->delegacion}}.<br>
                                Ubicada en {{$direccion_sede}}
    
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <a href="{{ route('solicitudes_index'); }}" class="btn btn-primary" style=" background-color:#CEA845;border-color:#CEA845;">Terminar</a>        
                        <!--</div>-->
                    </div>
                </div>
            </div>
        </section>
    <script src="./public/assets/js/estadistica/estadistica.js"></script>
    <script src="https://cdn.datatables.net/2.1.5/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.1.5/js/dataTables.bootstrap4.js"></script>
       
@endsection