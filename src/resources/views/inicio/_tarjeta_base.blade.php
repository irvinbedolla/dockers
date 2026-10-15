{{--
    Caja común de las tarjetas de estadísticas: la usan la fila de cifras
    (_tarjetas, en el Panel estadístico) y la tabla de posiciones
    (_tarjeta_posiciones, en el Inicio).
--}}
<style>
    .inicio-tarjeta {
        background: #fff;
        border: 1px solid #E3E8E8;
        border-radius: 12px;
        padding: 20px 22px;
        /* Sin esto un nombre largo ensancha su columna y empuja a las otras:
           minmax(0, 1fr) en la rejilla y min-width aquí son la misma defensa
           vista desde los dos lados. */
        min-width: 0;
    }

    .inicio-tarjeta__titulo {
        font-size: 12px;
        font-weight: 600;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #7B8A8B;
        margin: 0 0 14px;
    }
</style>
