<?php
$pageTitle = 'Beranda';
$categories = [
    ['name' => 'Pantai', 'slug' => 'pantai', 'pill_class' => 'pill-1'],
    ['name' => 'Goa', 'slug' => 'goa', 'pill_class' => 'pill-2'],
    ['name' => 'Sejarah', 'slug' => 'sejarah', 'pill_class' => 'pill-3'],
    ['name' => 'Kuliner', 'slug' => 'kuliner', 'pill_class' => 'pill-4'],
    ['name' => 'Alam', 'slug' => 'alam', 'pill_class' => 'pill-5'],
    ['name' => 'Buatan', 'slug' => 'buatan', 'pill_class' => 'pill-6'],
];

try {
    $db = getDB();
    $stmt = $db->query("
        SELECT d.*, c.name as category, 
               COALESCE(AVG(r.rating), 0) as rating, 
               COUNT(r.id) as reviews,
               IF(d.ticket_price < 20000, 'Murah', NULL) as badge
        FROM destinations d
        JOIN categories c ON d.category_id = c.id
        LEFT JOIN reviews r ON r.dest_id = d.id AND r.status = 'approved'
        WHERE d.status = 'active'
        GROUP BY d.id
        ORDER BY rating DESC
        LIMIT 6
    ");
    $popularDestinations = $stmt->fetchAll();
    
    if (empty($popularDestinations)) {
        $popularDestinations = [];
    }
} catch (Exception $e) {
    $popularDestinations = [];
    error_log("DB Error: " . $e->getMessage());
}

$itineraries = [
    ['title' => 'Trip 1 hari Pantai Selatan', 'time' => '08:00 - 16:00', 'budget' => 120000, 'notes' => 'Pantai Logending, Karang Bolong, kuliner sore', 'category' => 'pantai'],
    ['title' => 'Half day Goa & Sejarah', 'time' => '09:00 - 13:00', 'budget' => 90000, 'notes' => 'Goa Jatijajar + Benteng Van der Wijck', 'category' => 'semua'],
    ['title' => 'Kuliner malam Kebumen', 'time' => '18:00 - 21:00', 'budget' => 75000, 'notes' => 'Sate Ambal, jajanan lokal, kopi tradisional', 'category' => 'kuliner'],
];

$testimonials = [
    ['name' => 'Reza', 'role' => 'Backpacker', 'comment' => 'Budgetku pas banget. Rekomendasinya akurat dan gampang dipakai.', 'rating' => 4.8],
    ['name' => 'Budi', 'role' => 'Kepala Keluarga', 'comment' => 'Bisa hitung biaya untuk 4 orang dengan cepat. Hemat waktu.', 'rating' => 4.7],
    ['name' => 'Citra', 'role' => 'Road Tripper', 'comment' => 'Cocok buat mampir singkat. Destinasinya jelas dan rapi.', 'rating' => 4.6],
];

$baseUrl = defined('BASE_URL') ? BASE_URL : '/';

ob_start();
?>
<section class="hero" data-reveal>
    <div class="container hero-grid">
        <div class="hero-copy">
            <span class="eyebrow">Wisata cerdas, budget tepat</span>
            <h1>Rencanakan wisata Kebumen sesuai budget, tanpa ribet.</h1>
            <p>KebumenGo membantu kamu memilih destinasi terbaik berdasarkan biaya tiket, makan, dan parkir. Semua transparan sebelum berangkat.</p>
            <?php include __DIR__ . '/../partials/hero-search.php'; ?>
            <div class="hero-stats">
                <div>
                    <strong>30+</strong>
                    <span>Destinasi terkurasi</span>
                </div>
                <div>
                    <strong>2 menit</strong>
                    <span>Rata-rata waktu rencana</span>
                </div>
                <div>
                    <strong>4.8/5</strong>
                    <span>Rating kepuasan</span>
                </div>
            </div>
        </div>
        <div class="hero-collage" aria-hidden="true">
            <div class="hero-tile tile-1" style="background-image: url('<?= $baseUrl; ?>public/images/destinations/pantai-selatan.jpg'); background-size: cover; background-position: center;">Pantai Selatan</div>
            <div class="hero-tile tile-2" style="background-image: url('<?= $baseUrl; ?>public/images/destinations/goa.jpg'); background-size: cover; background-position: center;">Goa Eksotis</div>
            <div class="hero-tile tile-3" style="background-image: url('<?= $baseUrl; ?>public/images/destinations/kuliner.jpg'); background-size: cover; background-position: center;">Kuliner Lokal</div>
            <div class="hero-tile tile-4" style="background-image: url('<?= $baseUrl; ?>public/images/destinations/hills.jpg'); background-size: cover; background-position: center;">Sunrise Hills</div>
        </div>
    </div>
</section>

<section class="section" id="kategori" data-reveal>
    <div class="container">
        <div class="section-head">
            <h2>Jelajahi berdasarkan kategori</h2>
            <p>Pilih gaya liburan yang kamu inginkan. Pantai, goa, kuliner, semua ada di sini.</p>
        </div>
        <div class="category-grid">
            <?php foreach ($categories as $category): ?>
                <?php include __DIR__ . '/../partials/category-pill.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section surface" data-reveal>
    <div class="container">
        <div class="section-head">
            <h2>Destinasi populer minggu ini</h2>
            <p>Rekomendasi paling sering dipilih wisatawan dengan budget hemat.</p>
        </div>
        <div class="destination-grid">
            <?php foreach ($popularDestinations as $destination): ?>
                <?php include __DIR__ . '/../partials/destination-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section promo" id="tentang" data-reveal>
    <div class="container promo-grid">
        <div class="promo-copy">
            <span class="eyebrow">Kenapa KebumenGo</span>
            <h2>Semua biaya transparan sejak awal.</h2>
            <p>Kami menggabungkan tiket masuk, makan, dan parkir untuk setiap destinasi. Jadi kamu bisa fokus menikmati perjalanan.</p>
            <div class="promo-features">
                <div class="promo-card">
                    <h3>Wisata gratis</h3>
                    <p>Temukan spot tanpa tiket masuk dan hemat lebih banyak.</p>
                </div>
                <div class="promo-card">
                    <h3>Kuliner murah</h3>
                    <p>Rekomendasi makanan lokal dengan harga bersahabat.</p>
                </div>
            </div>
        </div>
        <div class="promo-visual">
            <div>
                <span class="label">Budget Planner</span>
                <strong>Mulai dari Rp 50.000</strong>
            </div>
            <div>
                <span class="label">Rute Cepat</span>
                <strong>Half day trip</strong>
            </div>
        </div>
    </div>
</section>

<section class="section" data-reveal>
    <div class="container">
        <div class="section-head">
            <h2>Ulasan pengunjung</h2>
            <p>Cerita singkat dari wisatawan yang sudah mencoba KebumenGo.</p>
        </div>
        <div class="testimonial-track">
            <?php foreach ($testimonials as $testimonial): ?>
                <article class="testimonial-card">
                    <div class="testimonial-header">
                        <div>
                            <h4><?= htmlspecialchars($testimonial['name'], ENT_QUOTES, 'UTF-8'); ?></h4>
                            <span><?= htmlspecialchars($testimonial['role'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <span class="rating-badge"><?= number_format($testimonial['rating'], 1); ?>/5</span>
                    </div>
                    <p><?= htmlspecialchars($testimonial['comment'], ENT_QUOTES, 'UTF-8'); ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section surface" data-reveal>
    <div class="container">
        <div class="section-head">
            <h2>Rencana cepat untuk berbagai tipe traveler</h2>
            <p>Pilih itinerary instan sesuai waktu dan budget. Tinggal cek dan jalan.</p>
        </div>
        <div class="itinerary-grid">
            <?php foreach ($itineraries as $itinerary): ?>
                <article class="itinerary-card">
                    <div>
                        <span class="eyebrow"><?= htmlspecialchars($itinerary['time'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <h3><?= htmlspecialchars($itinerary['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p><?= htmlspecialchars($itinerary['notes'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                    <div class="itinerary-meta">
                        <span class="badge badge-accent">Budget <?= formatRupiah($itinerary['budget']); ?></span>
                        <a class="btn btn-outline" href="<?= $baseUrl; ?>rekomendasi?budget=<?= $itinerary['budget']; ?>&orang=1&kategori=<?= $itinerary['category']; ?>">Cek rekomendasi</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section cta" data-reveal>
    <div class="container cta-card">
        <div>
            <span class="eyebrow">Siap berangkat?</span>
            <h2>Mulai rencanakan wisata Kebumen hari ini.</h2>
            <p>Masukkan budget kamu dan dapatkan daftar destinasi yang cocok untuk semua anggota rombongan.</p>
        </div>
        <a class="btn btn-primary" href="<?= $baseUrl; ?>rekomendasi">Mulai hitung budget</a>
    </div>
</section>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
