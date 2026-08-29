<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Robin Rose Trading - Your Ultimate Healthcare Partner' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('public/css/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('public/css/main.css') ?>">
    <link rel="icon" type="image/png" href="<?= base_url('public/images/logo.png') ?>">
    <script src="<?= base_url('public/js/main.js') ?>"></script>
</head>
<body>

<!-- NAVBAR PARTIAL -->
<?= view('public_site/partials/navbar') ?>

<main>
    <?= $this->renderSection('content') ?>
</main>

<!-- FOOTER PARTIAL -->
<?= view('public_site/partials/footer') ?>

<script>
const obs = new IntersectionObserver(entries => {
    entries.forEach(e => { if (e.isIntersecting) e.target.classList.add("visible"); });
}, { threshold: 0.08 });
document.querySelectorAll(".reveal").forEach(el => obs.observe(el));
</script>

</body>
</html>