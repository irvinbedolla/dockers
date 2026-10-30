{{--
    Estilos del selector de día y hora (tira de días + horas libres). Lo usan
    el formulario de cita (_cita_modales) y Administración → Cancelación/Reagenda
    de citas. Usa --color-guinda y --color-oro: quien lo incluya los define.
--}}
    <style>
        .hc-contexto { display: block; margin-top: 2px; font-size: 13px; color: rgba(255, 255, 255, .8); }
        .hc-cuerpo { padding: 18px 20px; min-height: 380px; }

        .hc-semana { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 12px; }
        .hc-semana__texto { font-size: 16px; font-weight: 700; color: #2E3C3D; text-align: center; }
        .hc-flecha {
            width: 42px; height: 42px; border-radius: 50%;
            border: 1px solid #DDE3E3; background: #fff; color: var(--color-guinda);
            display: grid; place-items: center; font-size: 18px;
        }
        .hc-flecha:hover:not(:disabled) { background: #F2F6F6; }
        .hc-flecha:disabled { opacity: .35; cursor: not-allowed; }

        .hc-dias {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 8px;
            margin-bottom: 18px;
        }
        .hc-dia {
            display: flex; flex-direction: column; align-items: center; gap: 2px;
            padding: 10px 4px;
            border-radius: 12px;
            border: 2px solid #E3E8E8;
            background: #fff;
            color: #2E3C3D;
            line-height: 1.15;
            transition: border-color .12s, background-color .12s;
        }
        .hc-dia__semana { font-size: 12.5px; text-transform: capitalize; color: #6C7A7B; }
        .hc-dia__numero { font-size: 22px; font-weight: 800; }
        .hc-dia__mes { font-size: 12px; text-transform: capitalize; color: #6C7A7B; }
        .hc-dia__libres { margin-top: 4px; font-size: 11.5px; font-weight: 700; color: #1E7B3C; }
        .hc-dia:hover:not(:disabled) { border-color: #9FC3A9; }
        .hc-dia.is-activo { border-color: var(--color-guinda); background: var(--color-guinda); color: #fff; }
        .hc-dia.is-activo .hc-dia__semana,
        .hc-dia.is-activo .hc-dia__mes,
        .hc-dia.is-activo .hc-dia__libres { color: rgba(255, 255, 255, .9); }
        .hc-dia:disabled { background: #F5F7F7; color: #A6B1B2; cursor: not-allowed; }
        .hc-dia:disabled .hc-dia__libres { color: #A6B1B2; font-weight: 600; }

        .hc-grupo { margin-bottom: 14px; }
        .hc-grupo__titulo { font-size: 13px; font-weight: 700; color: #6C7A7B; margin-bottom: 8px; text-transform: uppercase; letter-spacing: .05em; }
        .hc-grupo__horas { display: grid; grid-template-columns: repeat(auto-fill, minmax(92px, 1fr)); gap: 8px; }
        .hc-hora {
            padding: 11px 6px;
            border-radius: 10px;
            border: 2px solid #CDE5D4;
            background: #F1FAF3;
            color: #1E5E33;
            font-size: 16px;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
        }
        .hc-hora:hover { border-color: #1E7B3C; }
        .hc-hora.is-elegida { border-color: var(--color-oro); background: var(--color-oro); color: #fff; box-shadow: 0 4px 12px rgba(206, 168, 69, .35); }
        .hc-dia:focus-visible, .hc-hora:focus-visible, .hc-flecha:focus-visible { outline: 3px solid var(--color-oro); outline-offset: 2px; }

        .hc-mensaje { padding: 36px 12px; text-align: center; color: #6C7A7B; }
        .hc-mensaje i { display: block; font-size: 34px; margin-bottom: 8px; color: #A6B1B2; }
        .hc-mensaje .btn { margin-top: 10px; }

        .hc-pie { display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap; background: #F8F9FA; }
        .hc-elegido { font-size: 14.5px; color: #6C7A7B; }
        .hc-elegido strong { color: #2E3C3D; }
        .hc-pie__botones { display: flex; gap: 8px; margin-left: auto; }

        /* Celular: la ventana ocupa la pantalla, los días se deslizan y los botones quedan abajo a lo ancho. */
        @media (max-width: 576px) {
            .hc-dialogo { margin: 0; max-width: none; height: 100%; }
            .hc-dialogo .modal-content { height: 100%; border-radius: 0; border: 0; }
            .hc-cuerpo { padding: 14px; }
            .hc-dias { display: flex; overflow-x: auto; scroll-snap-type: x mandatory; padding-bottom: 4px; }
            .hc-dia { flex: 0 0 76px; scroll-snap-align: start; }
            .hc-grupo__horas { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .hc-pie { flex-direction: column; align-items: stretch; }
            .hc-pie__botones { margin-left: 0; }
            .hc-pie__botones .btn { flex: 1; padding: 12px; }
            .hc-elegido { text-align: center; }
        }
    </style>
