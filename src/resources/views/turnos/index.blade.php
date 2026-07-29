@extends('layouts.app')


@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Turnos</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            @can('crear-turnos')
                                <div class="row g-3 align-items-end">
                                    

                                    <div class="col-12 col-md-2">
                                        <div class="form-group">
                                            <label for="ufs">Último Folio de Solicitudes en la Sede </label>
                                            <input id="ufs" name="ufs" type="text" class="form-control" value="{{ $last_sede_solicitud ?? '' }}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <div class="form-group">
                                            <label for="ufs">Último Folio de Solicitudes del Día</label>
                                            <input id="ufs" name="ufs" type="text" class="form-control" value="{{ $last_hora_solicitud ?? '' }}" readonly>
                                        </div>
                                    </div>
                            
                                    <div class="col-12 col-md-2">
                                        <div class="form-group">
                                            <label for="ufs">Último Folio de Ratificaciones en la Sede</label>
                                            <input id="ufs" name="ufs" type="text" class="form-control" value="{{ $last_sede_ratificacion ?? '' }}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <div class="form-group">
                                            <label for="ufs">Último Folio de Ratificacionesdel Día</label>
                                            <input id="ufs" name="ufs" type="text" class="form-control" value="{{ $last_hora_ratificacion ?? '' }}" readonly>
                                        </div>
                                    </div>
                                </div>

                                
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

<div id="nuevo_turno" style ="display: none;">
    <div>.</div>
    <div class="loader"></div>
</div>


@section('scripts')
    <script src="../public/assets/js/turnos/turnos.js"></script>
@endsection