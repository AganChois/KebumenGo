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
    $sort = strtolower(trim($_GET['sort'] ?? 'newest'));
    
    // Build WHERE conditions
    $conditions = [];
    $params = [];
    
    if ($search !== '') {
        $conditions[] = "d.name LIKE ?";
        $params[] = "%$search%";
    }
    
    if ($statusFilter !== '') {
        $conditions[] = "d.status = ?";
        $params[] = $statusFilter;
    }
    
    $whereClause = '';
    if (!empty($conditions)) {
        $whereClause = 'WHERE ' . implode(' AND ', $conditions);
    }
    
    // Determine sorting
    $orderBy = "ORDER BY d.created_at DESC";
    if ($sort === 'cheapest') {
        $orderBy = "ORDER BY d.ticket_price ASC";
    } elseif ($sort === 'most_expensive') {
        $orderBy = "ORDER BY d.ticket_price DESC";
    }
    
    // Count total destinations
    $totalCountQuery = "
        SELECT COUNT(*) 
        FROM destinations d
        $whereClause
    ";
    $totalCountStmt = $db->prepare($totalCountQuery);
    $totalCountStmt->execute($params);
    $totalDestinations = (int)$totalCountStmt->fetchColumn();
    $totalPages = (int)ceil($totalDestinations / $limit);
    
    // Fetch paginated destinations
    $sql = "
        SELECT d.id, d.name, c.name as category, d.ticket_price as price, 
               d.status, DATE_FORMAT(d.updated_at, '%d %b %Y') as updated
        FROM destinations d
        JOIN categories c ON d.category_id = c.id
        $whereClause
        $orderBy
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
    $destinations = $stmt->fetchAll();
    
    if (empty($destinations)) {
        $destinations = [];
    }
} catch (Exception $e) {
    $destinations = [];
    $totalDestinations = 0;
    $totalPages = 0;
    $page = 1;
    $offset = 0;
    error_log("DB Error: " . $e->getMessage());
}

