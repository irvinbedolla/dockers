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
                    setTimeout(function() {
                        $('#menu_carga').hide();

                    }, 14000);
                }
            }, false);
        });
    }, false);
})();

$(function(){
    $('#estado_solicitante').on('change', onSelectestadoChange);
})

function onSelectestadoChange(){
    //Al detectar el cambio en el select toma el valor del select con el id "estado"
    var municipio_id = $(this).val();
    $('#municipio_solicitante').prop('disabled', false);
    //Se ejecuta la consulta AJAX para buscar con el municipio_id
    $.get('../api/munSolicitante/'+municipio_id, function (data){
        var html_select = '<option value="">--Seleccione un estado --</option>';        
        for(var i=0; i<data.length; ++i)
            html_select += '<option value= "'+data[i].id+'">'+data[i].nombre+'</option>';
            $('#municipio_solicitante').html(html_select);
    });
}

$(function(){
    $('#estado_citado').on('change', onSelectestadoChange1);
})

function onSelectestadoChange1(){
    //Al detectar el cambio en el select toma el valor del select con el id "estado"
    var municipio_id = $(this).val();
    $('#municipio_citado').prop('disabled', false);
    //Se ejecuta la consulta AJAX para buscar con el municipio_id
    $.get('../api/munCitado/'+municipio_id, function (data){
        var html_select = '<option value="">--Seleccione un estado --</option>';        
        for(var i=0; i<data.length; ++i)
            html_select += '<option value= "'+data[i].id+'">'+data[i].nombre+'</option>';
            $('#municipio_citado').html(html_select);
        });
}

function nuevo_estadistica() {
    $('#menu_carga').show();
}

function consultar_estadistica() {
    $('#menu_carga').show();
}

function openCity(evt, cityName) {
    // Declare all variables
    var i, tabcontent, tablinks;
  
    // Get all elements with class="tabcontent" and hide them
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
      tabcontent[i].style.display = "none";
    }
  
    // Get all elements with class="tablinks" and remove the class "active"
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
      tablinks[i].className = tablinks[i].className.replace(" active", "");
    }
  
    // Show the current tab, and add an "active" class to the button that opened the tab
    document.getElementById(cityName).style.display = "block";
    evt.currentTarget.className += " active";
}
/*
$('#botonVerCitado').click(function (){
    console.log("si llego");
});
*/

function botonVerCitado(id){
    $.get('../api/citados/'+id, function (data){
        var html = '';
        
        for(var i=0; i<data.length; ++i){
            html += '<tr>';
                html += '<td>';
                html += ''+data[i].nombre+'';
                html += '</td>';
                html += '<td>';
                html += ''+data[i].direccion+'';
                html += '</td>';
            html += '</tr>';
        }

        $('#m_citados').html(html);
    });
    /*
    $.ajax({
        url: "{{ route('my_route')}}",
        //url: "../../sistema-integral/app/Http/Controllers/SeerController.php/buscarCitados",
        type: "POST",
        data: id,
        dataType: 'json',
        processData: false,
        contentType: false,
        success: function (datos) {
            if(datos.length > 0){
            document.getElementById("charcct").innerHTML = '<span class="font-weight-bold text-success form-text text-muted">Disponible</span>';
            }
            else{
            document.getElementById("charcct").innerHTML = '<span class="font-weight-bold text-danger form-text text-muted">No existe CCT</span>';
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR);
            console.log(textStatus);
            console.log(errorThrown);
        }
    });
    */
}


/*
function botonVerCitado(id){
    //document.getElementById("lista_usuarios").style.display='none';
    var url="../../sistema-integral/resources/views/estadisticas/prueba.blade.php";    
    $.post(url,{id:id},
    function(data){
        $('#m_citados').html(data);
    }); 
}
*/


function buscarCCT(cct){
    var formData = new FormData();
    formData.append("cct", cct);

    $.ajax({
        url: "../Movimientos/buscarDisponivilidadCCT",
        type: "POST",
        data: formData,
        dataType: 'json',
        processData: false,
        contentType: false,
        success: function (datos) {
            if(datos.length > 0){
            document.getElementById("charcct").innerHTML = '<span class="font-weight-bold text-success form-text text-muted">Disponible</span>';
            }
            else{
            document.getElementById("charcct").innerHTML = '<span class="font-weight-bold text-danger form-text text-muted">No existe CCT</span>';
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR);
            console.log(textStatus);
            console.log(errorThrown);
        }
    });
}

//Solicitud en línea trabajadores


$(function(){
    $('#ramaIndustrial').on('change', onSelectestadoChange);
})
/*
function onSelectestadoChange(){
    var economica_id = $(this).val();
    $('#actividad_economica').prop('disabled', false);
    
    $.get('./api/v1/actividadEconomica/'+economica_id, function (data){
        console.log(data);
        var html_select = '<option value="">--Seleccione una rama industrial --</option>';        
        for(var i=0; i<data.length; ++i)
            html_select += '<option value= "'+data[i].id+'">'+data[i].act_economica+'</option>';
            $('#actividad_economica').html(html_select);
    });
}
*/
