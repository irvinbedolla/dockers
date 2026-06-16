<?php
require_once('bdd.php');
include 'conexion.php';

session_start();
$isAdmin = (isset($_SESSION['sess_tipo']) && $_SESSION['sess_tipo'] == '0') ? true : false;
$id_login=$_SESSION['sess_id'];
$tipo_seccion=$_SESSION['sess_tipo'];

$sql_usuario="SELECT * from usuarios WHERE user_id=$id_login";
$result_usuario = mysqli_query($con, $sql_usuario);
while($row_usuario = mysqli_fetch_assoc($result_usuario)) {
    $sucursal = $row_usuario["sucursal"];
}

if($tipo_seccion == 0){
	$sql = "SELECT id, title, start, end, color FROM events ";	
}
else if($tipo_seccion == 1){
	$sql = "SELECT id, title, start, end, color FROM events where sucursal ='$sucursal'";
}
else{
	$sql = "SELECT id, title, start, end, color FROM events where id_usuario = $id_login";
}

$req = $bdd->prepare($sql);
$req->execute();
$events = $req->fetchAll();

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Inicio</title>

    <!-- Bootstrap Core CSS -->
    <link href="fullcalendar/bootstrap.min.css" rel="stylesheet">
	
	<!-- FullCalendar -->
	<link href='fullcalendar/fullcalendar.css' rel='stylesheet' />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/css/bootstrap-datetimepicker.min.css" integrity="sha512-WWc9iSr5tHo+AliwUnAQN1RfGK9AnpiOFbmboA0A0VJeooe69YR2rLgHw13KxF1bOSLmke+SNnLWxmZd8RTESQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
	<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/js/bootstrap-datetimepicker.min.js" integrity="sha512-Y+0b10RbVUTf3Mi0EgJue0FoheNzentTMMIE2OreNbqnUPNbQj8zmjK3fs5D2WhQeGWIem2G2UkKjAL/bJ/UXQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
	<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
	<script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>

    <!-- Custom CSS -->
    <style>
    body {
        padding-top: 70px;
        
    }
	#calendar {
		max-width: 800px;
	}
	.col-centered{
		float: none;
		margin: 0 auto;
	}
    </style>



</head>

