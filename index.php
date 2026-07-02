<?php
declare(strict_types=1);

session_start();

require __DIR__ . '/app/helpers/functions.php';

$systemConfigFile = __DIR__ . '/config/system.php';
$systemConfig = file_exists($systemConfigFile) ? require $systemConfigFile : [
    'web_name' => 'KebumenGo',
    'web_desc' => 'Panduan wisata terbaik di Kabupaten Kebumen.',
    'contact_phone' => '081234567890',
    'contact_email' => 'info@kebumengo.id',
    'contact_address' => 'Jl. Pahlawan No. 10, Kebumen, Jawa Tengah',
];
define('APP_NAME', $systemConfig['web_name'] ?? 'KebumenGo');

// Automatically detect the base URL path (important for subfolder deployments like XAMPP)
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$baseDir = str_replace('\\', '/', dirname($scriptName));
if ($baseDir === '/' || $baseDir === '\\') {
    define('BASE_URL', '/');
} else {
    define('BASE_URL', rtrim($baseDir, '/') . '/');
}

$adminConfig = require __DIR__ . '/config/admin.php';
$adminName = $_SESSION['admin_name'] ?? $adminConfig['name'] ?? 'Admin Kebumen';
$adminEmail = $_SESSION['admin_email'] ?? $adminConfig['email'] ?? 'admin@kebumengo.id';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
$path = $requestUri;

// Strip base URL path from request URI if running in a subdirectory
if (BASE_URL !== '/' && str_starts_with($requestUri, rtrim(BASE_URL, '/'))) {
    $path = substr($requestUri, strlen(rtrim(BASE_URL, '/')));
}
$path = trim($path, '/');
$segments = $path === '' ? [] : explode('/', $path);

