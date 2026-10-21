@extends('layouts.app')
@section('title', 'Historial de ediciones')
@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Historial de ediciones de solicitudes y audiencias</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">

                            @if ($errors->any())
                                <div class="alert alert-dark alert-dismissible fade show" role="alert">
                                    <strong>¡Revise los campos!</strong>
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif

                            <form method="GET" action="{{ route('historial_ediciones') }}">
                                <div class="row g-2 align-items-end">
                                    <div class="col-12 col-md-3">
                                        <label for="nue">NUE</label>
                                        <input type="text" class="form-control" name="nue" id="nue"
                                               placeholder="Ej. MOR/SOL/2026/00576" value="{{ request('nue') }}">
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <label for="usuario">Usuario</label>
                                        <select class="form-control" name="usuario" id="usuario">
                                            <option value="">Todos</option>
                                            @foreach ($usuarios as $usuario)
                                                <option value="{{ $usuario->user_id }}" @selected((string) request('usuario') === (string) $usuario->user_id)>{{ $usuario->user_nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-6 col-md-2">
                                        <label for="desde">Desde</label>
                                        <input type="date" class="form-control" name="desde" id="desde" value="{{ request('desde') }}">
                                    </div>
                                    <div class="col-6 col-md-2">
                                        <label for="hasta">Hasta</label>
                                        <input type="date" class="form-control" name="hasta" id="hasta" value="{{ request('hasta') }}">
                                    </div>
                                    <div class="col-12 col-md-2 d-flex gap-1">
                                        <button type="submit" class="btn btn-primary" title="Buscar"><i class="bi bi-search"></i></button>
                                        <a href="{{ route('historial_ediciones') }}" class="btn btn-light" title="Limpiar"><i class="bi bi-x-lg"></i></a>
                                    </div>
                                </div>
                            </form>
                            <br>

                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>NUE</th>
                                            <th>Delegación</th>
                                            <th>Editado desde</th>
                                            <th>Usuario</th>
                                            <th>Rol</th>
                                            <th class="text-center">Registros afectados</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($ediciones as $edicion)
                                            <tr>
                                                <td>{{ $edicion->created_at->format('d/m/Y H:i') }}</td>
                                                <td>{{ $edicion->NUE ?? '—' }}</td>
                                                <td>{{ $edicion->delegacion ?? '—' }}</td>
                                                <td>{{ $edicion->origen ?? '—' }}</td>
                                                <td>{{ $edicion->user_nombre ?? '—' }}</td>
                                                <td>{{ $edicion->user_rol ?? '—' }}</td>
                                                <td class="text-center">{{ $edicion->detalles_count }}</td>
                                                <td class="text-end">
                                                    <a href="{{ route('historial_ediciones_detalle', $edicion->id) }}" class="btn btn-sm btn-primary">
                                                        <i class="bi bi-eye"></i> Ver
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted">No hay ediciones registradas con esos filtros.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            {{ $ediciones->links('pagination::bootstrap-4') }}

                            <br>
                            <a href="{{ route('configuracion') }}" class="btn btn-warning">Regresar</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
