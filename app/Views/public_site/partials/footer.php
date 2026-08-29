<footer class="footer">
    <div class="footer-container">
        <div class="footer-grid">
            <div class="footer-brand">
                <img src="<?= base_url('images/logo.png') ?>" alt="Robin Rose Trading" class="footer-logo">
                <p class="footer-tagline">Your Ultimate Healthcare Partner</p>
                <p class="footer-desc">Providing quality medical supplies and healthcare solutions to hospitals, clinics, schools, and communities across the Philippines.</p>
                <div class="footer-social">
                    <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-viber"></i></a>
                    <a href="mailto:Redrosalinda1876@gmail.com" class="social-icon"><i class="fa fa-envelope"></i></a>
                </div>
            </div>
            <div class="footer-links">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="<?= base_url('/') ?>">Home</a></li>
                    <li><a href="<?= base_url('products') ?>">Products</a></li>
                    <li><a href="<?= base_url('services') ?>">Services</a></li>
                    <li><a href="<?= base_url('about') ?>">About Us</a></li>
                    <li><a href="<?= base_url('announcements') ?>">Announcements</a></li>
                    <li><a href="<?= base_url('portal') ?>">Client Portal</a></li>
                </ul>
            </div>
            <div class="footer-categories">
                <h4>Product Categories</h4>
                <ul>
                    <li><a href="<?= base_url('products?cat=diagnostic') ?>">Diagnostic Equipment</a></li>
                    <li><a href="<?= base_url('products?cat=otc') ?>">OTC Medicines</a></li>
                    <li><a href="<?= base_url('products?cat=respiratory') ?>">Respiratory Care</a></li>
                    <li><a href="<?= base_url('products?cat=ppe') ?>">PPE & Infection Control</a></li>
                    <li><a href="<?= base_url('products?cat=wound') ?>">Wound Care</a></li>
                    <li><a href="<?= base_url('products?cat=mobility') ?>">Mobility & Rehab</a></li>
                </ul>
            </div>
            <div class="footer-contact">
                <h4>Contact Us</h4>
                <div class="contact-item"><i class="fa fa-phone"></i><span>09292379053</span></div>
                <div class="contact-item"><i class="fa fa-envelope"></i><span>Redrosalinda1876@gmail.com</span></div>
                <div class="contact-item"><i class="fa fa-location-dot"></i><span>Ortega St., Philippines</span></div>
                <div class="footer-badges">
                    <span class="badge"><i class="fa fa-certificate"></i> FDA Certified</span>
                    <span class="badge"><i class="fa fa-file-invoice"></i> BIR Compliant</span>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> Robin Rose Trading. All Rights Reserved.</p>
            <p>Designed with <i class="fa fa-heart" style="color:#e63946"></i> for Healthcare</p>
        </div>
    </div>
</footer>