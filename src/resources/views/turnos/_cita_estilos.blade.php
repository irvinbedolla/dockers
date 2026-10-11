{{--
    Estilos del formulario de cita (_cita_formulario y _cita_modales). Los
    colores y botones van aquí también para que funcionen dentro del layout
    del sistema, donde style.css pone blancos los .btn al pasar el mouse.
--}}
<style>
    :root {
        --color-guinda: #496163;
        --color-guinda-dark: #530c3a;
        --color-oro: #CEA845;
        --color-oro-dark: #b59238;
    }
    .card-header-guinda { background-color: var(--color-guinda); color: #fff; font-weight: 600; }
    .btn-guinda { background-color: var(--color-guinda); color: #fff; border: none; }
    .btn-oro { background-color: var(--color-oro); color: #fff; border: none; font-weight: 500; }
    .btn.btn-guinda:not(.btn-social):not(.btn-social-icon):hover,
    .btn.btn-guinda:not(.btn-social):not(.btn-social-icon):focus,
    .btn.btn-guinda:not(.btn-social):not(.btn-social-icon):active { background-color: var(--color-guinda-dark); border-color: var(--color-guinda-dark) !important; color: #fff; }
    .btn.btn-oro:not(.btn-social):not(.btn-social-icon):hover,
    .btn.btn-oro:not(.btn-social):not(.btn-social-icon):focus,
    .btn.btn-oro:not(.btn-social):not(.btn-social-icon):active { background-color: var(--color-oro-dark); border-color: var(--color-oro-dark) !important; color: #fff; }
    .btn.btn-guinda:disabled, .btn.btn-oro:disabled { opacity: .55; }
</style>
    {{-- Formulario de cita en línea: pasos, ayudas y validación en pantalla. --}}
    <style>
        .gc-intro { color: #4A5859; font-size: 15px; margin: 0 0 18px; }
        .gc-req { color: #C8102E; font-weight: 700; }
        .gc-opcional { color: #8A9899; font-weight: 400; font-size: 13px; }

        .gc-pasos {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 8px;
            list-style: none;
            padding: 0;
            margin: 0 0 22px;
            counter-reset: none;
        }
        .gc-pasos li {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 10px;
            border-radius: 10px;
            background: #EEF2F2;
            color: #5E6E6F;
            font-size: 13.5px;
            font-weight: 600;
            min-width: 0;
        }
        .gc-pasos__num {
            flex: 0 0 auto;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: #fff;
            color: var(--color-guinda);
            font-size: 13px;
            font-weight: 800;
        }
        .gc-pasos__texto { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .gc-pasos li.is-listo { background: #E3F4E8; color: #1E7B3C; }
        .gc-pasos li.is-listo .gc-pasos__num { background: #1E7B3C; color: #fff; font-size: 0; }
        .gc-pasos li.is-listo .gc-pasos__num::before { content: "\2713"; font-size: 14px; }
        @media (max-width: 576px) {
            .gc-pasos { gap: 4px; }
            .gc-pasos li { flex-direction: column; gap: 4px; padding: 8px 4px; font-size: 11.5px; text-align: center; }
        }

        .gc-seccion {
            border: 1px solid #E3E8E8;
            border-radius: 12px;
            padding: 18px 20px 20px;
            margin: 0 0 18px;
            background: #fff;
        }
        .gc-titulo {
            float: none;
            width: auto;
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0 0 14px;
            font-size: 18px;
            font-weight: 700;
            color: var(--color-guinda);
        }
        .gc-num {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: var(--color-guinda);
            color: #fff;
            font-size: 15px;
        }
        .gc-form .form-label { font-weight: 600; color: #2E3C3D; font-size: 14.5px; }
        .gc-form .form-control,
        .gc-form .form-select { min-height: 46px; font-size: 16px; }   /* 16px: el celular no hace zoom */
        .gc-form .form-control:focus,
        .gc-form .form-select:focus { border-color: var(--color-oro); box-shadow: 0 0 0 .2rem rgba(206, 168, 69, .25); }
        .gc-mayus { text-transform: uppercase; }
        .gc-mayus::placeholder { text-transform: none; }

        .gc-ayuda-tramite.is-activa {
            margin-top: 8px;
            padding: 8px 12px;
            border-radius: 8px;
            background: #FBF4E1;
            color: #6B5310;
            font-size: 13.5px;
        }

        .gc-oficina {
            height: 100%;
            min-height: 72px;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: 10px;
            border: 1px dashed #C9D3D3;
            color: #8A9899;
        }
        .gc-oficina i { font-size: 24px; }
        .gc-oficina__etiqueta { display: block; font-size: 12.5px; }
        .gc-oficina__nombre { display: block; font-size: 17px; font-weight: 700; }
        .gc-oficina.is-lista { border: 1px solid var(--color-guinda); background: #F2F6F6; color: var(--color-guinda); }

        .gc-horario { display: flex; flex-direction: column; align-items: flex-start; gap: 8px; }
        .gc-horario__boton { padding: 12px 22px; font-size: 16px; font-weight: 600; border-radius: 10px; }
        .gc-horario__boton:disabled { opacity: .5; }
        .gc-turno {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 4px;
            padding: 12px 16px;
            border-radius: 10px;
            background: #E3F4E8;
            color: #1E5E33;
            font-size: 15px;
        }
        .gc-turno i { font-size: 24px; }
        .gc-turno strong { display: block; font-size: 17px; }
        .gc-error {
            padding: 8px 12px;
            border-radius: 8px;
            background: #FBE7EA;
            color: #A3162E;
            font-size: 14px;
            font-weight: 600;
        }
        .gc-aviso-cambio {
            padding: 8px 12px;
            border-radius: 8px;
            background: #FBF4E1;
            color: #6B5310;
            font-size: 13.5px;
        }

        .gc-enviar { text-align: center; padding-top: 6px; }
        .gc-enviar__boton { min-width: 260px; padding: 14px 28px; font-size: 18px; font-weight: 700; border-radius: 12px; }
        .gc-enviar__nota { margin: 10px auto 0; max-width: 520px; color: #6C7A7B; font-size: 13.5px; }
        @media (max-width: 576px) {
            .gc-seccion { padding: 14px 14px 16px; }
            .gc-enviar__boton { width: 100%; min-width: 0; }
        }
    </style>
    {{-- Selector de día y hora. --}}
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
