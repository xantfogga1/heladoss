<?php
// add_order.php
header('Content-Type: application/json');
require 'db.php';

// leer body JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['items']) || !is_array($input['items']) || count($input['items']) === 0) {
    http_response_code(400);
    echo json_encode(['error' => 'No hay items en el pedido.']);
    exit;
}

$cliente_nombre = isset($input['cliente_nombre']) ? trim($input['cliente_nombre']) : null;
$cliente_email = isset($input['cliente_email']) ? trim($input['cliente_email']) : null;

try {
    $pdo->beginTransaction();

    // calcular total y validar productos
    $total = 0.0;
    $items_db = [];
    $stmtProd = $pdo->prepare("SELECT id, precio FROM productos WHERE id = ?");
    foreach ($input['items'] as $it) {
        $pid = (int)$it['producto_id'];
        $cantidad = (int)$it['cantidad'];
        if ($cantidad <= 0) continue;
        $stmtProd->execute([$pid]);
        $p = $stmtProd->fetch();
        if (!$p) {
            throw new Exception("Producto con id $pid no existe.");
        }
        $precio = (float)$p['precio'];
        $subtotal = $precio * $cantidad;
        $total += $subtotal;
        $items_db[] = [
            'producto_id' => $pid,
            'cantidad' => $cantidad,
            'precio_unitario' => $precio,
            'subtotal' => $subtotal
        ];
    }

    if (count($items_db) === 0) {
        throw new Exception('No hay items válidos en el pedido.');
    }

    // insertar pedido
    $stmtInsert = $pdo->prepare("INSERT INTO pedidos (cliente_nombre, cliente_email, total) VALUES (?, ?, ?)");
    $stmtInsert->execute([$cliente_nombre, $cliente_email, $total]);
    $pedido_id = $pdo->lastInsertId();

    // insertar items
    $stmtItem = $pdo->prepare("INSERT INTO pedido_items (pedido_id, producto_id, sku, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
    $getSku = $pdo->prepare("SELECT sku FROM productos WHERE id = ?");
    foreach ($items_db as $it) {
        $getSku->execute([$it['producto_id']]);
        $sku = $getSku->fetchColumn();
        $stmtItem->execute([$pedido_id, $it['producto_id'], $sku, $it['cantidad'], $it['precio_unitario'], $it['subtotal']]);
    }

    $pdo->commit();

    echo json_encode(['success' => true, 'pedido_id' => (int)$pedido_id, 'total' => number_format($total, 2)]);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
