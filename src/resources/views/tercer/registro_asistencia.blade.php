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
                                <h4>Registrar asistencia de: {{ $persona->nombre }} {{ $persona->primer_apellido }} {{ $persona->segundo_apellido }}</h4>
                                <p>Correo: {{ $persona->correo ?? 'N/A' }}</p> 
                                <p>Tel: {{ $persona->telefono ?? 'N/A' }}</p>
                                <form method="POST" action="{{ route('registro_asistencia_te.guardar', $persona->id) }}">
                                    @csrf
                                    <input type="hidden" name="id_asistente" value="{{ $persona->id }}">
                                    <div class="form-group">
                                        <label>Conferencias</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="convesatorio1" id="convesatorio1" {{ ($persona->convesatorio1 ?? 'No') === 'Si' ? 'checked' : '' }}>
                                            <label class="form-check-label {{ ($persona->convesatorio1 ?? 'No') === 'Si' ? 'text-success fw-semibold' : '' }}" for="convesatorio1">Conversatorio 1
                                                @if(($persona->convesatorio1 ?? 'No') === 'Si')
                                                    <i class="bi bi-check-circle-fill text-success ms-1"></i>
                                                @endif
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="convesatorio2" id="convesatorio2" {{ ($persona->convesatorio2 ?? 'No') === 'Si' ? 'checked' : '' }}>
                                            <label class="form-check-label {{ ($persona->convesatorio2 ?? 'No') === 'Si' ? 'text-success fw-semibold' : '' }}" for="convesatorio2">Conversatorio 2
                                                @if(($persona->convesatorio2 ?? 'No') === 'Si')
                                                    <i class="bi bi-check-circle-fill text-success ms-1"></i>
                                                @endif
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="convesatorio3" id="convesatorio3" {{ ($persona->convesatorio3 ?? 'No') === 'Si' ? 'checked' : '' }}>
                                            <label class="form-check-label {{ ($persona->convesatorio3 ?? 'No') === 'Si' ? 'text-success fw-semibold' : '' }}" for="convesatorio3">Conversatorio 3
                                                @if(($persona->convesatorio3 ?? 'No') === 'Si')
                                                    <i class="bi bi-check-circle-fill text-success ms-1"></i>
                                                @endif
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="convesatorio4" id="convesatorio4" {{ ($persona->convesatorio4 ?? 'No') === 'Si' ? 'checked' : '' }}>
                                            <label class="form-check-label {{ ($persona->convesatorio4 ?? 'No') === 'Si' ? 'text-success fw-semibold' : '' }}" for="convesatorio4">Conversatorio 4
                                                @if(($persona->convesatorio4 ?? 'No') === 'Si')
                                                    <i class="bi bi-check-circle-fill text-success ms-1"></i>
                                                @endif
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="convesatorio5" id="convesatorio5" {{ ($persona->conversatorio5 ?? 'No') === 'Si' ? 'checked' : '' }}>
                                            <label class="form-check-label {{ ($persona->convesatorio5 ?? 'No') === 'Si' ? 'text-success fw-semibold' : '' }}" for="convesatorio5">Conversatorio 5
                                                @if(($persona->convesatorio5 ?? 'No') === 'Si')
                                                    <i class="bi bi-check-circle-fill text-success ms-1"></i>
                                                @endif
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="convesatorio6" id="convesatorio6" {{ ($persona->convesatorio6 ?? 'No') === 'Si' ? 'checked' : '' }}>
                                            <label class="form-check-label {{ ($persona->convesatorio6 ?? 'No') === 'Si' ? 'text-success fw-semibold' : '' }}" for="convesatorio6">Conversatorio 6
                                                @if(($persona->convesatorio6 ?? 'No') === 'Si')
                                                    <i class="bi bi-check-circle-fill text-success ms-1"></i>
                                                @endif
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="convesatorio7" id="convesatorio7" {{ ($persona->convesatorio7 ?? 'No') === 'Si' ? 'checked' : '' }}>
                                            <label class="form-check-label {{ ($persona->convesatorio7 ?? 'No') === 'Si' ? 'text-success fw-semibold' : '' }}" for="convesatorio7">Conversatorio 7
                                                @if(($persona->convesatorio7 ?? 'No') === 'Si')
                                                    <i class="bi bi-check-circle-fill text-success ms-1"></i>
                                                @endif
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="convesatorio8" id="convesatorio8" {{ ($persona->convesatorio8 ?? 'No') === 'Si' ? 'checked' : '' }}>
                                            <label class="form-check-label {{ ($persona->convesatorio8 ?? 'No') === 'Si' ? 'text-success fw-semibold' : '' }}" for="convesatorio8">Conversatorio 8
                                                @if(($persona->convesatorio8 ?? 'No') === 'Si')
                                                    <i class="bi bi-check-circle-fill text-success ms-1"></i>
                                                @endif
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="convesatorio9" id="convesatorio9" {{ ($persona->convesatorio9 ?? 'No') === 'Si' ? 'checked' : '' }}>
                                            <label class="form-check-label {{ ($persona->convesatorio9 ?? 'No') === 'Si' ? 'text-success fw-semibold' : '' }}" for="convesatorio9">Conversatorio 9
                                                @if(($persona->convesatorio9 ?? 'No') === 'Si')
                                                    <i class="bi bi-check-circle-fill text-success ms-1"></i>
                                                @endif
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="convesatorio10" id="convesatorio10" {{ ($persona->convesatorio10 ?? 'No') === 'Si' ? 'checked' : '' }}>
                                            <label class="form-check-label {{ ($persona->convesatorio10 ?? 'No') === 'Si' ? 'text-success fw-semibold' : '' }}" for="convesatorio10">Conversatorio 10
                                                @if(($persona->convesatorio10 ?? 'No') === 'Si')
                                                    <i class="bi bi-check-circle-fill text-success ms-1"></i>
                                                @endif
                                            </label>
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