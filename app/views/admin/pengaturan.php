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
$systemConfigFile = __DIR__ . '/../../../config/system.php';
if (!file_exists($systemConfigFile)) {
    $defaultSystem = [
        'web_name' => 'KebumenGo',
        'web_desc' => 'Panduan wisata terbaik di Kabupaten Kebumen.',
        'contact_phone' => '081234567890',
        'contact_email' => 'info@kebumengo.id',
        'contact_address' => 'Jl. Pahlawan No. 10, Kebumen, Jawa Tengah',
    ];
    $content = "<?php\n\ndeclare(strict_types=1);\n\nreturn " . var_export($defaultSystem, true) . ";\n";
    file_put_contents($systemConfigFile, $content);
}
$systemConfig = require $systemConfigFile;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($token)) {
        setFlash('error', 'Token keamanan tidak valid. Silakan coba lagi.');
        redirect('admin/pengaturan');
    }

    $actionType = $_POST['action_type'] ?? 'profile';

    if ($actionType === 'profile') {
        $name = sanitize($_POST['name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');

        if (empty($name) || empty($email)) {
            setFlash('error', 'Nama dan email wajib diisi.');
            redirect('admin/pengaturan?tab=profile');
        }

        $adminConfig = require __DIR__ . '/../../../config/admin.php';
        $adminConfig['name'] = $name;
        $adminConfig['email'] = $email;

        try {
            $content = "<?php\n\ndeclare(strict_types=1);\n\nreturn " . var_export($adminConfig, true) . ";\n";
            file_put_contents(__DIR__ . '/../../../config/admin.php', $content);

            $_SESSION['admin_name'] = $name;
            $_SESSION['admin_email'] = $email;

            setFlash('success', 'Profil berhasil diperbarui.');
        } catch (Exception $e) {
            setFlash('error', 'Gagal menyimpan profil: ' . $e->getMessage());
        }
        redirect('admin/pengaturan?tab=profile');
    }

    if ($actionType === 'system') {
        $webName = sanitize($_POST['web_name'] ?? '');
        $webDesc = sanitize($_POST['web_desc'] ?? '');
        $contactPhone = sanitize($_POST['contact_phone'] ?? '');
        $contactEmail = sanitize($_POST['contact_email'] ?? '');
        $contactAddress = sanitize($_POST['contact_address'] ?? '');

        if (empty($webName)) {
            setFlash('error', 'Nama website wajib diisi.');
            redirect('admin/pengaturan?tab=system');
        }

        $newSystemConfig = [
            'web_name' => $webName,
            'web_desc' => $webDesc,
            'contact_phone' => $contactPhone,
            'contact_email' => $contactEmail,
            'contact_address' => $contactAddress,
        ];

        try {
            $content = "<?php\n\ndeclare(strict_types=1);\n\nreturn " . var_export($newSystemConfig, true) . ";\n";
            file_put_contents($systemConfigFile, $content);
            setFlash('success', 'Pengaturan sistem umum berhasil diperbarui.');
        } catch (Exception $e) {
            setFlash('error', 'Gagal menyimpan pengaturan sistem: ' . $e->getMessage());
        }
        redirect('admin/pengaturan?tab=system');
    }

    if ($actionType === 'security') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            setFlash('error', 'Semua kolom password wajib diisi.');
            redirect('admin/pengaturan?tab=security');
        }

        $adminConfig = require __DIR__ . '/../../../config/admin.php';
        $passwordHash = $adminConfig['password_hash'];

        if (!password_verify($currentPassword, $passwordHash)) {
            setFlash('error', 'Password saat ini salah.');
            redirect('admin/pengaturan?tab=security');
        }

        if (strlen($newPassword) < 8) {
            setFlash('error', 'Password baru minimal 8 karakter.');
            redirect('admin/pengaturan?tab=security');
        }

        if ($newPassword !== $confirmPassword) {
            setFlash('error', 'Konfirmasi password baru tidak cocok.');
            redirect('admin/pengaturan?tab=security');
        }

        $adminConfig['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);

        try {
            $content = "<?php\n\ndeclare(strict_types=1);\n\nreturn " . var_export($adminConfig, true) . ";\n";
            file_put_contents(__DIR__ . '/../../../config/admin.php', $content);
            setFlash('success', 'Password berhasil diperbarui.');
        } catch (Exception $e) {
            setFlash('error', 'Gagal memperbarui password: ' . $e->getMessage());
        }
        redirect('admin/pengaturan?tab=security');
    }
}

$adminName = $_SESSION['admin_name'] ?? 'Admin KebumenGo';
$adminEmail = $_SESSION['admin_email'] ?? 'admin@kebumengo.id';

