<?php
// src/admin/mpesa-logs.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';
requireAdmin();

$logsDir = __DIR__ . '/../../logs';
$logFiles = [
    'callback' => $logsDir . '/mpesa_callback.log',
    'processed' => $logsDir . '/mpesa_processed.log',
    'errors' => $logsDir . '/mpesa_errors.log'
];

$selectedLog = $_GET['log'] ?? 'callback';
$logFile = $logFiles[$selectedLog] ?? $logFiles['callback'];

$logs = [];
if (file_exists($logFile)) {
    $logs = array_reverse(array_filter(explode("\n", file_get_contents($logFile))));
    $logs = array_slice($logs, 0, 100); // Show last 100 entries
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>M-Pesa Logs - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<nav class="navbar navbar-expand bg-dark navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">Celica Admin</a>
        <div>
            <a class="btn btn-outline-light btn-sm" href="dashboard.php">Dashboard</a>
            <a class="btn btn-outline-light btn-sm" href="payments.php">Payments</a>
            <a class="btn btn-outline-light btn-sm" href="/public/logout.php">Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h3><i class="fas fa-file-alt"></i> M-Pesa Transaction Logs</h3>
    
    <div class="mb-3">
        <div class="btn-group" role="group">
            <a href="?log=callback" class="btn <?= $selectedLog === 'callback' ? 'btn-primary' : 'btn-outline-primary' ?>">
                <i class="fas fa-phone"></i> Callback Logs
            </a>
            <a href="?log=processed" class="btn <?= $selectedLog === 'processed' ? 'btn-primary' : 'btn-outline-primary' ?>">
                <i class="fas fa-check"></i> Processed Logs
            </a>
            <a href="?log=errors" class="btn <?= $selectedLog === 'errors' ? 'btn-primary' : 'btn-outline-primary' ?>">
                <i class="fas fa-exclamation-triangle"></i> Error Logs
            </a>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5><i class="fas fa-list"></i> <?= ucfirst($selectedLog) ?> Log (Last 100 entries)</h5>
            <button class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                <i class="fas fa-sync"></i> Refresh
            </button>
        </div>
        <div class="card-body">
            <?php if (empty($logs)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No log entries found.</p>
                    <small class="text-muted">Logs will appear here when M-Pesa transactions are processed.</small>
                </div>
            <?php else: ?>
                <div style="max-height: 600px; overflow-y: auto;">
                    <?php foreach ($logs as $index => $log): ?>
                        <div class="border-bottom py-2 <?= $index % 2 === 0 ? 'bg-light' : '' ?>">
                            <small class="font-monospace"><?= htmlspecialchars($log) ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="mt-3">
        <a href="payments.php" class="btn btn-primary">
            <i class="fas fa-credit-card"></i> View Payments
        </a>
        <a href="dashboard.php" class="btn btn-secondary">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
    </div>
</div>
</body>
</html>