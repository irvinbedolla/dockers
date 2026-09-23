{{--
    Fila de tarjetas de estadísticas del Inicio.

    Vive aparte del saludo y de la bifurcación por rol porque la ven todos:
    Directivo, Conciliador, Auxiliar y demás.

    Para sumar una tarjeta basta con un @include más abajo, pero la rejilla ya
    no se acomoda sola: son cuatro columnas fijas. Si se agrega una quinta hay
    que decidir aquí si la fila pasa a cinco o si arranca una segunda fila.
--}}

<style>
    .inicio-tarjetas {
        display: grid;
        /* Cuatro columnas fijas y no auto-fill. Con auto-fill, en cuanto la
           fila dejaba de dar para cuatro se iban a tres y la cuarta caía sola
           en el renglón de abajo, que es justo lo que no se quiere. Así las
           cuatro se angostan juntas y siempre se ven de un vistazo. */
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 18px;
        margin-bottom: 18px;
        align-items: start;
        /* La rejilla se mide a sí misma para que las tarjetas puedan
           apretarse según el ancho que de verdad les tocó. El viewport no
           sirve para eso: el menú lateral se colapsa de 250px a 78px sin que
           la pantalla cambie de tamaño, y son 172px que cambian el reparto. */
        container-type: inline-size;
    }

    /* 1024px es el corte que ya usa el layout para esconder el menú lateral.
       De ahí para abajo es tableta: dos y dos. */
    @media (max-width: 1024px) {
        .inicio-tarjetas { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    @media (max-width: 575.98px) {
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

       En cuatro columnas, una fila de 1254px deja tarjetas de 300px, y de ahí
       para abajo las rejillas internas -96px de nombre, 52px de valor y la
       barra en medio- dejan de caber y la barra se queda sin espacio. En una
       pantalla de 1366px con el menú abierto la tarjeta mide 250px, así que
       este caso es la norma en las máquinas del Centro, no la excepción.

       Las reglas viven aquí y no en cada tarjeta porque el disparador es el
       ancho de la fila, que es cosa de la fila. Va envuelto en el media query
       porque el umbral sólo tiene sentido mientras sean cuatro columnas: en
       tableta la misma fila de 1000px da tarjetas de 490px.
       --------------------------------------------------------------------- */
    @media (min-width: 1025px) {
        @container (max-width: 1254px) {
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

            /* El primer lugar deja de ser una línea de tres columnas: a este
               ancho el nombre se partía en cuatro renglones y la cifra se le
               encimaba. Avatar y nombre arriba, la cifra abajo con todo el
               ancho para ella. */
            .tp-lider {
                grid-template-columns: auto 1fr;
                grid-template-areas: "foto texto" "valor valor";
                gap: 8px 9px;
                padding: 9px 10px;
            }
            .tp-lider > .tp-avatar--grande { grid-area: foto; }
            .tp-lider__texto { grid-area: texto; }
            .tp-lider__valor {
                grid-area: valor;
                display: flex;
                align-items: baseline;
                gap: 5px;
                font-size: 20px;
                text-align: left;
            }
            .tp-lider__valor small { margin-top: 0; }

            .tp-lider__nombre { font-size: 12.5px; }
            .tp-lider__pie { font-size: 10.5px; }
            .tp-avatar--grande { width: 40px; height: 40px; }
            .tp-avatar--grande .tp-iniciales { font-size: 14px; }
            .tp-medalla { min-width: 17px; height: 17px; line-height: 17px; font-size: 10px; }

            .tp-grupo__titulo { font-size: 10px; margin-bottom: 8px; }
            .tp-fila { grid-template-columns: 13px auto 1fr auto; gap: 7px; padding: 4px 8px; }
            .tp-nombre { font-size: 11.5px; }
            .tp-valor { font-size: 12px; min-width: 24px; }
        }
    }

    /* Navegadores sin soporte de @container: se decide por viewport. Queda
       algo más apretado de lo necesario cuando el menú está colapsado, que es
       el precio de no poder medir la fila. */
    @supports not (container-type: inline-size) {
        @media (min-width: 1025px) and (max-width: 1563px) {
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

            /* El primer lugar deja de ser una línea de tres columnas: a este
               ancho el nombre se partía en cuatro renglones y la cifra se le
               encimaba. Avatar y nombre arriba, la cifra abajo con todo el
               ancho para ella. */
            .tp-lider {
                grid-template-columns: auto 1fr;
                grid-template-areas: "foto texto" "valor valor";
                gap: 8px 9px;
                padding: 9px 10px;
            }
            .tp-lider > .tp-avatar--grande { grid-area: foto; }
            .tp-lider__texto { grid-area: texto; }
            .tp-lider__valor {
                grid-area: valor;
                display: flex;
                align-items: baseline;
                gap: 5px;
                font-size: 20px;
                text-align: left;
            }
            .tp-lider__valor small { margin-top: 0; }

            .tp-lider__nombre { font-size: 12.5px; }
            .tp-lider__pie { font-size: 10.5px; }
            .tp-avatar--grande { width: 40px; height: 40px; }
            .tp-avatar--grande .tp-iniciales { font-size: 14px; }
            .tp-medalla { min-width: 17px; height: 17px; line-height: 17px; font-size: 10px; }

            .tp-grupo__titulo { font-size: 10px; margin-bottom: 8px; }
            .tp-fila { grid-template-columns: 13px auto 1fr auto; gap: 7px; padding: 4px 8px; }
            .tp-nombre { font-size: 11.5px; }
            .tp-valor { font-size: 12px; min-width: 24px; }
        }
    }
</style>

<div class="inicio-tarjetas">
    @include('inicio._tarjeta_convenios')
    @include('inicio._tarjeta_mes')
    @include('inicio._tarjeta_tasa')

    {{-- La tabla de posiciones va al final: cierra la fila por la derecha. --}}
    @include('inicio._tarjeta_posiciones')
</div>
