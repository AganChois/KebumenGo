<?php
$pageTitle = 'Detail Destinasi';
$slug = $viewData['slug'] ?? 'pantai-logending';

try {
    $db = getDB();
    
    $stmt = $db->prepare("
        SELECT d.*, c.name as category, c.slug as category_slug,
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

    // Handle review submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrfToken($token)) {
            setFlash('error', 'Token keamanan tidak valid. Silakan coba lagi.');
            redirect('destinasi/' . $slug);
        }

        $name = sanitize($_POST['name'] ?? '');
        $rating = max(1, min(5, (int)($_POST['rating'] ?? 5)));
        $comment = sanitize($_POST['comment'] ?? '');

        if (empty($name) || empty($comment)) {
            setFlash('error', 'Nama dan ulasan wajib diisi.');
        } else {
            try {
                $stmtInsert = $db->prepare("INSERT INTO reviews (dest_id, name, rating, comment, status) VALUES (?, ?, ?, ?, 'pending')");
                $stmtInsert->execute([$destination['id'], $name, $rating, $comment]);
                setFlash('success', 'Ulasan Anda berhasil dikirim dan sedang menunggu moderasi.');
            } catch (Exception $e) {
                setFlash('error', 'Gagal mengirim ulasan: ' . $e->getMessage());
            }
        }
        redirect('destinasi/' . $slug);
    }

    $destination['facilities'] = !empty($destination['facilities']) ? json_decode($destination['facilities'], true) : [];
    $destination['tips'] = ['Patuhi peraturan setempat', 'Jaga kebersihan lokasi', 'Bawa barang secukupnya'];
    $destination['main_photo'] = !empty($destination['main_photo']) ? $destination['main_photo'] : 'public/images/placeholders/destination-placeholder.svg';

    $stmtReviews = $db->prepare("SELECT * FROM reviews WHERE dest_id = ? AND status = 'approved' ORDER BY created_at DESC LIMIT 3");
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
            <div class="media-main" style="background-image: url('<?= $baseUrl . htmlspecialchars($destination['main_photo'], ENT_QUOTES, 'UTF-8'); ?>'); background-size: cover; background-position: center;"></div>
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
                <a class="btn btn-primary" href="<?= $baseUrl; ?>rekomendasi?budget=<?= $destination['ticket_price'] + $destination['est_food'] + $destination['est_parking']; ?>&orang=1&kategori=<?= $destination['category_slug']; ?>">Hitung Budget</a>
                <a class="btn btn-outline" href="<?= $baseUrl; ?>destinasi">Lihat Destinasi Lain</a>
            </div>
        </div>
    </div>
</section>

<section class="section surface" data-reveal>
    <div class="container info-grid">
        <div class="info-card">
            <h3 style="margin-bottom: 16px;">Fasilitas</h3>
            <ul class="pill-list">
                <?php foreach ($destination['facilities'] as $facility): ?>
                    <li><?= htmlspecialchars($facility, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="info-card">
            <h3 style="margin-bottom: 16px;">Jam Operasional</h3>
            <p style="font-weight: 600; color: var(--text-primary);"><?= htmlspecialchars($destination['operational_day'] ?? 'Senin - Minggu', ENT_QUOTES, 'UTF-8'); ?></p>
            <p style="color: var(--text-secondary); font-size: 14px; margin-top: 4px;">
                <?= date('H:i', strtotime($destination['open_time'] ?? '07:00:00')); ?> - <?= date('H:i', strtotime($destination['close_time'] ?? '17:00:00')); ?> WIB
            </p>
        </div>
        <div class="info-card">
            <h3 style="margin-bottom: 16px;">Tips Perjalanan</h3>
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
            <?php if ($success = getFlash('success')): ?>
                <div class="alert alert-success" style="background: #DEF7EC; color: #03543F; padding: 12px; border-radius: 8px; margin-bottom: 16px; font-weight: 500; font-size: 14px;">
                    <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>
            <?php if ($error = getFlash('error')): ?>
                <div class="alert alert-error" style="background: #FDE8E8; color: #9B1C1C; padding: 12px; border-radius: 8px; margin-bottom: 16px; font-weight: 500; font-size: 14px;">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>
            <form method="post" action="<?= $baseUrl; ?>destinasi/<?= htmlspecialchars($slug, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="csrf_token" value="<?= csrfToken(); ?>">
                <label>
                    Nama
                    <input type="text" name="name" placeholder="Nama kamu" required />
                </label>
                <label>
                    Rating
                    <select name="rating">
                        <option value="5">5</option>
                        <option value="4">4</option>
                        <option value="3">3</option>
                        <option value="2">2</option>
                        <option value="1">1</option>
                    </select>
                </label>
                <label>
                    Komentar
                    <textarea name="comment" rows="4" placeholder="Bagikan pengalamanmu" required></textarea>
                </label>
                <button class="btn btn-primary" type="submit">Kirim Ulasan</button>
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
