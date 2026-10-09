{{--
    Estilos compartidos por las pantallas de Turnos (inicio y todos los
    turnos). Colores institucionales y los mismos tonos de estatus que la
    agenda (App\Support\SemaforoAgenda), para que "pendiente" sea del mismo
    gris en todas partes.
--}}
<style>
    /* .modal también: Bootstrap mueve los diálogos fuera de .tn. */
    .tn, .modal {
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

    /* Con colores fijos y no con var(--tn-*): los modales de Bootstrap se
       pintan fuera de .tn y ahí esas variables no existen, así que el botón
       quedaba blanco sobre blanco. Se usan las variables propias de .btn para
       que hover, activo y deshabilitado salgan bien también. */
    .tn-boton-dorado {
        --bs-btn-color: #fff;
        --bs-btn-bg: #CEA845;
        --bs-btn-border-color: #CEA845;
        --bs-btn-hover-color: #fff;
        --bs-btn-hover-bg: #B8952F;
        --bs-btn-hover-border-color: #B8952F;
        --bs-btn-focus-shadow-rgb: 206, 168, 69;
        --bs-btn-active-color: #fff;
        --bs-btn-active-bg: #A6852A;
        --bs-btn-active-border-color: #A6852A;
        --bs-btn-disabled-color: #fff;
        --bs-btn-disabled-bg: #CEA845;
        --bs-btn-disabled-border-color: #CEA845;
        --bs-btn-disabled-opacity: .55;
        font-weight: 600;
    }

    /* style.css pinta de blanco el fondo de todo .btn en hover, focus y
       active (con borde transparente !important) y quita el contorno de
       enfoque. Estos selectores le ganan en especificidad (0,5,0 contra
       0,4,0) sin tocar la hoja global, que usan los demás botones. */
    .btn.tn-boton-dorado:not(.btn-social):not(.btn-social-icon):focus {
        background-color: #CEA845;
        border-color: #CEA845 !important;
        color: #fff;
    }
    .btn.tn-boton-dorado:not(.btn-social):not(.btn-social-icon):hover {
        background-color: #B8952F;
        border-color: #B8952F !important;
        color: #fff;
    }
    .btn.tn-boton-dorado:not(.btn-social):not(.btn-social-icon):active {
        background-color: #A6852A;
        border-color: #A6852A !important;
        color: #fff;
    }
    .btn.tn-boton-dorado:focus-visible {
        outline: 2px solid #496163;
        outline-offset: 2px;
    }
</style>
<style>
    .tn .pagination .page-link { color: var(--tn-verde); }
    .tn .pagination .page-item.active .page-link { background: var(--tn-verde); border-color: var(--tn-verde); color: #fff; }
</style>
