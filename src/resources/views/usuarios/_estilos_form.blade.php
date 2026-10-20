{{-- Estilos del alta y la edición de usuarios. --}}
    <style>
        .uf { max-width: 920px; }
        .uf-seccion { margin-bottom: 14px; }
        .uf-seccion__titulo { display: flex; align-items: center; gap: 10px; margin: 0 0 4px; font-size: 16px; font-weight: 700; color: var(--tn-tinta); }
        .uf-seccion__num {
            width: 26px; height: 26px; border-radius: 50%; display: grid; place-items: center;
            background: var(--tn-verde); color: #fff; font-size: 13px; font-weight: 700;
        }
        .uf-seccion__ayuda { margin: 0 0 14px 36px; font-size: 12.5px; color: var(--tn-tenue); }
        .uf label { font-size: 13px; font-weight: 700; color: var(--tn-suave); margin-bottom: 4px; }
        .uf .req { color: #8C1D40; }
        .uf .form-text { font-size: 12px; color: var(--tn-tenue); }
        .uf hr { display: none; }
        .uf-pie { display: flex; justify-content: flex-end; gap: 10px; flex-wrap: wrap; margin-top: 4px; }
        .uf-cabeza { display: flex; align-items: center; gap: 14px; }
        .uf-cabeza__foto { width: 56px; height: 56px; border-radius: 50%; object-fit: cover; border: 1px solid var(--tn-borde); flex: 0 0 auto; }
        .uf-cabeza__letras { display: grid; place-items: center; background: #E7ECEC; color: var(--tn-verde); font-weight: 700; font-size: 18px; }
        .uf-meta { display: flex; flex-wrap: wrap; gap: 4px 14px; font-size: 12.5px; color: var(--tn-tenue); margin-top: 2px; }
        .uf-meta strong { color: var(--tn-suave); font-weight: 600; }
        @media (max-width: 576px) { .uf-seccion__ayuda { margin-left: 0; } .uf-pie .btn { flex: 1 1 100%; } }
        .uf-roles { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.4fr); gap: 12px; }
        .uf-roles.is-invalid .uf-roles__grupo { border-color: #DC3545; }
        .uf-roles__grupo { border: 1px solid var(--tn-borde); border-radius: 10px; padding: 10px 14px 8px; margin: 0; }
        .uf-roles__grupo legend { float: none; width: auto; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--tn-tenue); padding: 0 6px; margin: 0; }
        .uf-roles__lista { columns: 2; column-gap: 16px; }
        .uf-roles__lista .form-check { break-inside: avoid; }
        .uf-roles__ayuda { font-size: 12px; color: var(--tn-tenue); margin: 2px 0 6px; }
        .uf-roles__aviso { font-size: 12px; color: #8A6A12; background: #FBF4E1; border-radius: 6px; padding: 4px 8px; margin-top: 6px; }
        .uf .form-check-label { font-weight: 500; color: var(--tn-tinta); font-size: 13.5px; }
        @media (max-width: 768px) { .uf-roles { grid-template-columns: 1fr; } .uf-roles__lista { columns: 1; } }
        /* Agenda del conciliador */
        .uf-opciones { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; }
        .uf-opcion {
            display: flex; gap: 10px; align-items: flex-start; margin: 0;
            padding: 10px 12px; border: 1px solid var(--tn-borde); border-radius: 10px; cursor: pointer;
            font-weight: 500 !important;
        }
        .uf-opcion:has(input:checked) { border-color: var(--tn-verde); box-shadow: inset 0 0 0 1px var(--tn-verde); background: #F7FAFA; }
        .uf-opcion input { margin-top: 3px; flex: 0 0 auto; }
        .uf-opcion__titulo { display: block; font-weight: 700; font-size: 14px; color: var(--tn-tinta); }
        .uf-opcion__texto { display: block; font-size: 12px; color: var(--tn-tenue); line-height: 1.35; }
        .uf-dias { display: flex; flex-wrap: wrap; gap: 6px; }
        .btn.uf-dia { min-width: 54px; border: 1px solid var(--tn-borde); background: #fff; color: var(--tn-suave); font-weight: 700; font-size: 13px; }
        .btn-check:checked + .btn.uf-dia { background: var(--tn-verde); border-color: var(--tn-verde); color: #fff; }
        .btn.uf-dia:not(.btn-social):not(.btn-social-icon):hover { border-color: var(--tn-verde-claro) !important; color: var(--tn-verde); background: #fff; }
        .btn-check:checked + .btn.uf-dia:not(.btn-social):not(.btn-social-icon):hover { background: #354647; color: #fff; }
        .uf-seccion__num .bi { font-size: 13px; }
        .uf-sinotifico { padding: 10px 14px; border: 1px solid #EBDDB0; border-radius: 10px; background: #FBF6E7; }
        .uf-sinotifico .form-check-label { font-weight: 600; }
        @media (max-width: 768px) { .uf-opciones { grid-template-columns: 1fr; } }
    </style>
