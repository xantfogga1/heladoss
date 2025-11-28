<?php
include "config.php";

$id = $_POST['id'];

// Verificamos si el producto ya está en el carrito
$existe = $conexion->query("SELECT * FROM carrito WHERE producto_id = $id");

if ($existe->num_rows > 0) {
    // Si ya existe solo se suma 1
    $conexion->query("UPDATE carrito SET cantidad = cantidad + 1 WHERE producto_id = $id");
} else {
    // Si no existe, se agrega
    $conexion->query("INSERT INTO carrito (producto_id, cantidad) VALUES ($id, 1)");
}

echo "ok";
?>
