<!DOCTYPE html>
<html>
<head>
<title>Robin Rose Trading — <?= ucfirst($type) ?> Report</title>
<style>
    body { font-family: Arial, sans-serif; font-size: 12px; color:#000; padding: 30px; }
    .letterhead { text-align:center; border-bottom: 3px solid #7b1113; padding-bottom: 10px; margin-bottom: 20px; }
    .letterhead h2 { margin:0; color:#7b1113; }
    .letterhead p { margin:0; font-size: 11px; }
    table { width:100%; border-collapse: collapse; margin-top: 15px; }
    th, td { border: 1px solid #ccc; padding: 6px 8px; font-size: 11px; text-align: left; }
    th { background: #7b1113; color:#fff; }
    .meta { font-size: 10px; color:#666; margin-top: 20px; text-align:right; }
    @media print { .no-print { display:none; } }
</style>
</head>
<body>
    <div class="letterhead">
        <h2>Robin Rose Trading</h2>
        <p><?= ucfirst($type) ?> Report</p>
    </div>

    <table>
        <thead><tr><?php foreach($columns as $c): ?><th><?= esc($c) ?></th><?php endforeach; ?></tr></thead>
        <tbody>
            <?php if (empty($data)): ?>
                <tr><td colspan="<?= count($columns) ?>" style="text-align:center;">No records found.</td></tr>
            <?php else: foreach($data as $row): ?>
                <tr><?php foreach((array)$row as $val): ?><td><?= esc($val) ?></td><?php endforeach; ?></tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>

    <p class="meta">Generated on <?= $generated_at ?> — Robin Rose Trading Internal System</p>

    <div class="no-print" style="margin-top:20px;">
        <button onclick="window.print()">Print / Save as PDF</button>
    </div>
</body>
</html>