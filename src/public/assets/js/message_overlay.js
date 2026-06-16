function showMessageOverlay(mensaje, imagen, alto, ancho, type){
  $(".titleMessageOverlay").addClass("hide");
  $("."+type).removeClass("hide");

  $(".titleMessageOverlay").html(mensaje);
  $(".textMessageOverlay").html("<img src='"+imagen+"' width='"+ancho+"' height='"+alto+"'>");

  $(".messageOverlay").css("height", "100%");
  $(".messageOverlay").css("width", "100%");
}

function closeMessageOverlay(){
  $(".messageOverlay").css("height", "0");
  $(".messageOverlay").css("width", "0");
}