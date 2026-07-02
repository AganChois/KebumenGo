<?php
declare(strict_types=1);

// Ensure admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ' . (defined('BASE_URL') ? BASE_URL : '/') . 'admin/login');
    exit;
}

$db = getDB();
$baseUrl = defined('BASE_URL') ? BASE_URL : '/';

$type = $_GET['type'] ?? 'analitik';
$format = $_GET['format'] ?? 'pdf';

if (!in_array($type, ['analitik', 'destinasi', 'ulasan'])) {
    $type = 'analitik';
}
if (!in_array($format, ['pdf', 'excel'])) {
    $format = 'pdf';
}

// Load System Config for Kop Surat
$systemConfigFile = __DIR__ . '/../../../config/system.php';
$sysConfig = file_exists($systemConfigFile) ? require $systemConfigFile : [
    'web_name' => 'KebumenGo',
    'web_desc' => 'Panduan wisata terbaik di Kabupaten Kebumen.',
    'contact_phone' => '081234567890',
    'contact_email' => 'info@kebumengo.id',
    'contact_address' => 'Jl. Pahlawan No. 10, Kebumen, Jawa Tengah',
];

// Fetch Data based on Type
$reportTitle = '';
$headers = [];
$rows = [];

if ($type === 'analitik') {
    $reportTitle = 'Laporan Analitik & Performa Pariwisata';
    
    // Summary metrics
    $totalPageviews = (int)$db->query("SELECT COUNT(*) FROM page_views")->fetchColumn();
    $uniqueVisitors = (int)$db->query("SELECT COUNT(DISTINCT ip_address) FROM page_views")->fetchColumn();
    $bounces = (int)$db->query("
        SELECT COUNT(*) FROM (
            SELECT ip_address FROM page_views GROUP BY ip_address HAVING COUNT(*) = 1
        ) AS bounces
    ")->fetchColumn();
    $bounceRate = $uniqueVisitors > 0 ? round(($bounces / $uniqueVisitors) * 100, 1) : 0.0;
    
    // Traffic Sources
    $sourcesRaw = $db->query("
        SELECT 
            COALESCE(SUM(CASE WHEN referer IS NULL OR referer = '' THEN 1 ELSE 0 END), 0) as direct,
            COALESCE(SUM(CASE WHEN referer LIKE '%google%' OR referer LIKE '%bing%' OR referer LIKE '%yahoo%' OR referer LIKE '%duckduckgo%' THEN 1 ELSE 0 END), 0) as organic,
            COALESCE(SUM(CASE WHEN referer LIKE '%instagram%' OR referer LIKE '%tiktok%' OR referer LIKE '%facebook%' OR referer LIKE '%t.co%' OR referer LIKE '%twitter%' THEN 1 ELSE 0 END), 0) as social,
            COALESCE(SUM(CASE WHEN referer IS NOT NULL AND referer != '' AND referer NOT LIKE '%google%' AND referer NOT LIKE '%bing%' AND referer NOT LIKE '%yahoo%' AND referer NOT LIKE '%duckduckgo%' AND referer NOT LIKE '%instagram%' AND referer NOT LIKE '%tiktok%' AND referer NOT LIKE '%facebook%' AND referer NOT LIKE '%t.co%' AND referer NOT LIKE '%twitter%' THEN 1 ELSE 0 END), 0) as referral
        FROM page_views
    ")->fetch();
    
    // Daily Views (last 7 days)
    $weeklyViews = $db->query("
        SELECT DATE(created_at) as view_date, COUNT(*) as count 
        FROM page_views 
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
        GROUP BY DATE(created_at)
        ORDER BY DATE(created_at) DESC
    ")->fetchAll();

} elseif ($type === 'destinasi') {
    $reportTitle = 'Laporan Data Destinasi Wisata';
    
    $destinations = $db->query("
        SELECT d.id, d.name, c.name as category_name, d.ticket_price, d.status, d.created_at 
        FROM destinations d
        LEFT JOIN categories c ON d.category_id = c.id
        ORDER BY d.name ASC
    ")->fetchAll();

} elseif ($type === 'ulasan') {
    $reportTitle = 'Laporan Moderasi & Ulasan Pengunjung';
    
    $reviews = $db->query("
        SELECT r.id, d.name as destination_name, r.name as reviewer_name, r.rating, r.comment, r.status, r.created_at 
        FROM reviews r
        LEFT JOIN destinations d ON r.dest_id = d.id
        ORDER BY r.created_at DESC
    ")->fetchAll();
}

// Handle Excel File Export
if ($format === 'excel') {
    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=laporan-" . $type . "-" . date('Ymd-His') . ".xls");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private", false);
    
    // Output standard HTML Table which Excel imports seamlessly
    ?>
    <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
    <head>
        <meta http-equiv="content-type" content="application/vnd.ms-excel; charset=UTF-8">
        <style>
            .title { font-size: 16pt; font-weight: bold; text-align: center; }
            .subtitle { font-size: 10pt; color: #555555; text-align: center; }
            th { background-color: #1e3a8a; color: #ffffff; font-weight: bold; border: 1px solid #000000; }
            td { border: 1px solid #000000; }
            .header-info { font-weight: bold; }
        </style>
    </head>
    <body>
        <table>
            <tr>
                <td colspan="5" class="title"><?= htmlspecialchars($sysConfig['web_name'], ENT_QUOTES, 'UTF-8'); ?></td>
            </tr>
            <tr>
                <td colspan="5" class="subtitle"><?= htmlspecialchars($sysConfig['contact_address'], ENT_QUOTES, 'UTF-8'); ?> | Telp: <?= htmlspecialchars($sysConfig['contact_phone'], ENT_QUOTES, 'UTF-8'); ?></td>
            </tr>
            <tr><td colspan="5"></td></tr>
            <tr>
                <td colspan="5" style="font-size: 14pt; font-weight: bold; text-align: center;"><?= $reportTitle; ?></td>
            </tr>
            <tr>
                <td colspan="5" class="subtitle">Tanggal Cetak: <?= date('d F Y H:i:s'); ?></td>
            </tr>
            <tr><td colspan="5"></td></tr>
        </table>

        <?php if ($type === 'analitik'): ?>
            <table border="1">
                <tr>
                    <th colspan="2">Ringkasan Metrik</th>
                </tr>
                <tr>
                    <td>Total Halaman Dilihat (Pageviews)</td>
                    <td><?= $totalPageviews; ?></td>
                </tr>
                <tr>
                    <td>Pengunjung Unik (IP Unik)</td>
                    <td><?= $uniqueVisitors; ?></td>
                </tr>
                <tr>
                    <td>Bounce Rate</td>
                    <td><?= $bounceRate; ?>%</td>
                </tr>
            </table>
            <br>
            <table border="1">
                <tr>
                    <th colspan="2">Sumber Trafik</th>
                </tr>
                <tr>
                    <td>Langsung (Direct)</td>
                    <td><?= $sourcesRaw['direct']; ?> hit</td>
                </tr>
                <tr>
                    <td>Pencarian Organik (Google, Bing, dll.)</td>
                    <td><?= $sourcesRaw['organic']; ?> hit</td>
                </tr>
                <tr>
                    <td>Media Sosial (Instagram, Facebook, Twitter, dll.)</td>
                    <td><?= $sourcesRaw['social']; ?> hit</td>
                </tr>
                <tr>
                    <td>Rujukan (Referral)</td>
                    <td><?= $sourcesRaw['referral']; ?> hit</td>
                </tr>
            </table>
            <br>
            <table border="1">
                <tr>
                    <th>Tanggal</th>
                    <th>Jumlah Tayangan (Hits)</th>
                </tr>
                <?php foreach ($weeklyViews as $day): ?>
                    <tr>
                        <td><?= date('d-m-Y', strtotime($day['view_date'])); ?></td>
                        <td><?= $day['count']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>

        <?php elseif ($type === 'destinasi'): ?>
            <table border="1">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Destinasi</th>
                        <th>Kategori</th>
                        <th>Harga Tiket</th>
                        <th>Status</th>
                        <th>Tanggal Ditambahkan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($destinations as $dest): ?>
                        <tr>
                            <td><?= $dest['id']; ?></td>
                            <td><?= htmlspecialchars($dest['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($dest['category_name'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= $dest['ticket_price']; ?></td>
                            <td><?= $dest['status'] === 'active' ? 'Aktif' : 'Tidak Aktif'; ?></td>
                            <td><?= date('d-m-Y', strtotime($dest['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php elseif ($type === 'ulasan'): ?>
            <table border="1">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Destinasi</th>
                        <th>Nama Pengunjung</th>
                        <th>Rating</th>
                        <th>Komentar</th>
                        <th>Status</th>
                        <th>Tanggal Ulasan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reviews as $rev): ?>
                        <tr>
                            <td><?= $rev['id']; ?></td>
                            <td><?= htmlspecialchars($rev['destination_name'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($rev['reviewer_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= $rev['rating']; ?> / 5</td>
                            <td><?= htmlspecialchars($rev['comment'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= $rev['status'] === 'approved' ? 'Disetujui' : 'Pending'; ?></td>
                            <td><?= date('d-m-Y', strtotime($rev['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </body>
    </html>
    <?php
    exit;
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak Laporan - <?= htmlspecialchars($sysConfig['web_name'], ENT_QUOTES, 'UTF-8'); ?></title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #1e293b; margin: 0; padding: 20px; line-height: 1.5; }
        
        /* Kop Surat */
        .kop-surat { display: flex; align-items: center; justify-content: center; border-bottom: 4px double #0f172a; padding-bottom: 15px; margin-bottom: 30px; }
        .kop-logo-img { width: 70px; height: 70px; margin-right: 20px; object-fit: contain; }
        .kop-details { text-align: left; }
        .kop-name { font-size: 24px; font-weight: 800; text-transform: uppercase; color: #0f172a; margin: 0; }
        .kop-desc { font-size: 11px; color: #64748b; margin: 2px 0 0 0; font-style: italic; }
        .kop-contact { font-size: 11px; color: #334155; margin: 5px 0 0 0; font-weight: 500; }
        
        .report-title { text-align: center; font-size: 18px; font-weight: 800; margin: 20px 0; text-transform: uppercase; color: #0f172a; letter-spacing: 0.5px; }
        .print-date { text-align: center; font-size: 11px; color: #64748b; margin-top: -15px; margin-bottom: 25px; }
        
        /* Summary Grid */
        .summary-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 30px; }
        .summary-card { border: 1px solid #e2e8f0; background-color: #f8fafc; border-radius: 12px; padding: 15px; text-align: center; }
        .summary-val { font-size: 22px; font-weight: 700; color: #0f172a; margin-top: 5px; }
        .summary-label { font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 600; letter-spacing: 0.5px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 30px; }
        th, td { border: 1px solid #e2e8f0; padding: 10px 14px; text-align: left; font-size: 12px; }
        th { background-color: #f1f5f9; font-weight: 700; color: #0f172a; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; }
        tr:nth-child(even) { background-color: #f8fafc; }
        
        .badge { display: inline-block; padding: 3px 8px; border-radius: 6px; font-size: 10px; font-weight: 700; text-transform: uppercase; }
        .badge-active { background-color: #dcfce7; color: #166534; }
        .badge-inactive { background-color: #fee2e2; color: #991b1b; }
        
        .no-print-bar { background-color: #0f172a; color: #ffffff; padding: 12px 24px; display: flex; align-items: center; justify-content: space-between; border-radius: 12px; margin-bottom: 30px; }
        .btn-print { background-color: #2563eb; color: #ffffff; border: none; border-radius: 8px; padding: 8px 16px; font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: background-color 0.2s; }
        .btn-print:hover { background-color: #1d4ed8; }
        .btn-close { color: #94a3b8; text-decoration: none; font-size: 13px; }
        .btn-close:hover { color: #ffffff; }

        @media print {
            .no-print-bar { display: none; }
            body { padding: 0; }
            .summary-card { background-color: #ffffff !important; border: 1px solid #cbd5e1 !important; }
        }
    </style>
</head>
<body>
    <!-- Top Action Bar for PDF View -->
    <div class="no-print-bar">
        <span style="font-size: 14px; font-weight: 500;">Pratinjau Cetak Laporan</span>
        <div style="display: flex; align-items: center; gap: 15px;">
            <a href="javascript:window.history.back()" class="btn-close">Kembali</a>
            <button onclick="window.print()" class="btn-print">
                Cetak Laporan
            </button>
        </div>
    </div>

    <!-- Kop Surat (Letterhead) -->
    <div class="kop-surat">
        <img src="<?= $baseUrl; ?>public/images/logo.svg" alt="Logo" class="kop-logo-img">
        <div class="kop-details">
            <h1 class="kop-name"><?= htmlspecialchars($sysConfig['web_name'], ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="kop-desc"><?= htmlspecialchars($sysConfig['web_desc'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p class="kop-contact">
                Alamat: <?= htmlspecialchars($sysConfig['contact_address'], ENT_QUOTES, 'UTF-8'); ?> | 
                Telp: <?= htmlspecialchars($sysConfig['contact_phone'], ENT_QUOTES, 'UTF-8'); ?> | 
                Email: <?= htmlspecialchars($sysConfig['contact_email'], ENT_QUOTES, 'UTF-8'); ?>
            </p>
        </div>
    </div>

    <!-- Report Title -->
    <h2 class="report-title"><?= $reportTitle; ?></h2>
    <p class="print-date">Dicetak pada: <?= date('d F Y H:i:s'); ?></p>

    <!-- Report Content -->
    <?php if ($type === 'analitik'): ?>
        <div class="summary-grid">
            <div class="summary-card">
                <div class="summary-label">Total Tayangan Halaman</div>
                <div class="summary-val"><?= number_format($totalPageviews, 0, ',', '.'); ?></div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Pengunjung Unik</div>
                <div class="summary-val"><?= number_format($uniqueVisitors, 0, ',', '.'); ?></div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Rasio Pantulan (Bounce Rate)</div>
                <div class="summary-val"><?= $bounceRate; ?>%</div>
            </div>
        </div>

        <h3 style="font-size: 14px; font-weight: 700; color: #0f172a; margin-top: 30px;">Sumber Trafik Pengunjung</h3>
        <table>
            <thead>
                <tr>
                    <th>Sumber Acuan (Referer)</th>
                    <th style="width: 250px;">Total Hits</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Langsung (Direct / Tanpa Referer)</td>
                    <td style="font-weight: 600;"><?= number_format((int)$sourcesRaw['direct'], 0, ',', '.'); ?></td>
                </tr>
                <tr>
                    <td>Mesin Pencari Organik (Google, Bing, Yahoo)</td>
                    <td style="font-weight: 600;"><?= number_format((int)$sourcesRaw['organic'], 0, ',', '.'); ?></td>
                </tr>
                <tr>
                    <td>Jaringan Sosial (Instagram, TikTok, Twitter, Facebook)</td>
                    <td style="font-weight: 600;"><?= number_format((int)$sourcesRaw['social'], 0, ',', '.'); ?></td>
                </tr>
                <tr>
                    <td>Situs Rujukan Lainnya (Referral)</td>
                    <td style="font-weight: 600;"><?= number_format((int)$sourcesRaw['referral'], 0, ',', '.'); ?></td>
                </tr>
            </tbody>
        </table>

        <h3 style="font-size: 14px; font-weight: 700; color: #0f172a; margin-top: 30px;">Tren Kunjungan Harian (7 Hari Terakhir)</h3>
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Jumlah Tayangan Halaman (Hits)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($weeklyViews as $day): ?>
                    <tr>
                        <td style="font-weight: 500;"><?= date('l, d F Y', strtotime($day['view_date'])); ?></td>
                        <td style="font-weight: 600; color: #2563eb;"><?= $day['count']; ?> hit</td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($weeklyViews)): ?>
                    <tr>
                        <td colspan="2" style="text-align: center; color: #64748b;">Belum ada aktivitas kunjungan tercatat.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

    <?php elseif ($type === 'destinasi'): ?>
        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">ID</th>
                    <th>Nama Destinasi</th>
                    <th>Kategori</th>
                    <th>Harga Tiket</th>
                    <th style="width: 100px;">Status</th>
                    <th>Tanggal Ditambahkan</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($destinations as $dest): ?>
                    <tr>
                        <td><?= $dest['id']; ?></td>
                        <td style="font-weight: 600;"><?= htmlspecialchars($dest['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= htmlspecialchars($dest['category_name'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= formatRupiah($dest['ticket_price']); ?></td>
                        <td>
                            <span class="badge <?= $dest['status'] === 'active' ? 'badge-active' : 'badge-inactive'; ?>">
                                <?= $dest['status'] === 'active' ? 'Aktif' : 'Tidak Aktif'; ?>
                            </span>
                        </td>
                        <td><?= date('d M Y H:i', strtotime($dest['created_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($destinations)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: #64748b;">Belum ada destinasi tersimpan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

    <?php elseif ($type === 'ulasan'): ?>
        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">ID</th>
                    <th>Destinasi Wisata</th>
                    <th>Nama Pengunjung</th>
                    <th style="width: 80px;">Rating</th>
                    <th>Komentar Ulasan</th>
                    <th style="width: 100px;">Status</th>
                    <th>Tanggal Ulasan</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reviews as $rev): ?>
                    <tr>
                        <td><?= $rev['id']; ?></td>
                        <td style="font-weight: 600;"><?= htmlspecialchars($rev['destination_name'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= htmlspecialchars($rev['reviewer_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td style="font-weight: 700; color: #ea580c;">★ <?= $rev['rating']; ?> / 5</td>
                        <td style="font-style: italic; color: #334155;">"<?= htmlspecialchars($rev['comment'], ENT_QUOTES, 'UTF-8'); ?>"</td>
                        <td>
                            <span class="badge <?= $rev['status'] === 'approved' ? 'badge-active' : 'badge-inactive'; ?>">
                                <?= $rev['status'] === 'approved' ? 'Disetujui' : 'Pending'; ?>
                            </span>
                        </td>
                        <td><?= date('d M Y H:i', strtotime($rev['created_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($reviews)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: #64748b;">Belum ada ulasan yang dibuat oleh pengunjung.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- Signature / Footer kop -->
    <div style="margin-top: 50px; display: flex; justify-content: flex-end; text-align: center; font-size: 12px;">
        <div style="width: 200px;">
            <p>Kebumen, <?= date('d F Y'); ?></p>
            <p style="font-weight: 600; margin-bottom: 60px;">Administrator Panel</p>
            <p style="text-decoration: underline; font-weight: 700;"><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin Kebumen', ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
    </div>

    <script>
        // Automatically open the print dialog when the page loads
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 600);
        };
    </script>
</body>
</html>
