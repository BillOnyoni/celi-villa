<?php
// admin/mpesa_logs.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';
requireAdmin();

$logFiles = [
    'callback' => __DIR__ . '/../logs/mpesa_callback.log',
    'processed' => __DIR__ . '/../logs/mpesa_processed.log',
    'errors' => __DIR__ . '/../logs/mpesa_errors.log'
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
</head>
<body>
<nav class="navbar navbar-expand bg-dark navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">Celica Admin</a>
        <div>
            <a class="btn btn-outline-light btn-sm" href="dashboard.php">Dashboard</a>
            <a class="btn btn-outline-light btn-sm" href="payments.php">Payments</a>
            <a class="btn btn-outline-light btn-sm" href="/logout.php">Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h3>M-Pesa Logs</h3>
    
    <div class="mb-3">
        <div class="btn-group" role="group">
            <a href="?log=callback" class="btn <?= $selectedLog === 'callback' ? 'btn-primary' : 'btn-outline-primary' ?>">Callback Logs</a>
            <a href="?log=processed" class="btn <?= $selectedLog === 'processed' ? 'btn-primary' : 'btn-outline-primary' ?>">Processed Logs</a>
            <a href="?log=errors" class="btn <?= $selectedLog === 'errors' ? 'btn-primary' : 'btn-outline-primary' ?>">Error Logs</a>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h5><?= ucfirst($selectedLog) ?> Log (Last 100 entries)</h5>
        </div>
        <div class="card-body">
            <?php if (empty($logs)): ?>
                <p class="text-muted">No log entries found.</p>
            <?php else: ?>
                <div style="max-height: 600px; overflow-y: auto;">
                    <?php foreach ($logs as $log): ?>
                        <div class="border-bottom py-2">
                            <small class="font-monospace"><?= htmlspecialchars($log) ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="mt-3">
        <button class="btn btn-secondary" onclick="location.reload()">Refresh</button>
        <a href="payments.php" class="btn btn-primary">View Payments</a>
    </div>
</div>
</body>
</html>