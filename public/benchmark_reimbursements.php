<?php

// Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Reimbursement;
use Illuminate\Support\Facades\DB;

$results = [];

// 1. Benchmark Baseline Eloquent (10 records with Eager Loading)
$start = microtime(true);
$reimbursements = Reimbursement::with(['user', 'client', 'category'])
    ->latest('transaction_date')
    ->limit(10)
    ->get();
$end = microtime(true);
$results[] = [
    'name' => 'Eloquent (10 rows + With Relations)',
    'time_ms' => round(($end - $start) * 1000, 2),
    'count' => $reimbursements->count(),
    'desc' => 'Simulates the current admin panel query.'
];

// 2. Benchmark Raw SQL (10 records)
$start = microtime(true);
$raw = DB::select('SELECT * FROM reimbursements ORDER BY transaction_date DESC LIMIT 10');
$end = microtime(true);
$results[] = [
    'name' => 'Raw SQL (10 rows)',
    'time_ms' => round(($end - $start) * 1000, 2),
    'count' => count($raw),
    'desc' => 'Pure database query latency (no Laravel overhead).'
];

// 3. Benchmark Small Payload (3 records)
$start = microtime(true);
$small = Reimbursement::with(['user', 'client', 'category'])
    ->latest('transaction_date')
    ->limit(3)
    ->get();
$end = microtime(true);
$results[] = [
    'name' => 'Small Sample (3 rows)',
    'time_ms' => round(($end - $start) * 1000, 2),
    'count' => $small->count(),
    'desc' => 'Checks if data volume is the bottleneck.'
];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reimbursement Benchmark</title>
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; padding: 2rem; max-width: 800px; margin: 0 auto; background: #fca5a5; }
        .card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        h1 { margin-top: 0; color: #1e293b; }
        table { width: 100%; border-collapse: collapse; margin-top: 1.5rem; }
        th, td { padding: 0.75rem; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background: #f8fafc; font-weight: 600; color: #475569; }
        tr:last-child td { border-bottom: none; }
        .time { font-family: monospace; font-weight: bold; color: #2563eb; }
        .server-info { margin-top: 2rem; font-size: 0.875rem; color: #64748b; border-top: 1px solid #e2e8f0; padding-top: 1rem; }
    </style>
</head>
<body>
    <div class="card">
        <h1>🚀 Performance Benchmark</h1>
        <p>Testing latency for <code>/admin/reimbursements</code> logic.</p>

        <table>
            <thead>
                <tr>
                    <th>Test Case</th>
                    <th>Records</th>
                    <th>Time (ms)</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $result): ?>
                <tr>
                    <td><?php echo htmlspecialchars($result['name']); ?></td>
                    <td><?php echo $result['count']; ?></td>
                    <td class="time"><?php echo $result['time_ms']; ?> ms</td>
                    <td><?php echo htmlspecialchars($result['desc']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="server-info">
            <strong>Server Time:</strong> <?php echo date('Y-m-d H:i:s'); ?> <br>
            <strong>PHP Version:</strong> <?php echo phpversion(); ?> <br>
            <strong>Region:</strong> <?php echo env('VERCEL_REGION', 'Unknown'); ?> (Should be hnd1/Tokyo)
        </div>
    </div>
</body>
</html>
