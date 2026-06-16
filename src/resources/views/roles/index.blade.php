@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Roles</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            @can('crear-rol')
                                <a class="btn btn-warning" href="{{ route('roles.create') }}" onclick=crear_rol();>Nuevo</a>
                            @endcan
                            <div class="table-responsive">
                                <table id="example" class="table table-striped mt-2">
                                    <thead style="background-color:#4A001F">
                                        <th style="color:#fff">Rol</th>
                                        <th style="color:#fff">Acciones</th>
                                    </thead>
                                    <tbody> 
                                        @foreach ($roles as $role)
                                        <tr>
                                            <td>{{ $role->name }}</td>
                                            <td>
                                                <a class="btn btn-primary" href="{{ route('roles.edit', $role->id) }}" onclick=editar_rol();>Editar</a>
                                                <form method="POST" action="{{ route('roles.destroy', $role->id) }} ">
                                                    @csrf
                                                    <input type="hidden" name="_method" value="DELETE">
                                                    <button class="btn btn-danger" onclick=editar_rol(); type="submit">Eliminar</button>
                                                </form>
                                            </td>
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
    <script src="../public/assets/js/general/menu.js"></script>
@endsection