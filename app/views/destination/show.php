<?php
$pageTitle = 'Detail Destinasi';
$slug = $viewData['slug'] ?? 'pantai-logending';

try {
    $db = getDB();
    
    $stmt = $db->prepare("
        SELECT d.*, c.name as category, 
               COALESCE(AVG(r.rating), 0) as rating, 
               COUNT(r.id) as reviews
        FROM destinations d
        JOIN categories c ON d.category_id = c.id
        LEFT JOIN reviews r ON r.dest_id = d.id AND r.status = 'approved'
        WHERE d.slug = ? AND d.status = 'active'
        GROUP BY d.id
    ");
    $stmt->execute([$slug]);
    $destination = $stmt->fetch();

    if (!$destination) {
        redirect('destinasi'); // redirect if not found
    }

    $destination['facilities'] = !empty($destination['facilities']) ? json_decode($destination['facilities'], true) : [];
    $destination['tips'] = ['Patuhi peraturan setempat', 'Jaga kebersihan lokasi', 'Bawa barang secukupnya'];
    $destination['main_photo'] = !empty($destination['main_photo']) ? $destination['main_photo'] : 'public/images/placeholders/destination-placeholder.svg';

    $stmtReviews = $db->prepare("SELECT * FROM reviews WHERE dest_id = ? AND status = 'approved' ORDER BY created_at DESC LIMIT 5");
    $stmtReviews->execute([$destination['id']]);
    $reviewsList = $stmtReviews->fetchAll();

} catch (Exception $e) {
    error_log("DB Error: " . $e->getMessage());
    redirect('destinasi');
}

$baseUrl = defined('BASE_URL') ? BASE_URL : '/';

ob_start();
?>
<section class="section detail-hero" data-reveal>
    <div class="container detail-grid">
        <div class="detail-media">
            <div class="media-stack">
                <div class="media-main" style="background-image: url('<?= $baseUrl . htmlspecialchars($destination['main_photo'], ENT_QUOTES, 'UTF-8'); ?>'); background-size: cover; background-position: center;"></div>
                <div class="media-thumb" style="background-image: url('<?= $baseUrl . htmlspecialchars($destination['main_photo'], ENT_QUOTES, 'UTF-8'); ?>'); background-size: cover; background-position: center;"></div>
            </div>
        </div>
        <div class="detail-info">
            <span class="eyebrow"><?= htmlspecialchars($destination['category'], ENT_QUOTES, 'UTF-8'); ?></span>
            <h1><?= htmlspecialchars($destination['name'], ENT_QUOTES, 'UTF-8'); ?></h1>
            <div class="detail-meta">
                <span><?= htmlspecialchars($destination['location'] ?? 'Kebumen, Jawa Tengah', ENT_QUOTES, 'UTF-8'); /* ponytail: default location fallback */ ?></span>
                <span class="divider"></span>
                <span><?= number_format($destination['rating'], 1); ?> / 5 (<?= $destination['reviews']; ?>)</span>
            </div>
            <p><?= htmlspecialchars($destination['description'], ENT_QUOTES, 'UTF-8'); ?></p>
            <div class="price-breakdown">
                <div><span>Tiket Masuk</span><strong><?= formatRupiah($destination['ticket_price']); ?> / orang</strong></div>
                <div><span>Estimasi Makan</span><strong><?= formatRupiah($destination['est_food']); ?> / orang</strong></div>
                <div><span>Estimasi Parkir</span><strong><?= formatRupiah($destination['est_parking']); ?> / kendaraan</strong></div>
                <div class="total"><span>Total per Orang</span><strong><?= formatRupiah($destination['ticket_price'] + $destination['est_food']); ?></strong></div>
            </div>
            <div class="detail-actions">
                <a class="btn btn-primary" href="<?= $baseUrl; ?>rekomendasi">Hitung Budget</a>
                <a class="btn btn-outline" href="<?= $baseUrl; ?>destinasi">Lihat Destinasi Lain</a>
            </div>
        </div>
    </div>
</section>

<section class="section surface" data-reveal>
    <div class="container info-grid">
        <div class="info-card">
            <h3>Fasilitas</h3>
            <ul class="pill-list">
                <?php foreach ($destination['facilities'] as $facility): ?>
                    <li><?= htmlspecialchars($facility, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="info-card">
            <h3>Jam Operasional</h3>
            <p><?= htmlspecialchars($destination['operational_day'] ?? 'Senin - Minggu', ENT_QUOTES, 'UTF-8'); ?></p>
            <p><?= htmlspecialchars($destination['open_time'] ?? '07:00:00', ENT_QUOTES, 'UTF-8'); ?> - <?= htmlspecialchars($destination['close_time'] ?? '17:00:00', ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
        <div class="info-card">
            <h3>Tips perjalanan</h3>
            <ul class="tip-list">
                <?php foreach ($destination['tips'] as $tip): ?>
                    <li><?= htmlspecialchars($tip, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="info-card">
            <h3>Lokasi</h3>
            <div class="map-embed">
                <iframe src="<?= htmlspecialchars($destination['maps_embed'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Peta <?= htmlspecialchars($destination['name'], ENT_QUOTES, 'UTF-8'); ?>"></iframe>
            </div>
        </div>
    </div>
</section>

<section class="section" data-reveal>
    <div class="container review-grid">
        <div class="review-form">
            <h3>Tulis ulasan</h3>
            <form>
                <label>
                    Nama
                    <input type="text" placeholder="Nama kamu" />
                </label>
                <label>
                    Rating
                    <select>
                        <option>5</option>
                        <option>4</option>
                        <option>3</option>
                        <option>2</option>
                        <option>1</option>
                    </select>
                </label>
                <label>
                    Komentar
                    <textarea rows="4" placeholder="Bagikan pengalamanmu"></textarea>
                </label>
                <button class="btn btn-primary" type="button">Kirim Ulasan</button>
            </form>
        </div>
        <div class="review-list">
            <h3>Ulasan terbaru</h3>
            <?php if (empty($reviewsList)): ?>
                <p>Belum ada ulasan.</p>
            <?php else: ?>
                <?php foreach ($reviewsList as $rev): ?>
                    <article class="review-card">
                        <div class="review-header">
                            <strong><?= htmlspecialchars($rev['name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            <span><?= number_format($rev['rating'], 1); ?>/5</span>
                        </div>
                        <p><?= htmlspecialchars($rev['comment'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
