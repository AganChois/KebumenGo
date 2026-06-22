<?php
declare(strict_types=1);

session_start();

require __DIR__ . '/app/helpers/functions.php';

define('APP_NAME', 'KebumenGo');

// Automatically detect the base URL path (important for subfolder deployments like XAMPP)
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$baseDir = str_replace('\\', '/', dirname($scriptName));
if ($baseDir === '/' || $baseDir === '\\') {
    define('BASE_URL', '/');
} else {
    define('BASE_URL', rtrim($baseDir, '/') . '/');
}

$adminConfig = require __DIR__ . '/config/admin.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
$path = $requestUri;

// Strip base URL path from request URI if running in a subdirectory
if (BASE_URL !== '/' && str_starts_with($requestUri, rtrim(BASE_URL, '/'))) {
    $path = substr($requestUri, strlen(rtrim(BASE_URL, '/')));
}
$path = trim($path, '/');
$segments = $path === '' ? [] : explode('/', $path);

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
                
                $stmt = $db->prepare("INSERT INTO destinations (category_id, name, slug, description, main_photo, ticket_price, est_food, est_parking, open_time, close_time, operational_day, maps_embed, facilities) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$categoryId, $name, $slug, $description, $mainPhoto, $ticketPrice, $estFood, $estParking, $openTime, $closeTime, $operationalDay, $mapsEmbed, $facilitiesJson]);
                
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
                
                $stmt = $db->prepare("UPDATE destinations SET category_id = ?, name = ?, slug = ?, description = ?, main_photo = ?, ticket_price = ?, est_food = ?, est_parking = ?, open_time = ?, close_time = ?, operational_day = ?, maps_embed = ?, facilities = ? WHERE id = ?");
                $stmt->execute([$categoryId, $name, $slug, $description, $mainPhoto, $ticketPrice, $estFood, $estParking, $openTime, $closeTime, $operationalDay, $mapsEmbed, $facilitiesJson, $id]);
                
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
            setFlash('success', 'Status ulasan berhasil diperbarui (demo).');
            redirect('admin/ulasan');
        }
    }

    if ($path === 'admin/dashboard') {
        $view = __DIR__ . '/app/views/admin/dashboard.php';
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