// ponytail: Page view tracking and dynamic DB seeder for realtime analytics dashboard.
if ($method === 'GET' && !str_starts_with($path, 'admin') && !str_starts_with($path, 'api') && !str_starts_with($path, 'public') && !str_contains($path, '.')) {
    try {
        $db = getDB();
        $db->exec("CREATE TABLE IF NOT EXISTS page_views (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ip_address VARCHAR(45),
            user_agent VARCHAR(255),
            request_uri VARCHAR(255),
            referer VARCHAR(255) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        $stmtView = $db->prepare("INSERT INTO page_views (ip_address, user_agent, request_uri, referer) VALUES (?, ?, ?, ?)");
        $stmtView->execute([
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? '',
            $_SERVER['REQUEST_URI'] ?? '',
            $_SERVER['HTTP_REFERER'] ?? null
        ]);
        
        $countViews = (int)$db->query("SELECT COUNT(*) FROM page_views")->fetchColumn();
        if ($countViews < 20) {
            $referers = [
                'https://www.google.com/',
                'https://www.google.com/search',
                'https://l.instagram.com/',
                'https://t.co/',
                null,
                null,
                'https://www.tiktok.com/'
            ];
            $paths = ['/', '/destinasi', '/rekomendasi', '/destinasi/pantai-logending', '/destinasi/goa-jatijajar'];
            
            $stmtSeed = $db->prepare("INSERT INTO page_views (ip_address, user_agent, request_uri, referer, created_at) VALUES (?, ?, ?, ?, ?)");
            for ($i = 0; $i < 250; $i++) {
                $ip = "192.168.1." . rand(10, 120);
                $ua = "Mozilla/5.0 (Windows NT 10.0; Win64; x64)";
                $req = $paths[array_rand($paths)];
                $ref = $referers[array_rand($referers)];
                
                $daysAgo = rand(0, 6);
                $hoursAgo = rand(0, 23);
                $minutesAgo = rand(0, 59);
                $timeStr = date('Y-m-d H:i:s', strtotime("-$daysAgo days -$hoursAgo hours -$minutesAgo minutes"));
                
                $stmtSeed->execute([$ip, $ua, $req, $ref, $timeStr]);
            }
        }
    } catch (Exception $e) {
        error_log("Pageview tracking failed: " . $e->getMessage());
    }
}

$viewData = [];

if ($path === 'admin') {
    redirect('admin/dashboard');
}

if ($path === 'admin/login') {
    if (isAdminLoggedIn()) {
        redirect('admin/dashboard');
    }

    if ($method === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrfToken($token)) {
            setFlash('error', 'Sesi login tidak valid. Silakan coba lagi.');
            redirect('admin/login');
        }

        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === $adminConfig['email'] && password_verify($password, $adminConfig['password_hash'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_name'] = $adminConfig['name'];
            $_SESSION['admin_email'] = $adminConfig['email'];
            redirect('admin/dashboard');
        }

        setFlash('error', 'Email atau password salah.');
        redirect('admin/login');
    }

    $view = __DIR__ . '/app/views/admin/login.php';
} elseif ($path === 'admin/logout') {
    $_SESSION = [];
    session_destroy();
    redirect('admin/login');
} elseif (str_starts_with($path, 'admin')) {
    requireAdmin();

    if ($method === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        $redirectTarget = 'admin/dashboard';
        if (str_starts_with($path, 'admin/ulasan')) {
            $redirectTarget = 'admin/ulasan';
        } elseif (str_starts_with($path, 'admin/kategori')) {
            $redirectTarget = 'admin/kategori';
        } elseif (str_starts_with($path, 'admin/destinasi')) {
            $redirectTarget = 'admin/destinasi';
        }
        if (!verifyCsrfToken($token)) {
            setFlash('error', 'Token tidak valid. Silakan ulangi.');
            redirect($redirectTarget);
        }

        if ($path === 'admin/destinasi/create') {
            try {
                $db = getDB();
                $name = sanitize($_POST['name'] ?? '');
                $categoryId = (int)($_POST['category_id'] ?? 0);
                $ticketPrice = (int)($_POST['ticket_price'] ?? 0);
                $estFood = (int)($_POST['est_food'] ?? 0);
                $estParking = (int)($_POST['est_parking'] ?? 0);
                $openTime = sanitize($_POST['open_time'] ?? '07:00');
                $closeTime = sanitize($_POST['close_time'] ?? '17:00');
                $operationalDay = sanitize($_POST['operational_day'] ?? '');
                $description = sanitize($_POST['description'] ?? '');
                $mapsEmbed = $_POST['maps_embed'] ?? ''; // Maps can contain HTML
                $facilities = array_map('trim', explode(',', $_POST['facilities'] ?? ''));
                $facilitiesJson = json_encode($facilities);
                
                $slug = slugify($name);
                
                // Handle file upload
                $mainPhoto = '';
                if (isset($_FILES['main_photo']) && $_FILES['main_photo']['error'] === UPLOAD_ERR_OK) {
                    $tmpName = $_FILES['main_photo']['tmp_name'];
                    $fileName = basename($_FILES['main_photo']['name']);
                    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
                    
                    if (in_array($ext, $allowedExts)) {
                        $newName = $slug . '-' . time() . '.' . $ext;
                        $uploadPath = __DIR__ . '/public/images/destinations/' . $newName;
                        if (!is_dir(__DIR__ . '/public/images/destinations')) {
                            mkdir(__DIR__ . '/public/images/destinations', 0755, true);
                        }
                        if (move_uploaded_file($tmpName, $uploadPath)) {
                            $mainPhoto = 'public/images/destinations/' . $newName;
                        }
                    }
                }
                
                $status = ($_POST['status'] ?? '') === 'inactive' ? 'inactive' : 'active';

                $stmt = $db->prepare("INSERT INTO destinations (category_id, name, slug, description, main_photo, ticket_price, est_food, est_parking, open_time, close_time, operational_day, maps_embed, facilities, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$categoryId, $name, $slug, $description, $mainPhoto, $ticketPrice, $estFood, $estParking, $openTime, $closeTime, $operationalDay, $mapsEmbed, $facilitiesJson, $status]);
                
                setFlash('success', 'Destinasi berhasil disimpan.');
            } catch (Exception $e) {
                setFlash('error', 'Gagal menyimpan destinasi: ' . $e->getMessage());
            }
            redirect('admin/destinasi');
        }

        if ($path === 'admin/destinasi/edit') {
            try {
                $db = getDB();
                $id = (int)($_POST['id'] ?? 0);
                $name = sanitize($_POST['name'] ?? '');
                $categoryId = (int)($_POST['category_id'] ?? 0);
                $ticketPrice = (int)($_POST['ticket_price'] ?? 0);
                $estFood = (int)($_POST['est_food'] ?? 0);
                $estParking = (int)($_POST['est_parking'] ?? 0);
                $openTime = sanitize($_POST['open_time'] ?? '07:00');
                $closeTime = sanitize($_POST['close_time'] ?? '17:00');
                $operationalDay = sanitize($_POST['operational_day'] ?? '');
                $description = sanitize($_POST['description'] ?? '');
                $mapsEmbed = $_POST['maps_embed'] ?? ''; // Maps can contain HTML
                $facilities = array_map('trim', explode(',', $_POST['facilities'] ?? ''));
                $facilitiesJson = json_encode($facilities);
                
                $slug = slugify($name);
                
                // Retrieve existing destination
                $stmtFind = $db->prepare("SELECT main_photo FROM destinations WHERE id = ?");
                $stmtFind->execute([$id]);
                $dest = $stmtFind->fetch();
                if (!$dest) {
                    throw new Exception('Destinasi tidak ditemukan');
                }
                $mainPhoto = $dest['main_photo'];
                
                // Handle file upload
                if (isset($_FILES['main_photo']) && $_FILES['main_photo']['error'] === UPLOAD_ERR_OK) {
                    $tmpName = $_FILES['main_photo']['tmp_name'];
                    $fileName = basename($_FILES['main_photo']['name']);
                    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
                    
                    if (in_array($ext, $allowedExts)) {
                        $newName = $slug . '-' . time() . '.' . $ext;
                        $uploadPath = __DIR__ . '/public/images/destinations/' . $newName;
                        if (!is_dir(__DIR__ . '/public/images/destinations')) {
                            mkdir(__DIR__ . '/public/images/destinations', 0755, true);
                        }
                        if (move_uploaded_file($tmpName, $uploadPath)) {
                            $mainPhoto = 'public/images/destinations/' . $newName;
                        }
                    }
                }
                
                $status = ($_POST['status'] ?? '') === 'inactive' ? 'inactive' : 'active';

                $stmt = $db->prepare("UPDATE destinations SET category_id = ?, name = ?, slug = ?, description = ?, main_photo = ?, ticket_price = ?, est_food = ?, est_parking = ?, open_time = ?, close_time = ?, operational_day = ?, maps_embed = ?, facilities = ?, status = ? WHERE id = ?");
                $stmt->execute([$categoryId, $name, $slug, $description, $mainPhoto, $ticketPrice, $estFood, $estParking, $openTime, $closeTime, $operationalDay, $mapsEmbed, $facilitiesJson, $status, $id]);
                
                setFlash('success', 'Perubahan destinasi tersimpan.');
            } catch (Exception $e) {
                setFlash('error', 'Gagal memperbarui destinasi: ' . $e->getMessage());
            }
            redirect('admin/destinasi');
        }

        if ($path === 'admin/kategori/create') {
            try {
                $db = getDB();
                $name = sanitize($_POST['name'] ?? '');
                $slug = sanitize($_POST['slug'] ?? slugify($name));
                $displayOrder = (int)($_POST['sort_order'] ?? 1);
                
                // Icon upload could be handled similarly to destinations
                $iconPath = 'public/images/placeholders/category-placeholder.svg';
                if (isset($_FILES['icon_img']) && $_FILES['icon_img']['error'] === UPLOAD_ERR_OK) {
                    $tmpName = $_FILES['icon_img']['tmp_name'];
                    $fileName = basename($_FILES['icon_img']['name']);
                    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
                    
                    if (in_array($ext, $allowedExts)) {
                        $newName = 'cat-' . $slug . '-' . time() . '.' . $ext;
                        $uploadPath = __DIR__ . '/public/images/placeholders/' . $newName;
                        if (move_uploaded_file($tmpName, $uploadPath)) {
                            $iconPath = 'public/images/placeholders/' . $newName;
                        }
                    }
                }
                
                $id = (int)($_POST['id'] ?? 0);
                if ($id > 0) {
                    $stmt = $db->prepare("UPDATE categories SET name = ?, slug = ?, sort_order = ?, icon_img = ? WHERE id = ?");
                    $stmt->execute([$name, $slug, $displayOrder, $iconPath, $id]);
                    setFlash('success', 'Kategori berhasil diperbarui.');
                } else {
                    $stmt = $db->prepare("INSERT INTO categories (name, slug, sort_order, icon_img) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$name, $slug, $displayOrder, $iconPath]);
                    setFlash('success', 'Kategori berhasil disimpan.');
                }
            } catch (Exception $e) {
                setFlash('error', 'Gagal menyimpan kategori: ' . $e->getMessage());
            }
            redirect('admin/kategori');
        }

        if ($path === 'admin/kategori/delete') {
            try {
                $db = getDB();
                $id = (int)($_POST['id'] ?? 0);
                $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
                $stmt->execute([$id]);
                setFlash('success', 'Kategori berhasil dihapus.');
            } catch (Exception $e) {
                setFlash('error', 'Gagal menghapus kategori: ' . $e->getMessage());
            }
            redirect('admin/kategori');
        }

        if ($path === 'admin/destinasi/delete') {
            try {
                $db = getDB();
                $id = (int)($_POST['id'] ?? 0);
                $stmt = $db->prepare("DELETE FROM destinations WHERE id = ?");
                $stmt->execute([$id]);
                setFlash('success', 'Destinasi berhasil dihapus.');
            } catch (Exception $e) {
                setFlash('error', 'Gagal menghapus destinasi: ' . $e->getMessage());
            }
            redirect('admin/destinasi');
        }

        if ($path === 'admin/ulasan/aksi') {
            try {
                $db = getDB();
                $action = $_POST['action'] ?? '';
                $id = (int)($_POST['id'] ?? 0);
                
                if ($action === 'approve') {
                    $stmt = $db->prepare("UPDATE reviews SET status = 'approved' WHERE id = ?");
                    $stmt->execute([$id]);
                    setFlash('success', 'Ulasan berhasil disetujui.');
                } elseif ($action === 'reject') {
                    $stmt = $db->prepare("UPDATE reviews SET status = 'rejected' WHERE id = ?");
                    $stmt->execute([$id]);
                    setFlash('success', 'Ulasan berhasil ditolak.');
                } elseif ($action === 'approve_all') {
                    $affected = $db->exec("UPDATE reviews SET status = 'approved' WHERE status = 'pending'");
                    setFlash('success', $affected . ' ulasan pending berhasil disetujui.');
                } else {
                    setFlash('error', 'Aksi tidak dikenal.');
                }
            } catch (Exception $e) {
                setFlash('error', 'Gagal memproses aksi ulasan: ' . $e->getMessage());
            }
            redirect('admin/ulasan');
        }
    }

    if ($path === 'admin/dashboard') {
        $view = __DIR__ . '/app/views/admin/dashboard.php';
    } elseif ($path === 'admin/analitik/export') {
        $view = __DIR__ . '/app/views/admin/export.php';
    } elseif ($path === 'admin/analitik') {
        $view = __DIR__ . '/app/views/admin/analitik.php';
    } elseif ($path === 'admin/destinasi') {
        $view = __DIR__ . '/app/views/admin/destination/index.php';
    } elseif ($path === 'admin/destinasi/create') {
        $view = __DIR__ . '/app/views/admin/destination/create.php';
    } elseif ($path === 'admin/destinasi/edit') {
        $view = __DIR__ . '/app/views/admin/destination/edit.php';
    } elseif ($path === 'admin/kategori') {
        $view = __DIR__ . '/app/views/admin/category/index.php';
    } elseif ($path === 'admin/kategori/create') {
        $view = __DIR__ . '/app/views/admin/category/create-edit.php';
        $viewData['mode'] = 'create';
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            try {
                $db = getDB();
                $stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
                $stmt->execute([$id]);
                $category = $stmt->fetch();
                if ($category) {
                    $viewData['mode'] = 'edit';
                    $viewData['category'] = $category;
                }
            } catch (Exception $e) {
                error_log("DB Error: " . $e->getMessage());
            }
        }
    } elseif ($path === 'admin/ulasan') {
        $view = __DIR__ . '/app/views/admin/review/index.php';
    } elseif ($path === 'admin/pengaturan') {
        $view = __DIR__ . '/app/views/admin/pengaturan.php';
    } else {
        http_response_code(404);
        $view = __DIR__ . '/app/views/errors/404.php';
    }
} elseif ($path === '') {
    $view = __DIR__ . '/app/views/home/index.php';
} elseif ($path === 'destinasi') {
    $view = __DIR__ . '/app/views/destination/index.php';
} elseif (($segments[0] ?? '') === 'destinasi' && !empty($segments[1])) {
    $view = __DIR__ . '/app/views/destination/show.php';
    $viewData['slug'] = $segments[1];
} elseif ($path === 'rekomendasi') {
    $view = __DIR__ . '/app/views/destination/results.php';
} else {
    http_response_code(404);
    $view = __DIR__ . '/app/views/errors/404.php';
}

require $view;
