{{-- Una sede de la agenda del conciliador. $i (índice o "__I__" en la plantilla), $f (sede, modalidad, dias, inicio, fin, sala, desde, hasta). --}}
@php
    use App\Support\SedesConciliador as SC;
    $f = (array) $f;
    $diasFila = (array) ($f['dias'] ?? []);
@endphp
<div class="cn-fila {{ ($f['modalidad'] ?? '') === SC::PRESENCIAL ? '' : 'cn-fila--virtual' }}" data-fila style="--c: {{ SC::COLORES[$f['sede'] ?? ''] ?? '#496163' }};">
    <div class="cn-fila__cabeza">
        <h6 data-titulo>{{ ($f['sede'] ?? '') ?: 'Nueva sede' }}</h6>
        <button type="button" class="btn btn-sm btn-link text-danger p-0" data-quitar title="Quitar esta sede"><i class="bi bi-x-lg"></i> Quitar</button>
    </div>
    <div class="cn-campos">
        <div>
            <label for="cn-{{ $i }}-sede">Sede</label>
            <select class="form-select form-select-sm" id="cn-{{ $i }}-sede" name="sedes[{{ $i }}][sede]" required>
                <option value="">Elige…</option>
                @foreach (SC::SEDES as $s)
                    <option value="{{ $s }}" @selected(($f['sede'] ?? '') === $s)>{{ $s }}{{ $s === $conciliador->delegacion ? ' (su sede)' : '' }}</option>
                @endforeach
            </select>
        </div>
        <div class="cn-campo--modo">
            <label>Modalidad</label>
            <div class="cn-modos">
                @foreach (SC::MODALIDADES as $valor => $etiqueta)
                    <input type="radio" class="btn-check" name="sedes[{{ $i }}][modalidad]" id="cn-{{ $i }}-modo-{{ $valor }}" value="{{ $valor }}" autocomplete="off" @checked(($f['modalidad'] ?? '') === $valor)>
                    <label class="btn" for="cn-{{ $i }}-modo-{{ $valor }}"><i class="bi {{ $valor === SC::PRESENCIAL ? 'bi-building' : 'bi-camera-video' }}"></i> {{ $etiqueta }}</label>
                @endforeach
            </div>
        </div>
        <div class="cn-campo--dias">
            <label>Días</label>
            <div class="cn-dias">
                @foreach (\App\Models\PermisosConciliador::DIAS as $dia => $corto)
                    <input type="checkbox" class="btn-check" name="sedes[{{ $i }}][dias][]" id="cn-{{ $i }}-dia-{{ $dia }}" value="{{ $dia }}" autocomplete="off" @checked(in_array($dia, $diasFila, true))>
                    <label class="btn" for="cn-{{ $i }}-dia-{{ $dia }}">{{ $corto }}</label>
                @endforeach
            </div>
        </div>
        <div>
            <label for="cn-{{ $i }}-inicio">Desde</label>
            <input type="time" class="form-control form-control-sm" id="cn-{{ $i }}-inicio" name="sedes[{{ $i }}][inicio]" value="{{ $f['inicio'] ?? '' }}" step="60" data-campo="inicio">
        </div>
        <div>
            <label for="cn-{{ $i }}-fin">Hasta</label>
            <input type="time" class="form-control form-control-sm" id="cn-{{ $i }}-fin" name="sedes[{{ $i }}][fin]" value="{{ $f['fin'] ?? '' }}" step="60" data-campo="fin">
        </div>
        <div>
            <label for="cn-{{ $i }}-sala">Sala</label>
            <input type="text" class="form-control form-control-sm" id="cn-{{ $i }}-sala" name="sedes[{{ $i }}][sala]" value="{{ $f['sala'] ?? '' }}" maxlength="40" data-campo="sala"
                   placeholder="{{ ($f['sede'] ?? '') && ($f['sede'] ?? '') !== $conciliador->delegacion ? SC::SALA_FUERA : 'La de siempre' }}">
        </div>
        {{-- Vigencia: para coberturas ("desde el 9 de diciembre atiende Zitácuaro"). Vacías = siempre. --}}
        <div class="cn-vigencia">
            <span class="cn-vigencia__titulo"><i class="bi bi-calendar-range"></i> Vigencia <small>(opcional)</small></span>
            <label for="cn-{{ $i }}-desde">Desde</label>
            <input type="date" class="form-control form-control-sm" id="cn-{{ $i }}-desde" name="sedes[{{ $i }}][desde]" value="{{ $f['desde'] ?? '' }}">
            <label for="cn-{{ $i }}-hasta">Hasta</label>
            <input type="date" class="form-control form-control-sm" id="cn-{{ $i }}-hasta" name="sedes[{{ $i }}][hasta]" value="{{ $f['hasta'] ?? '' }}">
            <input type="hidden" name="sedes[{{ $i }}][exclusiva]" value="0">
            <input type="checkbox" class="btn-check" value="1" id="cn-{{ $i }}-exclusiva" name="sedes[{{ $i }}][exclusiva]" autocomplete="off" @checked(! empty($f['exclusiva']))>
            <label class="btn cn-exclusiva" for="cn-{{ $i }}-exclusiva" title="Mientras esté vigente, sólo quien la tenga exclusiva recibe audiencias nuevas de esta sede">
                <i class="bi bi-unlock cn-exclusiva__no"></i><i class="bi bi-lock-fill cn-exclusiva__si"></i> Exclusiva
            </label>
            <span class="cn-sub">Sin fechas, atiende esta sede siempre. Con "Desde", el reparto le da audiencias de esta sede a partir de ese día; con "Hasta", deja de dárselas después.
                <b>Exclusiva:</b> mientras esté vigente, sólo quien la tenga exclusiva recibe audiencias nuevas de esta sede; los demás conciliadores que la atienden dejan de recibirlas.</span>
        </div>
    </div>
</div>
