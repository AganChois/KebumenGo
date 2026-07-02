<?php
$baseUrl = defined('BASE_URL') ? BASE_URL : '/';
$currentPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '', '/');
$isDestinasi = str_starts_with($currentPath, 'admin/destinasi') || str_starts_with($currentPath, 'admin/kategori');
$isUlasan = str_starts_with($currentPath, 'admin/ulasan');
$isDashboard = $currentPath === 'admin/dashboard';

$mode = $viewData['mode'] ?? 'create';
$category = $viewData['category'] ?? [];
$pageTitle = $mode === 'create' ? 'Tambah Kategori' : 'Edit Kategori';
$actionLabel = $mode === 'create' ? 'Simpan Kategori' : 'Simpan Perubahan';

function navClass(bool $active, string $extra = ''): string
{
    $base = $active
        ? 'flex items-center gap-3 rounded-[10px] bg-textPrimary px-3 py-2 text-white'
        : 'flex items-center gap-3 rounded-[10px] px-3 py-2 text-textSecondary hover:bg-surface';

    return trim($base . ' ' . $extra);
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="<?= $baseUrl; ?>public/images/logo.svg">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?> | Admin KebumenGo</title>
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
                    <a href="<?= $baseUrl; ?>admin/analitik" class="<?= navClass(false); ?>">
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
                    <a href="<?= $baseUrl; ?>admin/pengaturan" class="<?= navClass(false); ?>">
                        <i data-lucide="settings" class="h-4 w-4"></i>
                        Pengaturan
                    </a>
                </nav>
            </div>

            <div class="mt-8 rounded-xl border border-border bg-surface p-4">
                <div class="flex items-center gap-3 text-textPrimary">
                    <i data-lucide="rocket" class="h-5 w-5"></i>
                    <div>
                        <p class="text-sm font-semibold">Siap scale up?</p>
                        <span class="text-xs text-textSecondary">Fitur premium untuk tim</span>
                    </div>
                </div>
            </div>

            <button class="mt-auto w-full rounded-lg bg-textPrimary px-4 py-3 text-sm font-semibold text-white">
                ⬆ Upgrade ke Pro
            </button>
        </aside>

        <main class="ml-[240px] flex h-screen w-full flex-col overflow-y-auto">
            <header class="flex items-center justify-between border-b border-border bg-white px-8 py-4">
                <div>
                    <h1 class="text-2xl font-semibold"><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></h1>
                    <p class="text-sm text-textSecondary">Pastikan kategori konsisten dengan konten destinasi.</p>
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
                <?php include __DIR__ . '/../../partials/admin-flash.php'; ?>
                <form class="space-y-6 rounded-xl border border-border bg-white p-6" method="post" action="<?= $baseUrl; ?>admin/kategori/create" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken(); ?>">
                    <?php if ($mode === 'edit' && !empty($category['id'])): ?>
                        <input type="hidden" name="id" value="<?= htmlspecialchars((string)$category['id'], ENT_QUOTES, 'UTF-8'); ?>">
                    <?php endif; ?>
                    <div class="grid gap-4 md:grid-cols-3">
                        <label class="text-sm font-semibold">
                            Nama Kategori
                            <input type="text" name="name" value="<?= htmlspecialchars($category['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required class="mt-2 w-full rounded-xl border border-border px-3 py-2.5 text-sm">
                        </label>
                        <label class="text-sm font-semibold">
                            Slug
                            <input type="text" name="slug" value="<?= htmlspecialchars($category['slug'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required class="mt-2 w-full rounded-xl border border-border px-3 py-2.5 text-sm">
                        </label>
                        <label class="text-sm font-semibold">
                            Urutan Tampil
                            <input type="number" name="sort_order" min="0" value="<?= htmlspecialchars((string)($category['sort_order'] ?? '1'), ENT_QUOTES, 'UTF-8'); ?>" class="mt-2 w-full rounded-xl border border-border px-3 py-2.5 text-sm">
                        </label>
                    </div>
                    <label class="text-sm font-semibold">
                        Deskripsi Singkat
                        <textarea name="description" rows="3" placeholder="Deskripsi kategori" class="mt-2 w-full rounded-xl border border-border px-3 py-2.5 text-sm"></textarea>
                    </label>
                    <label class="text-sm font-semibold">
                        Ikon/Foto Kategori
                        <input type="file" name="icon_img" class="mt-2 w-full rounded-xl border border-border px-3 py-2.5 text-sm">
                    </label>
                    <div class="flex flex-wrap justify-end gap-3">
                        <a href="<?= $baseUrl; ?>admin/kategori" class="rounded-lg border border-border px-4 py-2 text-sm font-semibold text-textSecondary">Batal</a>
                        <button class="rounded-lg bg-textPrimary px-4 py-2 text-sm font-semibold text-white" type="submit"><?= htmlspecialchars($actionLabel, ENT_QUOTES, 'UTF-8'); ?></button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>