<?php
// get_products.php
header('Content-Type: application/json');
require 'db.php';

$stmt = $pdo->query("SELECT id, sku, nombre, descripcion, precio, imagen FROM productos ORDER BY id");
$productos = $stmt->fetchAll();
echo json_encode($productos, JSON_UNESCAPED_UNICODE);
