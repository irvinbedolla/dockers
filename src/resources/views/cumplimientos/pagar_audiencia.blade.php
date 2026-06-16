@extends('layouts.app')
@php
    $fechaActual = date('Y-m-d');
@endphp
@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Cumplimiento en Audiencias</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <!--<a class="btn btn-warning" href="{{ route('todas_audiencias') }}"  onclick=nuevo_poder();> Regresar</a>-->
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
                                            @foreach($cumplimientos as $pago)
                                                <tr>
                                                    <td>{{date_format($pago->fecha,"d-m-Y")}}</td> 
                                                    <td>{{date_format($pago->hora,"H:i:s")}}</td>
                                                    <td>${{number_format($pago->monto, 2)}}</td>
                                                    <td>{{$pago->estatus}}</td>
                                                    <td>
                                                        @if($pago->estatus == "Pendiente" || $pago->estatus == "Incomparecencia trabajador")
                                                            <button type="button" class="btn btn-info open-modal" data-bs-toggle="modal" data-bs-target="#exampleModal" data-id="{{ $pago->id }}">
                                                                Generar Cumplimiento
                                                            </button>
                                                        @endif
                                                        @if($pago->estatus == "Pendiente")
                                                            @if($pago->tipo_pago == 'Ratificacion')
                                                            <a class="btn btn-danger" href="{{ route('cumplimiento_rechazar', $pago->id) }}" onclick=consultar_estadistica();>Generar incumplimiento</a>
                                                            @else
                                                            <a class="btn btn-danger" href="{{ route('cumplimiento_rechazar_audiencia', $pago->id) }}" onclick=consultar_estadistica();>Generar incumplimiento</a>
                                                            @endif
                                                            <form method="POST" action="{{ route('cumplimiento_incomparecencia', $pago->id) }}" style="display:inline;">
                                                                @csrf
                                                                <input type="hidden" name="fecha_audiencia" value="{{ $pago->fecha }}">
                                                                <input type="hidden" name="hora_audiencia" value="{{ $pago->hora }}">
                                                                <button type="submit" class="btn btn-danger" onclick="consultar_estadistica();">
                                                                    Genearar Incomparecencia
                                                                </button>
                                                            </form>
                                                        @endif
                                                        @if($pago->estatus == "No pagado")
                                                            <button type="button" class="btn btn-warning open-modal-pena" data-bs-toggle="modal" data-bs-target="#penaModal" data-id="{{ $pago->id }}">
                                                                Pagar con pena convencional
                                                            </button>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @php
                                                            $totalRegistros = $cumplimientos->count();
                                                        @endphp

                                                        @if($pago->estatus == "Pagado")
                                                            @if($totalRegistros == 1)
                                                                <a class="btn btn-success" href="{{ route('PDFcumplimientoParcial', $pago->id) }}" target="_blank">
                                                                    PDF
                                                                </a>    
                                                            @else
                                                                <a class="btn btn-success" href="{{ route('PDFcumplimientoParcial', $pago->id) }}" target="_blank">
                                                                    PDF
                                                                </a>
                                                            @endif
                                                        @elseif($pago->estatus == "Pagado con pena convencional")
                                                            <a class="btn btn-success" href="{{ route('PDFcumplimientoParcial', $pago->id) }}" target="_blank">
                                                                PDF
                                                            </a>
                                                        {{--@if($pago->estatus == "Pagado")
                                                            <a class="btn btn-success" href="{{ route('PDFcumplimientoParcial', $pago->id) }}" target="_blank">PDF</a>--}}
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
    <form class='needs-validation novalidate'  method='POST' action="{{route('pagoA_audiencia')}}">
        @csrf
        <input type="hidden" id="modal-id" name="id" value="">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Descripción</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <textarea name="observaciones" style="width:100%"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="modal fade" id="penaModal" tabindex="-1" aria-labelledby="penaModalLabel" aria-hidden="true">
    <form class='needs-validation novalidate' method='POST' action="{{ route('cumplimiento_pagar_pena_audiencia') }}">
        @csrf
        <input type="hidden" id="pena-modal-id" name="id" value="">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="penaModalLabel">Pagar con pena convencional</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Monto de pena convencional</label>
                        <input type="number" step="0.01" min="0" name="monto_pc" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Observaciones</label>
                        <textarea name="observaciones" style="width:100%"></textarea>
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
        $('.open-modal-pena').click(function() {
            const id = $(this).data('id');
            document.getElementById('pena-modal-id').value = id;
        });
    </script>
    <script src="../../public/assets/js/poderes/general.js"></script>
@endsection
