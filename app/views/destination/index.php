<?php
$pageTitle = 'Destinasi';
try {
    $db = getDB();
    $stmt = $db->query("
        SELECT d.*, c.name as category, 
               COALESCE(AVG(r.rating), 0) as rating, 
               COUNT(r.id) as reviews,
               IF(d.ticket_price < 20000, 'Murah', NULL) as badge
        FROM destinations d
        JOIN categories c ON d.category_id = c.id
        LEFT JOIN reviews r ON r.dest_id = d.id AND r.status = 'approved'
        WHERE d.status = 'active'
        GROUP BY d.id
        ORDER BY d.created_at DESC
    ");
    $destinations = $stmt->fetchAll();
    
    // Fallback if DB is completely empty or query fails (though exception will be caught)
    if (empty($destinations)) {
        $destinations = [];
    }
} catch (Exception $e) {
    // If DB fails, fallback to empty array or show error
    $destinations = [];
    error_log("DB Error: " . $e->getMessage());
}

$baseUrl = defined('BASE_URL') ? BASE_URL : '/';

ob_start();
?>
<section class="section page-hero" data-reveal>
    <div class="container">
        <span class="eyebrow">Direktori destinasi</span>
        <h1>Semua destinasi wisata Kebumen</h1>
        <p>Filter cepat, urutkan harga, dan temukan destinasi yang cocok untuk rencana liburanmu.</p>
        <div class="info-strip">
            <span>Tip: mulai dari budget Rp 75.000 untuk 1 orang.</span>
            <a class="btn btn-outline" href="<?= $baseUrl; ?>rekomendasi">Coba hitung budget</a>
        </div>
        <div class="filter-bar">
            <div class="filter-tabs">
                <button class="tab is-active" data-category-filter="semua" type="button">Semua</button>
                <button class="tab" data-category-filter="pantai" type="button">Pantai</button>
                <button class="tab" data-category-filter="goa" type="button">Goa</button>
                <button class="tab" data-category-filter="sejarah" type="button">Sejarah</button>
                <button class="tab" data-category-filter="kuliner" type="button">Kuliner</button>
                <button class="tab" data-category-filter="alam" type="button">Alam</button>
                <button class="tab" data-category-filter="buatan" type="button">Buatan</button>
            </div>
            <div class="filter-actions">
                <input type="text" placeholder="Cari nama destinasi" data-search-input />
                <select data-sort-select>
                    <option value="price-asc">Urutkan: Termurah</option>
                    <option value="rating-desc">Urutkan: Rating tertinggi</option>
                </select>
            </div>
        </div>
    </div>
</section>

<style>
.page-btn.is-active {
    background: var(--primary);
    color: #fff;
    border-color: var(--primary);
    font-weight: 600;
}
</style>

<section class="section surface" data-reveal>
    <div class="container">
        <div class="destination-grid">
            <?php foreach ($destinations as $destination): ?>
                <?php include __DIR__ . '/../partials/destination-card.php'; ?>
            <?php endforeach; ?>
        </div>
        <div class="pagination" data-pagination-container>
            <!-- Rendered dynamically -->
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const grid = document.querySelector('.destination-grid');
    if (!grid) return;

    const cards = Array.from(grid.querySelectorAll('.destination-card'));
    const searchInput = document.querySelector('[data-search-input]');
    const sortSelect = document.querySelector('[data-sort-select]');
    const categoryButtons = document.querySelectorAll('[data-category-filter]');
    const paginationContainer = document.querySelector('[data-pagination-container]');

    let activeCategory = 'semua';
    let searchQuery = '';
    let sortBy = 'price-asc';
    let currentPage = 1;
    const itemsPerPage = 6;

    const filterAndSort = () => {
        // 1. Filter
        const filtered = cards.filter(card => {
            const cat = card.getAttribute('data-category') || '';
            const name = card.getAttribute('data-name') || '';
            const matchesCategory = activeCategory === 'semua' || cat === activeCategory;
            const matchesSearch = name.includes(searchQuery.toLowerCase().trim());
            return matchesCategory && matchesSearch;
        });

        // 2. Sort
        filtered.sort((a, b) => {
            if (sortBy === 'price-asc') {
                return parseFloat(a.getAttribute('data-price')) - parseFloat(b.getAttribute('data-price'));
            } else if (sortBy === 'rating-desc') {
                return parseFloat(b.getAttribute('data-rating')) - parseFloat(a.getAttribute('data-rating'));
            }
            return 0;
        });

        // Re-append in sorted order
        filtered.forEach(card => grid.appendChild(card));

        // Hide non-matching cards, show matching cards
        cards.forEach(card => card.style.display = 'none');

        // 3. Paginate
        const totalItems = filtered.length;
        const totalPages = Math.ceil(totalItems / itemsPerPage);
        if (currentPage > totalPages) currentPage = Math.max(1, totalPages);

        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;

        filtered.forEach((card, idx) => {
            if (idx >= startIndex && idx < endIndex) {
                card.style.display = 'block';
            }
        });

        // Render pagination buttons
        paginationContainer.innerHTML = '';
        if (totalPages > 1) {
            for (let i = 1; i <= totalPages; i++) {
                const btn = document.createElement('button');
                btn.className = `page-btn${i === currentPage ? ' is-active' : ''}`;
                btn.textContent = i;
                btn.type = 'button';
                btn.addEventListener('click', () => {
                    currentPage = i;
                    filterAndSort();
                });
                paginationContainer.appendChild(btn);
            }
            paginationContainer.style.display = 'flex';
        } else {
            paginationContainer.style.display = 'none';
        }
    };

    // Listeners
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            searchQuery = e.target.value;
            currentPage = 1;
            filterAndSort();
        });
    }

    if (sortSelect) {
        sortSelect.addEventListener('change', (e) => {
            sortBy = e.target.value;
            currentPage = 1;
            filterAndSort();
        });
    }

    categoryButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            categoryButtons.forEach(b => b.classList.remove('is-active'));
            btn.classList.add('is-active');
            activeCategory = btn.getAttribute('data-category-filter') || 'semua';
            currentPage = 1;
            filterAndSort();
        });
    });

    // Run on init
    filterAndSort();
});
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