$activeTab = $_GET['tab'] ?? 'profile';
if (!in_array($activeTab, ['profile', 'system', 'security'])) {
    $activeTab = 'profile';
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="<?= $baseUrl; ?>public/images/logo.svg">
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
                <!-- Settings Tabs Navigation -->
                <div class="flex border-b border-border gap-6">
                    <button onclick="switchTab('profile')" id="btn-profile" class="pb-3 border-b-2 <?= $activeTab === 'profile' ? 'border-primary text-primary font-semibold' : 'border-transparent text-textSecondary font-semibold hover:text-textPrimary' ?>">Profil Saya</button>
                    <button onclick="switchTab('system')" id="btn-system" class="pb-3 border-b-2 <?= $activeTab === 'system' ? 'border-primary text-primary font-semibold' : 'border-transparent text-textSecondary font-semibold hover:text-textPrimary' ?>">Sistem Umum</button>
                    <button onclick="switchTab('security')" id="btn-security" class="pb-3 border-b-2 <?= $activeTab === 'security' ? 'border-primary text-primary font-semibold' : 'border-transparent text-textSecondary font-semibold hover:text-textPrimary' ?>">Keamanan</button>
                </div>

                <!-- Form Section -->
                <?php include __DIR__ . '/../partials/admin-flash.php'; ?>

                <!-- Tab: Profil Saya -->
                <div id="tab-content-profile" class="<?= $activeTab === 'profile' ? '' : 'hidden' ?> bg-white border border-border rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-semibold mb-6">Informasi Akun</h2>
                    <form method="post" action="<?= $baseUrl; ?>admin/pengaturan" class="space-y-5">
                        <input type="hidden" name="csrf_token" value="<?= csrfToken(); ?>">
                        <input type="hidden" name="action_type" value="profile">
                        <div class="flex gap-6 items-center mb-8">
                            <div class="relative w-24 h-24 rounded-full border-4 border-surface overflow-hidden group cursor-pointer">
                                <img src="https://ui-avatars.com/api/?name=<?= urlencode($adminName); ?>&background=2563EB&color=fff&size=200" alt="Avatar" class="w-full h-full object-cover">
                            </div>
                            <div>
                                <p class="text-xs text-textSecondary">Foto profil di-generate otomatis berdasarkan nama Anda.</p>
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

                        <div class="mt-8 flex justify-end">
                            <button type="submit" class="bg-primary hover:bg-blue-700 transition-colors text-white px-6 py-2.5 rounded-xl font-semibold text-sm">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>

                <!-- Tab: Sistem Umum -->
                <div id="tab-content-system" class="<?= $activeTab === 'system' ? '' : 'hidden' ?> bg-white border border-border rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-semibold mb-6">Pengaturan Sistem Umum</h2>
                    <form method="post" action="<?= $baseUrl; ?>admin/pengaturan" class="space-y-5">
                        <input type="hidden" name="csrf_token" value="<?= csrfToken(); ?>">
                        <input type="hidden" name="action_type" value="system">
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-textPrimary mb-2">Nama Website</label>
                                <input type="text" name="web_name" value="<?= htmlspecialchars($systemConfig['web_name'] ?? 'KebumenGo', ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-xl border border-border bg-white px-4 py-2.5 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-textPrimary mb-2">Deskripsi Website</label>
                                <textarea name="web_desc" rows="3" class="w-full rounded-xl border border-border bg-white px-4 py-2.5 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"><?= htmlspecialchars($systemConfig['web_desc'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-sm font-medium text-textPrimary mb-2">Telepon Kontak</label>
                                    <input type="text" name="contact_phone" value="<?= htmlspecialchars($systemConfig['contact_phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-xl border border-border bg-white px-4 py-2.5 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-textPrimary mb-2">Email Kontak</label>
                                    <input type="email" name="contact_email" value="<?= htmlspecialchars($systemConfig['contact_email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-xl border border-border bg-white px-4 py-2.5 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-textPrimary mb-2">Alamat Kantor</label>
                                <input type="text" name="contact_address" value="<?= htmlspecialchars($systemConfig['contact_address'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-xl border border-border bg-white px-4 py-2.5 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                            </div>
                        </div>

                        <div class="mt-8 flex justify-end">
                            <button type="submit" class="bg-primary hover:bg-blue-700 transition-colors text-white px-6 py-2.5 rounded-xl font-semibold text-sm">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>

                <!-- Tab: Keamanan -->
                <div id="tab-content-security" class="<?= $activeTab === 'security' ? '' : 'hidden' ?> bg-white border border-border rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-semibold mb-6">Ubah Password</h2>
                    <form method="post" action="<?= $baseUrl; ?>admin/pengaturan" class="space-y-5">
                        <input type="hidden" name="csrf_token" value="<?= csrfToken(); ?>">
                        <input type="hidden" name="action_type" value="security">
                        
                        <div class="space-y-4 max-w-md">
                            <div>
                                <label class="block text-sm font-medium text-textPrimary mb-2">Password Saat Ini</label>
                                <input type="password" name="current_password" placeholder="••••••••" class="w-full rounded-xl border border-border bg-white px-4 py-2.5 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-textPrimary mb-2">Password Baru</label>
                                <input type="password" name="new_password" placeholder="Minimal 8 karakter" class="w-full rounded-xl border border-border bg-white px-4 py-2.5 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-textPrimary mb-2">Konfirmasi Password Baru</label>
                                <input type="password" name="confirm_password" placeholder="Minimal 8 karakter" class="w-full rounded-xl border border-border bg-white px-4 py-2.5 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" required>
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

        function switchTab(tabId) {
            // Hide all tab contents
            document.getElementById('tab-content-profile').classList.add('hidden');
            document.getElementById('tab-content-system').classList.add('hidden');
            document.getElementById('tab-content-security').classList.add('hidden');
            
            // Remove active styles from all buttons
            const buttons = ['profile', 'system', 'security'];
            buttons.forEach(id => {
                const btn = document.getElementById('btn-' + id);
                btn.className = "pb-3 border-b-2 border-transparent text-textSecondary font-semibold hover:text-textPrimary";
            });
            
            // Show selected content
            document.getElementById('tab-content-' + tabId).classList.remove('hidden');
            
            // Add active styles to selected button
            const activeBtn = document.getElementById('btn-' + tabId);
            activeBtn.className = "pb-3 border-b-2 border-primary font-semibold text-primary";

            // Update URL query parameter without page reload
            const url = new URL(window.location);
            url.searchParams.set('tab', tabId);
            window.history.pushState({}, '', url);
        }
    </script>
</body>
</html>
