{{-- Estilos del Seguimiento del caso (lista y línea de tiempo). Requiere turnos/_estilos. --}}
<style>
    /* Lista */
    .sg-filtros { display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end; }
    .sg-filtros > div { display: flex; flex-direction: column; }
    .sg-filtros label { font-size: 12px; font-weight: 700; color: var(--tn-suave); margin-bottom: 4px; }
    .sg-filtros .form-control, .sg-filtros .form-select { min-height: 42px; border-radius: 10px; border: 1.5px solid #D5DDDD; }
    .sg-filtros .form-control:focus, .sg-filtros .form-select:focus { border-color: var(--tn-verde); box-shadow: 0 0 0 4px rgba(73, 97, 99, .14); }
    .sg-filtros .sg-filtros__buscar { flex: 1 1 280px; }
    .sg-filtros .btn { min-height: 42px; border-radius: 10px; }
    .sg-tabla { width: 100%; font-size: 13px; border-collapse: separate; border-spacing: 0; }
    .sg-tabla th { font-size: 11.5px; text-transform: uppercase; letter-spacing: .05em; color: var(--tn-tenue); font-weight: 700; padding: 10px; border-bottom: 1px solid var(--tn-borde); white-space: nowrap; background: #fff; }
    .sg-tabla td { padding: 11px 10px; border-bottom: 1px solid #F0F3F3; vertical-align: top; color: var(--tn-tinta); }
    .sg-tabla tbody tr { cursor: pointer; }
    .sg-tabla tbody tr:hover td { background: #F8FAFA; }
    .sg-tabla .sg-celda-persona b { display: block; font-size: 13.5px; }
    .sg-tabla small { display: block; color: var(--tn-tenue); font-size: 11.5px; }
    .sg-chip { display: inline-block; padding: 2px 9px; border-radius: 999px; font-size: 11.5px; font-weight: 700; white-space: nowrap; }
    .sg-chip--tramite { background: #FBF4E1; color: #7A5D10; }
    .sg-chip--favorable { background: #E5F3EA; color: #1E7B3C; }
    .sg-chip--resuelto { background: #E7ECEC; color: #354647; }
    .sg-chip--cita { background: #EEF0F3; color: #5F6B7A; }
    .sg-chip--excepcion { background: #F6E4EA; color: #8C1D40; white-space: normal; }
    .sg-origen { display: inline-block; padding: 0 7px; border-radius: 6px; font-size: 10.5px; font-weight: 700; background: var(--tn-fondo); color: var(--tn-suave); }
    .sg-origen--linea { background: #FBF4E1; color: #8A6A12; }

    /* Caso */
    .sg-cabeza { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; flex-wrap: wrap; }
    .sg-persona { font-size: 22px; font-weight: 800; color: var(--tn-tinta); margin: 0; }
    .sg-datos { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
    .sg-dato { padding: 3px 10px; border-radius: 999px; background: var(--tn-fondo); font-size: 12px; color: var(--tn-suave); }
    .sg-dato b { color: var(--tn-tinta); }
    .sg-estado { display: inline-block; padding: 6px 14px; border-radius: 999px; font-weight: 800; font-size: 13px; background: #FBF4E1; color: #7A5D10; }
    .sg-estado.is-resuelto { background: #E7ECEC; color: #354647; }
    .sg-estado.is-favorable { background: #E5F3EA; color: #1E7B3C; }
    .sg-dias { font-size: 12.5px; color: var(--tn-tenue); margin-top: 6px; }

    .sg-responsables { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 8px; margin-top: 16px; }
    .sg-responsable { display: flex; gap: 10px; align-items: center; padding: 10px 12px; border-radius: 10px; background: var(--tn-fondo); }
    .sg-responsable > .bi { width: 32px; height: 32px; border-radius: 9px; display: grid; place-items: center; background: #fff; color: var(--tn-verde); font-size: 15px; flex: 0 0 auto; }
    .sg-responsable span { display: block; font-size: 11px; font-weight: 700; color: var(--tn-tenue); text-transform: uppercase; letter-spacing: .04em; }
    .sg-responsable b { display: block; font-size: 13.5px; color: var(--tn-tinta); overflow-wrap: anywhere; }
    .sg-proxima { display: flex; gap: 10px; align-items: center; margin-top: 10px; padding: 10px 14px; border-radius: 10px; background: #FBF4E1; color: #6B5210; font-size: 13.5px; }
    .sg-proxima > .bi { font-size: 18px; color: #A9862B; }
    .sg-proxima b { color: #4F3C08; }

    .sg-etapas { display: grid; grid-auto-flow: column; grid-auto-columns: minmax(0, 1fr); gap: 6px; margin: 18px 0 6px; }
    .sg-etapa { text-align: center; font-size: 11.5px; font-weight: 700; color: var(--tn-tenue); }
    .sg-etapa::before { content: ""; display: block; height: 6px; border-radius: 99px; background: #E3E8E8; margin-bottom: 6px; }
    .sg-etapa.is-hecha { color: var(--tn-verde); }
    .sg-etapa.is-hecha::before { background: var(--tn-verde); }

    .sg-linea { list-style: none; margin: 18px 0 0; padding: 0; position: relative; }
    .sg-linea::before { content: ""; position: absolute; left: 131px; top: 6px; bottom: 6px; width: 2px; background: #E3E8E8; }
    .sg-evento { display: grid; grid-template-columns: 112px 40px 1fr; gap: 0 8px; align-items: start; padding: 8px 0; }
    .sg-evento__fecha { text-align: right; font-size: 12px; color: var(--tn-suave); line-height: 1.3; padding-top: 4px; }
    .sg-evento__fecha b { display: block; color: var(--tn-tinta); font-size: 12.5px; }
    .sg-evento__punto { position: relative; z-index: 1; width: 30px; height: 30px; margin: 0 auto; border-radius: 50%; display: grid; place-items: center; background: #EEF2F2; color: var(--tn-verde); border: 2px solid #fff; box-shadow: 0 0 0 1px #E3E8E8; font-size: 14px; }
    .sg-evento--ok .sg-evento__punto { background: #E5F3EA; color: #1E7B3C; }
    .sg-evento--alerta .sg-evento__punto { background: #FDECEA; color: #B42318; }
    .sg-evento__titulo { font-weight: 700; color: var(--tn-tinta); font-size: 14px; padding-top: 4px; }
    .sg-evento__detalle { font-size: 12.5px; color: var(--tn-suave); overflow-wrap: anywhere; }
    .sg-evento__etapa { display: inline-block; margin-left: 6px; padding: 0 7px; border-radius: 99px; background: var(--tn-fondo); color: var(--tn-tenue); font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; vertical-align: 2px; }
    .sg-evento--futuro { opacity: .62; }
    .sg-evento--futuro .sg-evento__punto { background: #fff; border-style: dashed; }
    .sg-docs { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 6px; }
    .sg-doc { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 8px; border: 1px solid var(--tn-borde); background: #fff; color: var(--tn-verde); font-size: 12px; font-weight: 600; text-decoration: none; }
    .sg-doc:hover { border-color: var(--tn-verde-claro); background: #F8FAFA; color: var(--tn-verde); }
    .sg-doc .bi { color: #B42318; }
    .sg-hueco, .sg-hoy { display: grid; grid-template-columns: 112px 40px 1fr; gap: 0 8px; }
    .sg-hueco span { grid-column: 3; font-size: 11.5px; font-weight: 700; color: #A9862B; }
    .sg-hoy { margin: 4px 0; }
    .sg-hoy span { grid-column: 2 / 4; font-size: 11px; font-weight: 800; color: var(--tn-dorado); text-transform: uppercase; letter-spacing: .08em; border-top: 2px dashed #E6C766; padding-top: 4px; }
    .sg-nota { font-size: 12px; color: var(--tn-tenue); margin-top: 14px; }

    @media (max-width: 576px) {
        .sg-linea::before { left: 15px; }
        .sg-evento, .sg-hueco, .sg-hoy { grid-template-columns: 32px 1fr; }
        .sg-evento__fecha { grid-column: 2; grid-row: 1; text-align: left; padding: 0; }
        .sg-evento__fecha b { display: inline; margin-right: 4px; }
        .sg-evento__punto { grid-column: 1; grid-row: 1 / span 4; width: 28px; height: 28px; }
        .sg-evento__titulo, .sg-evento__detalle, .sg-docs { grid-column: 2; }
        .sg-hueco span, .sg-hoy span { grid-column: 2; }
        .sg-etapas { grid-auto-flow: row; grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .sg-cabeza .text-end { text-align: left !important; }
    }
    @media print {
        .sg-no-imprimir, .navbar, .main-sidebar, footer { display: none !important; }
        .sg-evento--futuro { opacity: 1; }
    }
</style>
