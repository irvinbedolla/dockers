{{--
    Fila de tarjetas de cifras del mes: convenios pagados, montos convenidos
    y tasa de conciliación, en tres columnas fijas.

    Antes iba en el Inicio de todos los roles; ahora sólo vive en el Panel
    estadístico (estadisticas/panel, EstadisticasController). En el Inicio se
    quedó la tabla de posiciones (_tarjeta_posiciones).

    Espera $convenios, $conveniosMes y $tasaConciliacion. Para sumar otra
    tarjeta de cifras hay que decidir aquí si la fila pasa a cuatro columnas
    o si arranca una segunda fila.
--}}

@include('inicio._tarjeta_base')

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
