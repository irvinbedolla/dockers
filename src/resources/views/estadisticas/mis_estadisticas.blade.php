@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Estadisticas</h3>
        </div>
        <div class="section-body">
            <?php $fecha_actual = date('d-m-Y');?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="text-center">Reporte</h3>
                            
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
                                <form class='needs-validation novalidate' id='form_roles' method='POST' action="{{route('mis_reportes')}}" target="_blank">
                                    @csrf
                                    <div class="row">
                                        <div class="col-xs-12 col-sm-6 col-md-4">
                                            <div class="form-group">
                                                <label for="name">Fecha Inicial</label>
                                                <input type="date" class="form-control" name="fecha_inicial" required>
                                                <div class="invalid-feedback">
                                                    La fecha inicial es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-6 col-md-4">
                                            <div class="form-group">
                                                <label for="name">Fecha Final</label>
                                                <input type="date" class="form-control" name="fecha_final" required>
                                                <div class="invalid-feedback">
                                                    La fecha final es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-xs-12 col-sm-12 col-md-4">
                                            <label for="name">Generar</label><br>
                                            <button type="submit" class="btn btn-primary">PDF</button>
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
    <script src="../public/js/estadistica/estadistica.js"></script>
    <script>
        $('#reporte').change(function(){
            var valorCambiado =$(this).val();
            if((valorCambiado == 'Concentrado')){
                $('#resu_detallado').css('display','none');
                $('#concentrado').css('display','block');
            }
            else{
                $('#resu_detallado').css('display','block');
                $('#concentrado').css('display','none');
            }
        });
    </script>
@endsection