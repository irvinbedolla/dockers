{{--
    Ejemplo (con datos inventados) de dónde viene el folio en el citatorio:
    el "Número de identificación único", arriba a la derecha. Espera $abierto
    (si el ejemplo empieza desplegado).
--}}
<details class="at-ejemplo" @if ($abierto) open @endif>
    <summary><i class="bi bi-question-circle me-1" aria-hidden="true"></i> ¿Dónde encuentro mi folio?</summary>
    <div class="at-ejemplo__cuerpo">
        <p class="mb-2">
            Es el <b>Número de identificación único</b>. Lo encontrarás en la parte superior derecha, de tu <b>citatorio</b>, junto a la oficina:
        </p>

        <div class="at-doc" role="img" aria-label="Ejemplo de citatorio: el folio MOR/SOL/2026/01234 aparece junto a Número de identificación único, arriba a la derecha">
            <div class="at-doc__cinta">
                <span class="at-doc__logo">Sí<b>Concilio</b></span>
                <span class="at-doc__logo at-doc__logo--der">MICHOACÁN</span>
            </div>
            <div class="at-doc__arriba">
                <div class="at-doc__marca" aria-hidden="true">Tu folio <i class="bi bi-arrow-right"></i></div>
                <dl class="at-doc__datos">
                    <dt>Oficina:</dt><dd>Morelia</dd>
                    <dt>Número de identificación único:</dt><dd class="at-doc__folio">MOR/SOL/2026/01234</dd>
                </dl>
            </div>
            <div class="at-doc__titulo">CENTRO DE CONCILIACIÓN LABORAL DEL ESTADO DE MICHOACÁN DE OCAMPO</div>
            <div class="at-doc__texto">
                <p><b>FECHA DE EMISIÓN DEL CITATORIO:</b> 2 de octubre del 2026</p>
                <p><b>ASUNTO:</b> CITATORIO DE AUDIENCIA DE CONCILIACIÓN</p>
                <p><b>SOLICITANTE:</b> <span class="at-doc__tapa"></span></p>
                <p><b>CITADO:</b> <span class="at-doc__tapa at-doc__tapa--corta"></span></p>
                <p class="at-doc__parrafo">… para que asista a la <b>Audiencia de Conciliación</b> de fecha <span class="at-doc__fecha">20 de octubre del 2026 a las 10:30 horas</span> …</p>
            </div>
        </div>

        <p class="small mb-0 mt-2" style="color: var(--suave);">
            Tu folio representa la sede (MOR, URU, ZAM, ZIT, SAH o LAZ), tipo de trámite solicitud y/o ratificación, año y número del expediente asignado.
        </p>
    </div>
</details>
