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
                            <h3 class="text-center">Alta de Usuarios</h3>
                             
                            <!--Se realiza la validación de campos para ver si dejó alguno vacío-->
                            @if ($errors->any())
                                <div class="alert alert-dark alert-dismissible fade show" role="alert">
                                    <strong>¡Revise los campos!</strong>
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                            <!--<span class="badge badge-danger">{{ $error }}</span>-->
                                        @endforeach
                                    </ul>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>

                            @endif

                            <!--Se realiza el envío de datos con formulario de Laravel Collective-->
                            <form class='needs-validation novalidate' id='form_roles' method='POST' action="{{route('usuarios.store')}}">
                                @csrf
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label for="name">Nombre</label>
                                            <input type="text" class="form-control" name="name" required>
                                            <div class="invalid-feedback">
                                                El nombre es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label for="email">Email</label>
                                            <input type="email" class="form-control" name="email" required>
                                            <div class="invalid-feedback">
                                                El Email es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                   
                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label for="password">Password</label>
                                            <input type="password" class="form-control" name="password" required>
                                            <div class="invalid-feedback">
                                                La contraseña es obligatoria.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label for="confirm-password">Confirmar Password</label>
                                            <input type="password" class="form-control" name="confirm-password" required>
                                            <div class="invalid-feedback">
                                                La contraseña es obligatoria.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label for="name">Roles</label>
                                            <select name="roles" class="form-control" required>
                                                @foreach($roles as $rol)
                                                    <option value="{{ $rol }}">{{ $rol }}</option>
                                                @endforeach
                                            </select>
                                            <div class="invalid-feedback">
                                                Debes seleccionar un Rol.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label for="name">Delegacion</label>
                                            <select name="delegacion" class="form-control" required>
                                                <option value="">Seleccione</option>
                                                <option value="Morelia">Morelia</option>
                                                <option value="Zitácuaro">Zitácuaro</option>
                                                <option value="Uruapan">Uruapan</option>
                                                <option value="Lázaro Cárdenas">Lázaro Cárdenas</option>
                                                <option value="Zamora">Zamora</option>
                                                <option value="Sahuayo">Sahuayo</option>
                                            </select>
                                            <div class="invalid-feedback">
                                                La delegacion es obligatoria.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label for="confirm-password">CURP</label>
                                            <input type="text" class="form-control" name="profile_photo_path" required>
                                            <div class="invalid-feedback">
                                                La CURP es obligatoria.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label for="name">Tipo</label>
                                            <select name="type" class="form-control" required>
                                                <option value="">Seleccione</option>
                                                <option value="Seer">Seer</option>
                                                <option value="Si concilio">Si concilio</option>
                                                <option value="Ambos">Ambos</option>
                                            </select>
                                            <div class="invalid-feedback">
                                                El tipo es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                        <button type="submit" class="btn btn-primary">Guardar</button>
                                    </div>
                                    
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
    <script src="../public/js/usuarios/usuarios.js"></script>
@endsection