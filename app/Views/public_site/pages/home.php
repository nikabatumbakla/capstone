<?= $this->extend('public_site/layouts/main') ?>

<?= $this->section('content') ?>

<!-- PROMO BAR -->
<div class="promo-bar">
    <span class="promo-pill">🎉 NEW</span>
    <span><strong>iScan</strong> — barcode lookup now available in-store!</span>
    <a href="<?= base_url('announcements') ?>">Learn More →</a>
</div>

<!-- HERO -->
<section class="hero" id="home">
    <div class="hero-bg-cross"></div>
    <div class="hero-container">
        <div class="hero-content">
            <div class="hero-label"><i class="fa fa-circle-check"></i> <?= esc($hero->badge_text ?? 'FDA Certified & BIR Compliant') ?></div>
            <h1 class="hero-title">
                <?= esc($hero->headline_line1 ?? 'Your Ultimate') ?><br>
                <span class="accent"><?= esc($hero->headline_accent ?? 'Healthcare') ?></span><br>
                <span class="italic"><?= esc($hero->headline_line3 ?? 'Partner') ?></span>
            </h1>
            <p class="hero-sub"><?= esc($hero->subtext ?? '') ?></p>
            <div class="hero-actions">
                <a href="<?= base_url('products') ?>" class="btn btn-red"><i class="fa fa-box-open"></i> Browse Products</a>
                <a href="<?= base_url('contact') ?>"  class="btn btn-blue"><i class="fa fa-file-invoice"></i> Send a Quote</a>
            </div>
            <div class="hero-stats">
                <?php for($i=1;$i<=4;$i++): ?>
                <div class="stat"><span class="num"><?= esc($hero->{"stat{$i}_number"} ?? '') ?></span><span class="lbl"><?= esc($hero->{"stat{$i}_label"} ?? '') ?></span></div>
                <?php endfor; ?>
            </div>
        </div>
        <div class="hero-visual">
            <div class="hero-img-box">
                <?php if (file_exists(FCPATH . 'images/hero-medkit.png')): ?>
                    <img src="<?= base_url('images/hero-medkit.png') ?>" alt="Medical Kit" class="medkit-img">
                <?php else: ?>
                <svg class="medkit-svg" viewBox="0 0 320 320" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="40" y="100" width="240" height="180" rx="18" fill="url(#caseGrad)"/>
                    <rect x="110" y="70"  width="100" height="40"  rx="16" fill="#c1121f"/>
                    <rect x="120" y="78"  width="80"  height="24"  rx="12" fill="#e63946"/>
                    <rect x="138" y="158" width="44"  height="14"  rx="7"  fill="white"/>
                    <rect x="153" y="143" width="14"  height="44"  rx="7"  fill="white"/>
                    <ellipse cx="85"  cy="148" rx="16" ry="10" fill="#ff8fa3"/>
                    <ellipse cx="85"  cy="148" rx="8"  ry="10" fill="#ffb3c1"/>
                    <ellipse cx="235" cy="148" rx="16" ry="10" fill="#a8dadc"/>
                    <ellipse cx="235" cy="148" rx="8"  ry="10" fill="#c8eef0"/>
                    <rect x="70"  y="200" width="60" height="8"  rx="4" fill="#fff" opacity=".9"/>
                    <rect x="125" y="198" width="10" height="12" rx="3" fill="#a8dadc"/>
                    <rect x="68"  y="202" width="8"  height="4"  rx="2" fill="#457b9d"/>
                    <rect x="185" y="185" width="22" height="40" rx="6" fill="#fff" opacity=".9"/>
                    <rect x="183" y="181" width="26" height="10" rx="5" fill="#a8dadc"/>
                    <rect x="215" y="190" width="18" height="35" rx="5" fill="#fff" opacity=".9"/>
                    <rect x="213" y="186" width="22" height="9"  rx="4" fill="#ff8fa3"/>
                    <rect x="72"  y="218" width="50" height="30" rx="5" fill="#f1faee" opacity=".9"/>
                    <ellipse cx="84"  cy="226" rx="6" ry="7" fill="#a8dadc"/>
                    <ellipse cx="100" cy="226" rx="6" ry="7" fill="#a8dadc"/>
                    <ellipse cx="84"  cy="240" rx="6" ry="7" fill="#457b9d"/>
                    <ellipse cx="100" cy="240" rx="6" ry="7" fill="#457b9d"/>
                    <circle cx="235" cy="220" r="20" fill="#fff" opacity=".88"/>
                    <circle cx="235" cy="220" r="12" fill="#f1faee" opacity=".9"/>
                    <circle cx="235" cy="220" r="5"  fill="#e63946" opacity=".7"/>
                    <ellipse cx="160" cy="295" rx="100" ry="12" fill="rgba(29,53,87,.12)"/>
                    <defs>
                        <linearGradient id="caseGrad" x1="40" y1="100" x2="280" y2="280" gradientUnits="userSpaceOnUse">
                            <stop offset="0%"   stop-color="#e63946"/>
                            <stop offset="100%" stop-color="#c1121f"/>
                        </linearGradient>
                    </defs>
                </svg>
                <?php endif; ?>
            </div>
            <div class="hero-badge">
                <span class="hero-badge-icon"><i class="fa fa-shield-halved"></i></span>
                <div class="hero-badge-text">
                    <strong>Trusted by 200+ Clients</strong>
                    <span>Hospitals · Clinics · Schools · LGUs</span>
                </div>
            </div>
            <div class="hero-badge2">
                <i class="fa fa-certificate"></i>
                <div><strong>FDA Certified</strong><span>BIR Compliant</span></div>
            </div>
        </div>
    </div>
