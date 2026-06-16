function validacionCamposInput(valor, tipoValidacion, elementoMsj, msj, aplicaVacio, msjVacio){
  console.log(`Validando campo con valor: "${valor}", tipo: "${tipoValidacion}"`);
    if(aplicaVacio === 0 && valor.trim() === ""){
      /*elementoMsj.text(msjVacio);*/
      elementoMsj.textContent = msjVacio;
      return false;
    }
  
    let patron;
    switch(tipoValidacion){
      case "soloLetras":
        patron = /^[A-ZÑÁÉÍÓÚÜ.\s]+$/; // Mayúsculas y espacios
        break;
      case "soloNumeros":
        patron = /^\d+$/;  // Solo dígitos
        break;
      case "correoElectronico":
        patron = /^\s*[\w\-\+_]+(\.[\w\-\+_]+)*@[\w\-\+_]+\.[\w\-\+_]+(\.[\w\-\+_]+)*\s*$/;
        break;
      case "numeroTelefonico":
        patron = /^[0-9]{10}$/;
        break;
      default:
        return true; // Sin validación para tipos no definidos
    }
  
    if(!patron.test(valor)){
        elementoMsj.textContent = msj;
        return false;
    }
  
    elementoMsj.textContent = '';
    return true;
}

    document.querySelectorAll('.soloLetras').forEach(input => {
        input.addEventListener('input', () => {
            input.value = input.value.replace(/[^a-zA-ZñÑáéíóúÁÉÍÓÚÜ.\s]/g, '').toUpperCase();
        });
    });
  
    document.querySelectorAll('.soloNumeros').forEach(input => {
        input.addEventListener('input', () => {
            input.value = input.value.replace(/[^0-9]/g, '');
        });
    });
    
    //Número de teléfono
    document.querySelectorAll('.numeroTelefonico').forEach(input => {
        // Permitir solo números mientras escribes
        input.addEventListener('input', () => {
          input.value = input.value.replace(/[^0-9]/g, '');
        });
      
        // Validar al perder foco
        input.addEventListener('blur', () => {
          let mensajeError = input.nextElementSibling; // asumiendo que el .invalid-feedback está justo después
          if (input.value.length !== 10) {
            mensajeError.textContent = 'El número debe contener 10 dígitos';
            mensajeError.style.display = 'block';
            input.classList.add('is-invalid');
            input.classList.remove('is-valid');
          } else {
            mensajeError.textContent = '';
            mensajeError.style.display = 'none';
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
          }
        });
      });
      
      // Validación en el submit del formulario
      document.querySelector('form.needs-validation').addEventListener('submit', function(e) {
        let valido = true;
      
        this.querySelectorAll('input.numeroTelefonico').forEach(input => {
          let msjElemento = input.nextElementSibling;
          if (input.value.length !== 10) {
            msjElemento.textContent = 'Teléfono no válido (debe tener 10 dígitos)';
            msjElemento.style.display = 'block';
            input.classList.add('is-invalid');
            input.classList.remove('is-valid');
            valido = false;
          } else {
            msjElemento.textContent = '';
            msjElemento.style.display = 'none';
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
          }
        });
      
        if (!valido) {
          e.preventDefault();
          e.stopPropagation();
        }
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
        // Reemplaza todo lo que no sea dígito ni punto por vacío
        input.value = input.value.replace(/[^0-9.]/g, '');

        // Permite solo un punto decimal (el primer punto que encuentre)
        let parts = input.value.split('.');
        if(parts.length > 2) {
        // Si hay más de un punto, elimina todos los demás
        input.value = parts[0] + '.' + parts.slice(1).join('');
        }
    });
    });

    document.querySelector('form.needs-validation').addEventListener('submit', function(e){
    let valido = true;
  
    // Para cada input con clase de validación:
    this.querySelectorAll('input').forEach(input => {
      let clase = input.className;
      let msjElemento = input.parentElement.querySelector('.invalid-feedback');
  
      if(clase.includes('soloLetras')){
        if(!validacionCamposInput(input.value, 'soloLetras', msjElemento, 'Solo letras permitidas', 0, 'Campo obligatorio')){
          input.classList.add('is-invalid');
          input.classList.remove('is-valid');
          valido = false;
        } else {
          input.classList.remove('is-invalid');
          input.classList.add('is-valid');
        }
      }
      else if(clase.includes('soloNumeros')){
        if(!validacionCamposInput(input.value, 'soloNumeros', msjElemento, 'Solo números permitidos', 0, 'Campo obligatorio')){
          input.classList.add('is-invalid');
          input.classList.remove('is-valid');
          valido = false;
        } else {
          input.classList.remove('is-invalid');
          input.classList.add('is-valid');
        }
      }
      else if(clase.includes('correoElectronico')){
        if(!validacionCamposInput(input.value, 'correoElectronico', msjElemento, 'Correo no válido', 0, 'Campo obligatorio')){
          input.classList.add('is-invalid');
          input.classList.remove('is-valid');
          valido = false;
        } else {
          input.classList.remove('is-invalid');
          input.classList.add('is-valid');
        }
      }
    });
    
    console.log('valido?', valido);
    if(!valido) e.preventDefault();
  });
  /*document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form.needs-validation');
    form.addEventListener('submit', function(e) {
        if (!validacionCamposInput()) {
            e.preventDefault();
            e.stopPropagation();
        }
        form.classList.add('was-validated');
    });
});*/
  
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
  function validarInput(input, idResultado) {
      var curp = input.value.toUpperCase(),
      resultado = document.getElementById(idResultado),
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

