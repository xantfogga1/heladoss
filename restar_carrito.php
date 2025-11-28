<?php
include "config.php";

$id = $_POST['id'];

$consulta = $conexion->query("SELECT cantidad FROM carrito WHERE producto_id = $id");
$data = $consulta->fetch_assoc();

if ($data['cantidad'] > 1) {
    $conexion->query("UPDATE carrito SET cantidad = cantidad - 1 WHERE producto_id = $id");
} else {
    $conexion->query("DELETE FROM carrito WHERE producto_id = $id");
}

echo "ok";
?>
