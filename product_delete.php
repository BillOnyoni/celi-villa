<?php
// admin/product_delete.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';
requireAdmin();

$id = (int)($_GET['id'] ?? 0);
if ($id) {
    $stmt = $conn->prepare("SELECT image FROM products WHERE product_id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($r = $res->fetch_assoc()) {
        if ($r['image'] && $r['image'] !== 'placeholder.png') @unlink(__DIR__ . '/../assets/img/' . $r['image']);
    }
    $del = $conn->prepare("DELETE FROM products WHERE product_id = ?");
    $del->bind_param("i", $id);
    $del->execute();
}
redirect('products.php');
