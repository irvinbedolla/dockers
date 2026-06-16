@extends('layouts.app')
@php
    $fechaActual = date('Y-m-d');
@endphp
@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Cumplimiento en Ratificaciones</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <a class="btn btn-warning" href="{{ route('todas_ratificaciones') }}"  onclick=nuevo_poder();> Regresar</a>
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
                                                    <!--td>{{\Carbon\Carbon::parse($pago->fecha)->translatedFormat('d-m-Y')}}</!--td--> 
                                                    <!--td>{{\Carbon\Carbon::parse($pago->hora)->translatedFormat('d-m-Y')}}</!--td-->
                                                    <td>{{date_format($pago->fecha,"d-m-Y")}}</td> 
                                                    <td>{{date_format($pago->hora,"H:i:s")}}</td>
                                                    <td>${{number_format($pago->monto, 2)}}</td>
                                                    <td>{{$pago->estatus}}</td>
                                                    <td>
                                                        @if($pago->estatus == "Pendiente")
                                                            <button type="button" class="btn btn-info open-modal" data-bs-toggle="modal" data-bs-target="#exampleModal" data-id="{{ $pago->id }}">
                                                                Pagar
                                                            </button>
                                                            <a class="btn btn-danger" href="{{ route('cumplimiento_rechazar', $pago->id) }}" onclick=consultar_estadistica();>Rechazar</a>
                                                            <form method="POST" action="{{ route('cumplimiento_incomparecencia', $pago->id) }}" style="display:inline;">
                                                                @csrf
                                                                <input type="hidden" name="fecha_audiencia" value="{{ optional($pago->fecha)->format('Y-m-d') }}">
                                                                <input type="hidden" name="hora_audiencia" value="{{ optional($pago->hora)->format('H:i:s') }}">
                                                                <button type="submit" class="btn btn-danger" onclick="consultar_estadistica();">
                                                                    No comparece trabajador
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($pago->estatus == "Pagado")
                                                            @if($total == 1)
                                                                <a class="btn btn-success" href="{{ route('PDFcumplimientoR', $pago->id_solicitud) }}" target="_blank">PDF</a>
                                                            @else
                                                                <a class="btn btn-success" href="{{ route('PDFpagos', $pago->id) }}"  target="_blank">PDF</a>
                                                            @endif
                                                        @elseif($pago->estatus == "No pagado")
                                                            <a class="btn btn-info" href="{{ route('PDFincumplimientoRatificacion', $pago->id) }}" target="_blank">PDF</a>
                                                        @elseif($pago->estatus == "Incomparecencia trabajador")
                                                            <a class="btn btn-info" href="{{ route('PDFIncomparecenciaCumplimientoRati', $pago->id) }}" target="_blank">PDF</a>
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
    <form class='needs-validation novalidate'  method='POST' action="{{route('ratificacion_pagoA')}}">
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
