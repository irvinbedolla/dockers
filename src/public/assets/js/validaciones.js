(function () {
  'use strict';
  window.addEventListener('load', function () {
      // Fetch all the forms we want to apply custom Bootstrap validation styles to
      var forms = document.getElementsByClassName('needs-validation');
      // Loop over them and prevent submission
      var validation = Array.prototype.filter.call(forms, function (form) {
          form.addEventListener('submit', function (event) {
              if (form.checkValidity() === false ){
                  event.preventDefault();
                  event.stopPropagation();
                  form.classList.add('was-validated');
              } else {
                  $('#menu_carga').show();
              }
          }, false);
      });
  },false);
})();


function validacionCamposInput(valor, tipoValidacion, elementoMsj, msj, aplicaVacio, msjVacio){

  // console.log(tipoValidacion);
  if(aplicaVacio == 0 && valor == ""){
    $(elementoMsj).text(msjVacio);
    return false;
  }

  var patron = "sinValidacion";
  if(valor != ""){
    if(tipoValidacion == "soloLetras"){ patron = /^[a-zA-Z\ñ\Ñ\.\,\s]+$/; }
    else if(tipoValidacion == "soloLetrasSinEspacios"){ patron = /^[a-zA-Z\ñ\Ñ\.\,]+$/; }
    else if(tipoValidacion == "soloNumeros"){ patron = /^[0-9\.]\d*$/; }
    else if(tipoValidacion == "soloLetrasYNumeros"){ patron = /^[0-9a-zA-Z\ñ\Ñ\.\,\s]+$/; }
    else if(tipoValidacion == "soloLetrasYNumerosSinEspacios"){ patron = /^[0-9a-zA-Z\ñ\Ñ\.\,]+$/; }
    else if(tipoValidacion == "correoElectronico"){ patron = /^\s*[\w\-\+_]+(\.[\w\-\+_]+)*\@[\w\-\+_]+\.[\w\-\+_]+(\.[\w\-\+_]+)*\s*$/; }
    else if(tipoValidacion == "numeroTelefonico"){ patron = /^[0-9]{10}$/; }
    else if(tipoValidacion == "fecha"){ patron = /^([12]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]))$/; }
    else if(tipoValidacion == "hora"){ patron = /^([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/; }
    else if(tipoValidacion == "minutosSegundos"){ patron = /^[0-5][0-9](:[0-5][0-9])?$/; }
    else if(tipoValidacion == "rfcPersonaFisica"){ patron = /^[A-ZÑ&]{4}\d{6}[A-V1-9][A-Z1-9][0-9A]$/; }
    else if(tipoValidacion == "rfcPersonaMoral"){ patron = /^[A-ZÑ&]{3}\d{6}[A-V1-9][A-Z1-9][0-9A]$/; }
    else if(tipoValidacion == "claveInterbancaria"){ patron = /^[0-9]{18}$/; }
    else if(tipoValidacion == "numeroIccid"){ patron = /^[0-9]{19}$/; }
    else if(tipoValidacion == "numeroIccidAsignar"){ patron = /^[0-9]{18}$/; }
    else if(tipoValidacion == "numeroImsi"){ patron = /^[0-9]{15}$/; }
    else if(tipoValidacion == "numeroNIR"){ patron = /^[0-9]{2,3}$/; }
    else if(tipoValidacion == "numeroImei"){ patron = /^[0-9]{14,15}$/; }
    else if(tipoValidacion == "numeroPin"){ patron = /^[0-9]{4}$/; }
    else if(tipoValidacion == "numeroPuk"){ patron = /^[0-9\.]\d*$/; }
    else if(tipoValidacion == "importe"){ patron = /^(?:- ?)?\d+(.\d{1,2})?$/; }
    else if(tipoValidacion == "longitud"){ patron = /^[\-\+]?(0(\.\d{1,10})?|([1-9](\d)?)(\.\d{1,10})?|1[0-7]\d{1}(\.\d{1,10})?|180\.0{1,10})$/; }
    else if(tipoValidacion == "latitud"){ patron = /^[\-\+]?((0|([1-8]\d?))(\.\d{1,10})?|90(\.0{1,10})?)$/; }
    else if(tipoValidacion == "numeroCuentaBancario"){ patron = /^[0-9]{4}$/; }
    else if(tipoValidacion == "nip"){ patron = /^[0-9]{4}$/; }
    else if(tipoValidacion == "nroTarjeta"){ patron = /^[0-9]{15,16}$/; }
    else if(tipoValidacion == "anio"){ patron = /^[0-9]{4}$/; }
    else if(tipoValidacion == "mes"){ patron = /^[0-9]{2}$/; }
    else if(tipoValidacion == "cvc"){ patron = /^[0-9]{3}$/; }
    else if(tipoValidacion == "km"){ patron = /^(?:- ?)?\d+(.\d{1,10})?$/; }
    else if(tipoValidacion == "curp"){ patron = /^[A-Z]{4}\d{6}[H,M][A-Z]{5}[A-Z\d][0-9]$/;}
    
  }

  if(patron == "sinValidacion"){
    return true;
  }
  else{
    if(!valor.search(patron)){
      return true;
    }
    else{
      $(elementoMsj).text(msj);
      return false;
    }
  }
}

