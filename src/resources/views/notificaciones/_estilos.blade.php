{{--
    Estilos del apartado de Notificaciones. Parten de los de Turnos
    (tarjetas, botón dorado, paginación) para que se vean de la misma familia.
--}}
@include('turnos._estilos')
<style>
    .nt-filtros {
        display: grid;
        grid-template-columns: minmax(220px, 2fr) repeat(auto-fit, minmax(150px, 1fr));
        gap: 12px;
        align-items: end;
    }
    .nt-filtros label {
        display: block;
        font-size: 11.5px;
        font-weight: 600;
        color: var(--tn-tenue);
        margin-bottom: 4px;
    }
    .nt-filtros__acciones { display: flex; gap: 8px; }

    .nt-pestanas { display: flex; flex-wrap: wrap; gap: 8px; margin: 16px 0 12px; }
    .nt-pestana {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 7px 14px;
        border-radius: 10px;
        border: 1px solid var(--tn-borde);
        background: #fff;
        color: var(--tn-tinta);
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
    }
    .nt-pestana:hover { color: var(--tn-tinta); border-color: var(--tn-verde-claro); }
    .nt-pestana.is-activa { border-color: var(--tn-verde); box-shadow: inset 0 0 0 1px var(--tn-verde); }
    .nt-pestana__num {
        min-width: 26px;
        padding: 1px 8px;
        border-radius: 999px;
        background: var(--tn-fondo);
        font-size: 12px;
        text-align: center;
        font-variant-numeric: tabular-nums;
    }

    .nt-tabla { margin: 0; }
    /* style.css pinta las cabeceras con .table:not(.table-sm) thead th
       (gris claro, más específico); con este selector se le gana y la
       cabecera queda oscura con letra blanca. */
    .nt-tabla.table:not(.table-sm) thead th,
    .nt-tabla thead th {
        --bs-table-bg: #354647;
        background: #354647;
        color: #fff !important;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: .03em;
        white-space: nowrap;
        border: 0;
        vertical-align: middle;
    }
    .nt-tabla td { vertical-align: middle; font-size: 13.5px; color: var(--tn-tinta); }
    .nt-nue { font-weight: 700; white-space: nowrap; font-variant-numeric: tabular-nums; }
    .nt-sub { display: block; font-size: 12px; color: var(--tn-tenue); }
    /* Hay citados de 200+ caracteres ("QUIEN O QUIENES RESULTEN..."): dos
       líneas y el nombre completo en el title. */
    .nt-citado {
        font-weight: 600;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        max-width: 300px;
        line-height: 1.3;
    }
    .nt-direccion { min-width: 220px; max-width: 340px; line-height: 1.35; }
    .nt-direccion__lugar { display: block; font-size: 12px; color: var(--tn-suave); font-weight: 600; }

    .nt-estatus {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }
    .nt-estatus::before { content: ""; width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
    .nt-estatus--gris  { background: #EEF0F3; color: #5F6B7A; }
    .nt-estatus--ambar { background: #FBF4E1; color: #8A6A12; }
    .nt-estatus--verde { background: #E3F4E8; color: #1E7B3C; }
    .nt-estatus--azul  { background: #E4EFF5; color: #2F6F8F; }
    .nt-estatus--rojo  { background: #FBE7EA; color: #A3162E; }

    .nt-tipo {
        display: inline-block;
        padding: 1px 8px;
        border-radius: 6px;
        font-size: 11.5px;
        font-weight: 700;
        background: #EEF2F2;
        color: var(--tn-verde);
    }
    .nt-tipo--multa { background: #F6E4EA; color: #8C1D40; }

    .nt-vacio { padding: 48px 16px; text-align: center; color: var(--tn-suave); }
    .nt-vacio i { font-size: 34px; display: block; margin-bottom: 10px; color: var(--tn-verde-claro); }

    .nt-pie {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 14px;
        font-size: 13px;
        color: var(--tn-tenue);
    }
    .nt-por { display: inline-flex; align-items: center; gap: 6px; }
    .nt-por .form-select { width: auto; }

    .nt-acciones { display: flex; gap: 6px; justify-content: flex-end; flex-wrap: wrap; }
</style>
