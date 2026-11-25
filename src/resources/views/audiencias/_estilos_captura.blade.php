@include('turnos._estilos')
<style>
    /* ── Encabezado y tarjetas ─────────────────────────────────────────── */
    .au-captura .section-header .page__heading { color: var(--tn-tinta); }
    .au-captura .au-sub { font-size: 13px; color: var(--tn-tenue); }
    .au-captura .au-sub strong { color: var(--tn-verde); font-weight: 700; }

    .au-captura .card {
        background: #fff;
        border: 1px solid var(--tn-borde);
        border-radius: 12px;
        box-shadow: none;
    }
    .au-captura .card > .card-body { padding: 18px 20px; }

    /* Títulos dentro de la tarjeta ("Concepto de Pago", "Total a pagar"…). */
    .au-captura .card-body h3,
    .au-captura .card-body h4 { color: var(--tn-tinta); font-weight: 700; }
    .au-captura .card-body h4 { font-size: 16px; margin: 18px 0 8px; }
    .au-captura .card-body > h3:first-child { font-size: 18px; margin-bottom: 14px; }
    .au-captura .au-total { color: #1E7B3C; font-weight: 800; font-variant-numeric: tabular-nums; }

    /* Barra de acciones de la audiencia. */
    .au-barra { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; }
    .au-barra .btn { margin: 0; }
    .au-pie { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 16px; }

    /* Bloques de texto de la resolución (antes con borde negro). */
    .au-bloque {
        border: 1px solid var(--tn-borde);
        border-radius: 12px;
        background: var(--tn-fondo);
        padding: 12px 16px 4px;
        margin-bottom: 6px;
    }

    .au-captura label,
    .modal label { font-weight: 600; color: var(--tn-suave); }

    /* ── Tablas: el encabezado oscuro de Solicitudes / Índice ──────────── */
    .au-captura .table { margin: 0; }
    .tn.au-captura .table > :not(caption) > * > th,
    .tn.au-captura .table > :not(caption) > * > td { padding: 10px 12px; height: auto; }
    .tn.au-captura .table thead th {
        background: #354647; --bs-table-bg: #354647; color: #fff !important;
        font-size: 12px; font-weight: 600; letter-spacing: .03em; white-space: nowrap; border: 0;
    }
    .au-captura .table td { vertical-align: middle; font-size: 13.5px; color: var(--tn-tinta); }
    .au-captura .table-striped > tbody > tr:nth-of-type(odd) > * { --bs-table-accent-bg: var(--tn-fondo); }
    .au-captura .table-responsive { border: 1px solid var(--tn-borde); border-radius: 10px; }
    .au-parte { font-weight: 700; color: var(--tn-verde); white-space: nowrap; }

    /* ── Modales ───────────────────────────────────────────────────────── */
    .modal .modal-content { border: 1px solid var(--tn-borde); border-radius: 12px; }
    .modal .modal-header { border-bottom: 1px solid var(--tn-borde); }
    .modal .modal-footer { border-top: 1px solid var(--tn-borde); }
    .modal .modal-title { font-weight: 700; color: var(--tn-tinta); }

    /* ── Botones ───────────────────────────────────────────────────────── */
    :is(.tn, .modal) .btn:is(.btn-primary, .btn-success, .btn-danger, .btn-info, .btn-warning, .btn-secondary, .btn-dark) {
        --bs-btn-color: var(--au-c);
        --bs-btn-bg: var(--au-bg);
        --bs-btn-border-color: var(--au-borde);
        --bs-btn-hover-color: #fff;
        --bs-btn-hover-bg: var(--au-hover);
        --bs-btn-hover-border-color: var(--au-hover);
        --bs-btn-active-color: #fff;
        --bs-btn-active-bg: var(--au-activo);
        --bs-btn-active-border-color: var(--au-activo);
        --bs-btn-disabled-color: var(--au-c);
        --bs-btn-disabled-bg: var(--au-bg);
        --bs-btn-disabled-border-color: var(--au-borde);
        /* style.css les pone color y sombra directos; aquí se igualan. */
        background-color: var(--au-bg);
        border-color: var(--au-borde);
        color: var(--au-c);
        box-shadow: none;
        font-weight: 600;
    }
    /* style.css pinta de blanco (o de su color) todo .btn en hover/focus/active
       con !important: estos selectores le ganan en especificidad. */
    :is(.tn, .modal) .btn:is(.btn-primary, .btn-success, .btn-danger, .btn-info, .btn-warning, .btn-secondary, .btn-dark):not(.btn-social):not(.btn-social-icon):focus {
        background-color: var(--au-bg) !important;
        border-color: var(--au-borde) !important;
        color: var(--au-c) !important;
    }
    :is(.tn, .modal) .btn:is(.btn-primary, .btn-success, .btn-danger, .btn-info, .btn-warning, .btn-secondary, .btn-dark):not(.btn-social):not(.btn-social-icon):hover {
        background-color: var(--au-hover) !important;
        border-color: var(--au-hover) !important;
        color: #fff !important;
    }
    :is(.tn, .modal) .btn:is(.btn-primary, .btn-success, .btn-danger, .btn-info, .btn-warning, .btn-secondary, .btn-dark):not(.btn-social):not(.btn-social-icon):active {
        background-color: var(--au-activo) !important;
        border-color: var(--au-activo) !important;
        color: #fff !important;
    }
    :is(.tn, .modal) .btn:is(.btn-primary, .btn-success, .btn-danger, .btn-info, .btn-warning, .btn-secondary, .btn-dark):focus-visible {
        outline: 2px solid var(--au-hover);
        outline-offset: 2px;
    }

    :is(.tn, .modal) .btn.btn-primary {
        --au-c: #fff; --au-bg: #496163; --au-borde: #496163;
        --au-hover: #3A4E50; --au-activo: #2E3F41;
    }
    :is(.tn, .modal) .btn.btn-success {
        --au-c: #fff; --au-bg: #1E7B3C; --au-borde: #1E7B3C;
        --au-hover: #186632; --au-activo: #13522A;
    }
    :is(.tn, .modal) .btn.btn-danger {
        --au-c: #fff; --au-bg: #8C1D40; --au-borde: #8C1D40;
        --au-hover: #741733; --au-activo: #5E1229;
    }
    :is(.tn, .modal) .btn.btn-info {
        --au-c: #1D5F73; --au-bg: #E3EFF3; --au-borde: #C3DCE5;
        --au-hover: #1D5F73; --au-activo: #164B5B;
    }
    :is(.tn, .modal) .btn.btn-warning {
        --au-c: #7A5D10; --au-bg: #F8F0DA; --au-borde: #EBDDB4;
        --au-hover: #9A7A1F; --au-activo: #7A5D10;
    }
    :is(.tn, .modal) .btn.btn-secondary {
        --au-c: #5B6667; --au-bg: #EEF0F0; --au-borde: #DDE2E2;
        --au-hover: #5B6667; --au-activo: #485253;
    }
    :is(.tn, .modal) .btn.btn-dark {
        --au-c: #fff; --au-bg: #354647; --au-borde: #354647;
        --au-hover: #2E3C3D; --au-activo: #232E2F;
    }
</style>