<body>

    <!-- Navigation -->
    <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
        <div class="container">
            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav">
                    <li>
                        <a href="index.php">Regresar</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Content -->
    <div class="container">

        <div class="row">
            <div class="col-lg-12 text-center">
                <h1>Agenda pies felices</h1>
                <p class="lead">Registrar tus citas.</p>

		        <button type="button" class="btn btn-primary" id="btnNuevoEvento">
				    <i class="fas fa-plus"></i> Agendar Nueva Audiencia
				</button>
        
                <div id="calendar" class="col-centered">
                </div>
            </div>
			
        </div>
        <!-- /.row -->
		
		<!-- Modal -->
		<div class="modal fade" id="ModalAdd" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
		  	<div class="modal-dialog" role="document">
				<div class="modal-content">
					<form class="form-horizontal" method="POST" action="addEvent.php">
			  			<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<h4 class="modal-title" id="myModalLabel">Agregar Cita</h4>
						</div>
			  			<div class="modal-body">
					  		<div class="form-group">
								<label for="title" class="col-sm-2 control-label">Titulo</label>
								<div class="col-sm-10">
							  		<input type="text" name="title" class="form-control" id="title" placeholder="Titulo">
								</div>
					  		</div>
					  		<div class="form-group">
								<label for="color" class="col-sm-2 control-label">Doctor</label>
								<div class="col-sm-10">
								  	<select name="doctor" class="form-control" id="color">
										<?php
			                            $sql="SELECT user_id,username from usuarios where usuario_doctor = 1 order by username"; 
			                            $result = mysqli_query($con, $sql);
			                            while($row = mysqli_fetch_assoc($result)) { ?>
			                            	<option value="<?php echo $row["user_id"] ?>"> <?php echo $row["username"]; ?> </option>
			                            <?php }
			                            ?>
		                            </select>
								</div>
					  		</div>
					  		<div class="form-group">
					  			<label for="title" class="col-sm-2 control-label">Cliente</label>
								<div class="col-sm-10">
						  			<select name="cliente" class="form-control">
							  			<?php
			                            $sql="SELECT cliente_id,nombre from clientes order by nombre"; 
			                            $result = mysqli_query($con, $sql);
			                            while($row = mysqli_fetch_assoc($result)) { ?>
			                            	<option value="<?php echo $row["cliente_id"] ?>"> <?php echo $row["nombre"]; ?> </option>
			                            <?php }
			                            ?>
		                            </select>
		                        </div>
					  		</div>
					  		<div class="form-group">
					  			<label for="title" class="col-sm-2 control-label">Sucursal</label>
								<div class="col-sm-10">
						  			<select name="sucursal" class="form-control">
							  			<?php
		                                if($tipo_seccion==0 || $tipo_seccion==3){
		                                    $query = "SELECT * FROM sucursal order by nombre_sucursal";
		                                }
		                                else{
		                                    $query = "SELECT * FROM sucursal WHERE tipo_seccion = 1 order by nombre_sucursal";
		                                }
		                                $rs1 = mysqli_query($con, $query);
		                                while($row = mysqli_fetch_assoc($rs1)){ 
		                                ?>
		                                    <option value="<?=$row["id_sucursal"]?>"><?=$row["nombre_sucursal"]?></option>
		                                <?php
		                                }   
			                            ?>
		                            </select>
		                        </div>
					  		</div>
					  		<div class="form-group">
								<label for="start" class="col-sm-2 control-label">Fecha</label>
								<div class="col-sm-10">
						  			<input type="date" name="start" class="form-control">
						  			<select name="start1" class="form-control">
						  				<option value="09:00">9:00 AM</option>
						  				<option value="09:45">9:45 AM</option>
						  				<option value="10:30">10:30 AM</option>
						  				<option value="11:15">11:15 AM</option>
						  				<option value="12:00">12:00 PM</option>
						  				<option value="12:45">12:45 AM</option>
						  				<option value="13:30">1:30 PM</option>
						  				<option value="14:15">2:15 PM</option>
						  				<option value="15:00">3:00 PM</option>
						  				<option value="15:45">3:45 PM</option>
						  				<option value="16:30">4:30 PM</option>
						  				<option value="17:15">5:15 PM</option>
						  				<option value="18:00">6:00 PM</option>
						  				<option value="18:45">6:45 PM</option>
						  				<option value="19:30">7:30 PM</option>
						  				<option value="20:15">8:15 PM</option>
						  				<option value="21:00">9:00 PM</option>
						  				<option value="21:45">9:45 PM</option>
						  				<option value="22:30">10:30 PM</option>
						  				<option value="23:15">11:15 PM</option>
						  			</select>
								</div>
					  		</div>
					  	</div>
					  	<div class="form-group">
						    <div class="col-sm-offset-2 col-sm-10">
						        <div class="checkbox">
						            <label>
						                <input type="checkbox" name="es_recurrente" id="es_recurrente"> ¿Es cita recurrente?
						            </label>
						        </div>
						    </div>
						</div>

						<div id="opciones_recurrencia" style="display: none;">
						    <div class="form-group">
						        <label class="col-sm-2 control-label">Frecuencia</label>
						        <div class="col-sm-10">
						            <select name="frecuencia" class="form-control">
						                <option value="7">Semanal (cada 7 días)</option>
						                <option value="15">Quincenal (cada 15 días)</option>
						                <option value="30">Mensual (cada 30 días)</option>
						            </select>
						        </div>
						    </div>
						    <div class="form-group">
						        <label class="col-sm-2 control-label">Repeticiones</label>
						        <div class="col-sm-10">
						            <input type="number" name="repeticiones" class="form-control" min="1" max="12" value="1" placeholder="¿Cuántas veces se repite?">
						        </div>
						    </div>
						</div>

						  <div class="modal-footer">
							<button type="button" class="btn btn-info" data-dismiss="modal">Cerrar</button>
							<button type="submit" class="btn btn-primary">Guardar</button>
						  </div>
					</form>
				</div>
		  	</div>
		</div>
		
		
		
		<!-- Modal -->
		<div class="modal fade" id="ModalEdit" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
		  <div class="modal-dialog" role="document">
			<div class="modal-content">
			<form class="form-horizontal" method="POST" action="editEventTitle.php">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">Detalles de la Cita</h4>
			  </div>
			  <div class="modal-body">
				  <div class="form-group">
					<label for="title" class="col-sm-2 control-label">Titulo</label>
					<div class="col-sm-10">
					  <input type="text" name="title" class="form-control" id="title" placeholder="Titulo">
					</div>
				  </div>
				<div class="form-group">
								<label for="color" class="col-sm-2 control-label">Doctor</label>
								<div class="col-sm-10">
								  	<select name="doctor" class="form-control" id="color">
										<?php
			                            $sql="SELECT user_id,username from usuarios where usuario_doctor = 1 order by username"; 
			                            $result = mysqli_query($con, $sql);
			                            while($row = mysqli_fetch_assoc($result)) { ?>
			                            	<option value="<?php echo $row["user_id"] ?>"> <?php echo $row["username"]; ?> </option>
			                            <?php }
			                            ?>
		                            </select>
								</div>
					  		</div>
					  		<div class="form-group">
					  			<label for="title" class="col-sm-2 control-label">Cliente</label>
								<div class="col-sm-10">
						  			<select name="cliente" class="form-control">
							  			<?php
			                            $sql="SELECT cliente_id,nombre from clientes order by nombre"; 
			                            $result = mysqli_query($con, $sql);
			                            while($row = mysqli_fetch_assoc($result)) { ?>
			                            	<option value="<?php echo $row["cliente_id"] ?>"> <?php echo $row["nombre"]; ?> </option>
			                            <?php }
			                            ?>
		                            </select>
		                        </div>
					  		</div>
					  		<div class="form-group">
					  			<label for="title" class="col-sm-2 control-label">Sucursal</label>
								<div class="col-sm-10">
						  			<select name="sucursal" class="form-control">
							  			<?php
		                                if($tipo_seccion==0 || $tipo_seccion==3){
		                                    $query = "SELECT * FROM sucursal order by nombre_sucursal";
		                                }
		                                else{
		                                    $query = "SELECT * FROM sucursal WHERE tipo_seccion = 1 order by nombre_sucursal";
		                                }
		                                $rs1 = mysqli_query($con, $query);
		                                while($row = mysqli_fetch_assoc($rs1)){ 
		                                ?>
		                                    <option value="<?=$row["id_sucursal"]?>"><?=$row["nombre_sucursal"]?></option>
		                                <?php
		                                }   
			                            ?>
		                            </select>
		                        </div>
					  </div>
				  
				  <input type="hidden" name="id" class="form-control" id="id">
				
				
			  </div>

			  <div class="modal-footer">
				<button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
				<?php if($isAdmin){ ?>
				<button type="submit" class="btn btn-primary">Guardar</button>
				<?php } ?>
			  </div>
			</form>
			</div>
		  </div>
		</div>

    </div>
    <!-- /.container -->

    <!-- jQuery Version 1.11.1 -->
    <script src="js/jquery.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="js/bootstrap.min.js"></script>
	
	<!-- FullCalendar -->
	<script src='js/moment.min.js'></script>
	<script src='js/fullcalendar/fullcalendar.min.js'></script>
	<script src='js/fullcalendar/fullcalendar.js'></script>
	<script src='js/fullcalendar/locale/es.js'></script>
	
	<script>

	$(document).ready(function() {

		$('#es_recurrente').on('change', function() {
	        if ($(this).is(':checked')) {
	            $('#opciones_recurrencia').fadeIn();
	        } else {
	            $('#opciones_recurrencia').fadeOut();
	        }
	    });
		
		var date = new Date();
       	var yyyy = date.getFullYear().toString();
       	var mm = (date.getMonth()+1).toString().length == 1 ? "0"+(date.getMonth()+1).toString() : (date.getMonth()+1).toString();
       	var dd  = (date.getDate()).toString().length == 1 ? "0"+(date.getDate()).toString() : (date.getDate()).toString();
		var userIsAdmin = <?php echo $isAdmin ? 'true' : 'false'; ?>;

		$('#calendar').fullCalendar({
		    header: {
		        language: 'es',
		        left: 'prev,next today',
		        center: 'title',
		        right: 'month,basicWeek,basicDay',
		    },
		    defaultDate: yyyy+"-"+mm+"-"+dd,
		    
		    // RESTRICCIONES SEGÚN ROL:
		    //editable: userIsAdmin,   // Si no es admin, no podrá arrastrar ni redimensionar
		    selectable: userIsAdmin, // Si no es admin, no podrá hacer clic en un día para agregar
		    
		    selectHelper: true,
		    select: function(start, end) {
		        // Doble validación por seguridad
		        if(userIsAdmin) {
		            $('#ModalAdd #start').val(moment(start).format('YYYY-MM-DD HH:mm'));
		            $('#ModalAdd #end').val(moment(end).format('YYYY-MM-DD HH:mm'));
		            $('#ModalAdd').modal('show');
		        }
		    },
		    eventRender: function(event, element) {
		        element.bind('dblclick', function() {
		            // Solo permitir editar (doble clic) si es admin
		            if(userIsAdmin) {
		                $('#ModalEdit #id').val(event.id);
		                $('#ModalEdit #title').val(event.title);
		                $('#ModalEdit #color').val(event.color);
		                $('#ModalEdit').modal('show');
		            } else {
		                alert("Solo los administradores pueden modificar citas.");
		            }
		        });
		    },
			eventDrop: function(event, delta, revertFunc) { // si changement de position
				edit(event);
			},
			eventResize: function(event,dayDelta,minuteDelta,revertFunc) { // si changement de longueur
				edit(event);
			},
			events: [
			<?php foreach($events as $event): 
			
				$start = explode(" ", $event['start']);
				$end = explode(" ", $event['end']);
				if($start[1] == '00:00:00'){
					$start = $start[0];
				}else{
					$start = $event['start'];
				}
				if($end[1] == '00:00:00'){
					$end = $end[0];
				}else{
					$end = $event['end'];
				}
			?>
				{
					id: '<?php echo $event['id']; ?>',
					title: '<?php echo $event['title']; ?>',
					start: '<?php echo $start; ?>',
					end: '<?php echo $end; ?>',
					color: '<?php echo $event['color']; ?>',
					doctor: '<?php echo $event['id_usuario']; ?>',
				},
			<?php endforeach; ?>
			]
		});
		
		function edit(event){

			start = event.start.format('YYYY-MM-DD HH:mm:ss');
			if(event.end){
				end = event.end.format('YYYY-MM-DD HH:mm:ss');
			}else{
				end = start;
			}
			
			id =  event.id;
			
			Event = [];
			Event[0] = id;
			Event[1] = start;
			Event[2] = end;
			
			$.ajax({
			 url: 'editEventDate.php',
			 type: "POST",
			 data: {Event:Event},
			 success: function(rep) {
					if(rep == 'OK'){
						alert('Evento se ha guardado correctamente');
					}else{
						alert('No se pudo guardar. Inténtalo de nuevo.'); 
					}
				}
			});
		}
		
		$('#btnNuevoEvento').on('click', function() {
		    // 1. Limpiar el formulario dentro del modal ModalAdd
		    $('#ModalAdd form')[0].reset();
		    
		    // 2. Asignar la fecha de hoy por defecto al campo de fecha (id="start")
		    // Esto es necesario porque el input es readonly
		    var hoy = moment().format('YYYY-MM-DD');
		    $('#ModalAdd #start').val(hoy);

		    // 3. Abrir el modal usando la sintaxis de Bootstrap 3
		    $('#ModalAdd').modal('show');
		});
	});

</script>

</body>

</html>
