@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Ratificaciones</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="text-center">Consulta Ratificaciones</h3>
                            <form method="POST" action="{{ route('ratificaciones_busqueda') }}">
                                @csrf
                                <div class="row">
                                    <div class="col-xs-12 col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <label for="fecha_inicio">Fecha de inicio</label>
                                            <input type="date" class="form-control" name="fecha_inicio" required>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <label for="fecha_final">Fecha final</label>
                                            <input type="date" class="form-control" name="fecha_final" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer text-center">
                                    <button type="submit" class="btn btn-primary" onclick=consultar_estadistica(); >Consultar</button>
                                </div>
                            </form>
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



