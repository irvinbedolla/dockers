@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Notificaciones</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="text-center">Consulta Notificaciones</h3>
                            <form method="POST" action="{{ route('notificaciones_busqueda') }}">
                                @csrf
                                <div class="row">
                                    <div class="col-xs-12 col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <label for="fecha_inicio">Fecha de inicio</label>
                                            <input type="date" class="form-control" name="fecha_inicio" id="fecha_inicio" required>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <label for="fecha_final">Fecha final</label>
                                            <input type="date" class="form-control" name="fecha_final" id="fecha_final" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-center mt-4">
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
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const fechaInicio = document.getElementById("fecha_inicio");
            const fechaFinal = document.getElementById("fecha_final");

            // Mostrar el calendario cuando se hace clic en los campos
            fechaInicio.addEventListener("click", function() {
                this.showPicker?.(); // El signo ? asegura compatibilidad
            });

            fechaFinal.addEventListener("click", function() {
                this.showPicker?.();
            });
        });
    </script>
@endsection



