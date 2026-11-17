{{--
    Estilos de las láminas del Reporte estadístico. Los usan la página, la
    imagen y el PDF, así que sólo hay CSS que dompdf entiende: posiciones
    absolutas y tablas (nada de flex ni grid). Cada lámina mide 960 × 540
    (16:9, como la presentación). $fuente: ruta de la fuente según el modo.
--}}
@php
    $fuente = fn ($f) => $modo === 'pdf'
        ? public_path('assets/reporte_estadistico/fonts/'.$f)
        : asset('assets/reporte_estadistico/fonts/'.$f);
@endphp
<style>
    @font-face { font-family: 'ReMontserrat'; font-weight: normal; src: url('{{ $fuente('Montserrat-Regular.ttf') }}') format('truetype'); }
    @font-face { font-family: 'ReMontserrat'; font-weight: bold;   src: url('{{ $fuente('Montserrat-Bold.ttf') }}') format('truetype'); }
    @font-face { font-family: 'ReOpenSans';   font-weight: normal; src: url('{{ $fuente('OpenSans-Regular.ttf') }}') format('truetype'); }
    @font-face { font-family: 'ReOpenSans';   font-weight: bold;   src: url('{{ $fuente('OpenSans-Bold.ttf') }}') format('truetype'); }

    .re-lam {
        position: relative; width: 960px; height: 540px; overflow: hidden;
        background: #FFFFFF; color: #1F1F1F;
        font-family: 'ReMontserrat', Arial, sans-serif; font-size: 12px; line-height: 1.35;
    }
    .re-lam * { box-sizing: border-box; }

    /* Barras grises de los costados */
    .re-b { position: absolute; }
    .re-b1 { left: 0;     top: 0;    width: 26px; height: 486px; background: #616161; }
    .re-b2 { left: 26px;  top: 0;    width: 24px; height: 392px; background: #9A9A9A; }
    .re-b3 { left: 915px; top: 148px; width: 24px; height: 392px; background: #9A9A9A; }
    .re-b4 { left: 939px; top: 82px; width: 21px; height: 458px; background: #777777; }
    .re-lam--portada .re-b1 { width: 41px; }
    .re-lam--portada .re-b2 { left: 41px; width: 32px; }
    .re-lam--portada .re-b3 { left: 894px; width: 29px; }
    .re-lam--portada .re-b4 { left: 923px; width: 37px; }

    .re-logo { position: absolute; left: 811px; top: 19px; width: 132px; height: 44px; }
    .re-pie  { position: absolute; left: 430px; top: 472px; width: 100px; height: 50px; }

    .re-titulo {
        position: absolute; left: 90px; width: 780px; text-align: center;
        font-size: 18px; line-height: 1.2; color: #111;
    }
    .re-sub { font-size: 12px; color: #5E6E6F; }

    /* Tablas */
    .re-tabla { position: absolute; border-collapse: collapse; }
    .re-tabla td, .re-tabla th {
        border: 1px solid #D9D9D9; text-align: center; vertical-align: middle;
        padding: 3px 4px; overflow: hidden;
    }
    .re-h24 td, .re-h24 th { height: 24px; } .re-h26 td, .re-h26 th { height: 26px; }
    .re-h42 td, .re-h42 th { height: 42px; } .re-h44 td, .re-h44 th { height: 44px; }
    .re-h46 td, .re-h46 th { height: 46px; } .re-h47 td, .re-h47 th { height: 47px; }
    .re-h48 td, .re-h48 th { height: 48px; } .re-h50 td, .re-h50 th { height: 50px; }
    .re-h53 td, .re-h53 th { height: 53px; } .re-h54 td, .re-h54 th { height: 54px; }
    .re-h56 td, .re-h56 th { height: 56px; } .re-h58 td, .re-h58 th { height: 58px; }
    .re-h61 td, .re-h61 th { height: 61px; }
    .re-tabla tr.re-chica td { font-size: 10px; padding: 0 3px; line-height: 1.1; }
    .re-tabla tr.re-cabeza-chica th { font-size: 9.5px !important; padding: 2px 3px; line-height: 1.2; }
    .re-tabla .re-banda th {
        background: #4A6163; color: #FFFFFF; font-weight: normal; font-size: 14px;
        line-height: 1.3; border-color: #4A6163;
    }
    .re-tabla .re-cols th {
        background: #F1F1F1; color: #222; font-weight: normal; font-size: 10px; line-height: 1.3; padding: 2px;
    }
    .re-tabla td { font-family: 'ReOpenSans', Arial, sans-serif; font-size: 11.5px; color: #111; }
    .re-tabla td.re-nombre { font-weight: bold; }
    .re-tabla tr.re-total td { font-weight: bold; }
    .re-tabla--oscura th { background: #4A6163 !important; color: #fff !important; border-color: #3B4E50 !important; font-size: 12px !important; }
    .re-tabla--oscura td { border-color: #333; }
    .re-tabla tr.re-destacado td { background: #FFD966; font-weight: bold; }

    /* Gráficas */
    .re-marco-graf { position: absolute; border: 1px solid #222; background: #fff; }
    .re-marco-graf--suave { border-color: #D0D0D0; }
    .re-graf { position: absolute; }
    .re-graf img, .re-graf svg { display: block; width: 100%; height: 100%; }
    .re-graf-titulo { position: absolute; width: 100%; top: 10px; text-align: center; font-family: Georgia, 'Times New Roman', serif; font-size: 14px; color: #222; }

    .re-texto { position: absolute; font-size: 11.5px; line-height: 1.5; color: #222; text-align: justify; }
    .re-texto ul { margin: 0; padding-left: 16px; }
    .re-texto li { margin: 0 0 2px; }
    .re-centro { text-align: center; }

    /* Mapa */
    .re-mapa { position: absolute; left: 50px; top: 28px; width: 890px; height: 501px; }
    .re-mapa-dato { position: absolute; font-size: 8.5px; line-height: 1.25; color: #111; white-space: nowrap; }
    .re-mapa-dato b { font-weight: bold; }

    .re-vacio { position: absolute; left: 150px; width: 660px; text-align: center; font-size: 12px; color: #8A9899; }
</style>
