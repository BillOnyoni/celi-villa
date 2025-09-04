<?php
// check_payment_status.php
require_once 'config/db.php';
require_once 'config/functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$order_id = (int)($_GET['order_id'] ?? 0);
if (!$order_id) {
    echo json_encode(['error' => 'Order ID required']);
    exit;
}

// Check if this order belongs to the current user
$stmt = $conn->prepare("SELECT status FROM orders WHERE order_id = ? AND user_id = ? LIMIT 1");
$stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $status = $row['status'];
    
    // If payment is complete, clear the pending order from session
    if ($status === 'paid') {
        unset($_SESSION['pending_order']);
        unset($_SESSION['cart']); // Clear cart on successful payment
    }
    
    echo json_encode(['status' => $status]);
} else {
    echo json_encode(['error' => 'Order not found']);
}
?>