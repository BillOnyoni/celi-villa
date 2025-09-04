<?php
// src/config/functions.php
if (session_status() == PHP_SESSION_NONE) session_start();

function sanitize($v) {
    return htmlspecialchars(trim($v), ENT_QUOTES, 'UTF-8');
}

function getCartCount() {
    if (!isset($_SESSION['cart'])) return 0;
    $count = 0;
    foreach ($_SESSION['cart'] as $qty) $count += (int)$qty;
    return $count;
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function sendEmail($to, $subject, $message) {
    // Basic mail wrapper — replace with PHPMailer in production
    $headers = "From: Celica Computers Villa <no-reply@celicavilla.local>\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    return mail($to, $subject, $message, $headers);
}

function formatPrice($amount) {
    return number_format($amount, 2);
}

/**
 * handleImageUpload
 * - $file is $_FILES['image']
 * - $targetDir is relative to project root e.g. __DIR__ . '/../../public/assets/img/'
 * - returns filename on success, throws Exception on error
 */
function handleImageUpload($file, $targetDir) {
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return 'placeholder.png';
    }
    if ($file['error'] !== UPLOAD_ERR_OK) throw new Exception("Upload error code: " . $file['error']);

    $allowed = ['image/jpeg','image/png','image/webp','image/gif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, $allowed)) throw new Exception("Unsupported image type.");

    // limit 5MB
    if ($file['size'] > 5 * 1024 * 1024) throw new Exception("File too large (max 5MB).");

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $safeName = bin2hex(random_bytes(8)) . '.' . $ext;
    $dest = rtrim($targetDir, '/') . '/' . $safeName;

    if (!move_uploaded_file($file['tmp_name'], $dest)) throw new Exception("Failed to move uploaded file.");

    return $safeName;
}

/** Admin auth check (call at top of admin pages) */
function requireAdmin() {
    if (session_status() == PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
        redirect('/src/admin/index.php');
    }
}
?>