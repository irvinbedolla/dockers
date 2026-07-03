{{--
    Partial: _pollLock.blade.php
    ──────────────────────────────────────────────────────────────────────────
    Polling de lock activo para el wizard de solicitudes auxiliares.

    Incluir con:  @include('solicitudes.auxiliares._pollLock')

    Requiere que la variable $draftId esté disponible en la vista padre.

    Comportamiento:
    • Cada POLL_INTERVAL ms hace un fetch a /solicitudes/check-lock?draft_id=…
    • Si el servidor responde { active: false } (lock cancelado o expirado desde
      otra pestaña), muestra un overlay bloqueante sobre toda la página.
    • El overlay impide cualquier interacción y dirige al usuario al inicio.
    ──────────────────────────────────────────────────────────────────────────
--}}

{{-- ── Overlay de proceso cancelado ─────────────────────────────────────── --}}
<div id="lockCancelledOverlay"
     role="alertdialog"
     aria-modal="true"
     aria-labelledby="lockOverlayTitle"
     style="
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.78);
        z-index: 99999;
        justify-content: center;
        align-items: center;
     ">
    <div style="
            background: #fff;
            border-radius: 10px;
            padding: 44px 40px 36px;
            max-width: 460px;
            width: 90%;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0,0,0,0.35);
         ">

        {{-- Ícono --}}
        <div style="font-size: 58px; color: #dc3545; margin-bottom: 14px; line-height: 1;">
            <i class="bi bi-exclamation-triangle-fill"></i>
        </div>

        {{-- Título --}}
        <h4 id="lockOverlayTitle"
            style="font-weight: 700; font-size: 1.25rem; color: #222; margin-bottom: 12px;">
            Proceso cancelado
        </h4>

        {{-- Descripción --}}
        <p style="color: #666; font-size: 0.9rem; line-height: 1.65; margin-bottom: 28px;">
            El proceso de solicitud fue <strong>cancelado desde otra ventana o pestaña</strong>.<br>
            Todos los datos e&nbsp;imágenes capturados hasta este punto han sido eliminados.<br><br>
            Para continuar, inicia una nueva solicitud desde el principio.
        </p>

        {{-- Botón de acción --}}
        <a href="{{ route('dashboard') }}"
           class="btn"
           style="background-color: #CEA845; border-color: #CEA845; color: #fff;
                  font-weight: 600; padding: 11px 32px; border-radius: 5px; font-size: 0.95rem;">
            <i class="bi bi-house-door-fill mr-1"></i>
            Ir al inicio
        </a>
    </div>
</div>

{{-- ── Script de polling ────────────────────────────────────────────────── --}}
<script>
(function () {
    'use strict';

    var DRAFT_ID      = @json($draftId ?? '');
    var CHECK_URL     = '{{ route("solicitud.checkLock") }}';
    var POLL_INTERVAL = 12000; // ms entre verificaciones (12 s)
    var overlay       = document.getElementById('lockCancelledOverlay');
    var polling       = true;

    /**
     * Consulta al servidor si el lock para este draftId sigue activo.
     * Si no, muestra el overlay bloqueante.
     */
    function verificarLock() {
        if (!polling || !DRAFT_ID) return;

        fetch(CHECK_URL + '?draft_id=' + encodeURIComponent(DRAFT_ID), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(function (response) {
            if (!response.ok) return null;      // error HTTP: no interrumpir flujo
            return response.json();
        })
        .then(function (data) {
            if (!data) return;
            if (data.active === false) {
                polling = false;                 // detener polling
                mostrarOverlay();
            }
        })
        .catch(function () {
            // Error de red (sin conexión, timeout…): ignorar silenciosamente,
            // el lock expiará de todas formas por TTL.
        });
    }

    function mostrarOverlay() {
        overlay.style.display = 'flex';
        // Bloquear scroll de la página subyacente
        document.body.style.overflow = 'hidden';
    }

    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) verificarLock();
    });
    window.addEventListener('focus', verificarLock);

    (function loop() {
        if (!polling) return;
        verificarLock();
        setTimeout(loop, POLL_INTERVAL);
    })();
})();
</script>
