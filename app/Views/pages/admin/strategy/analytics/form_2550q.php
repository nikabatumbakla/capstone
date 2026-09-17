<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    body { font-family: Arial, sans-serif; font-size: 9px; color:#000; }
    .form-title { text-align:center; margin-bottom:10px; }
    .form-title h4 { margin:2px 0; }
    table.grid { width:100%; border-collapse: collapse; margin-bottom:8px; }
    table.grid td, table.grid th { border:1px solid #000; padding:4px 6px; vertical-align:top; }
    .label { font-size:8px; color:#333; }
    .val { font-weight:bold; font-size:10px; }
    .section-title { background:#e8e8e8; font-weight:bold; padding:4px 6px; border:1px solid #000; }
    .right { text-align:right; }
    .center { text-align:center; }
    .no-border { border:none !important; }
</style>
</head>
<body>
    <div class="form-title">
        <p class="label">BIR Form No. 2550Q — Quarterly Value-Added Tax (VAT) Return</p>
        <h4><?= esc($store['store_name'] ?? 'Robin Rose Trading') ?></h4>
        <p class="label"><?= esc($store['store_address'] ?? '') ?> | TIN: <?= esc($store['store_tin'] ?? 'N/A') ?></p>
    </div>

    <table class="grid">
        <tr>
            <td style="width:33%;"><span class="label">Return Period</span><br><span class="val">Q<?= $quarter ?> <?= $year ?></span></td>
            <td style="width:33%;"><span class="label">Quarter Covered</span><br><span class="val"><?= ['','Jan–Mar','Apr–Jun','Jul–Sep','Oct–Dec'][$quarter] ?> <?= $year ?></span></td>
            <td style="width:34%;"><span class="label">Taxpayer's Registered Name</span><br><span class="val"><?= esc($store['store_name'] ?? '') ?></span></td>
        </tr>
    </table>

    <div class="section-title">Part II — Total Tax Payable</div>
    <table class="grid">
        <tr><td>15 Net VAT Payable/(Excess Input Tax)</td><td class="right val"><?= number_format($s['net_vat_payable'], 2) ?></td></tr>
        <tr><td>20 Total Tax Credits/Payment</td><td class="right">0.00</td></tr>
        <tr><td>21 Tax Still Payable/(Excess Credits)</td><td class="right val"><?= number_format($s['net_vat_payable'], 2) ?></td></tr>
        <tr><td><b>26 TOTAL AMOUNT PAYABLE/(Excess Credits)</b></td><td class="right val"><b><?= $s['net_vat_payable'] < 0 ? '(' . number_format(abs($s['net_vat_payable']), 2) . ')' : number_format($s['net_vat_payable'], 2) ?></b></td></tr>
    </table>

    <div class="section-title">Part IV — Details of VAT Computation</div>
    <table class="grid">
        <tr class="center"><th>Total Sales and Output Tax</th><th>A. Sales for the Quarter (Excl. VAT)</th><th>B. Output Tax for the Quarter</th></tr>
        <tr><td>31 VATable Sales</td><td class="right"><?= number_format($s['vatable_sales'], 2) ?></td><td class="right"><?= number_format($s['output_vat'], 2) ?></td></tr>
        <tr><td>32 Zero-Rated Sales</td><td class="right">0.00</td><td class="right no-border"></td></tr>
        <tr><td>33 Exempt Sales</td><td class="right">0.00</td><td class="right no-border"></td></tr>
        <tr><td><b>34 Total Sales & Output Tax Due</b></td><td class="right val"><?= number_format($s['vatable_sales'], 2) ?></td><td class="right val"><?= number_format($s['output_vat'], 2) ?></td></tr>
        <tr><td><b>37 Total Adjusted Output Tax Due</b></td><td></td><td class="right val"><?= number_format($s['output_vat'], 2) ?></td></tr>
    </table>

    <table class="grid">
        <tr class="center"><th>Current Transactions</th><th>A. Purchases</th><th>B. Input Tax</th></tr>
        <tr><td>44 Domestic Purchases</td><td class="right"><?= number_format($s['gross_purchases'] - $s['input_vat'], 2) ?></td><td class="right"><?= number_format($s['input_vat'], 2) ?></td></tr>
        <tr><td><b>50 Total Current Purchases/Input Tax</b></td><td class="right val"><?= number_format($s['gross_purchases'] - $s['input_vat'], 2) ?></td><td class="right val"><?= number_format($s['input_vat'], 2) ?></td></tr>
        <tr><td><b>51 Total Available Input Tax</b></td><td></td><td class="right val"><?= number_format($s['input_vat'], 2) ?></td></tr>
        <tr><td><b>60 Total Allowable Input Tax</b></td><td></td><td class="right val"><?= number_format($s['input_vat'], 2) ?></td></tr>
        <tr><td><b>61 Net VAT Payable/(Excess Input Tax)</b></td><td></td><td class="right val"><b><?= number_format($s['net_vat_payable'], 2) ?></b></td></tr>
    </table>

    <p class="label" style="margin-top:20px;">This is a system-generated draft for internal reference. Official filing requires manual verification and a valid Permit to Use (PTU) per BIR RR 16-2005 / RMO 10-2005.</p>
    <p class="label">Generated: <?= date('F j, Y g:i A') ?></p>
</body>
</html>