</section>

<!-- TRUST BAR -->
<div class="trust-bar">
    <div class="trust-inner">
        <div class="trust-item"><i class="fa fa-certificate"></i> FDA Certified</div>
        <div class="trust-item"><i class="fa fa-file-invoice"></i> BIR Compliant</div>
        <div class="trust-item"><i class="fa fa-truck-fast"></i> Institutional Delivery</div>
        <div class="trust-item"><i class="fa fa-store"></i> In-Store Shopping &amp; Pickup</div>
        <div class="trust-item"><i class="fa fa-barcode"></i> iScan In-Store</div>
    </div>
</div>

<!-- CATEGORIES -->
<section class="section" id="categories">
    <div class="sec-wrap">
        <div class="sec-head reveal">
            <div class="sec-tag">Browse</div>
            <h2 class="sec-title">Product <span>Categories</span></h2>
            <p class="sec-sub">Everything your healthcare facility needs — all in one trusted supplier.</p>
        </div>
        <div class="cats-grid">
            <?php foreach ($categories as $cat): ?>
            <a href="<?= base_url('products?cat=' . $cat['category_id']) ?>" class="cat-card reveal">
                <div class="cat-ico"><i class="fa fa-box"></i></div>
                <div class="cat-name"><?= esc($cat['name']) ?></div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- FEATURED PRODUCTS -->
<section class="section section-alt" id="products">
    <div class="sec-wrap">
        <div class="sec-head reveal">
            <div class="sec-tag">Featured</div>
            <h2 class="sec-title">Popular <span>Products</span></h2>
            <p class="sec-sub">Top-selling items trusted by healthcare professionals across the Philippines.</p>
        </div>
        <div class="prod-grid">
            <?php if (empty($featured)): ?>
                <p class="text-center text-muted">No products available yet.</p>
            <?php else: foreach ($featured as $p): ?>
            <div class="prod-card reveal">
                <div class="prod-img">
                    <?php if ($p['image_path']): ?>
                        <img src="<?= base_url($p['image_path']) ?>" alt="<?= esc($p['name']) ?>" style="max-height:140px;object-fit:contain;">
                    <?php else: ?>
                        <i class="fa fa-box-open" style="font-size:3rem;color:var(--blue-light);"></i>
                    <?php endif; ?>
                    <span class="prod-stock <?= ($p['stock'] ?? 0) > 0 ? 'in' : 'out' ?>"><?= ($p['stock'] ?? 0) > 0 ? 'In Stock' : 'Out of Stock' ?></span>
                </div>
                <div class="prod-body">
                    <div class="prod-name"><?= esc($p['name']) ?></div>
                    <div class="prod-desc"><?= esc($p['description'] ?? '') ?></div>
                    <div class="prod-foot">
                        <span class="prod-price"><?= $p['price'] ? '₱' . number_format($p['price'], 2) : 'Request Quote' ?></span>
                        <a href="<?= base_url('contact') ?>" class="btn-q"><?= $p['price'] ? 'Order Now' : 'Get Quote' ?></a>
                    </div>
                </div>
            </div>
            <?php endforeach; endif; ?>
        </div>
        <div style="text-align:center;margin-top:2.5rem;">
            <a href="<?= base_url('products') ?>" class="btn btn-red"><i class="fa fa-box-open"></i> View All Products</a>
        </div>
    </div>
