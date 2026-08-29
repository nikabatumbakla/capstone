<?= $this->extend('public_site/layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-hero">
    <h1>Contact &amp; Inquiries</h1>
    <p>Reach out for quotes, orders, or any healthcare supply questions</p>
    <div class="breadcrumb">
        <a href="<?= base_url('/') ?>">Home</a> <i class="fa fa-chevron-right"></i> Contact
    </div>
</div>

<section class="section">
    <div class="section-container">
        <div class="contact-grid">
            <!-- Contact Info -->
            <div class="contact-info">
                <div class="contact-info-card">
                    <h3 style="font-family:var(--font-display);color:var(--blue);margin-bottom:1.5rem;">Get in Touch</h3>
                    <div class="contact-method">
                        <div class="contact-icon-wrap"><i class="fa fa-phone"></i></div>
                        <div>
                            <span>Phone / Viber</span>
                            <strong><a href="tel:09292379053" style="color:var(--blue)">09292379053</a></strong>
                        </div>
                    </div>
                    <div class="contact-method">
                        <div class="contact-icon-wrap"><i class="fa fa-envelope"></i></div>
                        <div>
                            <span>Email Address</span>
                            <strong><a href="mailto:Redrosalinda1876@gmail.com" style="color:var(--blue)">Redrosalinda1876@gmail.com</a></strong>
                        </div>
                    </div>
                    <div class="contact-method">
                        <div class="contact-icon-wrap"><i class="fa fa-location-dot"></i></div>
                        <div>
                            <span>Store / Office Address</span>
                            <strong>Ortega St., Philippines</strong>
                        </div>
                    </div>
                    <div class="contact-method">
                        <div class="contact-icon-wrap"><i class="fa fa-clock"></i></div>
                        <div>
                            <span>Business Hours</span>
                            <strong>Mon – Sat: 8:00 AM – 6:00 PM</strong>
                        </div>
                    </div>
                </div>

                <!-- Social Media -->
                <div class="contact-info-card">
                    <h3 style="font-family:var(--font-display);color:var(--blue);margin-bottom:1rem;">Follow Us</h3>
                    <div style="display:flex;gap:0.8rem;flex-wrap:wrap;">
                        <a href="#" style="display:flex;align-items:center;gap:8px;padding:0.6rem 1rem;background:var(--cream);border-radius:8px;color:var(--blue);font-size:0.88rem;font-weight:600;">
                            <i class="fab fa-facebook-f" style="color:#1877f2"></i> Facebook
                        </a>
                        <a href="#" style="display:flex;align-items:center;gap:8px;padding:0.6rem 1rem;background:var(--cream);border-radius:8px;color:var(--blue);font-size:0.88rem;font-weight:600;">
                            <i class="fab fa-instagram" style="color:#e1306c"></i> Instagram
                        </a>
                        <a href="tel:09292379053" style="display:flex;align-items:center;gap:8px;padding:0.6rem 1rem;background:var(--cream);border-radius:8px;color:var(--blue);font-size:0.88rem;font-weight:600;">
                            <i class="fab fa-viber" style="color:#7360f2"></i> Viber
                        </a>
                    </div>
                </div>

                <!-- Map -->
                <div class="contact-info-card">
                    <h3 style="font-family:var(--font-display);color:var(--blue);margin-bottom:1rem;"><i class="fa fa-map-location-dot" style="color:var(--red)"></i> Our Location</h3>
                    <iframe
                        class="map-embed"
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d62074.23145870218!2d123.38604499999999!3d13.4245!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x33a1b1e5a31b6d5b%3A0x1234567890abcdef!2sIriga%20City%2C%20Camarines%20Sur!5e0!3m2!1sen!2sph!4v1234567890"
                        allowfullscreen="" loading="lazy">
                    </iframe>
                    <p style="font-size:0.82rem;color:var(--gray);margin-top:0.5rem;">Ortega St., Iriga City, Camarines Sur, Philippines</p>
                </div>
            </div>

            <!-- Inquiry Form -->
            <div class="contact-form-card">
                <h2 class="form-title"><i class="fa fa-paper-plane" style="color:var(--red)"></i> Send Us a Message</h2>
                <?php if (session()->getFlashdata('success')): ?>
                <div style="background:rgba(40,167,69,0.1);border:1px solid #28a745;color:#28a745;padding:1rem;border-radius:8px;margin-bottom:1.5rem;">
                    <i class="fa fa-check-circle"></i> <?= session()->getFlashdata('success') ?>
                </div>
                <?php endif; ?>
                <form action="<?= base_url('contact/submit') ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="form-row">
                        <div class="form-group">
                            <label>First Name *</label>
                            <input type="text" name="first_name" required placeholder="Juan">
                        </div>
                        <div class="form-group">
                            <label>Last Name *</label>
                            <input type="text" name="last_name" required placeholder="Dela Cruz">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Email Address *</label>
                            <input type="email" name="email" required placeholder="email@example.com">
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="text" name="phone" placeholder="09XXXXXXXXX">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Institution / Organization</label>
                        <input type="text" name="institution" placeholder="Hospital, School, Barangay, etc.">
                    </div>
                    <div class="form-group">
                        <label>Inquiry Type *</label>
                        <select name="inquiry_type" required>
                            <option value="">-- Select Inquiry Type --</option>
                            <option>Request a Quote</option>
                            <option>Product Information</option>
                            <option>Bulk Order / Partnership</option>
                            <option>iRent Service</option>
                            <option>iScan Inquiry</option>
                            <option>Delivery / Shipping</option>
                            <option>Client Account Setup</option>
                            <option>General Inquiry</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Products Needed (if applicable)</label>
                        <input type="text" name="products" placeholder="e.g. 100 boxes surgical masks, BP monitor x5">
                    </div>
                    <div class="form-group">
                        <label>Message *</label>
                        <textarea name="message" required placeholder="Describe your inquiry in detail..."></textarea>
                    </div>
                    <button type="submit" class="btn-primary" style="width:100%;justify-content:center;">
                        <i class="fa fa-paper-plane"></i> Send Inquiry
                    </button>
                </form>
            </div>
        </div>

        <!-- Quick Contact Table -->
        <div style="margin-top:3rem;">
            <h3 style="font-family:var(--font-display);color:var(--blue);margin-bottom:1rem;">Quick <span style="color:var(--red)">Contact Reference</span></h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Contact Method</th>
                        <th>Details</th>
                        <th>Best For</th>
                        <th>Response Time</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><i class="fa fa-phone" style="color:var(--red)"></i> Phone / Viber</td>
                        <td>09292379053</td>
                        <td>Urgent orders, quick questions</td>
                        <td><span class="status-badge status-delivered">Immediate</span></td>
                    </tr>
                    <tr>
                        <td><i class="fa fa-envelope" style="color:var(--red)"></i> Email</td>
                        <td>Redrosalinda1876@gmail.com</td>
                        <td>Quotations, formal orders, documents</td>
                        <td><span class="status-badge status-shipped">Within 24 hours</span></td>
                    </tr>
                    <tr>
                        <td><i class="fa fa-store" style="color:var(--red)"></i> Walk-in</td>
                        <td>Ortega St. Store</td>
                        <td>In-store shopping, iScan, same-day purchase</td>
                        <td><span class="status-badge status-delivered">Immediate</span></td>
                    </tr>
                    <tr>
                        <td><i class="fa fa-file-alt" style="color:var(--red)"></i> Online Inquiry Form</td>
                        <td>This page</td>
                        <td>Detailed inquiries, product lists, partnerships</td>
                        <td><span class="status-badge status-shipped">Within 24 hours</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

    <!-- PASTE YOUR ENTIRE ABOUT US CODE HERE -->
<?= $this->endSection() ?>