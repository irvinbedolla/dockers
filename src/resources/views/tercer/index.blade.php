@extends('layouts.app')


@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Tercer Encuentro</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <!--a class="btn btn-info" href="{{ route('pdf_tercer_encuentro') }}" target="_blank">PDF</a-->
                           
                                    <table id="example" class="table-striped" style="width:100%">
                                        <thead style="background-color: #354647;">
                                            <th style="color: #fff; width: 3%">Folio</th>
                                            <th style="color: #fff; width: 20%">Nombre</th>
                                            <th style="color: #fff; width: 10%">Teléfono</th>
                                            <th style="color: #fff; width: 20%">Email</th>
                                            <th style="color: #fff;">Lugar de visita</th>
                                            <th style="color: #fff; width: 7%">Sexo</th>
                                            @auth
                                                @hasanyrole('Super Usuario|Tercer Encuentro')
                                                    <th style="color: #fff;">Registro de asistencia</th>
                                                    <th style="color: #fff;">Editar datos</th>  
                                                @endrole
                                            @endauth
                                        </thead>
                                        <tbody>
                                            @foreach($personas as $persona)
                                                <tr>
                                                    <td>{{$persona->id}}</td>
                                                    <td>{{$persona->nombre}} {{$persona->primer_apellido}} {{$persona->segundo_apellido}}</td>
                                                    <td>{{$persona->telefono}}</td>
                                                    <td>{{$persona->correo}}</td>
                                                    <td>{{$persona->lugar}}</td>
                                                    <td>{{$persona->sexo}}</td>
                                                    @auth
                                                        @hasanyrole('Super Usuario|Tercer Encuentro')
                                                            <td><a type="button" class="btn btn-success text-white" href="{{ route('registro_asistencia_te', $persona->id) }}">Registro</a></td>
                                                            <td><a type="button" class="btn btn-primary text-white" href="{{ route('editar_datos_te', $persona->id) }}">Editar</a></td>
                                                        @endrole
                                                    @endauth
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('body_end')
<div id="nuevo_turno" style ="display: none;">
    <div>.</div>
    <div class="loader"></div>
</div>
@endpush


@section('scripts')
    <script src="{{ asset('assets/js/turnos/turnos.js') }}"></script>
@endsection