</section>

<!-- WHY CHOOSE US -->
<section class="section">
    <div class="sec-wrap">
        <div class="sec-head reveal">
            <div class="sec-tag">Why Us</div>
            <h2 class="sec-title">Your Trusted <span>Healthcare Partner</span></h2>
        </div>
        <div class="why-grid">
            <?php foreach ($why_us_cards as $r): ?>
            <div class="why-card reveal">
                <div class="why-ico"><i class="fa <?= esc($r['icon']) ?>"></i></div>
                <div class="why-title"><?= esc($r['title']) ?></div>
                <div class="why-desc"><?= esc($r['description']) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- SERVICES -->
<section class="section section-alt" id="services">
    <div class="sec-wrap">
        <div class="sec-head reveal">
            <div class="sec-tag">How We Serve</div>
            <h2 class="sec-title">Our <span>Services</span></h2>
            <p class="sec-sub">Flexible options to get healthcare supplies where they're needed most.</p>
        </div>
        <div class="svc-grid">
            <?php foreach ($service_cards as $s): ?>
            <div class="svc-card reveal">
                <div class="svc-ico"><i class="fa <?= esc($s['icon']) ?>"></i></div>
                <h3 class="svc-title"><?= esc($s['title']) ?></h3>
                <p class="svc-desc"><?= esc($s['description']) ?></p>
                <ul class="svc-list">
                    <?php foreach (explode("\n", $s['feature_list']) as $item): ?>
                        <?php if (trim($item) !== ''): ?><li><i class="fa fa-check-circle"></i> <?= esc(trim($item)) ?></li><?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="text-align:center;margin-top:2rem;">
            <a href="<?= base_url('services') ?>" class="btn btn-blue"><i class="fa fa-arrow-right"></i> View All Services</a>
        </div>
    </div>
</section>

<!-- ANNOUNCEMENTS PREVIEW -->
<section class="section" id="announcements">
    <div class="sec-wrap">
        <div class="sec-head reveal">
            <div class="sec-tag">Latest</div>
            <h2 class="sec-title">Announcements &amp; <span>Updates</span></h2>
        </div>
        <div class="ann-grid">
            <?php if (empty($recent_announcements)): ?>
                <p class="text-center text-muted">No announcements posted yet.</p>
            <?php else: foreach ($recent_announcements as $a): ?>
            <div class="ann-card reveal">
                <div class="ann-top" style="background:linear-gradient(135deg,#1d3557,#457b9d)">
                    <i class="fa fa-bullhorn ann-bg-icon"></i>
                    <div class="ann-title-text"><?= esc($a['title']) ?></div>
                </div>
                <div class="ann-body">
                    <p><?= esc(mb_strimwidth($a['content'], 0, 150, '...')) ?></p>
                    <div class="ann-date"><i class="fa fa-calendar"></i> <?= date('F Y', strtotime($a['created_at'])) ?></div>
                </div>
            </div>
            <?php endforeach; endif; ?>
        </div>
        <div style="text-align:center;margin-top:2rem;">
            <a href="<?= base_url('announcements') ?>" class="btn btn-blue"><i class="fa fa-newspaper"></i> View All Announcements</a>
        </div>
    </div>
</section>