function validacionCamposSelect(valor, elementoMsj, aplicaVacio, msjVacio){
  if(aplicaVacio == 0 && valor == "-1"){
    $(elementoMsj).text(msjVacio);
    return false;
  }

  return true;
}

//Función para validar una CURP
function curpValida(curp) {
    var re = /^([A-Z][AEIOUX][A-Z]{2}\d{2}(?:0[1-9]|1[0-2])(?:0[1-9]|[12]\d|3[01])[HM](?:AS|B[CS]|C[CLMSH]|D[FG]|G[TR]|HG|JC|M[CNS]|N[ETL]|OC|PL|Q[TR]|S[PLR]|T[CSL]|VZ|YN|ZS)[B-DF-HJ-NP-TV-Z]{3}[A-Z\d])(\d)$/,
    validado = curp.match(re);

    if (!validado)  //Coincide con el formato general?
        return false;
  
      //Validar que coincida el dígito verificador
      function digitoVerificador(curp17) {
        var diccionario  = "0123456789ABCDEFGHIJKLMNÑOPQRSTUVWXYZ",
        lngSuma      = 0.0,
        lngDigito    = 0.0;
        for(var i=0; i<17; i++)
          lngSuma = lngSuma + diccionario.indexOf(curp17.charAt(i)) * (18 - i);
          lngDigito = 10 - lngSuma % 10;
          if (lngDigito == 10) return 0;
    return lngDigito;
  }

  if (validado[2] != digitoVerificador(validado[1])) 
    return false;   

  return true; //Validado
}


//Handler para el evento cuando cambia el input
//Lleva la CURP a mayúsculas para validarlo
function validarInput(input) {
    var curp = input.value.toUpperCase(),
    resultado = document.getElementById("resultado"),
    valido = "No válido";
    console.log("llego");
    input.value = curp;
    if (curpValida(curp)) { // Acá se comprueba
        valido = "Válido";
        resultado.classList.add("ok");
    } else {
        resultado.classList.remove("ok");
    }  
  resultado.innerText = "CURP: " + curp + "\nFormato: " + valido;
}


function validarfechaNacimiento(){
    var fechaNacimiento = document.getElementById("fecha_nacimiento").value;
    var f = new Date();
    actual = f.getFullYear() + "-"+ f.getMonth()+ "-" +f.getDate();

    años = moment(actual).diff(moment(fechaNacimiento), 'year');
    
    //document.getElementById("documentacionAdulto").style.display = "none";
    //document.getElementById("documentacionMenor").style.display = "none";
   
    //document.getElementById("años_edad").value = edad;
    //Si la fecha de nacimiento es menos a 15 años
    if(años <= 15) {
      alert("Requieres tener al menos 15 años de edad. Debes presentarte con tu tutor legal.");
    }
    if(años > 15 && años < 18){
      alert("Debes presentarte con tu tutor legal.");
      //document.getElementById("documentacionMenor").style.display = "block";
    }
    else{
      //document.getElementById("documentacionAdulto").style.display = "block";
    }
    $('#años_edad').val(años);
    
    
}

$(function(){
  $('#check_lenguaje').on('change', validarcheckseñales);
})

function validarcheckseñales(){
  var check = document.getElementById("check_lenguaje").value;

  tipo = document.getElementById("lenguaje_señas").style.display;
  if (tipo == "none") {
    document.getElementById("lenguaje_señas").style.display = "block";
  }
  else{
    document.getElementById("lenguaje_señas").style.display = "none";
  }
}

$(function(){
  $('#check_discapacidad').on('change', validarcheckdiscapacidad);
})

function validarcheckdiscapacidad(){
  var check = document.getElementById("check_discapacidad").value;

  tipo = document.getElementById("discapacidad").style.display;
  if (tipo == "none") {
    document.getElementById("discapacidad").style.display = "block";
  }
  else{
    document.getElementById("discapacidad").style.display = "none";
  }

}

