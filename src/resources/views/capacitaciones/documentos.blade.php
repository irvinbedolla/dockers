@extends('layouts.app_editar')

@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Documentos</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <a class="btn btn-warning" href="{{ route('capacitaciones.personas') }}"> Regresar</a>
                                <div class="table-responsive">
                                    <table class="table table-striped mt-1">
                                        <thead style="background-color: #4A001F;">
                                        <th style="display: none;">ID</th>
                                            <th style="color: #fff;">Nombre del documento</th>
                                            <th style="color: #fff;">Documento</th>
                                            <th style="color: #fff;">Ver</th>
                                        </thead>
                                        <tbody>
                                            @foreach($documentos as $doc)
                                                <tr>
                                                    <td>{{ $doc->nombre }}</td>
                                                    <td>{{ $doc->documento }}</td>
                                                    <td><a target="_blank" class="btn btn-info" href="../../storage/app/documentos_personal/{{$doc->id_usuario}}/{{$doc->documento}}">PDF</a></td>
                                                </td>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            <!-- Centramos la paginación a la derecha-->
                            <div class="pagination justify-content-end">
                            </div>                        
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

