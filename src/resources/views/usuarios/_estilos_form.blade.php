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
    </style>
