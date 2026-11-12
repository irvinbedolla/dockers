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
    .cn-bloque .cn-bloque__vig { display: block; font-size: 10px; font-weight: 600; opacity: .95; }

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
    .cn-vigencia { grid-column: 1 / -1; display: flex; flex-wrap: wrap; align-items: center; gap: 6px 8px; padding-top: 8px; border-top: 1px dashed var(--tn-borde); }
    .cn-vigencia label { margin: 0; }
    .cn-vigencia input[type=date] { width: 150px; }
    .cn-vigencia__titulo { font-size: 12px; font-weight: 700; color: var(--tn-tinta); margin-right: 4px; }
    .cn-vigencia__titulo small { font-weight: 500; color: var(--tn-suave); }
    .cn-vigencia .cn-sub { flex-basis: 100%; }
    /* Exclusiva: botón que se prende, como los días. Apagado gris; prendido guinda con candado cerrado. */
    .cn-vigencia .btn.cn-exclusiva { margin-left: 10px; font-size: 12.5px; padding: 5px 12px; border: 1px solid var(--tn-borde); color: var(--tn-suave); background: #fff; border-radius: 8px; font-weight: 600; }
    .cn-vigencia .btn.cn-exclusiva:hover { border-color: #8C1D40; color: #8C1D40; }
    .cn-vigencia .btn-check:checked + .btn.cn-exclusiva { background: #8C1D40; border-color: #8C1D40; color: #fff; }
    .cn-vigencia .btn-check:focus-visible + .btn.cn-exclusiva { box-shadow: 0 0 0 3px rgba(140, 29, 64, .2); }
    .cn-exclusiva__si { display: none; }
    .btn-check:checked + .cn-exclusiva .cn-exclusiva__si { display: inline; }
    .btn-check:checked + .cn-exclusiva .cn-exclusiva__no { display: none; }

    /* Traspaso de audiencias */
    .tr-form { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 10px 14px; align-items: end; }
    .tr-form label { display: block; font-size: 12px; font-weight: 700; color: var(--tn-suave); margin-bottom: 3px; }
    .tr-agenda { display: flex; flex-wrap: wrap; gap: 6px 14px; align-items: center; font-size: 13px; }
    .tr-cifras { display: flex; flex-wrap: wrap; gap: 8px; margin: 4px 0 12px; }
    .tr-cifra { padding: 8px 12px; border-radius: 10px; border: 1px solid var(--tn-borde); background: #fff; font-size: 12.5px; color: var(--tn-suave); }
    .tr-cifra b { font-size: 18px; color: var(--tn-tinta); margin-right: 4px; font-variant-numeric: tabular-nums; }
    .tr-cifra--pasa { border-color: #B9DCC5; background: #F1F9F4; }
    .tr-cifra--pasa b { color: #1E7B3C; }
    .tr-cifra--choca { border-color: #E9C2B8; background: #FCF2EF; }
    .tr-cifra--choca b { color: #9A3A22; }
    .tr-tabla td, .tr-tabla th { vertical-align: middle; }
    .tr-pasa { color: #1E7B3C; font-weight: 700; font-size: 12px; white-space: nowrap; }
    .tr-choca { color: #9A3A22; font-weight: 700; font-size: 12px; }
    .tr-choca span { display: block; font-weight: 500; color: #7A3A2A; }
    .tr-fila--choca td { background: #FFF8F6; }
    .tr-aplicar { display: grid; grid-template-columns: 1fr auto; gap: 10px 14px; align-items: end; }
    @media (max-width: 767px) { .tr-aplicar { grid-template-columns: 1fr; } }
    .tr-decision { max-width: 420px; font-size: 12.5px; }
    .tr-masivo { display: flex; flex-wrap: wrap; align-items: center; gap: 6px; margin: 4px 0 10px; }
    .tr-alerta { border-radius: 10px; padding: 10px 14px; font-size: 13px; }
    .tr-alerta ul { padding-left: 18px; }
    .tr-alerta--roja { background: #FCEFEC; border: 1px solid #E9C2B8; color: #7A2A18; margin-bottom: 10px; }
    .tr-alerta--ambar { background: #FFF8E6; border: 1px solid #EBD69A; color: #6E5410; }

    /* Resumen del traspaso (se baja como imagen con html2canvas): estilos sencillos y fijos. */
    .tr-resumen-marco { overflow-x: auto; }
    .tr-resumen { width: 980px; background: #fff; padding: 22px 26px; font-family: Arial, Helvetica, sans-serif; color: #2E3C3D; border: 1px solid #E3E8E8; border-radius: 10px; }
    .tr-resumen__cabeza { display: flex; justify-content: space-between; align-items: flex-end; gap: 12px; }
    .tr-resumen__titulo { font-size: 20px; font-weight: 700; color: #354647; }
    .tr-resumen__sub { font-size: 13px; font-weight: 700; color: #496163; margin-top: 2px; }
    .tr-resumen__ccl { font-size: 11px; color: #8A9899; text-align: right; }
    .tr-resumen__barra { height: 4px; background: #496163; margin: 10px 0 12px; }
    .tr-resumen__barra span { display: block; width: 110px; height: 4px; background: #CEA845; }
    .tr-resumen__cifras { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; font-size: 12.5px; }
    .tr-resumen__cifras div { border: 1px solid #DDE4E4; border-radius: 8px; padding: 6px 10px; }
    .tr-resumen__cifras b { font-size: 16px; margin-right: 3px; }
    .tr-resumen__cifras .ok { background: #F1F9F4; border-color: #B9DCC5; color: #1E7B3C; }
    .tr-resumen__cifras .otro { background: #EAF2F8; border-color: #B9D2E3; color: #1F4E79; }
    .tr-resumen__cifras .queda { background: #F4F6F6; color: #5E6E6F; }
    .tr-resumen__cifras .empalme { background: #FFF8E6; border-color: #EBD69A; color: #6E5410; }
    .tr-resumen__tabla { width: 100%; border-collapse: collapse; font-size: 11.5px; }
    .tr-resumen__tabla th { background: #496163; color: #fff; text-align: left; padding: 5px 6px; font-size: 10.5px; text-transform: uppercase; }
    .tr-resumen__tabla td { padding: 4px 6px; border-bottom: 1px solid #E3E8E8; vertical-align: top; }
    .tr-resumen__queda td { color: #8A9899; }
    .tr-resumen__empalme td { background: #FFF8E6; }
    .tr-resumen__otro td { background: #EAF2F8; }
    .tr-resumen__pie { margin-top: 12px; font-size: 11px; color: #6B7A7B; }
    .cn-previa td, .cn-previa th { text-align: center; }
    .cn-aviso-previa { font-size: 12px; color: #8A6A12; margin-top: 6px; }

    @media (max-width: 991px) {
        .cn-campos { grid-template-columns: 1fr 1fr; }
        .cn-campos .cn-campo--dias, .cn-campos .cn-campo--modo { grid-column: 1 / -1; }
        .cn-tabla .cn-dia { min-width: 84px; }
    }
    @media (max-width: 480px) { .cn-campos { grid-template-columns: 1fr; } }
</style>
