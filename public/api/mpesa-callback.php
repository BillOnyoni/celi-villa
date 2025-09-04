<?php
// public/api/mpesa-callback.php
require_once __DIR__ . '/../../src/config/db.php';
require_once __DIR__ . '/../../src/config/functions.php';

// Ensure logs directory exists
$logsDir = __DIR__ . '/../../logs';
if (!is_dir($logsDir)) {
    mkdir($logsDir, 0755, true);
}

// Log all incoming requests for debugging
$input = file_get_contents('php://input');
$logEntry = date('Y-m-d H:i:s') . " - " . $input . PHP_EOL;
file_put_contents($logsDir . '/mpesa_callback.log', $logEntry, FILE_APPEND | LOCK_EX);

try {
    $data = json_decode($input, true);
    
    if (!$data || !isset($data['Body']['stkCallback'])) {
        throw new Exception("Invalid callback data structure");
    }
    
    $callback = $data['Body']['stkCallback'];
    $checkoutRequestID = $callback['CheckoutRequestID'] ?? null;
    $merchantRequestID = $callback['MerchantRequestID'] ?? null;
    $resultCode = $callback['ResultCode'] ?? null;
    $resultDesc = $callback['ResultDesc'] ?? '';
    
    if (!$checkoutRequestID) {
        throw new Exception("Missing CheckoutRequestID");
    }
    
    // Find payment record
    $stmt = $conn->prepare("SELECT payment_id, order_id, user_id FROM payments WHERE mpesa_code = ? LIMIT 1");
    $stmt->bind_param("s", $checkoutRequestID);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if (!$row = $res->fetch_assoc()) {
        throw new Exception("Payment record not found for CheckoutRequestID: $checkoutRequestID");
    }
    
    $payment_id = $row['payment_id'];
    $order_id = $row['order_id'];
    $user_id = $row['user_id'];
    
    if ($resultCode === 0) {
        // Payment successful
        $metadata = $callback['CallbackMetadata']['Item'] ?? [];
        $mpesaReceiptNumber = '';
        $amountPaid = 0;
        $transactionDate = '';
        $phoneNumber = '';
        
        foreach ($metadata as $item) {
            switch ($item['Name']) {
                case 'MpesaReceiptNumber':
                    $mpesaReceiptNumber = $item['Value'];
                    break;
                case 'Amount':
                    $amountPaid = $item['Value'];
                    break;
                case 'TransactionDate':
                    $transactionDate = $item['Value'];
                    break;
                case 'PhoneNumber':
                    $phoneNumber = $item['Value'];
                    break;
            }
        }
        
        // Update payment record with success details
        $updatePayment = $conn->prepare("UPDATE payments SET status = 'completed', mpesa_receipt = ?, amount_paid = ?, transaction_date = ?, phone_number = ?, updated_at = NOW() WHERE payment_id = ?");
        $updatePayment->bind_param("sdssi", $mpesaReceiptNumber, $amountPaid, $transactionDate, $phoneNumber, $payment_id);
        $updatePayment->execute();
        
        // Mark order as paid
        $updateOrder = $conn->prepare("UPDATE orders SET status = 'paid', updated_at = NOW() WHERE order_id = ?");
        $updateOrder->bind_param("i", $order_id);
        $updateOrder->execute();
        
        // Send confirmation email to customer
        $userStmt = $conn->prepare("SELECT email, username FROM users WHERE user_id = ?");
        $userStmt->bind_param("i", $user_id);
        $userStmt->execute();
        $userResult = $userStmt->get_result();
        if ($userData = $userResult->fetch_assoc()) {
            $subject = "Payment Confirmation - Order #$order_id";
            $message = "Dear {$userData['username']},\n\n";
            $message .= "Your payment has been successfully processed.\n\n";
            $message .= "Order ID: $order_id\n";
            $message .= "Amount: KES " . number_format($amountPaid, 2) . "\n";
            $message .= "M-Pesa Receipt: $mpesaReceiptNumber\n\n";
            $message .= "Thank you for shopping with Celica Computers Villa!\n\n";
            $message .= "Best regards,\nCelica Team";
            
            sendEmail($userData['email'], $subject, $message);
        }
        
        $status = 'completed';
        
    } else {
        // Payment failed or cancelled
        $status = 'failed';
        if ($resultCode === 1032) {
            $status = 'cancelled'; // User cancelled
        } elseif ($resultCode === 1037) {
            $status = 'timeout'; // Timeout
        }
        
        // Update payment record
        $updatePayment = $conn->prepare("UPDATE payments SET status = ?, result_desc = ?, updated_at = NOW() WHERE payment_id = ?");
        $updatePayment->bind_param("ssi", $status, $resultDesc, $payment_id);
        $updatePayment->execute();
        
        // Update order status
        $updateOrder = $conn->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE order_id = ?");
        $updateOrder->bind_param("si", $status, $order_id);
        $updateOrder->execute();
    }
    
    // Log successful processing
    $logEntry = date('Y-m-d H:i:s') . " - Processed: Order $order_id, Result: $resultCode, Status: $status" . PHP_EOL;
    file_put_contents($logsDir . '/mpesa_processed.log', $logEntry, FILE_APPEND | LOCK_EX);
    
} catch (Exception $e) {
    // Log errors
    $errorLog = date('Y-m-d H:i:s') . " - Error: " . $e->getMessage() . " - Data: " . $input . PHP_EOL;
    file_put_contents($logsDir . '/mpesa_errors.log', $errorLog, FILE_APPEND | LOCK_EX);
}

// Always respond with success to Safaricom
header('Content-Type: application/json');
echo json_encode([
    'ResultCode' => 0, 
    'ResultDesc' => 'Callback processed successfully'
]);
?>