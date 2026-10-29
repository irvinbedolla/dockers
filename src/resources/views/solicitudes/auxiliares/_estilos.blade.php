{{--
    Estilos de la captura de solicitudes del auxiliar (elegir tipo y datos
    generales). Mismos colores que Turnos y Mi módulo (turnos/_estilos).
--}}
@include('turnos._estilos')
<style>
    .sa { max-width: 1080px; margin: 0 auto; }
    .sa-cabeza { margin-bottom: 18px; }
    .sa-cabeza__regreso { font-size: 13px; color: var(--tn-verde-claro); text-decoration: none; }
    .sa-cabeza__regreso:hover { color: var(--tn-verde); }
    .sa-cabeza h3 { margin: 2px 0 0; }
    .sa-cabeza__sub { font-size: 13.5px; color: var(--tn-tenue); margin: 4px 0 0; }

    .sa-prellenado {
        display: flex; align-items: center; flex-wrap: wrap; gap: 10px 14px;
        padding: 12px 16px; margin-bottom: 16px; border-radius: 12px;
        background: #FBF4E1; border: 1px solid #F0DFA8; color: #6B5210; font-size: 13.5px;
    }
    .sa-prellenado__icono { width: 34px; height: 34px; border-radius: 10px; display: grid; place-items: center; background: #CEA845; color: #fff; flex: 0 0 auto; }
    .sa-prellenado__texto { display: flex; flex-direction: column; gap: 1px; min-width: 0; }
    .sa-prellenado__texto strong { color: #4F3C08; overflow-wrap: anywhere; }

    .sa-requisitos {
        display: flex; gap: 12px; align-items: flex-start;
        padding: 12px 16px; margin-bottom: 16px; border-radius: 12px;
        background: #EEF2F2; color: var(--tn-suave); font-size: 13px; line-height: 1.45;
    }
    .sa-requisitos .bi { font-size: 18px; color: var(--tn-verde); margin-top: 1px; }
    .sa-requisitos b { color: var(--tn-tinta); }

    .sa-bloque { margin-bottom: 14px; }
    .sa-bloque__titulo { display: flex; align-items: center; gap: 10px; margin: 0 0 14px; font-size: 15.5px; font-weight: 800; color: var(--tn-tinta); }
    .sa-bloque__num {
        width: 26px; height: 26px; border-radius: 50%; display: grid; place-items: center;
        background: var(--tn-verde); color: #fff; font-size: 13px; font-weight: 800; flex: 0 0 auto;
    }
    .sa-bloque__ayuda { font-size: 12.5px; color: var(--tn-tenue); font-weight: 400; margin-left: auto; }

    /* Campos */
    .sa .form-label { font-size: 13px; font-weight: 700; color: var(--tn-suave); margin-bottom: 6px; }
    .sa .sa-req { color: #B42318; }
    .sa .form-control, .sa .form-select {
        min-height: 46px; border-radius: 10px; border: 1.5px solid #D5DDDD; background-color: #fff;
        font-size: 14.5px; color: var(--tn-tinta); padding-left: 14px; box-shadow: none;
        transition: border-color .15s, box-shadow .15s, background-color .15s;
    }
    .sa .form-control::placeholder { color: #A3AFB0; }
    .sa .form-control:hover, .sa .form-select:hover { border-color: #B9C5C5; }
    .sa .form-control:focus, .sa .form-select:focus { border-color: var(--tn-verde); box-shadow: 0 0 0 4px rgba(73, 97, 99, .14); outline: 0; }
    .sa .form-select:disabled, .sa .form-control[readonly] { background-color: var(--tn-fondo); color: var(--tn-suave); }
    .sa .form-text { font-size: 12px; color: var(--tn-tenue); }
    .sa .was-validated .form-control:valid, .sa .was-validated .form-select:valid { border-color: #9CC5A8; }
    .sa .was-validated .form-control:invalid, .sa .was-validated .form-select:invalid { border-color: #E5A29A; }

    /* Prellenado con revisión: resaltado hasta que el auxiliar lo toca. */
    .sa .campo-prellenado { background-color: #FFF8E1 !important; border-color: #E6C766 !important; box-shadow: 0 0 0 3px rgba(206, 168, 69, .18) !important; }
    .sa-de-cita { display: inline-flex; align-items: center; gap: 4px; margin-left: 6px; padding: 1px 8px; border-radius: 99px; background: #FBF4E1; color: #7A5D10; font-size: 11px; font-weight: 700; vertical-align: 1px; }

    /* Objetos de la solicitud como fichas */
    .sa-objetos { display: flex; flex-wrap: wrap; gap: 8px; min-height: 46px; align-items: center; padding: 8px; border: 1.5px dashed #D5DDDD; border-radius: 10px; background: var(--tn-fondo); }
    .sa-objetos.is-error { border-color: #E5A29A; background: #FDF3F2; }
    .sa-objetos__vacio { font-size: 13px; color: var(--tn-tenue); padding: 0 6px; }
    .sa-objeto {
        display: inline-flex; align-items: center; gap: 8px; padding: 6px 6px 6px 12px; border-radius: 999px;
        background: #fff; border: 1.5px solid var(--tn-verde-claro); color: var(--tn-tinta); font-size: 13.5px; font-weight: 600;
    }
    .sa-objeto.is-sugerido { border-color: #E6C766; background: #FFFBEF; }
    .sa-objeto__sugerido { font-size: 10.5px; font-weight: 800; color: #7A5D10; text-transform: uppercase; letter-spacing: .04em; }
    .sa-objeto button { width: 24px; height: 24px; border: 0; border-radius: 50%; background: #EEF2F2; color: var(--tn-suave); display: grid; place-items: center; font-size: 15px; line-height: 1; cursor: pointer; }
    .sa-objeto button:hover { background: #FDECEA; color: #B42318; }
    .sa-objeto button.sa-objeto__ok { background: #E5F3EA; color: #1E7B3C; font-size: 13px; }
    .sa-objeto button.sa-objeto__ok:hover { background: #1E7B3C; color: #fff; }
    .sa-error { color: #B42318; font-size: 12.5px; margin-top: 6px; }

    .sa-acciones { display: flex; justify-content: flex-end; flex-wrap: wrap; gap: 10px; margin-top: 18px; }
    .sa-acciones .btn { min-height: 44px; padding: 0 22px; border-radius: 10px; font-weight: 700; }

    /* Elegir tipo de solicitud */
    .sa-tipos { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; }
    .sa-tipo {
        position: relative; display: flex; gap: 16px; align-items: center;
        padding: 22px 22px; border-radius: 14px; border: 1.5px solid var(--tn-borde); background: #fff;
        color: var(--tn-tinta); text-decoration: none; transition: border-color .15s, box-shadow .15s, transform .15s;
    }
    .sa-tipo:hover { color: var(--tn-tinta); border-color: var(--tn-verde-claro); box-shadow: 0 8px 22px rgba(46, 60, 61, .09); transform: translateY(-2px); }
    .sa-tipo:focus-visible { outline: 3px solid var(--tn-dorado); outline-offset: 2px; }
    .sa-tipo__icono { width: 58px; height: 58px; border-radius: 14px; display: grid; place-items: center; font-size: 28px; background: #EEF2F2; color: var(--tn-verde); flex: 0 0 auto; }
    .sa-tipo__titulo { display: block; font-size: 18px; font-weight: 800; }
    .sa-tipo__texto { display: block; font-size: 13px; color: var(--tn-suave); margin-top: 2px; }
    .sa-tipo .bi-chevron-right { margin-left: auto; color: var(--tn-tenue); }
    .sa-tipo.is-sugerido { border-color: #CEA845; background: #FFFCF3; box-shadow: 0 0 0 4px rgba(206, 168, 69, .18); }
    .sa-tipo.is-sugerido .sa-tipo__icono { background: #CEA845; color: #fff; }
    .sa-tipo__marca { position: absolute; top: -11px; left: 18px; padding: 2px 10px; border-radius: 99px; background: #CEA845; color: #fff; font-size: 11.5px; font-weight: 800; }
    @media (prefers-reduced-motion: no-preference) {
        .sa-tipo.is-sugerido { animation: saLate 2.2s ease-in-out 3; }
        @keyframes saLate { 0%, 100% { box-shadow: 0 0 0 4px rgba(206, 168, 69, .18); } 50% { box-shadow: 0 0 0 9px rgba(206, 168, 69, .08); } }
    }
    @media (max-width: 576px) {
        .sa-acciones .btn { flex: 1 1 auto; }
        .sa-bloque__ayuda { margin-left: 0; flex-basis: 100%; }
        .sa-bloque__titulo { flex-wrap: wrap; }
    }
</style>
