<?php
$baseUrl = defined('BASE_URL') ? BASE_URL : '/';
$error = getFlash('error');
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Admin | KebumenGo</title>
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
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-12px) rotate(0.5deg); }
        }
        @keyframes pulse-slow {
            0%, 100% { opacity: 0.2; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(1.1); }
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-4px); }
            75% { transform: translateX(4px); }
        }
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        .animate-pulse-slow {
            animation: pulse-slow 8s ease-in-out infinite;
        }
        .animate-shake {
            animation: shake 0.3s ease-in-out;
        }
        /* Custom scrollbar just in case */
        ::-webkit-scrollbar {
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
    </style>
</head>
<body class="bg-slate-50 font-sans text-textPrimary antialiased selection:bg-primary/20 selection:text-primary">
    <div class="flex min-h-screen">
        
        <!-- Sisi Kiri: Ilustrasi & Branding (Tampil di md ke atas) -->
        <div class="hidden md:flex md:w-1/2 lg:w-3/5 bg-gradient-to-br from-primary via-indigo-700 to-slate-900 p-12 lg:p-16 flex-col justify-between relative overflow-hidden">
            <!-- Background Decorative Glows -->
            <div class="absolute -top-40 -left-40 w-96 h-96 bg-blue-400/20 rounded-full blur-3xl animate-pulse-slow"></div>
            <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-indigo-500/20 rounded-full blur-3xl animate-pulse-slow" style="animation-delay: 2s;"></div>
            <div class="absolute top-1/2 left-1/3 -translate-x-1/2 -translate-y-1/2 w-80 h-80 bg-purple-500/10 rounded-full blur-3xl animate-pulse-slow" style="animation-delay: 4s;"></div>
            
            <!-- Top Header (Logo) -->
            <div class="flex items-center gap-3 z-10">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-white font-bold shadow-lg">
                    <img src="<?= $baseUrl; ?>public/images/logo.svg" alt="KG" class="h-6 w-6 object-contain">
                </div>
                <div>
                    <h2 class="text-white font-bold tracking-wide text-sm opacity-90">KebumenGo Portal</h2>
                    <p class="text-indigo-200 text-xs">Sistem Administrasi Konten</p>
                </div>
            </div>

            <!-- Central Content (Illustration & Welcome text) -->
            <div class="flex-1 flex flex-col items-center justify-center z-10 py-10">
                <div class="relative w-full max-w-md lg:max-w-lg flex items-center justify-center">
                    <!-- Glow behind illustration -->
                    <div class="absolute inset-0 bg-blue-500/15 rounded-full filter blur-3xl animate-float"></div>
                    <img src="<?= $baseUrl; ?>public/images/login.svg" alt="KebumenGo Login Illustration" 
                         class="w-full max-h-[360px] object-contain drop-shadow-[0_20px_50px_rgba(37,99,235,0.3)] animate-float">
                </div>
                
                <div class="mt-10 text-center max-w-md">
                    <h3 class="text-white text-2xl lg:text-3xl font-extrabold tracking-tight">Kembangkan Kebumen Lebih Baik</h3>
                    <p class="text-indigo-100/80 text-sm mt-3 leading-relaxed">
                        Kelola destinasi wisata, ulasan kuliner lokal, dan acara menarik di Kabupaten Kebumen melalui satu dashboard admin.
                    </p>
                </div>
            </div>

            <!-- Footer Panel Info -->
            <div class="text-indigo-200/50 text-xs z-10 flex justify-between items-center border-t border-white/10 pt-4">
                <span>&copy; <?= date('Y'); ?> KebumenGo Team.</span>
                <span>v2.0-stable</span>
            </div>
        </div>

        <!-- Sisi Kanan: Form Login (Lebar penuh di mobile, 40-50% di desktop) -->
        <div class="w-full md:w-1/2 lg:w-2/5 flex flex-col justify-center bg-white px-6 py-12 sm:px-12 lg:px-16 relative shadow-2xl z-10">
            <!-- Mobile Brand Logo Header -->
            <div class="flex items-center gap-3 md:hidden absolute top-8 left-6 sm:left-12">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary text-white font-bold shadow-md shadow-primary/20">
                    <img src="<?= $baseUrl; ?>public/images/logo.svg" alt="KG" class="h-6 w-6 object-contain brightness-0 invert">
                </div>
                <div>
                    <h1 class="text-base font-bold text-slate-800 tracking-tight">Admin KebumenGo</h1>
                </div>
            </div>

            <div class="max-w-md w-full mx-auto space-y-8 mt-8 md:mt-0">
                <!-- Welcome Title -->
                <div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Masuk Admin</h2>
                    <p class="text-slate-500 text-sm mt-2">Gunakan akses resmi Anda untuk mengelola konten web.</p>
                </div>

                <!-- Error Alert Box -->
                <?php if ($error): ?>
                    <div class="rounded-2xl bg-rose-50 border border-rose-100 p-4 text-sm text-rose-600 flex items-start gap-3 animate-shake shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 mt-0.5 shrink-0 text-rose-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                        </svg>
                        <div>
                            <span class="font-bold">Gagal Masuk:</span> <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Form -->
                <form class="space-y-6" method="post" action="<?= $baseUrl; ?>admin/login">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken(); ?>">
                    
                    <!-- Email field -->
                    <div class="space-y-2">
                        <label for="email" class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Alamat Email</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-primary transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.62a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                                </svg>
                            </div>
                            <input id="email" type="email" name="email" required placeholder="admin@kebumengo.id" 
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50/50 pl-11 pr-4 py-3.5 text-sm placeholder-slate-400 focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/10 focus:outline-none transition-all duration-200">
                        </div>
                    </div>

                    <!-- Password field -->
                    <div class="space-y-2">
                        <label for="password" class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Kata Sandi</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-primary transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                </svg>
                            </div>
                            <input id="password" type="password" name="password" required placeholder="Masukkan password" 
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50/50 pl-11 pr-4 py-3.5 text-sm placeholder-slate-400 focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/10 focus:outline-none transition-all duration-200">
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button class="w-full rounded-2xl bg-slate-900 hover:bg-slate-800 active:scale-[0.98] text-white py-3.5 text-sm font-semibold shadow-md shadow-slate-900/10 hover:shadow-xl hover:shadow-slate-900/15 transition-all duration-200" type="submit">
                        Masuk ke Dashboard
                    </button>
                </form>

                <!-- Demo Credentials Box -->
                <div class="rounded-2xl border border-amber-100 bg-amber-50/40 p-4 text-center">
                    <p class="text-xs font-bold text-amber-800 mb-1.5 uppercase tracking-wider">Akses Uji Coba</p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-1 sm:gap-4 text-xs text-amber-700">
                        <span>Email: <strong class="font-semibold text-slate-800">admin@kebumengo.id</strong></span>
                        <span class="hidden sm:inline text-amber-200">|</span>
                        <span>Pass: <strong class="font-semibold text-slate-800">kebumen2026</strong></span>
                    </div>
                </div>

                <!-- Mobile Footer Copyright -->
                <div class="text-center text-xs text-slate-400 md:hidden pt-4">
                    &copy; <?= date('Y'); ?> KebumenGo. All rights reserved.
                </div>
            </div>
        </div>
    </div>
</body>
</html>