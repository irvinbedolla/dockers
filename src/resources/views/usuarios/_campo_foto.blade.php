{{--
    Campo de foto de perfil. Lo comparten administracion/editar_usuario y
    usuarios/editar, asi que cualquier arreglo aqui llega a las dos.
    Espera la variable $user.

    La validacion del navegador revisa formato, peso y medidas ANTES de enviar,
    para que nadie descubra que su foto no servia despues de recargar la pagina.
    No sustituye la del servidor, que es la que manda: esta solo evita el viaje.
--}}
<div class="col-xs-12 col-sm-12 col-md-12">
    <hr>
    <div class="form-group">
        <label for="foto_perfil">Foto de perfil</label>
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <img id="foto_perfil_vista"
                 src="{{ $user->avatar_url }}"
                 alt="{{ $user->tieneFotoDePerfil() ? 'Foto de '.$user->name : 'Sin foto de perfil' }}"
                 width="72" height="72"
                 style="border-radius: 50%; object-fit: cover; border: 2px solid #dee2e6;">

            <div class="flex-grow-1" style="min-width: 260px;">
                <input type="file" name="foto_perfil" id="foto_perfil"
                       class="form-control"
                       accept="image/jpeg,image/png,image/webp">

                <div id="foto_perfil_aviso" class="text-danger small mt-1" role="alert" hidden></div>

                <small id="foto_perfil_ayuda" class="text-muted">
                    JPG, PNG o WebP. Mínimo 200×200 píxeles, máximo 8 MB.
                </small>

                @if ($user->tieneFotoDePerfil())
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox"
                               name="quitar_foto" value="1" id="quitar_foto">
                        <label class="form-check-label" for="quitar_foto">
                            Quitar la foto actual
                        </label>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var entrada = document.getElementById('foto_perfil');
    if (!entrada || entrada.dataset.validado) {
        return;
    }
    entrada.dataset.validado = '1';

    var vista = document.getElementById('foto_perfil_vista');
    var aviso = document.getElementById('foto_perfil_aviso');
    var ayuda = document.getElementById('foto_perfil_ayuda');

    var LADO_MINIMO = 200;
    var PESO_MAXIMO = 8 * 1024 * 1024;
    var TIPOS       = ['image/jpeg', 'image/png', 'image/webp'];

    var vistaOriginal = vista ? vista.src : null;
    var ayudaOriginal = ayuda ? ayuda.textContent : '';
    var urlPrevia     = null;

    function soltarPrevia() {
        if (urlPrevia) {
            URL.revokeObjectURL(urlPrevia);
            urlPrevia = null;
        }
    }

    function restaurar() {
        soltarPrevia();
        if (vista && vistaOriginal) { vista.src = vistaOriginal; }
        if (ayuda) { ayuda.textContent = ayudaOriginal; }
    }

    // Vacia el selector: asi nadie envia por accidente un archivo ya rechazado.
    function rechazar(mensaje) {
        entrada.value = '';
        entrada.classList.add('is-invalid');
        aviso.textContent = mensaje;
        aviso.hidden = false;
        restaurar();
    }

    function limpiarAviso() {
        entrada.classList.remove('is-invalid');
        aviso.hidden = true;
        aviso.textContent = '';
    }

    function enKb(bytes) {
        return bytes < 1048576
            ? Math.round(bytes / 1024) + ' KB'
            : (bytes / 1048576).toFixed(1) + ' MB';
    }

    entrada.addEventListener('change', function () {
        limpiarAviso();

        var archivo = entrada.files && entrada.files[0];

        if (!archivo) {
            restaurar();
            return;
        }

        if (TIPOS.indexOf(archivo.type) === -1) {
            rechazar('Ese formato no sirve. La foto debe ser JPG, PNG o WebP.');
            return;
        }

        if (archivo.size > PESO_MAXIMO) {
            rechazar('La foto pesa ' + enKb(archivo.size) + ' y el máximo son 8 MB. Redúcela antes de subirla.');
            return;
        }

        function revisar(ancho, alto, url) {
            if (ancho < LADO_MINIMO || alto < LADO_MINIMO) {
                rechazar('La foto mide ' + ancho + '×' + alto + ' píxeles y el mínimo es '
                         + LADO_MINIMO + '×' + LADO_MINIMO + '. Se vería borrosa.');
                return;
            }

            if (vista) { vista.src = url; }
            if (ayuda) {
                ayuda.textContent = 'Lista: ' + ancho + '×' + alto + ' píxeles, ' + enKb(archivo.size)
                                  + '. Se recortará en cuadro a 256×256 al guardar.';
            }
        }

        function ilegible() {
            rechazar('No se pudo leer la imagen. Puede estar dañada o no ser una imagen real.');
        }

        function medir(url, alFallar) {
            var prueba = new Image();
            prueba.onload  = function () { revisar(prueba.naturalWidth, prueba.naturalHeight, url); };
            prueba.onerror = alFallar;
            prueba.src     = url;
        }

        // Primero blob:, que no copia el archivo a memoria. Si la politica de
        // seguridad del sitio no admite ese esquema, se reintenta con una data:
        // URL, mas pesada pero permitida por cualquier CSP razonable. Asi la
        // vista previa funciona aunque el encabezado todavia no se actualice.
        soltarPrevia();
        urlPrevia = URL.createObjectURL(archivo);

        medir(urlPrevia, function () {
            soltarPrevia();

            var lector = new FileReader();
            lector.onload  = function () { medir(lector.result, ilegible); };
            lector.onerror = ilegible;
            lector.readAsDataURL(archivo);
        });
    });
})();
</script>
