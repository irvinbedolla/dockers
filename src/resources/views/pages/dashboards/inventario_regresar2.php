<?php 
    session_start();
    include 'conexion.php';

    $id_usuario=$_SESSION['sess_id'];

    $id = $_POST["id"];
    $cantidad = $_POST["cantidad"];
    $sql_usuario="SELECT * from usuarios WHERE user_id=$id_usuario";
    $result_usuario = mysqli_query($con, $sql_usuario);
    while($row_usuario = mysqli_fetch_assoc($result_usuario)) {
        $sucursal = $row_usuario["sucursal"];
    }

    // primero ver si existe ese producto en la sucursal
    $sql="SELECT * from productos where prod_id=".$id;
    $result = mysqli_query($con, $sql);
    while($row = mysqli_fetch_assoc($result)) {
        $nombre_producto = $row["p_nombre"];
        $sku=$row['p_codigo']; 
        $stock=$row['p_stock']; 
        $stockmin=$row['p_minimo'];
        $idprov=$row['p_proveedor'];     
        $pcompra=$row['p_pcompra']; 
        $pventa=$row['p_pventa'];  
        $tipo = $row["tipo"];
        $credito = $row["p_credito"];
        $pagina = $row["p_pagina"];
        $modelo = $row["p_modelo"]; 
        $serie = $row["p_serie"];
        $color = $row["p_color"];
        $obs = $row["p_obervaciones"];
        $sec = $row["secuencia"]; 
        $temporal = $row["temporal"]; 
    }
    if ($con->query($sql) === TRUE) {
        echo "Producto Agregado";
    } else {
        echo "Error: " . $sql . "<br>" . $con->error;
    }

    $validar = 0;
    date_default_timezone_set('America/Mexico_City');
    $time = time();
    $fecha_actual = date("Y-m-d");
    $hora_actual = date("H:i:s");

    //si exite se va actualizar donde el nombre y la sucursal sea ese
    $sql1="SELECT prod_id,p_pcompra,p_pventa from productos where p_region='PODOFARMA' and p_nombre='$nombre_producto'";
    $result1 = mysqli_query($con, $sql1);
    while($row1 = mysqli_fetch_assoc($result1)) {
        $validar = $row1["prod_id"];
        $compra = $row1["p_pcompra"];
        $venta = $row1["p_pventa"];
    }
    

        $sql1="UPDATE productos SET p_stock = p_stock + '$cantidad' WHERE prod_id='$validar' "; 
        if ($con->query($sql1) === TRUE) {
            echo "Producto Modificado";
        } else {
        echo "Error: " . $sql1   . "<br>" . $con->error;
        }
        //se va descontar del pies felices la cantidad
        $sql1="UPDATE productos SET p_stock = p_stock - '$cantidad' WHERE prod_id='$id' "; 
        if ($con->query($sql1) === TRUE) {
            echo "Producto Modificado";
        } else {
            echo "Error: " . $sql1   . "<br>" . $con->error;
        }
        //se va guardar el registro del movimiento
        $sql5 = "INSERT INTO movimientos(tipo_movimiento,mov_fecha,mov_hora,mov_articulo,mov_cantidad,mov_usuario,mov_sucursal,mov_nuevo) 
                VALUES ('Devolución','$fecha_actual','$hora_actual','$id','$cantidad','$id_usuario','$sucursal','$validar') ";
        if ($con->query($sql5) === TRUE) {
            echo "--";
        } 
        else{
            echo "E: " . $sql5 . "<br>" . $con->error;
        }
    
    

    include 'cerrar.php';   

  header("location:inventario_regresar1.php?id=$id");    
?>