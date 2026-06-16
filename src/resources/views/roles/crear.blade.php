@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Dashboard</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="text-center">Crear Roles</h3>

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
                            <form class='needs-validation novalidate' id='form_roles' method='POST' action="{{route('roles.store')}}">
                                @csrf
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-12">
                                        <div class="form-group">
                                            <label for="name">Nombre del Rol</label>
                                            <input type="text" class="form-control" name="name" required>
                                        </div>
                                    </div>         
                                    <div class="invalid-feedback">
                                        El tipo es obligatorio.
                                    </div>              

                                    <div class="col-xs-12 col-sm-12 col-md-12">
                                        <div class="form-group">
                                            <label for="name">Permisos para este Rol</label>
                                                <br/>
                                            @foreach($permission as $value)
                                                <label>
                                                    <input class="form-check-input" name="permission[]" type="checkbox" value="{{ $value->id }}" id="flexCheckDefault">
                                                    <label class="form-check-label" for="flexCheckDefault">{{ $value->name }}</label>
                                                </label>
                                                <br/>
                                            @endforeach
                                        </div>
                                        <div class="invalid-feedback">
                                        El tipo es obligatorio.
                                    </div>
                                    </div>                                    
                                        <button type="submit" class="btn btn-primary">Guardar</button>
                                </form>
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
    <script src="../public/js/roles/roles.js"></script>
@endsection