{{--
    Estilos compartidos por las pantallas de Turnos (inicio y todos los
    turnos). Colores institucionales y los mismos tonos de estatus que la
    agenda (App\Support\SemaforoAgenda), para que "pendiente" sea del mismo
    gris en todas partes.
--}}
<style>
    .tn {
        --tn-verde:       #496163;
        --tn-verde-claro: #829A9C;
        --tn-dorado:      #CEA845;
        --tn-tinta:       #2E3C3D;
        --tn-suave:       #5E6E6F;
        --tn-tenue:       #8A9899;
        --tn-borde:       #E3E8E8;
        --tn-fondo:       #F5F7F7;
    }

    .tn-tarjeta {
        background: #fff;
        border: 1px solid var(--tn-borde);
        border-radius: 12px;
        padding: 18px 20px;
    }

    .tn-titulo-seccion {
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--tn-tenue);
        margin: 0 0 12px;
    }

    /* Estatus */
    .tn-estatus {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
        text-transform: capitalize;
    }
    .tn-estatus::before {
        content: "";
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: currentColor;
    }
    .tn-estatus--pendiente  { background: #EEF0F3; color: #5F6B7A; }
    .tn-estatus--confirmada { background: #E3F4E8; color: #1E7B3C; }
    .tn-estatus--atendido   { background: #E7ECEC; color: #496163; }
    .tn-estatus--expirada   { background: #FDEBDD; color: #B4530F; }

    .tn-origen {
        display: inline-block;
        padding: 1px 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .03em;
    }
    .tn-origen--linea      { background: #FBF4E1; color: #8A6A12; }
    .tn-origen--ventanilla { background: var(--tn-fondo); color: var(--tn-suave); }
    .tn-origen--excepcion  { background: #F6E4EA; color: #8C1D40; }

    .tn-boton-dorado {
        background: var(--tn-dorado);
        border-color: var(--tn-dorado);
        color: #fff;
        font-weight: 600;
    }
    .tn-boton-dorado:hover,
    .tn-boton-dorado:focus { background: #B8952F; border-color: #B8952F; color: #fff; }
</style>
<style>
    .tn .pagination .page-link { color: var(--tn-verde); }
    .tn .pagination .page-item.active .page-link { background: var(--tn-verde); border-color: var(--tn-verde); color: #fff; }
</style>