function getDestPageUrl(int $pageNum) {
    $params = $_GET;
    $params['page'] = $pageNum;
    return '?' . http_build_query($params);
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="<?= $baseUrl; ?>public/images/logo.svg">
    <title>Manajemen Destinasi | Admin KebumenGo</title>
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
                <img src="<?= $baseUrl; ?>public/images/logo.svg" alt="Logo" class="h-10 w-10 object-contain">
                <div>
                    <p class="text-sm font-semibold"><?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8'); ?></p>
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
                    <h1 class="text-2xl font-semibold">Manajemen Destinasi</h1>
                    <p class="text-sm text-textSecondary">Kelola data destinasi wisata Kebumen secara terstruktur.</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-full bg-surface overflow-hidden">
                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($adminName); ?>&background=2563EB&color=fff" alt="Avatar" class="w-full h-full object-cover">
                        </div>
                        <div class="text-sm">
                            <p class="font-semibold"><?= htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8'); ?></p>
                            <span class="text-xs text-textSecondary"><?= htmlspecialchars($adminEmail, ENT_QUOTES, 'UTF-8'); ?></span>
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
                        <h2 class="text-lg font-semibold">Daftar destinasi</h2>
                        <p class="text-sm text-textSecondary">Pantau status publikasi dan harga tiket terbaru.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <a href="<?= $baseUrl; ?>admin/analitik/export?type=destinasi&format=excel" class="rounded-lg border border-border bg-white px-4 py-2 text-sm font-semibold text-textSecondary flex items-center gap-1.5 hover:bg-surface">
                            <i data-lucide="download" class="h-4 w-4"></i>
                            Unduh Excel
                        </a>
                        <a href="<?= $baseUrl; ?>admin/analitik/export?type=destinasi&format=pdf" target="_blank" class="rounded-lg border border-border bg-white px-4 py-2 text-sm font-semibold text-textSecondary flex items-center gap-1.5 hover:bg-surface">
                            <i data-lucide="file-text" class="h-4 w-4"></i>
                            Cetak PDF
                        </a>
                        <a href="<?= $baseUrl; ?>admin/kategori" class="rounded-lg border border-border bg-white px-4 py-2 text-sm font-semibold text-textSecondary hover:bg-surface">Kelola Kategori</a>
                        <a href="<?= $baseUrl; ?>admin/destinasi/create" class="rounded-lg bg-textPrimary px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">+ Tambah Destinasi</a>
                    </div>
                </div>

                <form method="get" action="<?= $baseUrl; ?>admin/destinasi" class="flex flex-wrap items-center gap-3">
                    <div class="relative w-full max-w-xs">
                        <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-textSecondary"></i>
                        <input type="text" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Cari nama destinasi" class="w-full rounded-xl border border-border bg-white py-2.5 pl-10 pr-4 text-sm focus:border-primary focus:outline-none">
                    </div>
                    <select name="status" class="rounded-xl border border-border bg-white px-3 py-2.5 text-sm focus:border-primary focus:outline-none">
                        <option value="">Semua status</option>
                        <option value="active" <?= ($_GET['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Aktif</option>
                        <option value="inactive" <?= ($_GET['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Tidak Aktif</option>
                    </select>
                    <select name="sort" class="rounded-xl border border-border bg-white px-3 py-2.5 text-sm focus:border-primary focus:outline-none">
                        <option value="newest" <?= ($_GET['sort'] ?? '') === 'newest' ? 'selected' : ''; ?>>Urutkan: Terbaru</option>
                        <option value="cheapest" <?= ($_GET['sort'] ?? '') === 'cheapest' ? 'selected' : ''; ?>>Urutkan: Termurah</option>
                        <option value="most_expensive" <?= ($_GET['sort'] ?? '') === 'most_expensive' ? 'selected' : ''; ?>>Urutkan: Termahal</option>
                    </select>
                    <button type="submit" class="rounded-lg bg-textPrimary px-4 py-2 text-sm font-semibold text-white">Filter</button>
                    <?php if (!empty($_GET['search']) || !empty($_GET['status']) || ($_GET['sort'] ?? 'newest') !== 'newest'): ?>
                        <a href="<?= $baseUrl; ?>admin/destinasi" class="rounded-lg border border-border bg-white px-4 py-2 text-sm font-semibold text-textSecondary">Reset</a>
                    <?php endif; ?>
                </form>

                <div class="overflow-hidden rounded-xl border border-border bg-white">
                    <table class="w-full text-sm">
                        <thead class="bg-surface text-left text-xs uppercase tracking-wider text-textSecondary">
                            <tr>
                                <th class="px-4 py-3">Nama</th>
                                <th class="px-4 py-3">Kategori</th>
                                <th class="px-4 py-3">Harga tiket</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Update</th>
                                <th class="px-4 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($destinations as $destination): ?>
                                <?php
                                $statusKey = strtolower($destination['status']);
                                $statusClass = 'bg-rose-100 text-rose-700';
                                $statusText = 'Tidak Aktif';
                                if ($statusKey === 'active') {
                                    $statusClass = 'bg-emerald-100 text-emerald-700';
                                    $statusText = 'Aktif';
                                }
                                ?>
                                <tr class="border-t border-border">
                                    <td class="px-4 py-3 font-semibold"><?= htmlspecialchars($destination['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="px-4 py-3 text-textSecondary"><?= htmlspecialchars($destination['category'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="px-4 py-3"><?= formatRupiah($destination['price']); ?></td>
                                    <td class="px-4 py-3">
                                        <span class="rounded-lg px-2 py-1 text-xs font-semibold <?= $statusClass; ?>">
                                            <?= $statusText; ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-textSecondary"><?= htmlspecialchars($destination['updated'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <a href="<?= $baseUrl; ?>admin/destinasi/edit?id=<?= $destination['id'] ?>" class="rounded-lg border border-border px-3 py-1.5 text-xs font-semibold">Edit</a>
                                            <form action="<?= $baseUrl; ?>admin/destinasi/delete" method="POST" onsubmit="return confirm('Yakin ingin menghapus destinasi ini? Data ulasan terkait juga akan terhapus.');" class="inline">
                                                <input type="hidden" name="csrf_token" value="<?= csrfToken(); ?>">
                                                <input type="hidden" name="id" value="<?= $destination['id'] ?>">
                                                <button type="submit" class="rounded-lg border border-border px-3 py-1.5 text-xs font-semibold text-textSecondary text-red-600 hover:bg-red-50">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="flex items-center justify-between border border-border bg-white px-4 py-3 sm:px-6 rounded-xl shadow-sm mt-4">
                        <div class="flex flex-1 justify-between sm:hidden">
                            <?php if ($page > 1): ?>
                                <a href="<?= getDestPageUrl($page - 1); ?>" class="relative inline-flex items-center rounded-md border border-border bg-white px-4 py-2 text-sm font-medium text-textSecondary hover:bg-surface">Previous</a>
                            <?php else: ?>
                                <span class="relative inline-flex items-center rounded-md border border-border bg-slate-50 px-4 py-2 text-sm font-medium text-slate-300 cursor-not-allowed">Previous</span>
                            <?php endif; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <a href="<?= getDestPageUrl($page + 1); ?>" class="relative inline-flex items-center rounded-md border border-border bg-white px-4 py-2 text-sm font-medium text-textSecondary hover:bg-surface">Next</a>
                            <?php else: ?>
                                <span class="relative inline-flex items-center rounded-md border border-border bg-slate-50 px-4 py-2 text-sm font-medium text-slate-300 cursor-not-allowed">Next</span>
                            <?php endif; ?>
                        </div>
                        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-textSecondary">
                                    Menampilkan <span class="font-semibold"><?= min($totalDestinations, $offset + 1); ?></span> sampai <span class="font-semibold"><?= min($totalDestinations, $offset + count($destinations)); ?></span> dari <span class="font-semibold"><?= $totalDestinations; ?></span> destinasi
                                </p>
                            </div>
                            <div>
                                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                                    <!-- Previous Button -->
                                    <?php if ($page > 1): ?>
                                        <a href="<?= getDestPageUrl($page - 1); ?>" class="relative inline-flex items-center rounded-l-md px-2 py-2 text-textSecondary ring-1 ring-inset ring-border hover:bg-surface focus:z-20 focus:outline-offset-0">
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
                                            <a href="<?= getDestPageUrl($i); ?>" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-textSecondary ring-1 ring-inset ring-border hover:bg-surface focus:z-20 focus:outline-offset-0"><?= $i; ?></a>
                                        <?php endif; ?>
                                    <?php endfor; ?>

                                    <!-- Next Button -->
                                    <?php if ($page < $totalPages): ?>
                                        <a href="<?= getDestPageUrl($page + 1); ?>" class="relative inline-flex items-center rounded-r-md px-2 py-2 text-textSecondary ring-1 ring-inset ring-border hover:bg-surface focus:z-20 focus:outline-offset-0">
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