<!-- TESTIMONIALS -->
<section class="section section-alt">
    <div class="sec-wrap">
        <div class="sec-head reveal">
            <div class="sec-tag">Testimonials</div>
            <h2 class="sec-title">What Our <span>Clients Say</span></h2>
            <?php if($store_rating['count'] > 0): ?>
            <div class="mt-2" style="font-size:14px;">
                <?php for($i=1;$i<=5;$i++): ?><i class="fa<?= $i <= round($store_rating['avg']) ? 's' : 'r' ?> fa-star" style="color:#f1c40f;"></i><?php endfor; ?>
                <span class="text-muted ms-2"><?= $store_rating['avg'] ?> out of 5 (<?= $store_rating['count'] ?> rating<?= $store_rating['count'] != 1 ? 's' : '' ?> from our client portal)</span>
            </div>
            <?php endif; ?>
        </div>
        <div class="test-grid">
            <?php if(empty($testimonials)): ?>
                <p class="text-center text-muted">No testimonials yet.</p>
            <?php else: foreach ($testimonials as $t): ?>
            <div class="test-card reveal">
                <div class="stars"><?= str_repeat('★', $t['rating']) . str_repeat('☆', 5 - $t['rating']) ?></div>
                <div class="test-text">"<?= esc($t['quote']) ?>"</div>
                <div class="test-author">
                    <div class="test-av"><?= strtoupper(substr($t['client_name'], 0, 1)) ?></div>
                    <div class="test-info"><strong><?= esc($t['client_name']) ?></strong><?php if($t['client_role']): ?><span><?= esc($t['client_role']) ?></span><?php endif; ?></div>
                </div>
            </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
</section>

<!-- CONTACT STRIP -->
<section class="contact-strip" id="contact-home">
    <div class="contact-strip-inner">
        <div class="cs-left reveal">
            <h2>Get in Touch</h2>
            <p>Whether you need a quote, bulk order, or just have a question — our team is ready to help you find the right healthcare solution.</p>
            <div class="cs-contacts">
                <div class="cs-item"><div class="cs-icon"><i class="fa fa-phone"></i></div><div><strong>09292379053</strong><span>Phone / Viber</span></div></div>
                <div class="cs-item"><div class="cs-icon"><i class="fa fa-envelope"></i></div><div><strong>Redrosalinda1876@gmail.com</strong><span>Email</span></div></div>
                <div class="cs-item"><div class="cs-icon"><i class="fa fa-location-dot"></i></div><div><strong>Ortega St., Philippines</strong><span>Store Address</span></div></div>
                <div class="cs-item"><div class="cs-icon"><i class="fa fa-clock"></i></div><div><strong>Mon – Sat: 8:00 AM – 6:00 PM</strong><span>Business Hours</span></div></div>
            </div>
        </div>
        <div class="cs-right reveal">
            <div class="cs-form-title"><i class="fa fa-paper-plane"></i> Send a Quick Inquiry</div>
            <form action="<?= base_url('contact/submit') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="form-row">
                    <div class="fg"><label>First Name</label><input type="text" name="first_name" required placeholder="Juan"></div>
                    <div class="fg"><label>Last Name</label><input type="text" name="last_name" required placeholder="Dela Cruz"></div>
                </div>
                <div class="fg"><label>Email</label><input type="email" name="email" required placeholder="email@example.com"></div>
                <div class="fg">
                    <label>Inquiry Type</label>
                    <select name="inquiry_type" required>
                        <option value="">-- Select --</option>
                        <option>Request a Quote</option>
                        <option>Bulk Order</option>
                        <option>iScan Inquiry</option>
                        <option>General Inquiry</option>
                    </select>
                </div>
                <div class="fg"><label>Message</label><textarea name="message" required placeholder="Describe your inquiry..."></textarea></div>
                <button type="submit" class="btn btn-red" style="width:100%;justify-content:center;border:none;font-family:var(--font-b);">
                    <i class="fa fa-paper-plane"></i> Send Message
                </button>
            </form>
        </div>
    </div>
</section>

<!-- CTA BANNER -->
<div class="cta-banner">
    <h2>Ready to Partner with Robin Rose Trading?</h2>
    <p>Join 200+ hospitals, clinics, schools, and barangays who trust us for their healthcare supply needs.</p>
    <div class="btns">
        <a href="tel:09292379053" class="btn btn-white"><i class="fa fa-phone"></i> Call Now</a>
        <a href="<?= base_url('contact') ?>" class="btn btn-outline-w"><i class="fa fa-envelope"></i> Full Inquiry Form</a>
    </div>
</div>

<?= $this->endSection() ?>