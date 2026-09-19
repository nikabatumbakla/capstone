<?= $this->extend('public_site/layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-hero">
    <h1>Our Services</h1>
    <p>Convenient healthcare supply solutions for every type of client</p>
    <div class="breadcrumb">
        <a href="<?= base_url('/') ?>">Home</a> <i class="fa fa-chevron-right"></i> Services
    </div>
</div>

<section class="section">
    <div class="section-container">
        <div class="section-header">
            <div class="section-tag">What We Offer</div>
            <h2 class="section-title">Serving Every <span>Healthcare Need</span></h2>
            <p class="section-subtitle">From institutional bulk delivery to walk-in purchases, we make getting medical supplies easy and reliable.</p>
        </div>

        <div class="services-grid" style="display:flex; flex-wrap:wrap; justify-content:center; gap:2rem;">
            <?php if(empty($service_cards)): ?>
                <p class="text-center text-muted">No services listed yet.</p>
            <?php else: foreach($service_cards as $s): ?>
            <div class="service-card" style="flex:0 1 300px;">
                <div class="service-icon"><i class="fa <?= esc($s['icon']) ?>"></i></div>
                <h3 class="service-title"><?= esc($s['title']) ?></h3>
                <p class="service-desc"><?= esc($s['description']) ?></p>
                <ul class="service-list">
                    <?php foreach(explode("\n", $s['feature_list']) as $item): ?>
                        <?php if(trim($item) !== ''): ?><li><i class="fa fa-check-circle"></i> <?= esc(trim($item)) ?></li><?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endforeach; endif; ?>
        </div>

        <!-- Service Comparison Table (static — factual policy details, iRent removed) -->
        <div style="margin-top:3rem;">
            <div class="section-header" style="text-align:left;">
                <h3 style="font-family:var(--font-display);font-size:1.5rem;color:var(--blue);">Service <span style="color:var(--red)">Comparison</span></h3>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Feature</th>
                        <th><i class="fa fa-truck-fast"></i> Institutional Delivery</th>
                        <th><i class="fa fa-store"></i> In-Store Shopping</th>
                        <th><i class="fa fa-box-archive"></i> In-Store Pick Up</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>Availability</td><td>By Schedule / Order</td><td>Mon–Sat, 8AM–6PM</td><td>24hrs after order</td></tr>
                    <tr><td>Min. Order</td><td>Bulk / Institutional</td><td>No Minimum</td><td>No Minimum</td></tr>
                    <tr><td>Payment Methods</td><td>Invoice / Bank Transfer / COD</td><td>Cash / GCash / Bank</td><td>Cash / GCash / Bank</td></tr>
                    <tr><td>Official Receipt</td><td><i class="fa fa-check" style="color:#28a745"></i> Yes</td><td><i class="fa fa-check" style="color:#28a745"></i> Yes</td><td><i class="fa fa-check" style="color:#28a745"></i> Yes</td></tr>
                    <tr><td>Dedicated Account Manager</td><td><i class="fa fa-check" style="color:#28a745"></i> Yes</td><td><i class="fa fa-minus" style="color:#aaa"></i> Walk-in Only</td><td><i class="fa fa-minus" style="color:#aaa"></i> Walk-in Only</td></tr>
                    <tr><td>iScan Barcode Lookup</td><td><i class="fa fa-minus" style="color:#aaa"></i> N/A</td><td><i class="fa fa-check" style="color:#28a745"></i> Available In-Store</td><td><i class="fa fa-check" style="color:#28a745"></i> Available In-Store</td></tr>
                </tbody>
            </table>
        </div>

        <!-- Client Types Table (static) -->
        <div style="margin-top:3rem;">
            <div class="section-header" style="text-align:left;">
                <h3 style="font-family:var(--font-display);font-size:1.5rem;color:var(--blue);">Who We <span style="color:var(--red)">Serve</span></h3>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Client Type</th>
                        <th>Examples</th>
                        <th>Service Available</th>
                        <th>Account Type</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><i class="fa fa-hospital" style="color:var(--red)"></i> Hospitals &amp; Clinics</td>
                        <td>Government &amp; Private Hospitals, Rural Health Units</td>
                        <td>Delivery, Pickup, In-Store</td>
                        <td><span class="status-badge status-delivered">Institutional</span></td>
                    </tr>
                    <tr>
                        <td><i class="fa fa-school" style="color:var(--red)"></i> Schools</td>
                        <td>Elementary, High School, Universities</td>
                        <td>Delivery, Pickup, In-Store</td>
                        <td><span class="status-badge status-shipped">Institutional</span></td>
                    </tr>
                    <tr>
                        <td><i class="fa fa-people-group" style="color:var(--red)"></i> Barangay</td>
                        <td>Barangay Health Centers, Day Care Centers</td>
                        <td>Delivery, Pickup, In-Store</td>
                        <td><span class="status-badge status-delivered">Institutional</span></td>
                    </tr>
                    <tr>
                        <td><i class="fa fa-building-columns" style="color:var(--red)"></i> LGU</td>
                        <td>Municipal, City, Provincial Governments</td>
                        <td>Delivery, Canvass/Bidding</td>
                        <td><span class="status-badge status-processing">Government</span></td>
                    </tr>
                    <tr>
                        <td><i class="fa fa-person" style="color:var(--red)"></i> Individual / Retail</td>
                        <td>Patients, Caregivers, Families</td>
                        <td>In-Store, Pickup</td>
                        <td><span class="status-badge status-pending">Retail</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- CTA -->
<section style="background:var(--cream);padding:4rem 2rem;text-align:center;">
    <div style="max-width:600px;margin:0 auto;">
        <div class="section-tag">Get Started</div>
        <h2 style="font-family:var(--font-display);font-size:2rem;color:var(--blue);margin:1rem 0;">Ready to place an order?</h2>
        <p style="color:var(--gray);margin-bottom:2rem;">Contact us today to discuss your healthcare supply needs or to set up an institutional account.</p>
        <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
            <a href="<?= base_url('contact') ?>" class="btn-primary"><i class="fa fa-envelope"></i> Contact Us</a>
            <a href="tel:09292379053" class="btn-outline"><i class="fa fa-phone"></i> 09292379053</a>
        </div>
    </div>
</section>

<?= $this->endSection() ?>