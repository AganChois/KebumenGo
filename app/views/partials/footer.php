<?php 
$baseUrl = defined('BASE_URL') ? BASE_URL : '/'; 
$sysConfig = $systemConfig ?? [
    'web_name' => 'KebumenGo',
    'web_desc' => 'Platform wisata cerdas untuk Kebumen. Hitung budget, pilih destinasi, dan nikmati perjalanan tanpa ribet.',
    'contact_phone' => '081234567890',
    'contact_email' => 'info@kebumengo.id',
    'contact_address' => 'Jl. Merdeka 100, Kebumen',
];
?>
<footer class="site-footer">
    <div class="container footer-grid">
        <div class="footer-brand">
            <img src="<?= $baseUrl; ?>public/images/logo.svg" alt="<?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8'); ?> logo" class="brand-logo">
            <p><?= htmlspecialchars($sysConfig['web_desc'] ?? 'Platform wisata cerdas untuk Kebumen.', ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
        <div class="footer-links">
            <h4>Menu</h4>
            <a href="<?= $baseUrl; ?>">Beranda</a>
            <a href="<?= $baseUrl; ?>destinasi">Destinasi</a>
            <a href="<?= $baseUrl; ?>rekomendasi">Rekomendasi Budget</a>
        </div>
        <div class="footer-links">
            <h4>Kontak</h4>
            <p><?= htmlspecialchars($sysConfig['contact_email'] ?? 'info@kebumengo.id', ENT_QUOTES, 'UTF-8'); ?></p>
            <p><?= htmlspecialchars($sysConfig['contact_address'] ?? 'Jl. Merdeka 100, Kebumen', ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
        <div class="footer-links">
            <h4>Sosial</h4>
            <a href="#">Instagram</a>
            <a href="#">TikTok</a>
            <a href="#">YouTube</a>
        </div>
    </div>
    <div class="footer-bottom">
        <span>Copyright 2026 <?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8'); ?>. All rights reserved.</span>
    </div>
</footer>
