<?php
// callback.php
// This file must be accessible via the callback URL you provided to Safaricom.
// It receives JSON and updates payments/orders accordingly.

require_once 'config/db.php';

// read incoming JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);
file_put_contents('mpesa_callback.log', date('Y-m-d H:i:s') . " - " . $input . PHP_EOL, FILE_APPEND);

// The structure depends on Safaricom response. Typical JSON contains
// Body -> stkCallback with CheckoutRequestID and ResultCode and CallbackMetadata (items)
$checkoutRequestID = $data['Body']['stkCallback']['CheckoutRequestID'] ?? null;
$resultCode = $data['Body']['stkCallback']['ResultCode'] ?? null;

if ($checkoutRequestID) {
    // find payment record by mpesa_code
    $stmt = $conn->prepare("SELECT payment_id, order_id FROM payments WHERE mpesa_code = ? LIMIT 1");
    $stmt->bind_param("s", $checkoutRequestID);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $payment_id = $row['payment_id'];
        $order_id = $row['order_id'];

        if ($resultCode === 0) {
            // success -> extract Mpesa receipt number and amount
            $metadata = $data['Body']['stkCallback']['CallbackMetadata']['Item'] ?? [];
            $mpesaReceipt = '';
            $amount = 0;
            foreach ($metadata as $item) {
                if ($item['Name'] === 'MpesaReceiptNumber') $mpesaReceipt = $item['Value'];
                if ($item['Name'] === 'Amount') $amount = $item['Value'];
            }
            // update payment record
            $upd = $conn->prepare("UPDATE payments SET mpesa_code = ?, amount = ? WHERE payment_id = ?");
            $upd->bind_param("sdi", $mpesaReceipt, $amount, $payment_id);
            $upd->execute();
            // mark order paid
            $upd2 = $conn->prepare("UPDATE orders SET status = 'paid' WHERE order_id = ?");
            $upd2->bind_param("i", $order_id);
            $upd2->execute();
        } else {
            // failed or cancelled: set order status accordingly
            $status = ($resultCode === 1032 ? 'cancelled' : 'pending');
            $upd2 = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
            $upd2->bind_param("si", $status, $order_id);
            $upd2->execute();
        }
    }
}

header('Content-Type: application/json');
echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
