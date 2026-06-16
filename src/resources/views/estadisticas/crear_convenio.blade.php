@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">SEER</h3>
        </div>
        <div class="section-body">
            <?php $fecha_actual = date('d-m-Y');?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="text-center">Registrar Pago</h3>
                            
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

                            @can('crear-seer')
                                @if($userRole[0] == "Auxiliar" || $userRole[0] == "Conciliador")
                                    <!--Se realiza el envío de datos con formulario de Laravel Collective-->
                                    <form method="POST" action="{{ route('seer.crear_convenio') }}" class="needs-validation novalidate">
                                        @csrf
                                        <div class="row">
                                            <div class="col-xs-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label for="email">Fecha</label>
                                                    <input type="date" class="form-control" name="fecha" required>
                                                    <div class="invalid-feedback">
                                                        El campo es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Número unico de identificación</label>
                                                    <input type="text" class="form-control" name="NUE" oninput="this.value = this.value.toUpperCase()" required>
                                                    <div class="invalid-feedback">
                                                        El campo es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label for="password">Monto</label>
                                                    <input type="number" name="monto" class="form-control" step="0.01" required>   
                                                    <div class="invalid-feedback">
                                                        El campo es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label for="password">Tipo de pago</label>
                                                    <select class="form-control" name="tipo_pago" required>
                                                        <option value="">Seleccione</option>
                                                        <option value="Efectivo">Efectivo</option>
                                                        <option value="Cheque">Cheque</option>
                                                        <option value="Transferencia">Transferencia</option>
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        El campo es obligatorio.
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <button type="submit" class="btn btn-primary">Guardar</button>
                                            </div>
                                                
                                        </div>
                                    </form>
                                @endif
                            @endcan


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
    <script src="../public/assets/js/estadistica/estadistica.js"></script>
@endsection

