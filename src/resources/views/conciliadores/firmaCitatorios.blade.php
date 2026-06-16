@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Citatorios entrega el trabajador</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            @if($solicitudes->isEmpty())
                                <div class="alert alert-info">
                                    No hay solicitudes pendientes de firma.
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-striped text-center">
                                        <thead style="background-color: #4A001F;">
                                            <th style=color:white;>Solicitud</th>
                                            <th style=color:white;>Solicitante</th>
                                            <!--<th style=color:white;>Citados</th>-->
                                            <th style=color:white;>Acción</th>
                                        </thead>
                                        <tbody>
                                            @foreach($solicitudes as $solicitud)
                                                <tr>
                                                    <td>{{ $solicitud->NUE }}</td>
                                                    <td>{{ $solicitud->nombre_solicitante }}</td>
                                                    {{--<td>
                                                        <ul class="list-unstyled mb-0">
                                                            @foreach($solicitud->citados as $citado)
                                                                <li>{{ $citado->nombre }} {{ $citado->primer_apellido }} {{ $citado->segundo_apellido }}</li>
                                                            @endforeach
                                                        </ul>
                                                    </td>--}}
                                                    <td>
                                                        <a href="{{ route('descargarCitatorios', $solicitud->id) }}" class="btn btn-primary btn-sm">Firmar citatorios</a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                            <!-- Centramos la paginación a la derecha-->
                            <div class="pagination justify-content-end"></div>                        
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

    <div id="nuevo_usuario" style ="display: none;">
        <div>.</div>
        <div class="loader"></div>
    </div>

@section('scripts')
    <script>
         $('.open-modal').click(function() {
            const id = $(this).data('id');
            document.getElementById('modal-id').value = id;
        });
    </script>
    <script src="../public/js/usuarios/usuarios.js"></script>
@endsection