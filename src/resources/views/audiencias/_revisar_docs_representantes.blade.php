{{--
    Pestaña Documentos: representantes del caso con los datos de su poder y
    sus PDF (App\Support\RevisionAudiencia::representantes). Espera $representantes.
--}}
<div class="col-12">
    <h5 class="ra-subtitulo">Representantes y poderes</h5>
    @forelse ($representantes as $r)
        <div class="ra-rep">
            @if ($r['falta'])
                <div class="ra-rep__cab">
                    <div>
                        <div class="ra-rep__nombre">Poder folio {{ $r['folio'] }}</div>
                        <div class="ra-rep__partes">Representa a: {{ implode(' · ', $r['partes']) }}</div>
                    </div>
                    <span class="ra-et ra-et--falta">No se encontró el poder</span>
                </div>
            @else
                <div class="ra-rep__cab">
                    <div>
                        <div class="ra-rep__nombre">{{ $r['representante'] ?? $r['otorga'] }}</div>
                        <div class="ra-rep__partes">Representa a: {{ implode(' · ', $r['partes']) }}</div>
                    </div>
                    <div class="ra-parte__chips">
                        <span class="ra-et ra-et--{{ $r['estado']['nivel'] }}">{{ $r['estado']['texto'] }}</span>
                        <span class="ra-et">{{ $r['tipo'] }}</span>
                        <span class="ra-et">Folio {{ $r['folio'] }}</span>
                        @unless ($r['conRep'])
                            <span class="ra-et ra-et--info">Comparece sin representante</span>
                        @endunless
                    </div>
                </div>
                <dl class="ra-rep__datos">
                    <div><dt>Otorga el poder</dt><dd>{{ $r['otorga'] }}</dd></div>
                    <div><dt>CURP</dt><dd>{{ $r['curp'] ?? '—' }}</dd></div>
                    <div><dt>Correo</dt><dd>{{ $r['correo'] ?? '—' }}</dd></div>
                    <div><dt>Teléfono</dt><dd>{{ $r['telefono'] ?? '—' }}</dd></div>
                    <div>
                        <dt>Identificación</dt>
                        <dd>
                            @if ($r['sinIdentificacion'])
                                <span class="ra-et ra-et--falta">Falta el tipo de identificación</span>
                            @else
                                {{ $r['identificacion'] }}
                            @endif
                        </dd>
                    </div>
                    @if ($r['conRep'])
                        <div><dt>Acredita con</dt><dd>{{ $r['documento'] ?? '—' }}</dd></div>
                    @endif
                    <div><dt>Expedición</dt><dd>{{ $r['expedicion'] ?? '—' }}</dd></div>
                    <div><dt>Vigencia</dt><dd>{{ $r['vigencia'] }}</dd></div>
                    @if ($r['descripcion'])
                        <div class="ra-rep__ancho"><dt>Descripción del documento</dt><dd>{{ $r['descripcion'] }}</dd></div>
                    @endif
                </dl>
                <div class="ra-rep__docs">
                    @foreach ($r['docs'] as $d)
                        @if ($d['url'])
                            <a class="btn btn-sm btn-outline-secondary" href="{{ $d['url'] }}" target="_blank" data-doc><i class="bi bi-file-earmark-pdf"></i> {{ $d['nombre'] }}</a>
                        @else
                            <span class="btn btn-sm btn-light disabled" aria-disabled="true">{{ $d['nombre'] }}: sin archivo</span>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    @empty
        <p class="text-muted mb-2">Ninguna parte tiene un representante registrado.</p>
    @endforelse
</div>
