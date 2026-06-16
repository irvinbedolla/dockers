@extends('layouts.app')
@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Plantillas Ratificaciones</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="example" class="table-striped" style="width:100%">
                                    <thead style="background-color: #4A001F;">
                                        <th style="color: #fff;">Documento</th>
                                        <th style="color: #fff;">Acciones</th>
                                    </thead>
                                    <tbody>
                                        @php
                                            $plantillas = [
                                                (object) [
                                                    'nombre' => 'convenio_ptu',
                                                    'documento' => 'Convenio PTU',
                                                    'archivo' => 'public/assets/documentos/plantillas/Convenio_PTU.docx',
                                                ],
                                            ];
                                        @endphp

                                        @foreach ($plantillas as $p)
                                            <tr>
                                                <td>{{ $p->documento }}</td>
                                                <td>
                                                    <a
                                                        class="btn btn-sm btn-primary"
                                                        href="{{ asset($p->archivo) }}"
                                                        download
                                                    >
                                                        Descargar
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection