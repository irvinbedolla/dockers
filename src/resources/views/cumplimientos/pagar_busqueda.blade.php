@extends('layouts.app1')
@php
    $fechaActual = date('Y-m-d');
@endphp
@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Cumplimiento</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <a class="btn btn-warning" href="{{ route('audiencias.cumplimiento') }}"  onclick=nuevo_poder();> Regresar</a>
                                <div class="table-responsive">
                                    <table id="example" class="table table-striped mt-2">
                                        <thead style="background-color: #4A001F;">
                                            <th style="color: #fff;">Fecha</th>
                                            <th style="color: #fff;">Hora</th>
                                            <th style="color: #fff;">Monto</th>
                                            <th style="color: #fff;">Estatus</th>
                                            <th style="color: #fff;">Pagar</th>
                                            <th style="color: #fff;">Documentos</th>
                                        </thead>
                                        <tbody>
                                            @foreach($solicitudes as $pago)
                                                <tr>
                                                    <td>{{ \Carbon\Carbon::parse($pago->fecha)->format('d/m/Y') }}</td>
                                                    <td>{{\Carbon\Carbon::parse($pago->hora)->translatedFormat('h:i')}} Hrs.</td>
                                                    <td>${{number_format($pago->monto, 2)}}</td>
                                                    <td>{{$pago->estatus}}</td>
                                                    <td>
                                                        @if($pago->estatus == "Pendiente")
                                                            <button type="button" class="btn btn-info open-modal" data-bs-toggle="modal" data-bs-target="#exampleModal" data-id="{{ $pago->id }}">
                                                                Pagar
                                                            </button>
                                                            <button type="button" class="btn btn-danger open-modal" data-bs-toggle="modal" data-bs-target="#IncumplimientoModal" data-id="{{ $pago->id }}">
                                                                No pagado
                                                            </button>
                                                            <button type="button" class="btn btn-warning open-modal" data-bs-toggle="modal" data-bs-target="#IncomparecenciaModal" data-id="{{ $pago->id }}">
                                                                Incomparecencia
                                                            </button>
                                                            <!--<a class="btn btn-danger" href="{{ route('cumplimiento_rechazar_busqueda', $pago->id) }}" onclick=consultar_estadistica();>No pagado</a>
                                                            <a class="btn btn-warning" href="{{ route('cumplimiento_incomparecencia', $pago->id) }}" onclick=consultar_estadistica();>Incomparecencia</a>-->
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($pago->estatus == "Pagado")
                                                            <a class="btn btn-success" href="{{ route('PDFcumplimiento', $pago->id) }}" target="_blank">PDF</a>
                                                        @elseif($pago->estatus == "No pagado")
                                                            <a class="btn btn-info" href="{{ route('PDFincumplimientoAudiencia', $pago->id) }}" target="_blank">PDF</a>
                                                        @elseif($pago->estatus == "Incomparecencia trabajador")
                                                            <a class="btn btn-info" href="{{ route('PDFIncomparecenciaCumplimiento', $pago->id) }}" target="_blank">PDF</a>
                                                        @endif
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

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <form class='needs-validation novalidate'  method='POST' action="{{route('cumplimiento_pagar_busqueda')}}">
        @csrf
        <input type="hidden" id="modal-id" name="id" value="">
        <div class="modal-dialog  modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Descripción</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-header">
                    <div class="col-xs-12 col-sm-12 col-md-4"> 
                        <div class="form-group">
                            <label for="name">Fecha de audiencia</label>
                            <input type="date" name="fecha_audiencia" class="form-control" required> 
                            <div class="invalid-feedback">
                                El campo fecha de audiencia es obligatoria.
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-4"> 
                        <div class="form-group">
                            <label for="name">Hora de audiencia</label>
                            <input type="time" name="hora_audiencia" class="form-control" required> 
                            <div class="invalid-feedback">
                                El campo hora de audiencia es obligatoria.
                            </div>
                        </div>
                    </div>
                    @foreach($solicitudes as $solicitud)
                        <div class="col-xs-12 col-sm-12 col-md-4"> 
                            <div class="form-group">
                                <label for="name">Forma de pago</label>
                                <input type="text" class="form-control" name="forma_pago" value="{{ $solicitud->forma_pago }}">
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="modal-body">
                    <textarea name="observaciones" style="width:100%; height: 200px;" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Modal pago no generado por incumplimiento del patrón -->
<div class="modal fade" id="IncumplimientoModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <form class='needs-validation novalidate'  method='POST' action="{{route('cumplimiento_rechazar_busqueda', ['id' => $solicitud->id])}}">
        @csrf
        <input type="hidden" id="modal-id" name="id" value="">
        <div class="modal-dialog  modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Incumplimiento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-header">
                    <div class="col-xs-12 col-sm-12 col-md-4"> 
                        <div class="form-group">
                            <label for="name">Fecha de audiencia</label>
                            <input type="date" name="fecha_audiencia" class="form-control" required> 
                            <div class="invalid-feedback">
                                El campo fecha de audiencia es obligatoria.
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-4"> 
                        <div class="form-group">
                            <label for="name">Hora de audiencia</label>
                            <input type="time" name="hora_audiencia" class="form-control" required> 
                            <div class="invalid-feedback">
                                El campo hora de audiencia es obligatoria.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </div>
        </div>
    </form>
</div>
<div class="modal fade" id="IncomparecenciaModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <form class='needs-validation novalidate'  method='POST' action="{{route('cumplimiento_incomparecencia', ['id' => $solicitud->id])}}">
        @csrf
        <input type="hidden" id="modal-id" name="id" value="">
        <div class="modal-dialog  modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Incomparecencia Trabajador</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-header">
                    <div class="col-xs-12 col-sm-12 col-md-4"> 
                        <div class="form-group">
                            <label for="name">Fecha de audiencia</label>
                            <input type="date" name="fecha_audiencia" class="form-control" required> 
                            <div class="invalid-feedback">
                                El campo fecha de audiencia es obligatoria.
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-4"> 
                        <div class="form-group">
                            <label for="name">Hora de audiencia</label>
                            <input type="time" name="hora_audiencia" class="form-control" required> 
                            <div class="invalid-feedback">
                                El campo hora de audiencia es obligatoria.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </div>
        </div>
    </form>
</div>

<div id="nuevo_poder" style ="display: none;">
    <div>.</div>
    <div class="loader"></div>
</div>

@section('scripts')
    <script>
        $('.open-modal').click(function() {
            const id = $(this).data('id'); // Obtiene el valor de data-id
            document.getElementById('modal-id').value = id;
        });
    </script>
    <script src="../../public/assets/js/poderes/general.js"></script>
@endsection
