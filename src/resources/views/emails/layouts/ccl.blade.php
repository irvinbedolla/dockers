{{--
    Diseño base de los correos del CCL (citas). Hecho con tablas y estilos en
    línea porque Outlook de escritorio usa el motor de Word: no entiende flex,
    grid, variables CSS ni márgenes en <div>. Gmail quita el <style> en algunas
    vistas, por eso lo importante también va en línea. Ancho fijo de 600 px,
    que en celular se ajusta con las reglas @media.

    Secciones: titulo, preheader (texto gris que se ve en la bandeja),
    hero_titulo, hero_texto y contenido.
--}}
<!DOCTYPE html>
<html lang="es" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="x-apple-disable-message-reformatting">
    <meta name="color-scheme" content="light only">
    <meta name="supported-color-schemes" content="light only">
    <title>@yield('titulo')</title>
    <!--[if mso]>
    <noscript><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript>
    <style>table, td, p, a, span, div { font-family: Arial, Helvetica, sans-serif !important; }</style>
    <![endif]-->
    <style>
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-collapse: collapse; }
        img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }
        body { margin: 0 !important; padding: 0 !important; width: 100% !important; }
        a { color: #496163; }
        a[x-apple-data-detectors] { color: inherit !important; text-decoration: none !important; }
        @media screen and (max-width: 620px) {
            .contenedor { width: 100% !important; }
            .px { padding-left: 20px !important; padding-right: 20px !important; }
            .col { display: block !important; width: 100% !important; text-align: left !important; }
            .col-espacio { display: block !important; width: 100% !important; height: 10px !important; }
            .contenedor-botones { width: 100% !important; }
            .boton, .boton a { display: block !important; width: 100% !important; box-sizing: border-box; text-align: center !important; }
            .folio { font-size: 26px !important; }
            .hero-titulo { font-size: 22px !important; }
            .etiqueta { width: 90px !important; }
            .col img { margin: 0 auto; }
        }
    </style>
</head>
<body style="margin:0; padding:0; background-color:#EEF2F2;">
    {{-- Preheader: lo que se lee junto al asunto en la bandeja. --}}
    <div style="display:none; max-height:0; overflow:hidden; mso-hide:all; font-size:1px; line-height:1px; color:#EEF2F2; opacity:0;">
        @yield('preheader')&#8199;&#65279;&#847;&#8199;&#65279;&#847;&#8199;&#65279;&#847;&#8199;&#65279;&#847;&#8199;&#65279;&#847;&#8199;&#65279;&#847;
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#EEF2F2" style="background-color:#EEF2F2;">
        <tr>
            <td align="center" style="padding:24px 12px;">
                <!--[if mso]><table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" align="center"><tr><td><![endif]-->
                <table role="presentation" class="contenedor" width="600" cellpadding="0" cellspacing="0" border="0" bgcolor="#FFFFFF"
                       style="width:600px; max-width:600px; background-color:#FFFFFF; border-radius:12px;">

                    {{-- Encabezado con los logos oficiales --}}
                    <tr>
                        <td align="center" style="padding:24px 32px 20px; border-bottom:4px solid #CEA845; border-radius:12px 12px 0 0;">
                            <img src="{{ isset($message) ? $message->embed(public_path('assets/images/correo/logo-ccl-correo.png')) : asset('assets/images/correo/logo-ccl-correo.png') }}"
                                 width="260" alt="Centro de Conciliación Laboral del Estado de Michoacán · Michoacán es Mejor"
                                 style="display:block; width:260px; max-width:100%; height:auto; font-family:Arial, Helvetica, sans-serif; font-size:14px; color:#496163;">
                        </td>
                    </tr>

                    {{-- Banda principal --}}
                    <tr>
                        <td class="px" bgcolor="#496163" style="background-color:#496163; padding:28px 32px;">
                            <h1 class="hero-titulo" style="margin:0; font-family:Arial, Helvetica, sans-serif; font-size:24px; line-height:30px; font-weight:bold; color:#FFFFFF;">
                                @yield('hero_titulo')
                            </h1>
                            <p style="margin:8px 0 0; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:22px; color:#DCE4E4;">
                                @yield('hero_texto')
                            </p>
                        </td>
                    </tr>

                    @yield('contenido')

                    @include('emails.partials._aviso_gratuito')

                    {{-- Pie --}}
                    <tr>
                        <td class="px" bgcolor="#F5F7F7" style="background-color:#F5F7F7; padding:20px 32px 24px; border-top:1px solid #E3E8E8; border-radius:0 0 12px 12px;">
                            <p style="margin:0; font-family:Arial, Helvetica, sans-serif; font-size:13px; line-height:19px; color:#5E6E6F; font-weight:bold;">
                                Centro de Conciliación Laboral del Estado de Michoacán de Ocampo
                            </p>
                            <p style="margin:6px 0 0; font-family:Arial, Helvetica, sans-serif; font-size:12px; line-height:18px; color:#8A9899;">
                                Este correo se envió automáticamente desde SICONCILIO; por favor no respondas a este mensaje.
                                Tus datos se tratan conforme a la Ley General de Protección de Datos Personales en Posesión de Sujetos Obligados.
                            </p>
                        </td>
                    </tr>
                </table>
                <!--[if mso]></td></tr></table><![endif]-->
            </td>
        </tr>
    </table>
</body>
</html>
