{{--
    Estilos de las tarjetas de accesos rápidos del Inicio (.ar), compartidos
    por recepción (_accesos_recepcion) y por Conciliación, Género e Igualdad
    (_accesos_excepcion).
--}}
@once
<style>
    .ar {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-top: 22px;
        text-align: left;
    }
    @media (max-width: 1100px) { .ar { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 560px)  { .ar { grid-template-columns: 1fr; } }

    .ar-tarjeta {
        display: flex;
        flex-direction: column;
        gap: 10px;
        min-width: 0;
        padding: 16px 16px 14px;
        border: 1px solid #E3E8E8;
        border-radius: 12px;
        background: #F8FAFA;
    }
    .ar-cabeza { display: flex; align-items: center; gap: 10px; }
    .ar-icono {
        display: inline-flex; align-items: center; justify-content: center; flex: none;
        width: 34px; height: 34px; border-radius: 10px;
        background: #E6EDED; color: #496163; font-size: 17px;
    }
    .ar-titulo { margin: 0; font-size: 13.5px; font-weight: 700; color: #2E3C3D; line-height: 1.2; }
    .ar-sub { display: block; font-size: 11.5px; font-weight: 500; color: #8A9899; }

    .ar-cifra { font-size: 30px; font-weight: 800; color: #2E3C3D; line-height: 1; font-variant-numeric: tabular-nums; }
    .ar-cifra small { font-size: 12.5px; font-weight: 600; color: #8A9899; margin-left: 4px; }

    .ar-botones { display: flex; flex-wrap: wrap; gap: 6px; }
    .ar-boton {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 6px 12px; border-radius: 999px;
        background: #496163; color: #fff; font-size: 12.5px; font-weight: 600; text-decoration: none;
    }
    .ar-boton:hover, .ar-boton:focus { background: #354647; color: #fff; }
    .ar-boton--claro { background: #fff; color: #496163; border: 1px solid #D5DEDE; }
    .ar-boton--claro:hover, .ar-boton--claro:focus { background: #EEF2F2; color: #354647; }

    .ar-chips { display: flex; flex-wrap: wrap; gap: 6px; }
    .ar-chip {
        display: inline-flex; align-items: baseline; gap: 5px;
        padding: 3px 10px; border-radius: 999px; border: 1px solid #E3E8E8; background: #fff;
        font-size: 12px; font-weight: 600; color: #5E6E6F; text-decoration: none;
    }
    a.ar-chip:hover { border-color: #829A9C; color: #354647; }
    .ar-chip b { color: #2E3C3D; font-variant-numeric: tabular-nums; }

    .ar-pie { margin-top: auto; font-size: 12px; font-weight: 600; color: #496163; text-decoration: none; }
    .ar-pie:hover { color: #354647; text-decoration: underline; }
    .ar-nota { font-size: 12px; color: #8A9899; }
</style>
@endonce
