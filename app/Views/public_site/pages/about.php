<?= $this->extend('public_site/layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-hero">
    <h1>About Robin Rose Trading</h1>
    <p>Our story, mission, and commitment to healthcare excellence</p>
    <div class="breadcrumb">
        <a href="<?= base_url('/') ?>">Home</a> <i class="fa fa-chevron-right"></i> About Us
    </div>
</div>

<!-- COMPANY OVERVIEW -->
<section class="section">
    <div class="section-container">
        <div class="about-grid">
            <div class="about-img-wrap">
                <img src="<?= base_url('public/images/hero-medkit.png') ?>" alt="Robin Rose Trading">
                <div class="about-cert">
                    <div class="cert-badge"><i class="fa fa-certificate"></i><span>FDA</span></div>
                    <div class="cert-badge"><i class="fa fa-file-invoice"></i><span>BIR</span></div>
                    <div class="cert-badge"><i class="fa fa-building"></i><span>Registered</span></div>
                </div>
            </div>
            <div class="about-content">
                <div class="section-tag">Our Story</div>
                <h2 class="about-title">Dedicated to Healthcare Excellence</h2>
                <p class="about-text"><?= esc($about->story_paragraph1 ?? '') ?></p>
                <p class="about-text"><?= esc($about->story_paragraph2 ?? '') ?></p>
                <div class="mission-vision">
                    <div class="mv-card">
                        <h4><i class="fa fa-bullseye"></i> Our Mission</h4>
                        <p><?= esc($about->mission_text ?? '') ?></p>
                    </div>
                    <div class="mv-card">
                        <h4><i class="fa fa-eye"></i> Our Vision</h4>
                        <p><?= esc($about->vision_text ?? '') ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CERTIFICATIONS (static — legal/regulatory facts, not admin-edited) -->
<section class="section section-alt">
    <div class="section-container">
        <div class="section-header">
            <div class="section-tag">Credentials</div>
            <h2 class="section-title">Certifications &amp; <span>Compliance</span></h2>
            <p class="section-subtitle">We operate with full transparency and regulatory compliance for your peace of mind.</p>
        </div>
        <div class="why-grid">
            <div class="why-card">
                <div class="why-icon"><i class="fa fa-certificate"></i></div>
                <div class="why-title">FDA Certification</div>
                <div class="why-desc">Robin Rose Trading is duly registered with the Philippine Food and Drug Administration. All products we carry are FDA-approved for sale and use in the Philippines.</div>
            </div>
            <div class="why-card">
                <div class="why-icon"><i class="fa fa-file-invoice-dollar"></i></div>
                <div class="why-title">BIR Compliance</div>
                <div class="why-desc">We are fully compliant with Bureau of Internal Revenue regulations. Official receipts and invoices are issued for all transactions.</div>
            </div>
            <div class="why-card">
                <div class="why-icon"><i class="fa fa-building-columns"></i></div>
                <div class="why-title">DTI Registered</div>
                <div class="why-desc">Registered business enterprise with the Department of Trade and Industry. Operating with full legal authority and business permits.</div>
            </div>
            <div class="why-card">
                <div class="why-icon"><i class="fa fa-shield-halved"></i></div>
                <div class="why-title">Quality Assured</div>
                <div class="why-desc">Every product undergoes quality checks before reaching our shelves. We partner only with reputable manufacturers and distributors.</div>
            </div>
        </div>

        <div style="margin-top:2.5rem;">
            <h3 style="font-family:var(--font-display);color:var(--blue);margin-bottom:1rem;">Compliance Overview</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Certification / License</th>
                        <th>Issuing Authority</th>
                        <th>Status</th>
                        <th>Coverage</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><i class="fa fa-certificate" style="color:var(--red)"></i> FDA License to Operate</td>
                        <td>Food and Drug Administration (FDA Philippines)</td>
                        <td><span class="status-badge status-delivered">Active</span></td>
                        <td>Medical devices, OTC products</td>
                    </tr>
                    <tr>
                        <td><i class="fa fa-file-invoice" style="color:var(--red)"></i> BIR Certificate of Registration</td>
                        <td>Bureau of Internal Revenue</td>
                        <td><span class="status-badge status-delivered">Active</span></td>
                        <td>All business transactions</td>
                    </tr>
                    <tr>
                        <td><i class="fa fa-store" style="color:var(--red)"></i> Mayor's Business Permit</td>
                        <td>Local Government Unit</td>
                        <td><span class="status-badge status-delivered">Active</span></td>
                        <td>Local business operations</td>
                    </tr>
                    <tr>
                        <td><i class="fa fa-building" style="color:var(--red)"></i> DTI Business Name Registration</td>
                        <td>Department of Trade and Industry</td>
                        <td><span class="status-badge status-delivered">Active</span></td>
                        <td>Business name "Robin Rose Trading"</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- TEAM -->
<section class="section">
    <div class="section-container">
        <div class="section-header">
            <div class="section-tag">Our People</div>
            <h2 class="section-title">Meet Our <span>Team</span></h2>
        </div>
        <div class="team-grid" style="display:flex; flex-wrap:wrap; justify-content:center; gap:1.6rem;">
    <?php if(empty($team)): ?>
        <p class="text-center text-muted">Team information coming soon.</p>
    <?php else: foreach($team as $m): ?>
    <div class="team-card" style="flex:0 1 200px;">
        <div class="team-ico"><i class="fa fa-user"></i></div>
        <div class="team-name"><?= esc($m['name']) ?></div>
        <div class="team-role"><?= esc($m['role']) ?></div>
    </div>
    <?php endforeach; endif; ?>
</div>
    </div>
</section>

<!-- COMMITMENT -->
<section class="section section-alt">
    <div class="section-container">
        <div class="section-header">
            <div class="section-tag">Our Commitment</div>
            <h2 class="section-title">Quality, Safety &amp; <span>Service</span></h2>
        </div>
        <div style="max-width:800px;margin:0 auto;text-align:center;">
            <p style="color:var(--gray);font-size:1rem;line-height:1.9;margin-bottom:1.5rem;">
                At Robin Rose Trading, we believe that access to quality healthcare products is a fundamental right,
                not a privilege. Every product we source, every partnership we build, and every delivery we make
                is guided by our unwavering commitment to the health and safety of our clients and their communities.
            </p>
            <p style="color:var(--gray);font-size:1rem;line-height:1.9;">
                We continuously update our product line to align with evolving health regulations, emerging medical needs,
                and feedback from our valued clients. From individual households to large government institutions,
                Robin Rose Trading is your reliable partner in healthcare.
            </p>
            <div style="display:flex;justify-content:center;gap:1rem;flex-wrap:wrap;margin-top:2rem;">
                <a href="<?= base_url('partner-gateway') ?>" class="btn-primary"><i class="fa fa-handshake"></i> Partner with Us</a>
                <a href="<?= base_url('products') ?>" class="btn-outline"><i class="fa fa-box-open"></i> Browse Products</a>
            </div>
        </div>
    </div>
</section>

<?= $this->endSection() ?>