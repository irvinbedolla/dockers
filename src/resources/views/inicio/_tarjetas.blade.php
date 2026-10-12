{{--
    Fila de tarjetas de estadísticas del Inicio.

    Vive aparte del saludo y de la bifurcación por rol porque la ven todos:
    Directivo, Conciliador, Auxiliar y demás.

    Arriba van las tres tarjetas de cifras en tres columnas fijas; abajo, la
    tabla de posiciones a todo lo ancho, con sus tres grupos lado a lado.
    Para sumar otra tarjeta de cifras hay que decidir aquí si la fila pasa a
    cuatro columnas o si arranca una segunda fila.
--}}

<style>
    .inicio-tarjetas {
        display: grid;
        /* Tres columnas fijas y no auto-fill. Con auto-fill, en cuanto la
           fila dejaba de dar para tres la última caía sola en el renglón de
           abajo, que es justo lo que no se quiere. Así se angostan juntas y
           siempre se ven de un vistazo. */
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
        margin-bottom: 18px;
        align-items: start;
        /* La rejilla se mide a sí misma para que las tarjetas puedan
           apretarse según el ancho que de verdad les tocó. El viewport no
           sirve para eso: el menú lateral se colapsa de 250px a 78px sin que
           la pantalla cambie de tamaño, y son 172px que cambian el reparto. */
        container: tarjetas / inline-size;
    }

    /* Tres tarjetas no se reparten bien en dos columnas (quedaría una sola
       abajo), así que de tres pasan directo a una. Con 900px de pantalla y
       el menú escondido cada tarjeta aún mide unos 270px. */
    @media (max-width: 899.98px) {
        .inicio-tarjetas { grid-template-columns: 1fr; }
    }

    .inicio-tarjeta {
        background: #fff;
        border: 1px solid #E3E8E8;
        border-radius: 12px;
        padding: 20px 22px;
        /* Sin esto un nombre largo ensancha su columna y empuja a las otras
           tres: minmax(0, 1fr) arriba y min-width aquí son la misma defensa
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

    /* ---------------------------------------------------------------------
       Modo apretado.

       En tres columnas, una fila de 936px deja tarjetas de 300px, y de ahí
       para abajo las rejillas internas -96px de nombre, 52px de valor y la
       barra en medio- dejan de caber y la barra se queda sin espacio.

       Las reglas viven aquí y no en cada tarjeta porque el disparador es el
       ancho de la fila, que es cosa de la fila. Va envuelto en el media query
       porque el umbral sólo tiene sentido mientras sean tres columnas.

       La tabla de posiciones ya no está en esta fila: se acomoda sola en
       _tarjeta_posiciones según su propio ancho.
       --------------------------------------------------------------------- */
    @media (min-width: 900px) {
        @container tarjetas (max-width: 936px) {
            .inicio-tarjeta { padding: 16px 13px; }
            .inicio-tarjeta__titulo { font-size: 10.5px; letter-spacing: .05em; margin-bottom: 11px; }

            /* Las dos o tres cifras de cabecera ya no caben lado a lado, así
               que se apilan. Se aprieta el interlineado para que apilarse no
               dispare el alto de la tarjeta. */
            .cv-totales, .ms-totales, .tc-totales { gap: 10px; }
            .cv-cifra, .ms-cifra, .tc-cifra { font-size: 19px; }
            .cv-pie, .ms-pie, .tc-pie { font-size: 10.5px; line-height: 1.3; }
            .tc-delta { font-size: 15px; }

            .cv-pastillas, .tc-pastillas { gap: 5px; margin-bottom: 11px; }
            .cv-pastilla, .tc-pastilla { font-size: 11px; padding: 6px 9px; }

            .cv-fila, .ms-fila, .tc-fila { grid-template-columns: 62px 1fr auto; gap: 6px; padding: 5px 2px; }
            .cv-sede, .ms-nombre, .tc-sede { font-size: 11.5px; }
            .cv-valor, .ms-valor, .tc-valor { font-size: 11.5px; min-width: 38px; }

            .ms-persona { gap: 6px; font-size: 11px; }
            .ms-gente { padding-left: 8px; }
            .ms-ayuda, .cv-nota, .tc-nota { font-size: 10.5px; }
        }
    }

    /* Navegadores sin soporte de @container: se decide por viewport. Queda
       algo más apretado de lo necesario cuando el menú está colapsado, que es
       el precio de no poder medir la fila. */
    @supports not (container-type: inline-size) {
        @media (min-width: 900px) and (max-width: 1245px) {
            .inicio-tarjeta { padding: 16px 13px; }
            .inicio-tarjeta__titulo { font-size: 10.5px; letter-spacing: .05em; margin-bottom: 11px; }

            /* Las dos o tres cifras de cabecera ya no caben lado a lado, así
               que se apilan. Se aprieta el interlineado para que apilarse no
               dispare el alto de la tarjeta. */
            .cv-totales, .ms-totales, .tc-totales { gap: 10px; }
            .cv-cifra, .ms-cifra, .tc-cifra { font-size: 19px; }
            .cv-pie, .ms-pie, .tc-pie { font-size: 10.5px; line-height: 1.3; }
            .tc-delta { font-size: 15px; }

            .cv-pastillas, .tc-pastillas { gap: 5px; margin-bottom: 11px; }
            .cv-pastilla, .tc-pastilla { font-size: 11px; padding: 6px 9px; }

            .cv-fila, .ms-fila, .tc-fila { grid-template-columns: 62px 1fr auto; gap: 6px; padding: 5px 2px; }
            .cv-sede, .ms-nombre, .tc-sede { font-size: 11.5px; }
            .cv-valor, .ms-valor, .tc-valor { font-size: 11.5px; min-width: 38px; }

            .ms-persona { gap: 6px; font-size: 11px; }
            .ms-gente { padding-left: 8px; }
            .ms-ayuda, .cv-nota, .tc-nota { font-size: 10.5px; }
        }
    }
</style>

<div class="inicio-tarjetas">
    @include('inicio._tarjeta_convenios')
    @include('inicio._tarjeta_mes')
    @include('inicio._tarjeta_tasa')
</div>

{{-- Abajo y a todo lo ancho: tres grupos que necesitan espacio para verse
     lado a lado. --}}
@include('inicio._tarjeta_posiciones')
