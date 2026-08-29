<?= $this->extend('public_site/layouts/main') ?>

<?= $this->section('content') ?>

<!-- PROMO BAR -->
<div class="promo-bar">
    <span class="promo-pill">🎉 NEW</span>
    <span>Introducing <strong>iRent</strong> – rent medical equipment &amp; <strong>iScan</strong> – barcode lookup in-store!</span>
    <a href="<?= base_url('announcements') ?>">Learn More →</a>
</div>

<!-- HERO -->
<section class="hero" id="home">
    <div class="hero-bg-cross"></div>
    <div class="hero-container">
        <div class="hero-content">
            <div class="hero-label"><i class="fa fa-circle-check"></i> FDA Certified &amp; BIR Compliant</div>
            <h1 class="hero-title">
                Your Ultimate<br>
                <span class="accent">Healthcare</span><br>
                <span class="italic">Partner</span>
            </h1>
            <p class="hero-sub">
                Robin Rose Trading supplies quality medical equipment, PPE, diagnostics, and healthcare essentials
                to hospitals, clinics, schools, and communities across the Philippines.
            </p>
            <div class="hero-actions">
                <a href="<?= base_url('products') ?>" class="btn btn-red"><i class="fa fa-box-open"></i> Browse Products</a>
                <a href="<?= base_url('contact') ?>"  class="btn btn-blue"><i class="fa fa-file-invoice"></i> Send a Quote</a>
            </div>
            <div class="hero-stats">
                <div class="stat"><span class="num">500+</span><span class="lbl">Products</span></div>
                <div class="stat"><span class="num">10</span><span class="lbl">Categories</span></div>
                <div class="stat"><span class="num">200+</span><span class="lbl">Clients Served</span></div>
                <div class="stat"><span class="num">FDA</span><span class="lbl">Certified</span></div>
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
        <div class="trust-item"><i class="fa fa-rotate"></i> iRent Available</div>
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
            <?php
            $categories = [
                ['icon'=>'fa-stethoscope',   'name'=>'Diagnostic &amp; Monitoring', 'slug'=>'diagnostic'],
                ['icon'=>'fa-pills',          'name'=>'OTC Medicines',               'slug'=>'otc'],
                ['icon'=>'fa-lungs',          'name'=>'Respiratory Care',            'slug'=>'respiratory'],
                ['icon'=>'fa-wheelchair',     'name'=>'Mobility &amp; Rehab',        'slug'=>'mobility'],
                ['icon'=>'fa-shield-virus',   'name'=>'PPE &amp; Infection Control', 'slug'=>'ppe'],
                ['icon'=>'fa-hard-hat',       'name'=>'Safety Equipment',            'slug'=>'safety'],
                ['icon'=>'fa-kit-medical',    'name'=>'Wound Care &amp; Emergency',  'slug'=>'wound'],
                ['icon'=>'fa-droplet',        'name'=>'Incontinence Care',           'slug'=>'incontinence'],
                ['icon'=>'fa-graduation-cap', 'name'=>'Educational &amp; Specialty', 'slug'=>'educational'],
                ['icon'=>'fa-bag-shopping',   'name'=>'General Merchandise',         'slug'=>'general'],
            ];
            foreach ($categories as $cat): ?>
            <a href="<?= base_url('products?cat=' . $cat['slug']) ?>" class="cat-card reveal">
                <div class="cat-ico"><i class="fa <?= $cat['icon'] ?>"></i></div>
                <div class="cat-name"><?= $cat['name'] ?></div>
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
            <?php
            $featured = [
                ['sku'=>'RRT-DBP-001','name'=>'Digital BP Monitor',         'desc'=>'Automatic upper arm monitor, irregular heartbeat detection. FDA approved.','price'=>'₱1,850',      'stock'=>true, 'badge'=>'Best Seller'],
                ['sku'=>'RRT-OXI-003','name'=>'Pulse Oximeter',             'desc'=>'Fingertip SpO2 and pulse rate monitor with large LED display.',             'price'=>'₱650',        'stock'=>true, 'badge'=>null],
                ['sku'=>'RRT-PPE-012','name'=>'Surgical Face Mask (50pcs)', 'desc'=>'3-ply disposable surgical masks, BFE ≥95%, comfortable ear-loop design.',   'price'=>'₱185',        'stock'=>true, 'badge'=>'Hot'],
                ['sku'=>'RRT-MOB-007','name'=>'Folding Wheelchair (Adult)', 'desc'=>'Lightweight aluminum frame, padded seat, foldable for easy transport.',      'price'=>'Request Quote','stock'=>true, 'badge'=>'iRent'],
                ['sku'=>'RRT-WND-024','name'=>'Complete First Aid Kit',     'desc'=>'Comprehensive 50-piece first aid kit in durable carrying case.',             'price'=>'₱850',        'stock'=>true, 'badge'=>'Complete Kit'],
                ['sku'=>'RRT-RSP-005','name'=>'Nebulizer Machine',          'desc'=>'Compressor nebulizer for efficient medication delivery. Quiet operation.',    'price'=>'₱1,450',      'stock'=>false,'badge'=>null],
            ];
            foreach ($featured as $p): ?>
            <div class="prod-card reveal">
                <div class="prod-img">
                    <?php if (file_exists(FCPATH . 'images/products/' . $p['sku'] . '.png')): ?>
                        <img src="<?= base_url('images/products/' . $p['sku'] . '.png') ?>" alt="<?= $p['name'] ?>" style="max-height:140px;object-fit:contain;">
                    <?php else: ?>
                        <i class="fa fa-box-open" style="font-size:3rem;color:var(--blue-light);"></i>
                    <?php endif; ?>
                    <?php if ($p['badge']): ?><span class="prod-badge"><?= $p['badge'] ?></span><?php endif; ?>
                    <span class="prod-stock <?= $p['stock'] ? 'in' : 'out' ?>"><?= $p['stock'] ? 'In Stock' : 'Out of Stock' ?></span>
                </div>
                <div class="prod-body">
                    <div class="prod-sku">SKU: <?= $p['sku'] ?></div>
                    <div class="prod-name"><?= $p['name'] ?></div>
                    <div class="prod-desc"><?= $p['desc'] ?></div>
                    <div class="prod-foot">
                        <span class="prod-price"><?= $p['price'] ?></span>
                        <a href="<?= base_url('contact') ?>" class="btn-q">
                            <?= $p['price'] === 'Request Quote' ? 'Get Quote' : 'Order Now' ?>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
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
            <?php
            $reasons = [
                ['icon'=>'fa-certificate',  'title'=>'FDA Certified Products', 'desc'=>'All products sourced from FDA-certified manufacturers ensuring safety and quality for every patient.'],
                ['icon'=>'fa-truck-fast',    'title'=>'Institutional Delivery', 'desc'=>'Direct delivery to hospitals, schools, barangays, and LGUs with reliable, on-time service.'],
                ['icon'=>'fa-rotate',        'title'=>'iRent Program',          'desc'=>'Rent medical equipment flexibly — ideal for post-surgery recovery and temporary institutional needs.'],
                ['icon'=>'fa-barcode',       'title'=>'iScan In-Store',         'desc'=>'Scan product barcodes in-store for instant pricing, specs, and availability info.'],
                ['icon'=>'fa-boxes-stacked', 'title'=>'500+ Products',          'desc'=>'Wide range across 10 categories — from basic PPE to specialized diagnostic equipment.'],
                ['icon'=>'fa-headset',       'title'=>'Dedicated Support',      'desc'=>'Personalized assistance for quotes, orders, and after-sales support from our knowledgeable team.'],
            ];
            foreach ($reasons as $r): ?>
            <div class="why-card reveal">
                <div class="why-ico"><i class="fa <?= $r['icon'] ?>"></i></div>
                <div class="why-title"><?= $r['title'] ?></div>
                <div class="why-desc"><?= $r['desc'] ?></div>
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
            <?php
            $services = [
                ['icon'=>'fa-truck-fast',  'title'=>'Institutional Delivery','desc'=>'We deliver directly to your institution. Bulk orders welcome for healthcare facilities across the Philippines.','list'=>['Hospitals &amp; Clinics','Schools &amp; Universities','Barangay Health Centers','Local Government Units']],
                ['icon'=>'fa-store',       'title'=>'In-Store Shopping',     'desc'=>'Visit our store at Ortega St. to browse the full catalog with the help of our knowledgeable staff.','list'=>['Full catalog in-store','Walk-in, no appointment','iScan barcode lookup','Multiple payment options']],
                ['icon'=>'fa-box-archive', 'title'=>'In-Store Pick Up',      'desc'=>'Order by phone or email and pick up at your convenience — ready within 24 hours.','list'=>['Order via phone/email','Ready within 24 hours','Pay upon pickup','Official receipt provided']],
            ];
            foreach ($services as $s): ?>
            <div class="svc-card reveal">
                <div class="svc-ico"><i class="fa <?= $s['icon'] ?>"></i></div>
                <h3 class="svc-title"><?= $s['title'] ?></h3>
                <p class="svc-desc"><?= $s['desc'] ?></p>
                <ul class="svc-list">
                    <?php foreach ($s['list'] as $item): ?>
                    <li><i class="fa fa-check-circle"></i> <?= $item ?></li>
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
            <?php
            $anns = [
                ['tag'=>'New Service','title'=>'iRent – Medical Equipment Rental',  'desc'=>'Flexible rentals for wheelchairs, nebulizers, oxygen concentrators, and more. Perfect for post-surgery and temporary institutional use.','date'=>'Available Now',     'bg'=>'linear-gradient(135deg,#1d3557,#457b9d)','icon'=>'fa-rotate'],
                ['tag'=>'New Feature','title'=>'iScan – Barcode Product Lookup',    'desc'=>'Walk-in customers can scan product barcodes at our in-store iScan station for instant pricing, specs, and availability.','date'=>'Available In-Store','bg'=>'linear-gradient(135deg,#c1121f,#e63946)','icon'=>'fa-barcode'],
                ['tag'=>'Compliance', 'title'=>'FDA License Renewed 2024',          'desc'=>'Robin Rose Trading remains fully FDA-licensed and BIR compliant. All products meet the latest Philippine healthcare regulatory standards.','date'=>'January 2024',     'bg'=>'linear-gradient(135deg,#457b9d,#a8dadc)','icon'=>'fa-certificate'],
            ];
            foreach ($anns as $a): ?>
            <div class="ann-card reveal">
                <div class="ann-top" style="background:<?= $a['bg'] ?>">
                    <i class="fa <?= $a['icon'] ?> ann-bg-icon"></i>
                    <span class="ann-tag"><?= $a['tag'] ?></span>
                    <div class="ann-title-text"><?= $a['title'] ?></div>
                </div>
                <div class="ann-body">
                    <p><?= $a['desc'] ?></p>
                    <div class="ann-date"><i class="fa fa-calendar"></i> <?= $a['date'] ?></div>
                </div>
            </div>
            <?php endforeach; ?>
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
        </div>
        <div class="test-grid">
            <?php
            $tests = [
                ['i'=>'R','name'=>'Dr. Reyes',           'org'=>'Iriga City Health Center',  'text'=>'"Robin Rose Trading has been our go-to supplier for medical consumables. Delivery is always on time and products are genuinely FDA certified."'],
                ['i'=>'M','name'=>'Kagawad Macaraeg',    'org'=>'Barangay Health Worker',     'text'=>'"Ordering PPE and wound care supplies for our barangay health station has never been easier. Very responsive team and competitive pricing!"'],
                ['i'=>'S','name'=>'School Nurse Santos', 'org'=>'Private Elementary School', 'text'=>'"The iRent service is a game-changer. We rent a nebulizer during flu season without buying one outright — saves our school clinic budget!"'],
            ];
            foreach ($tests as $t): ?>
            <div class="test-card reveal">
                <div class="stars">★★★★★</div>
                <div class="test-text"><?= $t['text'] ?></div>
                <div class="test-author">
                    <div class="test-av"><?= $t['i'] ?></div>
                    <div class="test-info"><strong><?= $t['name'] ?></strong><span><?= $t['org'] ?></span></div>
                </div>
            </div>
            <?php endforeach; ?>
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
                        <option>iRent Service</option>
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
        <a href="tel:09292379053"            class="btn btn-white"><i class="fa fa-phone"></i> Call Now</a>
        <a href="<?= base_url('contact') ?>" class="btn btn-outline-w"><i class="fa fa-envelope"></i> Full Inquiry Form</a>
    </div>
</div>

    <!-- PASTE YOUR ENTIRE HOME PAGE CODE HERE (FROM PROMO BAR TO CTA BANNER) -->
    <!-- Replace static categories with: foreach($categories as $cat) -->
<?= $this->endSection() ?>