{{--
    CURP y domicilio en el formulario de cita (público y ventanilla), para el
    prellenado de la solicitud (App\Support\FlujoCita::camposExtra decide si
    se muestran). La CURP se valida igual que App\Support\Curp: formato,
    fecha real y dígito verificador. Con una CURP válida se llenan solos la
    edad y el sexo si estaban vacíos.

    Espera $ventanilla.
--}}
<div class="col-12"><hr class="my-1" style="border-color:#E3E8E8;"></div>
<div class="col-md-6">
    <label class="form-label" for="gcCurp">CURP <span class="gc-req">*</span></label>
    <input type="text" id="gcCurp" name="curp" class="form-control gc-mayus" maxlength="18" autocomplete="off"
           placeholder="18 letras y números" value="{{ old('curp') }}"
           oninput="this.value = this.value.toUpperCase().replace(/\s/g, '');" required>
    <div class="form-text">
        {{ $ventanilla ? 'Con ella se adelanta el registro de su solicitud.' : 'Con ella adelantamos el registro de tu solicitud y te atendemos más rápido.' }}
        <a href="https://www.gob.mx/curp/" target="_blank" rel="noopener">Consultar CURP</a>
    </div>
    <div class="invalid-feedback" id="gcCurpError">Revisa la CURP.</div>
</div>
<div class="col-12">
    <span class="form-label d-block mb-1" style="font-weight:600;">{{ $ventanilla ? 'Domicilio de la persona' : 'Tu domicilio' }}</span>
</div>
<div class="col-md-6">
    <label class="form-label" for="gcCalle">Calle <span class="gc-req">*</span></label>
    <input type="text" id="gcCalle" name="domicilio_calle" class="form-control gc-mayus" maxlength="60" autocomplete="address-line1"
           value="{{ old('domicilio_calle') }}" oninput="this.value = this.value.toUpperCase();" required>
    <div class="invalid-feedback">Escribe la calle.</div>
</div>
<div class="col-6 col-md-3">
    <label class="form-label" for="gcNumExt">Número <span class="gc-req">*</span></label>
    <input type="text" id="gcNumExt" name="domicilio_numero" class="form-control gc-mayus" maxlength="15"
           value="{{ old('domicilio_numero') }}" oninput="this.value = this.value.toUpperCase();" required>
    <div class="invalid-feedback">Escribe el número.</div>
</div>
<div class="col-6 col-md-3">
    <label class="form-label" for="gcNumInt">Interior</label>
    <input type="text" id="gcNumInt" name="domicilio_interior" class="form-control gc-mayus" maxlength="15"
           value="{{ old('domicilio_interior') }}" oninput="this.value = this.value.toUpperCase();">
</div>
<div class="col-md-6">
    <label class="form-label" for="gcColonia">Colonia <span class="gc-req">*</span></label>
    <input type="text" id="gcColonia" name="domicilio_colonia" class="form-control gc-mayus" maxlength="60"
           value="{{ old('domicilio_colonia') }}" oninput="this.value = this.value.toUpperCase();" required>
    <div class="invalid-feedback">Escribe la colonia.</div>
</div>
<div class="col-6 col-md-3">
    <label class="form-label" for="gcCp">Código postal <span class="gc-req">*</span></label>
    <input type="text" id="gcCp" name="domicilio_cp" class="form-control" inputmode="numeric" maxlength="5" pattern="[0-9]{5}"
           value="{{ old('domicilio_cp') }}" oninput="this.value = this.value.replace(/\D/g, '').slice(0, 5);" required>
    <div class="invalid-feedback">5 dígitos.</div>
</div>

{{-- En línea y no en @push: la página pública no usa el layout del sistema. --}}
<script>
(function () {
    var campo = document.getElementById('gcCurp');
    if (!campo) { return; }
    var DIC = '0123456789ABCDEFGHIJKLMNÑOPQRSTUVWXYZ';
    var RE = /^[A-Z][AEIOUX][A-Z]{2}\d{2}(0[1-9]|1[0-2])(0[1-9]|[12]\d|3[01])[HMX](AS|BC|BS|CC|CL|CM|CS|CH|DF|DG|GT|GR|HG|JC|MC|MN|MS|NT|NL|OC|PL|QT|QR|SP|SL|SR|TC|TS|TL|VZ|YN|ZS|NE)[B-DF-HJ-NP-TV-Z]{3}[A-Z\d]\d$/;

    // Misma regla que App\Support\Curp::error.
    function error(c) {
        if (c.length !== 18) { return 'La CURP debe tener 18 caracteres.'; }
        if (!RE.test(c)) { return 'La CURP no tiene un formato válido. Revísala letra por letra.'; }
        var anio = (/\d/.test(c[16]) ? 1900 : 2000) + parseInt(c.substr(4, 2), 10);
        var mes = parseInt(c.substr(6, 2), 10), dia = parseInt(c.substr(8, 2), 10);
        var f = new Date(anio, mes - 1, dia);
        if (f.getFullYear() !== anio || f.getMonth() !== mes - 1 || f.getDate() !== dia) { return 'La fecha de nacimiento de la CURP no existe.'; }
        var suma = 0;
        for (var i = 0; i < 17; i++) { suma += DIC.indexOf(c[i]) * (18 - i); }
        if ((10 - (suma % 10)) % 10 !== parseInt(c[17], 10)) { return 'La CURP no es válida (el último dígito no corresponde). Revisa que esté bien escrita.'; }
        return '';
    }

    function revisar() {
        var c = campo.value.trim();
        var msg = c ? error(c) : 'Escribe la CURP.';
        campo.setCustomValidity(msg);
        document.getElementById('gcCurpError').textContent = msg || 'Revisa la CURP.';
        if (!msg) {
            // Edad y sexo desde la CURP, sólo si están vacíos.
            var edad = document.getElementById('gcEdad'), sexo = document.getElementById('gcSexo');
            var anio = (/\d/.test(c[16]) ? 1900 : 2000) + parseInt(c.substr(4, 2), 10);
            var nac = new Date(anio, parseInt(c.substr(6, 2), 10) - 1, parseInt(c.substr(8, 2), 10));
            var hoy = new Date(), años = hoy.getFullYear() - nac.getFullYear();
            if (hoy.getMonth() < nac.getMonth() || (hoy.getMonth() === nac.getMonth() && hoy.getDate() < nac.getDate())) { años--; }
            if (edad && !edad.value) { edad.value = años; }
            if (sexo && !sexo.value && (c[10] === 'H' || c[10] === 'M')) { sexo.value = c[10]; }
        }
    }
    campo.addEventListener('input', revisar);
    campo.addEventListener('blur', function () { campo.classList.toggle('is-invalid', !!campo.validationMessage); });
    revisar();
})();
</script>
