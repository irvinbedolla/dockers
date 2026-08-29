@extends('layouts.app')
@section('title', 'Retrocesos')
@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Retrocesos</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-xs-12 col-sm-4 col-md-4">
                                    <div class="card text-center">
                                        <div class="card-body">
                                            <h5 class="card-title">Audiencias</h5>
                                            <a href="{{ route('retroceso_audiencia') }}" class="btn btn-danger" style="width: 100%">
                                                <i class="bi bi-arrow-counterclockwise"></i> Retroceso
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-4 col-md-4">
                                    <div class="card text-center">
                                        <div class="card-body">
                                            <h5 class="card-title">Ratificaciones</h5>
                                            <a href="{{ route('retroceso_ratificacion') }}" class="btn btn-danger" style="width: 100%">
                                                <i class="bi bi-arrow-counterclockwise"></i> Retroceso
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-4 col-md-4">
                                    <div class="card text-center">
                                        <div class="card-body">
                                            <h5 class="card-title">Cumplimientos</h5>
                                            <button type="button" class="btn btn-secondary" style="width: 100%" disabled title="Próximamente">
                                                <i class="bi bi-arrow-counterclockwise"></i> Retroceso
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <br>
                            <a href="{{ route('configuracion') }}" class="btn btn-warning">Regresar</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
