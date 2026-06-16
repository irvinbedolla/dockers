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
                            @if(session('success'))
                                <div class="alert alert-success" role="alert">
                                    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                                </div>
                            @endif
                            @if(isset($persona))
                                <h4 style="margin-bottom: 25px;">Editar datos de: {{ $persona->nombre }} {{ $persona->primer_apellido }} {{ $persona->segundo_apellido }}</h4>
                                <p>Folio: {{$persona->id}}</p>
        
                                <form class="needs-validation" novalidate method="POST" action="{{ route('editar_datos_te.guardar', $persona->id) }}">
                                    @csrf
                                    <input type="hidden" name="id_asistente" value="{{ $persona->id }}">
                                    <div class="form-group">
                                        <div class="form">
                                            <label class="form-label">Nombre: </label>
                                            <input type="text" class="form-control" name="nombre" id="nombre" value="{{ old('nombre', $persona->nombre) }}" required>
                                            <div class="invalid-feedback">
                                                El nombre es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="form">
                                            <label class="form-label">Primer Apellido: </label>
                                            <input type="text" class="form-control" name="primer_apellido" id="primer_apellido" value="{{ old('primer_apellido', $persona->primer_apellido) }}" required>
                                            <div class="invalid-feedback">
                                                El primer apellido es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="form">
                                            <label class="form-label">Segundo Apellido: </label>
                                            <input type="text" class="form-control" name="segundo_apellido" id="segundo_apellido" value="{{ old('segundo_apellido', $persona->segundo_apellido) }}">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="form">
                                            <label class="form-label">Sexo: </label>
                                            <input type="text" class="form-control" name="sexo" id="sexo" value="{{ old('sexo', $persona->sexo) }}" required>
                                            <div class="invalid-feedback">
                                                El campo sexo es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="form">
                                            <label class="form-label">Lugar de Visita: </label>
                                            <input type="text" class="form-control" name="lugar" id="lugar" value="{{ old('lugar', $persona->lugar) }}" required>
                                            <div class="invalid-feedback">
                                                El lugar de visita es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="form">
                                            <label class="form-label">Correo Electrónico:</label>
                                            <input type="email" class="form-control" name="correo" id="correo" value="{{ old('correo', $persona->correo) }}" required>
                                            <div class="invalid-feedback">
                                                El correo electrónico es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="form">
                                            <label class="form-label">Teléfono: </label>
                                            <input type="tel" class="form-control" name="telefono" id="telefono" value="{{ old('telefono', $persona->telefono) }}" required>
                                            <div class="invalid-feedback">
                                                El campo teléfono es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <button class="btn btn-primary col-xs-12 col-sm-4 col-md-1 mr-2 mt-2" type="submit">Guardar</button>
                                        <a type="button" class="btn btn-info col-xs-12 col-sm-4 col-md-1 mt-2" href="{{ route('index_tercer_encuentro') }}">Regresar</a>
                                    </div>
                                </form>
                            @else
                                <p>Persona no encontrada.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection