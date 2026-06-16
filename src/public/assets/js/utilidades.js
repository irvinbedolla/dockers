function soloNumeros(e){
  tecla = (document.all) ? e.keyCode : e.which;
  if(tecla==8) return true;
  if(tecla==48) return true;
  if(tecla==49) return true;
  if(tecla==50) return true;
  if(tecla==51) return true;
  if(tecla==52) return true;
  if(tecla==53) return true;
  if(tecla==54) return true;
  if(tecla==55) return true;
  if(tecla==56) return true;
  if(tecla==57) return true;
  patron = /1/;
  te     = String.fromCharCode(tecla);

  return patron.test(te);
}