@extends('layouts.app')
@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Plantillas</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-xs-4 col-sm-4 col-md-4">
                                    <div align="center">
                                        <a href="#" class="btn btn-primary" style="width: 100%">Plantillas Solicitudes</a>
                                    </div>
                                </div>
                                
                                <div class="col-xs-12 col-sm-4 col-md-4">
                                    <div align="center">
                                        <a href="{{ route('plantillas_ratificaciones') }}" class="btn btn-primary"  style="width: 100%">Plantillas Ratificaciones</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection