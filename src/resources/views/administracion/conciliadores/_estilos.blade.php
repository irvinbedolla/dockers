{{-- Estilos de Administración → Conciliadores (index y editar). --}}
@include('turnos._estilos')
<style>
    .cn-cifras { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 10px; }
    .cn-cifra { padding: 12px 14px; border: 1px solid var(--tn-borde); border-radius: 12px; background: #fff; }
    .cn-cifra b { display: block; font-size: 22px; line-height: 1.1; color: var(--tn-tinta); font-variant-numeric: tabular-nums; }
    .cn-cifra span { font-size: 12.5px; color: var(--tn-suave); }
    .cn-cifra--multi { border-style: dashed; border-color: #CEA845; background: #FFFBF0; }
    .cn-cifra--alerta b { color: #8C1D40; }

    .cn-filtros { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; justify-content: space-between; }
    .cn-sedes { display: flex; flex-wrap: wrap; gap: 6px; }
    .cn-sede-filtro {
        padding: 5px 13px; border-radius: 999px; border: 1px solid var(--tn-borde); background: #fff;
        color: var(--tn-suave); font-size: 13px; font-weight: 600; text-decoration: none;
    }
    .cn-sede-filtro:hover { color: var(--tn-verde); border-color: var(--tn-verde-claro); }
    .cn-sede-filtro.is-activa { background: var(--tn-verde); border-color: var(--tn-verde); color: #fff; }

    /* Leyenda y bloques: el color es la sede; relleno = presencial, contorno punteado = virtual. */
    .cn-leyenda { display: flex; flex-wrap: wrap; gap: 6px 14px; font-size: 12px; color: var(--tn-suave); align-items: center; }
    .cn-punto { display: inline-block; width: 10px; height: 10px; border-radius: 3px; margin-right: 4px; vertical-align: -1px; }
    .cn-bloque {
        display: block; padding: 3px 7px; border-radius: 6px; font-size: 11.5px; font-weight: 600; line-height: 1.3;
        color: #fff; background: var(--c); border: 1.5px solid var(--c); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .cn-bloque small { font-weight: 500; opacity: .9; font-variant-numeric: tabular-nums; }
    .cn-bloque--virtual { background: #fff; color: var(--c); border-style: dashed; }
    .cn-bloque + .cn-bloque { margin-top: 3px; }

    .cn-tabla { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 13px; }
    .cn-tabla th { font-size: 11.5px; text-transform: uppercase; letter-spacing: .03em; color: var(--tn-tenue); font-weight: 700; padding: 8px 6px; border-bottom: 1px solid var(--tn-borde); }
    .cn-tabla td { padding: 10px 6px; border-bottom: 1px solid var(--tn-borde); vertical-align: top; }
    .cn-tabla tr:last-child td { border-bottom: 0; }
    .cn-tabla .cn-dia { width: 13%; min-width: 96px; }
    .cn-nombre { font-weight: 700; color: var(--tn-tinta); }
    .cn-sub { font-size: 12px; color: var(--tn-suave); }
    .cn-vacio { font-size: 11.5px; color: #C0C8C8; }
    .cn-insignias { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 4px; }
    .cn-insignia { font-size: 11px; font-weight: 700; padding: 1px 7px; border-radius: 999px; background: #EEF2F2; color: var(--tn-verde); white-space: nowrap; }
    .cn-insignia--multi { background: #FFFBF0; color: #8A6A12; border: 1px dashed #CEA845; }
    .cn-insignia--fuera { background: #EAF2F8; color: #2F6A8A; }
    .cn-insignia--sin { background: #F6E4EA; color: #8C1D40; }
    .cn-insignia--sede { background: #F5F7F7; color: var(--tn-suave); font-weight: 600; }

    .cn-historial { font-size: 12.5px; }
    .cn-historial li { padding: 6px 0; border-bottom: 1px dashed var(--tn-borde); }
    .cn-historial li:last-child { border-bottom: 0; }

    /* Editor */
    .cn-reglas { font-size: 12.5px; color: var(--tn-suave); margin: 0; padding-left: 18px; }
    .cn-fila { border: 1px solid var(--tn-borde); border-left: 4px solid var(--c, var(--tn-verde)); border-radius: 12px; padding: 14px 16px; background: #fff; }
    .cn-fila + .cn-fila { margin-top: 10px; }
    .cn-fila--virtual { border-left-style: dashed; }
    .cn-fila__cabeza { display: flex; justify-content: space-between; align-items: center; gap: 10px; margin-bottom: 10px; }
    .cn-fila__cabeza h6 { margin: 0; font-weight: 700; color: var(--tn-tinta); font-size: 15px; }
    .cn-campos { display: grid; grid-template-columns: minmax(150px, 1.2fr) minmax(190px, 1.2fr) minmax(220px, 2fr) 110px 110px minmax(120px, 1fr); gap: 10px; align-items: end; }
    .cn-campos label { display: block; font-size: 12px; font-weight: 700; color: var(--tn-suave); margin-bottom: 3px; }
    .cn-modos { display: flex; gap: 4px; }
    .cn-modos .btn, .cn-dias .btn { font-size: 12.5px; padding: 5px 10px; border: 1px solid var(--tn-borde); color: var(--tn-suave); background: #fff; }
    .cn-modos .btn-check:checked + .btn, .cn-dias .btn-check:checked + .btn { background: var(--tn-verde); border-color: var(--tn-verde); color: #fff; }
    .cn-dias { display: flex; gap: 4px; flex-wrap: wrap; }
    .cn-pendientes { font-size: 12px; color: var(--tn-suave); margin-top: 8px; }
    .cn-previa td, .cn-previa th { text-align: center; }
    .cn-aviso-previa { font-size: 12px; color: #8A6A12; margin-top: 6px; }

    @media (max-width: 991px) {
        .cn-campos { grid-template-columns: 1fr 1fr; }
        .cn-campos .cn-campo--dias, .cn-campos .cn-campo--modo { grid-column: 1 / -1; }
        .cn-tabla .cn-dia { min-width: 84px; }
    }
    @media (max-width: 480px) { .cn-campos { grid-template-columns: 1fr; } }
</style>
