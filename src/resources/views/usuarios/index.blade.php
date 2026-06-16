@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Usuarios</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            @can('crear-usuario')
                                <a class="btn btn-warning" href="{{ route('usuarios.create') }}" onclick=crear_usuario();> Nuevo</a>
                            @endcan
                            @can('ver-usuario')
                                <div class="table-responsive">
                                    <table id="example" class="table-striped" style="width:100%">
                                        <thead style="background-color: #4A001F;">
                                            <th style="color: #fff;">Folio</th>
                                            <th style="color: #fff;">Nombre</th>
                                            <th style="color: #fff;">E-mail</th>
                                            <th style="color: #fff;">Rol</th>
                                            <th style="color: #fff;">Delegacíon</th>
                                            <th style="color: #fff;">Acciones</th>
                                        </thead>
                                        <tbody class="contenidobusqueda">
                                            @foreach($usuarios as $usuario)
                                                <tr>
                                                    <td>{{$usuario->id}}</td>
                                                    <td>{{$usuario->name}}</td>
                                                    <td>{{$usuario->email}}</td>
                                                    <td>                                                
                                                        @if(!empty($usuario->getRoleNames()))
                                                            @foreach($usuario->getRoleNames() as $rolName)
                                                            <h5><span class="badge badge-dark">{{$rolName}}</span></h5>
                                                            @endforeach
                                                        @endif
                                                    </td>
                                                    <td>{{$usuario->delegacion}}</td>
                                                    <td>
                                                        @can('editar-usuario')
                                                            <a class="btn btn-info" href="{{ route('usuarios.edit', $usuario->id)}}" onclick=editar_usuario();>Editar</a>
                                                        @endcan
                                                        @can('borrar-usuario')
                                                            <form method="POST" action="{{ route('usuarios.destroy', $usuario->id) }} ">
                                                            @csrf
                                                                <input type="hidden" name="_method" value="DELETE">
                                                                <button class="btn btn-danger" onclick=editar_rol(); type="submit">Eliminar</button>
                                                            </form>
                                                        @endcan
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endcan
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

<div id="nuevo_usuario" style ="display: none;">
    <div>.</div>
    <div class="loader"></div>
</div>


@section('scripts')
    <script src="../public/assets/js/usuarios/usuarios.js"></script>
@endsection