$(function(){
  $('#check_fecha').on('change', validarcheckfecha);
})

function validarcheckfecha(){
  var check = document.getElementById("fecha_fin").value;

  tipo = document.getElementById("fecha_fin").style.display;
  if (tipo == "none") {
    document.getElementById("fecha_fin").style.display = "block";
  }
  else{
    document.getElementById("fecha_fin").style.display = "none";
  }
}

//Valida que solo se ingresen números
document.querySelectorAll('.soloNumeros').forEach(input => {
  input.addEventListener('input', () => {
      input.value = input.value.replace(/[^0-9]/g, '');
  });
});

//Valida los números teléfonicos
document.querySelectorAll('.numeroTelefonico').forEach(input => {
  const errorNode = (input.nextElementSibling && input.nextElementSibling.classList.contains('invalid-feedback'))
    ? input.nextElementSibling
    : null;

  // Permitir solo números mientras escribes
  input.addEventListener('input', () => {
    input.value = input.value.replace(/[^0-9]/g, '');
    if (errorNode) {
      errorNode.textContent = '';
      errorNode.style.display = 'none';
    }
    input.classList.remove('is-invalid');
    input.classList.remove('is-valid');
  });

  // Validar al perder foco (solo si se marca data-exact-10="true")
  input.addEventListener('blur', () => {
    const requiresExact10 = input.dataset.exact10 === 'true';
    if (!requiresExact10) {
      return;
    }

    if (input.value.length !== 10) {
      if (errorNode) {
        errorNode.textContent = 'El número debe contener 10 dígitos';
        errorNode.style.display = 'block';
      }
      input.classList.add('is-invalid');
      input.classList.remove('is-valid');
    } else {
      if (errorNode) {
        errorNode.textContent = '';
        errorNode.style.display = 'none';
      }
      input.classList.remove('is-invalid');
      input.classList.add('is-valid');
    }
  });
});

//Validación en tiempo real de correo electronico
document.querySelectorAll('.correoElectronico').forEach(input => {
  input.addEventListener('input', () => {
    // Permitir solo caracteres válidos en un correo (letras, números, @, puntos, guiones, guion bajo)
    input.value = input.value.replace(/[^a-zA-Z0-9@._\-]/g, '');

    // Validar formato básico de correo
    const valor = input.value;
    const mensajeError = input.nextElementSibling; // Asumiendo que el div .invalid-feedback está justo después

    // Regex básico para formato de correo
    const correoValido = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (valor === '') {
      mensajeError.style.display = 'none'; // No mostrar error si está vacío
      input.classList.remove('is-invalid');
      input.classList.remove('is-valid');
    } else if (!correoValido.test(valor)) {
      mensajeError.style.display = 'block'; // Mostrar error
      mensajeError.textContent = 'Debe ingresar un correo válido';
      input.classList.add('is-invalid');
      input.classList.remove('is-valid');
    } else {
      mensajeError.style.display = 'none'; // Ocultar error si es válido
      input.classList.remove('is-invalid');
      input.classList.add('is-valid');
    }
  });
});      

//Validación para los montos
document.querySelectorAll('.soloMontos').forEach(input => {
input.addEventListener('input', () => {
  //Normalizamos coma a punto para evitar problemas de teclado
  let value = input.value.replace(/,/g, '.');

  //Permite solo dígitos y punto
  value = value.replace(/[^0-9.]/g, '');

  //Permite solo un punto decimal (el primero)
  const firstDot = value.indexOf('.');
  if (firstDot !== -1) {
    const intPart = value.slice(0, firstDot);
    let decPart = value.slice(firstDot + 1).replace(/\./g, '');

    //Limita a máximo 3 decimales
    decPart = decPart.slice(0, 3);
    value = intPart + '.' + decPart;
  }

  //Si empieza con punto, lo convertimos a 0.
  if (value.startsWith('.')) {
    value = '0' + value;
  }

  input.value = value;
});
});


//Inicializacion variables
const elemento = document.getElementById("lenguaje_señas");
if (elemento) {
  // Si existe, inicializa su estilo para ocultarlo
  elemento.style.display = "none";
}
const elemento1 = document.getElementById("discapacidad");
if (elemento) {
  // Si existe, inicializa su estilo para ocultarlo
  elemento1.style.display = "none";
}
const elemento2 = document.getElementById("fecha_nacimiento");
if (elemento2) {
  fecha_nacimiento.max = new Date().toISOString().split("T")[0];
}
