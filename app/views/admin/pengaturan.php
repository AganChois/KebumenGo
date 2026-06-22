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

// Handle settings submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($token)) {
        setFlash('error', 'Token keamanan tidak valid. Silakan coba lagi.');
        redirect('admin/pengaturan');
    }

    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($email)) {
        setFlash('error', 'Nama dan email wajib diisi.');
        redirect('admin/pengaturan');
    }

    $adminConfig = require __DIR__ . '/../../../config/admin.php';
    $passwordHash = $adminConfig['password_hash'];

    // If changing password
    if (!empty($currentPassword) || !empty($newPassword) || !empty($confirmPassword)) {
        if (!password_verify($currentPassword, $passwordHash)) {
            setFlash('error', 'Password saat ini salah.');
            redirect('admin/pengaturan');
        }

        if (strlen($newPassword) < 8) {
            setFlash('error', 'Password baru minimal 8 karakter.');
            redirect('admin/pengaturan');
        }

        if ($newPassword !== $confirmPassword) {
            setFlash('error', 'Konfirmasi password baru tidak cocok.');
            redirect('admin/pengaturan');
        }

        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
    }

    try {
        $content = "<?php\n\ndeclare(strict_types=1);\n\nreturn [\n    'name' => " . var_export($name, true) . ",\n    'email' => " . var_export($email, true) . ",\n    'password_hash' => " . var_export($passwordHash, true) . ",\n];\n";
        file_put_contents(__DIR__ . '/../../../config/admin.php', $content);

        $_SESSION['admin_name'] = $name;
        $_SESSION['admin_email'] = $email;

        setFlash('success', 'Pengaturan berhasil diperbarui.');
    } catch (Exception $e) {
        setFlash('error', 'Gagal menyimpan pengaturan: ' . $e->getMessage());
    }

    redirect('admin/pengaturan');
}

$adminName = $_SESSION['admin_name'] ?? 'Admin KebumenGo';
$adminEmail = $_SESSION['admin_email'] ?? 'admin@kebumengo.id';
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pengaturan | Admin KebumenGo</title>
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
                    <h1 class="text-2xl font-semibold">Pengaturan</h1>
                    <p class="text-sm text-textSecondary">Konfigurasi akun dan preferensi sistem admin.</p>
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

            <div class="space-y-6 px-8 py-6 max-w-4xl">
                <!-- Settings Tabs Navigation (Visual only) -->
                <div class="flex border-b border-border gap-6">
                    <button class="pb-3 border-b-2 border-primary font-semibold text-primary">Profil Saya</button>
                    <button class="pb-3 border-b-2 border-transparent font-semibold text-textSecondary hover:text-textPrimary">Sistem Umum</button>
                    <button class="pb-3 border-b-2 border-transparent font-semibold text-textSecondary hover:text-textPrimary">Keamanan</button>
                </div>

                <!-- Form Section -->
                <?php include __DIR__ . '/../partials/admin-flash.php'; ?>
                <div class="bg-white border border-border rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-semibold mb-6">Informasi Akun</h2>
                    <form method="post" action="<?= $baseUrl; ?>admin/pengaturan" class="space-y-5">
                        <input type="hidden" name="csrf_token" value="<?= csrfToken(); ?>">
                        <div class="flex gap-6 items-center mb-8">
                            <div class="relative w-24 h-24 rounded-full border-4 border-surface overflow-hidden group cursor-pointer">
                                <img src="https://ui-avatars.com/api/?name=<?= urlencode($adminName); ?>&background=2563EB&color=fff&size=200" alt="Avatar" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                    <i data-lucide="camera" class="text-white h-6 w-6"></i>
                                </div>
                            </div>
                            <div>
                                <button type="button" class="px-4 py-2 bg-surface text-textPrimary text-sm font-semibold rounded-lg border border-border hover:bg-slate-100 transition">Ubah Foto Profil</button>
                                <p class="text-xs text-textSecondary mt-2">JPG, GIF atau PNG. Maksimal 2MB.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-medium text-textPrimary mb-2">Nama Lengkap</label>
                                <input type="text" name="name" value="<?= htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-xl border border-border bg-white px-4 py-2.5 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-textPrimary mb-2">Email</label>
                                <input type="email" name="email" value="<?= htmlspecialchars($adminEmail, ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-xl border border-border bg-white px-4 py-2.5 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" required>
                            </div>
                        </div>

                        <hr class="my-6 border-border">

                        <h2 class="text-lg font-semibold mb-6">Ubah Password</h2>
                        
                        <div class="space-y-4 max-w-md">
                            <div>
                                <label class="block text-sm font-medium text-textPrimary mb-2">Password Saat Ini</label>
                                <input type="password" name="current_password" placeholder="••••••••" class="w-full rounded-xl border border-border bg-white px-4 py-2.5 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-textPrimary mb-2">Password Baru</label>
                                <input type="password" name="new_password" placeholder="Minimal 8 karakter" class="w-full rounded-xl border border-border bg-white px-4 py-2.5 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-textPrimary mb-2">Konfirmasi Password Baru</label>
                                <input type="password" name="confirm_password" placeholder="Minimal 8 karakter" class="w-full rounded-xl border border-border bg-white px-4 py-2.5 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                            </div>
                        </div>

                        <div class="mt-8 flex justify-end">
                            <button type="submit" class="bg-primary hover:bg-blue-700 transition-colors text-white px-6 py-2.5 rounded-xl font-semibold text-sm">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
                
                <div class="mt-8 flex justify-center">
                    <form action="<?= $baseUrl; ?>admin/logout" method="GET">
                        <button type="submit" class="flex items-center gap-2 text-red-500 hover:text-red-600 hover:bg-red-50 px-4 py-2 rounded-lg transition-colors font-medium text-sm">
                            <i data-lucide="log-out" class="h-4 w-4"></i>
                            Logout dari Admin
                        </button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
