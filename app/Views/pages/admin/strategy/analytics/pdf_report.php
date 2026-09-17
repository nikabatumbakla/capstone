<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    @page { margin: 25px 30px; }
    body { font-family: Arial, sans-serif; font-size: 10px; color:#1a1a1a; }
    .letterhead { text-align:center; border-bottom: 2px solid #1a1a1a; padding-bottom: 8px; margin-bottom: 14px; }
    .letterhead img { max-height: 60px; margin-bottom: 4px; }
    .letterhead p { margin:1px 0; font-size: 9px; color:#333; }
    .report-title { text-align:center; font-size: 12px; font-weight:bold; margin: 10px 0 14px; text-transform: uppercase; letter-spacing: 0.6px; border-top: 1px solid #ccc; border-bottom: 1px solid #ccc; padding: 6px 0; }
    table { width:100%; border-collapse: collapse; }
    th, td { border: 1px solid #999; padding: 5px 7px; font-size: 9.5px; text-align: left; }
    th { background: #2c2c2c; color:#fff; font-weight:bold; text-transform: uppercase; font-size: 8.5px; letter-spacing: 0.3px; }
    tr:nth-child(even) { background: #f5f5f5; }
    .meta { font-size: 8.5px; color:#555; margin-top: 22px; border-top: 1px solid #ccc; padding-top: 6px; }
</style>
</head>
<body>
    <div class="letterhead">
        <img src="<?= FCPATH . 'images/report_header.jpg' ?>">
    </div>

    <div class="report-title"><?= esc($title) ?></div>

    <table>
        <thead><tr><?php foreach($columns as $c): ?><th><?= esc($c) ?></th><?php endforeach; ?></tr></thead>
        <tbody>
            <?php if (empty($data)): ?>
                <tr><td colspan="<?= count($columns) ?>" style="text-align:center;">No records found for this period.</td></tr>
            <?php else: foreach($data as $row): ?>
                <tr><?php foreach((array)$row as $val): ?><td><?= esc((string) $val) ?></td><?php endforeach; ?></tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>

    <table class="meta" style="border:none;">
        <tr style="background:none;"><td style="border:none; padding:2px 0;">Generated: <?= date('F j, Y g:i A') ?></td></tr>
        <tr style="background:none;"><td style="border:none; padding:2px 0;">This is a system-generated internal record and does not itself constitute a BIR-filed document.</td></tr>
    </table>
</body>
</html>