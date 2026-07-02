<?php $baseUrl = defined('BASE_URL') ? BASE_URL : '/'; ?>
<header class="site-header">
    <nav class="navbar container">
        <a class="brand" href="<?= $baseUrl; ?>">
            <img src="<?= $baseUrl; ?>public/images/logo.svg" alt="KebumenGo logo" class="brand-logo">
            <span class="brand-name">KebumenGo</span>
        </a>
        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="primary-menu" data-nav-toggle>
            <span></span>
            <span></span>
            <span></span>
        </button>
        <div class="nav-menu" id="primary-menu" data-nav-menu>
            <a class="nav-link" href="<?= $baseUrl; ?>" data-nav-link>Beranda</a>
            <a class="nav-link" href="<?= $baseUrl; ?>destinasi" data-nav-link>Destinasi</a>
            <a class="nav-link" href="<?= $baseUrl; ?>#tentang" data-nav-link>Tentang</a>
            <a class="btn btn-primary" href="<?= $baseUrl; ?>rekomendasi">Cari Rekomendasi</a>
        </div>
    </nav>
</header>
