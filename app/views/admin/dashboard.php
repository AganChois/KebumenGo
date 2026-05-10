<?php
$baseUrl = defined('BASE_URL') ? BASE_URL : '/';
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Destinasi | Admin KebumenGo</title>
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
                    <a href="<?= $baseUrl; ?>admin/dashboard" class="flex items-center gap-3 rounded-[10px] px-3 py-2 text-textSecondary hover:bg-surface">
                        <i data-lucide="layout-dashboard" class="h-4 w-4"></i>
                        Dashboard
                    </a>
                    <a href="#" class="flex items-center gap-3 rounded-[10px] px-3 py-2 text-textSecondary hover:bg-surface">
                        <i data-lucide="bar-chart-2" class="h-4 w-4"></i>
                        Analitik
                    </a>
                    <a href="#" class="flex items-center gap-3 rounded-[10px] bg-textPrimary px-3 py-2 text-white">
                        <i data-lucide="map-pin" class="h-4 w-4"></i>
                        Destinasi
                    </a>
                    <a href="#" class="flex items-center gap-3 rounded-[10px] px-3 py-2 text-textSecondary hover:bg-surface">
                        <i data-lucide="users" class="h-4 w-4"></i>
                        Pengunjung
                    </a>
                    <a href="#" class="flex items-center gap-3 rounded-[10px] px-3 py-2 text-textSecondary hover:bg-surface">
                        <i data-lucide="star" class="h-4 w-4"></i>
                        Ulasan
                    </a>
                    <a href="#" class="flex items-center justify-between rounded-[10px] px-3 py-2 text-textSecondary hover:bg-surface">
                        <span class="flex items-center gap-3">
                            <i data-lucide="message-square" class="h-4 w-4"></i>
                            Pesan
                        </span>
                        <span class="rounded-lg bg-accent/20 px-2 py-0.5 text-xs font-semibold text-accent">5</span>
                    </a>
                    <a href="#" class="flex items-center gap-3 rounded-[10px] px-3 py-2 text-textSecondary hover:bg-surface">
                        <i data-lucide="bell" class="h-4 w-4"></i>
                        Notifikasi
                    </a>
                </nav>
            </div>

            <div class="mt-8">
                <p class="text-xs font-semibold uppercase tracking-widest text-textSecondary">Account</p>
                <nav class="mt-4 grid gap-2">
                    <a href="#" class="flex items-center gap-3 rounded-[10px] px-3 py-2 text-textSecondary hover:bg-surface">
                        <i data-lucide="file-text" class="h-4 w-4"></i>
                        Laporan
                    </a>
                    <a href="#" class="flex items-center gap-3 rounded-[10px] px-3 py-2 text-textSecondary hover:bg-surface">
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
                    <h1 class="text-2xl font-semibold">Destinasi</h1>
                    <p class="text-sm text-textSecondary">Ringkasan performa destinasi wisata Kebumen.</p>
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
                <section class="grid gap-4 lg:grid-cols-4">
                    <div class="rounded-xl border border-border bg-surface p-5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-textSecondary">
                                    <i data-lucide="map-pin" class="h-5 w-5"></i>
                                </div>
                                <span class="text-sm text-textSecondary">Total Destinasi</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button class="rounded-lg bg-textPrimary px-3 py-1.5 text-xs font-semibold text-white">Laporan ↓</button>
                                <button class="rounded-lg p-2 text-textSecondary hover:bg-white">
                                    <i data-lucide="more-horizontal" class="h-4 w-4"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mt-5 flex items-end justify-between">
                            <div class="text-3xl font-semibold">48</div>
                            <div class="flex items-center gap-1 text-sm font-semibold text-emerald-600">
                                <i data-lucide="arrow-up-right" class="h-4 w-4"></i>
                                12%
                            </div>
                        </div>
                    </div>
                    <div class="rounded-xl border border-border bg-surface p-5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-textSecondary">
                                    <i data-lucide="clock" class="h-5 w-5"></i>
                                </div>
                                <span class="text-sm text-textSecondary">Destinasi Pending</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button class="rounded-lg bg-textPrimary px-3 py-1.5 text-xs font-semibold text-white">Laporan ↓</button>
                                <button class="rounded-lg p-2 text-textSecondary hover:bg-white">
                                    <i data-lucide="more-horizontal" class="h-4 w-4"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mt-5 flex items-end justify-between">
                            <div class="text-3xl font-semibold">7</div>
                            <div class="flex items-center gap-1 text-sm font-semibold text-emerald-600">
                                <i data-lucide="arrow-up-right" class="h-4 w-4"></i>
                                3%
                            </div>
                        </div>
                    </div>
                    <div class="rounded-xl border border-border bg-surface p-5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-textSecondary">
                                    <i data-lucide="star" class="h-5 w-5"></i>
                                </div>
                                <span class="text-sm text-textSecondary">Total Ulasan</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button class="rounded-lg bg-textPrimary px-3 py-1.5 text-xs font-semibold text-white">Laporan ↓</button>
                                <button class="rounded-lg p-2 text-textSecondary hover:bg-white">
                                    <i data-lucide="more-horizontal" class="h-4 w-4"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mt-5 flex items-end justify-between">
                            <div class="text-3xl font-semibold">1.284</div>
                            <div class="flex items-center gap-1 text-sm font-semibold text-emerald-600">
                                <i data-lucide="arrow-up-right" class="h-4 w-4"></i>
                                8.5%
                            </div>
                        </div>
                    </div>
                    <div class="rounded-xl border border-border bg-surface p-5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-textSecondary">
                                    <i data-lucide="users" class="h-5 w-5"></i>
                                </div>
                                <span class="text-sm text-textSecondary">Pengunjung Bulan Ini</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button class="rounded-lg bg-textPrimary px-3 py-1.5 text-xs font-semibold text-white">Laporan ↓</button>
                                <button class="rounded-lg p-2 text-textSecondary hover:bg-white">
                                    <i data-lucide="more-horizontal" class="h-4 w-4"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mt-5 flex items-end justify-between">
                            <div class="text-3xl font-semibold">9.420</div>
                            <div class="flex items-center gap-1 text-sm font-semibold text-emerald-600">
                                <i data-lucide="arrow-up-right" class="h-4 w-4"></i>
                                5.2%
                            </div>
                        </div>
                    </div>
                </section>

                <section class="grid grid-cols-12 gap-6">
                    <div class="col-span-12 xl:col-start-6 xl:col-span-7 rounded-xl border border-border bg-surface p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-lg font-semibold">Statistik Kunjungan</h2>
                                <p class="text-sm text-textSecondary">Tren kunjungan wisatawan per bulan</p>
                            </div>
                            <button class="rounded-lg border border-border bg-white px-3 py-1.5 text-xs text-textSecondary">Per 6 bulan</button>
                        </div>
                        <div class="mt-6 h-64">
                            <canvas id="visitChart" aria-label="Grafik kunjungan"></canvas>
                        </div>
                    </div>
                </section>

                <section class="grid grid-cols-12 gap-6">
                    <div class="col-span-12 xl:col-span-4 rounded-xl border border-border bg-surface p-6">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold">Sebaran per Kecamatan</h2>
                            <i data-lucide="external-link" class="h-4 w-4 text-textSecondary"></i>
                        </div>
                        <div class="mt-6 space-y-4">
                            <div>
                                <div class="flex items-center justify-between text-sm">
                                    <span>Ayah</span>
                                    <span class="font-semibold">12 destinasi</span>
                                </div>
                                <div class="mt-2 h-2 rounded-full bg-border">
                                    <div class="h-2 w-full rounded-full bg-textPrimary"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex items-center justify-between text-sm">
                                    <span>Buayan</span>
                                    <span class="font-semibold">9 destinasi</span>
                                </div>
                                <div class="mt-2 h-2 rounded-full bg-border">
                                    <div class="h-2 w-[75%] rounded-full bg-textPrimary"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex items-center justify-between text-sm">
                                    <span>Puring</span>
                                    <span class="font-semibold">8 destinasi</span>
                                </div>
                                <div class="mt-2 h-2 rounded-full bg-border">
                                    <div class="h-2 w-[66%] rounded-full bg-textPrimary"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex items-center justify-between text-sm">
                                    <span>Kebumen Kota</span>
                                    <span class="font-semibold">7 destinasi</span>
                                </div>
                                <div class="mt-2 h-2 rounded-full bg-border">
                                    <div class="h-2 w-[58%] rounded-full bg-textPrimary"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-span-12 xl:col-span-5 rounded-xl border border-border bg-surface p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-lg font-semibold">Destinasi Terpopuler</h2>
                                <p class="text-sm text-textSecondary">Update terakhir minggu ini</p>
                            </div>
                            <button class="rounded-lg border border-border bg-white px-3 py-1.5 text-xs text-textSecondary">Filter</button>
                        </div>
                        <div class="mt-6 space-y-4">
                            <div class="flex items-center justify-between gap-4 rounded-xl bg-white p-4">
                                <div class="flex items-center gap-4">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-surface text-textSecondary">
                                        <i data-lucide="map-pin" class="h-5 w-5"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold">Pantai Logending</p>
                                        <span class="text-xs text-textSecondary">Rp 10.000</span>
                                        <div class="mt-1 text-xs text-textSecondary">12 Jan 2025</div>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end gap-2 text-xs">
                                    <span class="rounded-lg bg-emerald-100 px-2 py-1 font-semibold text-emerald-700">Aktif</span>
                                    <span class="rounded-lg bg-textPrimary px-2 py-1 font-semibold text-white">Populer</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between gap-4 rounded-xl bg-white p-4">
                                <div class="flex items-center gap-4">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-surface text-textSecondary">
                                        <i data-lucide="map-pin" class="h-5 w-5"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold">Goa Jatijajar</p>
                                        <span class="text-xs text-textSecondary">Rp 15.000</span>
                                        <div class="mt-1 text-xs text-textSecondary">8 Jan 2025</div>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end gap-2 text-xs">
                                    <span class="rounded-lg bg-emerald-100 px-2 py-1 font-semibold text-emerald-700">Aktif</span>
                                    <span class="rounded-lg bg-amber-100 px-2 py-1 font-semibold text-amber-700">Pending</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between gap-4 rounded-xl bg-white p-4">
                                <div class="flex items-center gap-4">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-surface text-textSecondary">
                                        <i data-lucide="map-pin" class="h-5 w-5"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold">Benteng Van der Wijck</p>
                                        <span class="text-xs text-textSecondary">Rp 10.000</span>
                                        <div class="mt-1 text-xs text-textSecondary">6 Jan 2025</div>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end gap-2 text-xs">
                                    <span class="rounded-lg bg-emerald-100 px-2 py-1 font-semibold text-emerald-700">Aktif</span>
                                    <span class="rounded-lg bg-amber-100 px-2 py-1 font-semibold text-amber-700">Pending</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-span-12 xl:col-span-3 space-y-4">
                        <button class="flex w-full items-center justify-center gap-2 rounded-xl bg-textPrimary px-4 py-3 text-sm font-semibold text-white">
                            <i data-lucide="plus" class="h-4 w-4"></i>
                            Tambah Destinasi
                        </button>
                        <div class="rounded-xl border border-border bg-surface p-5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-semibold">Des 2, 2024</p>
                                </div>
                                <div class="flex items-center gap-2 text-textSecondary">
                                    <button class="rounded-lg p-1 hover:bg-white">
                                        <i data-lucide="chevron-left" class="h-4 w-4"></i>
                                    </button>
                                    <button class="rounded-lg p-1 hover:bg-white">
                                        <i data-lucide="chevron-right" class="h-4 w-4"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="mt-4 grid grid-cols-7 gap-2 text-center text-xs text-textSecondary">
                                <span>M</span><span>T</span><span>W</span><span>T</span><span>F</span><span>S</span><span>S</span>
                            </div>
                            <div class="mt-3 grid grid-cols-7 gap-2 text-center text-sm">
                                <span class="text-textSecondary">1</span>
                                <span class="text-textSecondary">2</span>
                                <span class="rounded-lg bg-textPrimary py-1 text-white">3</span>
                                <span class="text-textSecondary">4</span>
                                <span class="text-textSecondary">5</span>
                                <span class="text-textSecondary">6</span>
                                <span class="text-textSecondary">7</span>
                                <span class="text-textSecondary">8</span>
                                <span class="text-textSecondary">9</span>
                                <span class="text-textSecondary">10</span>
                                <span class="text-textSecondary">11</span>
                                <span class="text-textSecondary">12</span>
                                <span class="text-textSecondary">13</span>
                                <span class="text-textSecondary">14</span>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    <script>
        lucide.createIcons();

        const visitData = [4800, 6200, 7500, 8100, 9420, 8800];
        const targetData = [5000, 6500, 7000, 7800, 8200, 9000];
        const maxVisit = Math.max(...visitData);

        const ctx = document.getElementById('visitChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
                    datasets: [
                        {
                            label: 'Kunjungan',
                            data: visitData,
                            borderColor: '#2563EB',
                            backgroundColor: 'rgba(37, 99, 235, 0.12)',
                            fill: true,
                            tension: 0.35,
                            pointRadius: 4,
                            pointBackgroundColor: '#2563EB'
                        },
                        {
                            label: 'Target',
                            data: targetData,
                            borderColor: '#E2E8F0',
                            borderDash: [6, 6],
                            tension: 0.35,
                            pointRadius: 0
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const value = context.parsed.y || 0;
                                    let label = `${context.dataset.label}: ${value.toLocaleString('id-ID')}`;
                                    if (context.dataset.label === 'Kunjungan' && value === maxVisit) {
                                        label += ' (+37%)';
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#64748B'
                            }
                        },
                        y: {
                            grid: {
                                color: '#E2E8F0'
                            },
                            ticks: {
                                color: '#64748B',
                                callback: function (value) {
                                    return value.toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            });
        }
    </script>
</body>
</html>
