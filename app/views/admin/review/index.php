<?php
$baseUrl = defined('BASE_URL') ? BASE_URL : '/';
$currentPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '', '/');
$isDashboard = $currentPath === 'admin/dashboard';
$isAnalitik = str_starts_with($currentPath, 'admin/analitik');
$isDestinasi = str_starts_with($currentPath, 'admin/destinasi') || str_starts_with($currentPath, 'admin/kategori');
$isUlasan = str_starts_with($currentPath, 'admin/ulasan');
$isPengaturan = str_starts_with($currentPath, 'admin/pengaturan');

function navClass(bool $active, string $extra = ''): string
{
    $base = $active
        ? 'flex items-center gap-3 rounded-[10px] bg-textPrimary px-3 py-2 text-white'
        : 'flex items-center gap-3 rounded-[10px] px-3 py-2 text-textSecondary hover:bg-surface';

    return trim($base . ' ' . $extra);
}

try {
    $db = getDB();
    
    // Pagination parameters
    $limit = 10;
    $page = max(1, (int)($_GET['page'] ?? 1));
    $offset = ($page - 1) * $limit;
    
    // Filter and search parameters
    $search = sanitize($_GET['search'] ?? '');
    $statusFilter = strtolower(trim($_GET['status'] ?? ''));
    $ratingFilter = (int)($_GET['rating'] ?? 0);
    
    // Build WHERE conditions
    $conditions = [];
    $params = [];
    
    if ($search !== '') {
        $conditions[] = "(r.name LIKE ? OR r.comment LIKE ? OR d.name LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if ($statusFilter !== '') {
        $conditions[] = "r.status = ?";
        $params[] = $statusFilter;
    }
    
    if ($ratingFilter >= 1 && $ratingFilter <= 5) {
        $conditions[] = "r.rating = ?";
        $params[] = $ratingFilter;
    }
    
    $whereClause = '';
    if (!empty($conditions)) {
        $whereClause = 'WHERE ' . implode(' AND ', $conditions);
    }
    
    // Count total matching reviews
    $totalCountQuery = "
        SELECT COUNT(*) 
        FROM reviews r 
        JOIN destinations d ON r.dest_id = d.id
        $whereClause
    ";
    $totalCountStmt = $db->prepare($totalCountQuery);
    $totalCountStmt->execute($params);
    $totalReviews = (int)$totalCountStmt->fetchColumn();
    $totalPages = (int)ceil($totalReviews / $limit);
    
    // Fetch paginated reviews
    $sql = "
        SELECT r.id, COALESCE(u.name, r.name) as name, d.name as destination, r.rating, 
               r.status, DATE_FORMAT(r.created_at, '%d %b %Y') as date, r.comment
        FROM reviews r
        JOIN destinations d ON r.dest_id = d.id
        LEFT JOIN users u ON r.user_id = u.id
        $whereClause
        ORDER BY r.created_at DESC
        LIMIT ? OFFSET ?
    ";
    
    $stmt = $db->prepare($sql);
    $paramIndex = 1;
    foreach ($params as $paramValue) {
        $stmt->bindValue($paramIndex++, $paramValue);
    }
    $stmt->bindValue($paramIndex++, (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue($paramIndex++, (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    $reviews = $stmt->fetchAll();
    
    if (empty($reviews)) {
        $reviews = [];
    }
} catch (Exception $e) {
    $reviews = [];
    $totalReviews = 0;
    $totalPages = 0;
    $page = 1;
    $offset = 0;
    error_log("DB Error: " . $e->getMessage());
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Moderasi Ulasan | Admin KebumenGo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#2563EB',
                        accent: '#F59E0B',
                        surface: '#F8FAFC',
                        textPrimary: '#0F172A',
                        textSecondary: '#64748B',
                        border: '#E2E8F0'
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif']
                    }
                }
            }
        };
    </script>
</head>
<body class="bg-white font-sans text-textPrimary">
    <div class="flex h-screen overflow-hidden">
        <aside class="fixed left-0 top-0 flex h-screen w-[240px] flex-col border-r border-border bg-white px-5 py-6">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary text-white">KG</div>
                <div>
                    <p class="text-sm font-semibold">KebumenGo</p>
                    <span class="text-xs text-textSecondary">Admin Panel</span>
                </div>
            </div>

            <div class="mt-8">
                <p class="text-xs font-semibold uppercase tracking-widest text-textSecondary">Menu</p>
                <nav class="mt-4 grid gap-2">
                    <a href="<?= $baseUrl; ?>admin/dashboard" class="<?= navClass($isDashboard); ?>" <?= $isDashboard ? 'aria-current="page"' : ''; ?>>
                        <i data-lucide="layout-dashboard" class="h-4 w-4"></i>
                        Dashboard
                    </a>
                    <a href="<?= $baseUrl; ?>admin/analitik" class="<?= navClass($isAnalitik); ?>" <?= $isAnalitik ? 'aria-current="page"' : ''; ?>>
                        <i data-lucide="bar-chart-2" class="h-4 w-4"></i>
                        Analitik
                    </a>
                    <a href="<?= $baseUrl; ?>admin/destinasi" class="<?= navClass($isDestinasi); ?>" <?= $isDestinasi ? 'aria-current="page"' : ''; ?>>
                        <i data-lucide="map-pin" class="h-4 w-4"></i>
                        Destinasi
                    </a>
                    <a href="<?= $baseUrl; ?>admin/ulasan" class="<?= navClass($isUlasan); ?>" <?= $isUlasan ? 'aria-current="page"' : ''; ?>>
                        <i data-lucide="star" class="h-4 w-4"></i>
                        Ulasan
                    </a>
                </nav>
            </div>

            <div class="mt-8">
                <p class="text-xs font-semibold uppercase tracking-widest text-textSecondary">Account</p>
                <nav class="mt-4 grid gap-2">
                    <a href="<?= $baseUrl; ?>admin/pengaturan" class="<?= navClass($isPengaturan); ?>" <?= $isPengaturan ? 'aria-current="page"' : ''; ?>>
                        <i data-lucide="settings" class="h-4 w-4"></i>
                        Pengaturan
                    </a>
                </nav>
            </div>
        </aside>

        <main class="ml-[240px] flex h-screen w-full flex-col overflow-y-auto">
            <header class="flex items-center justify-between border-b border-border bg-white px-8 py-4">
                <div>
                    <h1 class="text-2xl font-semibold">Moderasi Ulasan</h1>
                    <p class="text-sm text-textSecondary">Setujui ulasan sebelum tampil di website.</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="relative hidden w-72 md:block">
                        <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-textSecondary"></i>
                        <input type="text" placeholder="Cari destinasi..." class="w-full rounded-xl border border-border bg-surface py-2.5 pl-10 pr-4 text-sm focus:border-primary focus:outline-none">
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-full bg-surface"></div>
                        <div class="text-sm">
                            <p class="font-semibold">Admin Kebumen</p>
                            <span class="text-xs text-textSecondary">admin@kebumengo.id</span>
                        </div>
                    </div>
                </div>
            </header>

            <div class="space-y-6 px-8 py-6">
                <?php $success = getFlash('success'); ?>
                <?php $error = getFlash('error'); ?>
                
                <?php if ($success): ?>
                    <div class="rounded-xl bg-emerald-50 border border-emerald-100 p-4 text-sm text-emerald-600 flex items-start gap-3 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 shrink-0 text-emerald-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="rounded-xl bg-rose-50 border border-rose-100 p-4 text-sm text-rose-600 flex items-start gap-3 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 shrink-0 text-rose-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                        </svg>
                        <div><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                <?php endif; ?>

                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold">Daftar ulasan</h2>
                        <p class="text-sm text-textSecondary">Kelola reputasi destinasi dengan cepat.</p>
                    </div>
                    <form method="post" action="<?= $baseUrl; ?>admin/ulasan/aksi">
                        <input type="hidden" name="csrf_token" value="<?= csrfToken(); ?>">
                        <input type="hidden" name="action" value="approve_all">
                        <button class="rounded-lg bg-textPrimary px-4 py-2 text-sm font-semibold text-white" type="submit">Approve Semua Pending</button>
                    </form>
                </div>

                <form method="get" action="<?= $baseUrl; ?>admin/ulasan" class="flex flex-wrap items-center gap-3">
                    <div class="relative w-full max-w-xs">
                        <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-textSecondary"></i>
                        <input type="text" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Cari ulasan, nama, destinasi..." class="w-full rounded-xl border border-border bg-white py-2.5 pl-10 pr-4 text-sm focus:border-primary focus:outline-none">
                    </div>
                    <select name="status" class="rounded-xl border border-border bg-white px-3 py-2 text-sm focus:border-primary focus:outline-none">
                        <option value="">Semua status</option>
                        <option value="pending" <?= ($_GET['status'] ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="approved" <?= ($_GET['status'] ?? '') === 'approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="rejected" <?= ($_GET['status'] ?? '') === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                    <select name="rating" class="rounded-xl border border-border bg-white px-3 py-2 text-sm focus:border-primary focus:outline-none">
                        <option value="">Semua rating</option>
                        <option value="5" <?= ($_GET['rating'] ?? '') === '5' ? 'selected' : ''; ?>>5 Bintang</option>
                        <option value="4" <?= ($_GET['rating'] ?? '') === '4' ? 'selected' : ''; ?>>4 Bintang</option>
                        <option value="3" <?= ($_GET['rating'] ?? '') === '3' ? 'selected' : ''; ?>>3 Bintang</option>
                        <option value="2" <?= ($_GET['rating'] ?? '') === '2' ? 'selected' : ''; ?>>2 Bintang</option>
                        <option value="1" <?= ($_GET['rating'] ?? '') === '1' ? 'selected' : ''; ?>>1 Bintang</option>
                    </select>
                    <button type="submit" class="rounded-lg bg-textPrimary px-4 py-2 text-sm font-semibold text-white">Filter</button>
                    <?php if (!empty($_GET['search']) || !empty($_GET['status']) || !empty($_GET['rating'])): ?>
                        <a href="<?= $baseUrl; ?>admin/ulasan" class="rounded-lg border border-border bg-white px-4 py-2 text-sm font-semibold text-textSecondary">Reset</a>
                    <?php endif; ?>
                </form>

                <div class="overflow-hidden rounded-xl border border-border bg-white">
                    <table class="w-full text-sm">
                        <thead class="bg-surface text-left text-xs uppercase tracking-wider text-textSecondary">
                            <tr>
                                <th class="px-4 py-3">Nama</th>
                                <th class="px-4 py-3">Destinasi</th>
                                <th class="px-4 py-3">Rating</th>
                                <th class="px-4 py-3">Komentar</th>
                                <th class="px-4 py-3">Tanggal</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reviews as $review): ?>
                                <?php
                                $statusKey = strtolower($review['status']);
                                $statusClass = 'bg-amber-100 text-amber-700';
                                if ($statusKey === 'approved') {
                                    $statusClass = 'bg-emerald-100 text-emerald-700';
                                } elseif ($statusKey === 'rejected') {
                                    $statusClass = 'bg-rose-100 text-rose-700';
                                }
                                ?>
                                <tr class="border-t border-border">
                                    <td class="px-4 py-3 font-semibold"><?= htmlspecialchars($review['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="px-4 py-3 text-textSecondary"><?= htmlspecialchars($review['destination'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="px-4 py-3"><?= htmlspecialchars((string)$review['rating'], ENT_QUOTES, 'UTF-8'); ?>/5</td>
                                    <td class="px-4 py-3 text-sm text-textSecondary truncate max-w-[200px] hover:text-wrap" title="<?= htmlspecialchars($review['comment'], ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($review['comment'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="px-4 py-3 text-textSecondary"><?= htmlspecialchars($review['date'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="px-4 py-3">
                                        <span class="rounded-lg px-2 py-1 text-xs font-semibold <?= $statusClass; ?>">
                                            <?= htmlspecialchars(ucfirst($review['status']), ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <?php if ($statusKey !== 'approved'): ?>
                                                <form method="post" action="<?= $baseUrl; ?>admin/ulasan/aksi">
                                                    <input type="hidden" name="csrf_token" value="<?= csrfToken(); ?>">
                                                    <input type="hidden" name="action" value="approve">
                                                    <input type="hidden" name="id" value="<?= htmlspecialchars((string)$review['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                                    <button class="rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 px-3 py-1.5 text-xs font-semibold" type="submit">Approve</button>
                                                </form>
                                            <?php endif; ?>
                                            <?php if ($statusKey !== 'rejected'): ?>
                                                <form method="post" action="<?= $baseUrl; ?>admin/ulasan/aksi">
                                                    <input type="hidden" name="csrf_token" value="<?= csrfToken(); ?>">
                                                    <input type="hidden" name="action" value="reject">
                                                    <input type="hidden" name="id" value="<?= htmlspecialchars((string)$review['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                                    <button class="rounded-lg border border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100 px-3 py-1.5 text-xs font-semibold text-rose-700" type="submit">Reject</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="flex items-center justify-between border border-border bg-white px-4 py-3 sm:px-6 rounded-xl shadow-sm">
                        <div class="flex flex-1 justify-between sm:hidden">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?= $page - 1; ?>" class="relative inline-flex items-center rounded-md border border-border bg-white px-4 py-2 text-sm font-medium text-textSecondary hover:bg-surface">Previous</a>
                            <?php else: ?>
                                <span class="relative inline-flex items-center rounded-md border border-border bg-slate-50 px-4 py-2 text-sm font-medium text-slate-300 cursor-not-allowed">Previous</span>
                            <?php endif; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?= $page + 1; ?>" class="relative inline-flex items-center rounded-md border border-border bg-white px-4 py-2 text-sm font-medium text-textSecondary hover:bg-surface">Next</a>
                            <?php else: ?>
                                <span class="relative inline-flex items-center rounded-md border border-border bg-slate-50 px-4 py-2 text-sm font-medium text-slate-300 cursor-not-allowed">Next</span>
                            <?php endif; ?>
                        </div>
                        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-textSecondary">
                                    Menampilkan <span class="font-semibold"><?= min($totalReviews, $offset + 1); ?></span> sampai <span class="font-semibold"><?= min($totalReviews, $offset + count($reviews)); ?></span> dari <span class="font-semibold"><?= $totalReviews; ?></span> ulasan
                                </p>
                            </div>
                            <div>
                                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                                    <!-- Previous Button -->
                                    <?php if ($page > 1): ?>
                                        <a href="?page=<?= $page - 1; ?>" class="relative inline-flex items-center rounded-l-md px-2 py-2 text-textSecondary ring-1 ring-inset ring-border hover:bg-surface focus:z-20 focus:outline-offset-0">
                                            <span class="sr-only">Previous</span>
                                            <i data-lucide="chevron-left" class="h-4 w-4"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="relative inline-flex items-center rounded-l-md px-2 py-2 text-slate-300 ring-1 ring-inset ring-border bg-slate-50 cursor-not-allowed">
                                            <span class="sr-only">Previous</span>
                                            <i data-lucide="chevron-left" class="h-4 w-4"></i>
                                        </span>
                                    <?php endif; ?>

                                    <!-- Page Numbers -->
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <?php if ($i === $page): ?>
                                            <span aria-current="page" class="relative z-10 inline-flex items-center bg-textPrimary px-4 py-2 text-sm font-semibold text-white focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-textPrimary"><?= $i; ?></span>
                                        <?php else: ?>
                                            <a href="?page=<?= $i; ?>" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-textSecondary ring-1 ring-inset ring-border hover:bg-surface focus:z-20 focus:outline-offset-0"><?= $i; ?></a>
                                        <?php endif; ?>
                                    <?php endfor; ?>

                                    <!-- Next Button -->
                                    <?php if ($page < $totalPages): ?>
                                        <a href="?page=<?= $page + 1; ?>" class="relative inline-flex items-center rounded-r-md px-2 py-2 text-textSecondary ring-1 ring-inset ring-border hover:bg-surface focus:z-20 focus:outline-offset-0">
                                            <span class="sr-only">Next</span>
                                            <i data-lucide="chevron-right" class="h-4 w-4"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="relative inline-flex items-center rounded-r-md px-2 py-2 text-slate-300 ring-1 ring-inset ring-border bg-slate-50 cursor-not-allowed">
                                            <span class="sr-only">Next</span>
                                            <i data-lucide="chevron-right" class="h-4 w-4"></i>
                                        </span>
                                    <?php endif; ?>
                                </nav>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();

        // Realtime search & filter
        const form = document.querySelector('form[method="get"]');
        if (form) {
            form.querySelectorAll('select').forEach(select => {
                select.addEventListener('change', () => {
                    form.submit();
                });
            });

            const searchInput = form.querySelector('input[name="search"]');
            if (searchInput) {
                let debounceTimer;
                searchInput.addEventListener('input', () => {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => {
                        form.submit();
                    }, 500);
                });

                // Restore focus & cursor to the end
                if (searchInput.value) {
                    searchInput.focus();
                    const val = searchInput.value;
                    searchInput.value = '';
                    searchInput.value = val;
                }
            }
        }
    </script>
</body>
</html>
