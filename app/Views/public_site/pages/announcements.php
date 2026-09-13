<?= $this->extend('public_site/layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-hero">
    <h1>Announcements</h1>
    <p>Stay updated with the latest news, services, and updates from Robin Rose Trading</p>
    <div class="breadcrumb">
        <a href="<?= base_url('/') ?>">Home</a> <i class="fa fa-chevron-right"></i> Announcements
    </div>
</div>

<section class="section">
    <div class="sec-wrap">
        <div class="sec-head reveal">
            <div class="sec-tag">Latest</div>
            <h2 class="sec-title">News &amp; <span>Updates</span></h2>
            <p class="sec-sub">Official announcements, new services, and compliance updates from our team.</p>
        </div>

        <?php if(empty($announcements)): ?>
            <div style="text-align:center; padding:4rem 1rem; color:var(--gray);">
                <i class="fa fa-inbox" style="font-size:3rem; opacity:.25; margin-bottom:1rem; display:block;"></i>
                <p>No announcements at this time. Check back soon.</p>
            </div>
        <?php else: ?>
        <div class="ann-grid">
            <?php
                $iconMap = ['is_pinned' => 'fa-thumbtack'];
                $colorCycle = [
                    'linear-gradient(135deg,#1d3557,#457b9d)',
                    'linear-gradient(135deg,#c1121f,#e63946)',
                    'linear-gradient(135deg,#457b9d,#a8dadc)',
                ];
                $i = 0;
                foreach($announcements as $a):
                    $bg = $a['is_pinned'] ? 'linear-gradient(135deg,#c1121f,#e63946)' : $colorCycle[$i % count($colorCycle)];
                    $icon = $a['is_pinned'] ? 'fa-thumbtack' : 'fa-bullhorn';
                    $i++;
            ?>
            <div class="ann-card reveal">
                <div class="ann-top" style="background:<?= $bg ?>">
                    <?php if($a['image_path']): ?>
                        <img src="<?= base_url($a['image_path']) ?>" alt="" style="position:absolute; inset:0; width:100%; height:100%; object-fit:cover; opacity:.35;">
                    <?php endif; ?>
                    <i class="fa <?= $icon ?> ann-bg-icon"></i>
                    <span class="ann-tag"><?= $a['is_pinned'] ? 'Important' : 'Announcement' ?></span>
                    <div class="ann-title-text"><?= esc($a['title']) ?></div>
                </div>
                <div class="ann-body">
                    <p><?= esc($a['content']) ?></p>
                    <div class="ann-date"><i class="fa fa-calendar"></i> <?= date('F d, Y', strtotime($a['created_at'])) ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if($total_pages > 1): ?>
        <div style="display:flex; justify-content:center; gap:.5rem; margin-top:3rem;">
            <?php for($p = 1; $p <= $total_pages; $p++): ?>
                <a href="?page=<?= $p ?>" class="btn <?= $p == $current_page ? 'btn-red' : 'btn-outline' ?>" style="padding:.5rem 1rem; min-width:auto;"><?= $p ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>

        <?php endif; ?>
    </div>
</section>

<div class="cta-banner">
    <h2>Have a question about any of our updates?</h2>
    <p>Our team is ready to help with quotes, bulk orders, or general inquiries.</p>
    <div class="btns">
        <a href="tel:09292379053" class="btn btn-white"><i class="fa fa-phone"></i> Call Now</a>
        <a href="<?= base_url('contact') ?>" class="btn btn-outline-w"><i class="fa fa-envelope"></i> Contact Us</a>
    </div>
</div>

<?= $this->endSection() ?>