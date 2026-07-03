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
                            <a class="btn btn-warning" href="{{ route('expedientes') }}"> Regresar</a>
                            @if($rol != "Capacitacion Admin")
                                <a class="btn btn-info" href="{{ route('expedientes.documento', $id) }}" onclick="nuevo_estadistica()";> Subir</a>
                            @endif
                                <div class="table-responsive">
                                    <table class="table table-striped mt-1">
                                        <thead style="background-color: #4A001F;">
                                            <th style="display: none;">ID</th>
                                            <th style="color: #fff;">Nombre del documento</th>
                                            <th style="color: #fff;">Documento</th>
                                            <th style="color: #fff;">Ver</th>
                                            @if($rol != "Capacitacion Admin")
                                                <th style="color: #fff;">Borrar</th>
                                            @endif
                                        </thead>
                                        <tbody>
                                            @foreach($documentos as $doc)
                                                <tr>
                                                    <td>{{ $doc->nombre }}</td>
                                                    <td>{{ $doc->documento }}</td>
                                                    <td><a target="_blank" class="btn btn-info" href="../../storage/app/documentos_personal/{{$doc->id_usuario}}/{{$doc->documento}}">PDF</a></td>
                                                    @if($rol != "Capacitacion Admin")
                                                    <td>
                                                        <form method="POST" action="{{ route('expedientes.delete', $doc->id) }} ">
                                                            @csrf
                                                            <input type="hidden" name="_method" value="DELETE">
                                                            <button class="btn btn-danger" onclick=editar_rol(); type="submit">Eliminar</button>
                                                        </form>
                                                    </td>
                                                    @endif
                                                </tr>
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

<div id="menu_carga" style ="display: none;">
    <div>.</div>
    <div class="loader"></div>
</div>


@section('scripts')
    <script src="../../public/js/estadistica/estadistica.js"></script>
@endsection
