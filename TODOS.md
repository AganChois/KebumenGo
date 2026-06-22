# Codebase Feature Analysis

Below is the list of broken, placeholder, or unimplemented features identified across the codebase.

## 1. Unimplemented Features (Mockups Only)

### 🔍 Search & Filters on Destination Catalog
* **File**: [app/views/destination/index.php](file:///Users/wahyutricahya/Web%20Development/KebumenGo/app/views/destination/index.php)
* **Status**: **Placeholder**
* **Detail**: The category tabs ("Pantai", "Goa", etc.), the search input, and the sort dropdown are purely static HTML. No JavaScript or backend logic filters the destination list.

### 📄 Destination Pagination
* **File**: [app/views/destination/index.php](file:///Users/wahyutricahya/Web%20Development/KebumenGo/app/views/destination/index.php)
* **Status**: **Placeholder**
* **Detail**: The pagination buttons (`1`, `2`, `3`) are hardcoded markup and do not filter or paginate records.

### ✍️ User Review Submission Form
* **File**: [app/views/destination/show.php](file:///Users/wahyutricahya/Web%20Development/KebumenGo/app/views/destination/show.php)
* **Status**: **Unintegrated**
* **Detail**: The review form is client-only. The submit button is `type="button"` and has no click handler or route handler in [index.php](file:///Users/wahyutricahya/Web%20Development/KebumenGo/index.php) to process and store incoming reviews.

### 📊 Admin Dashboard Statistics & Charts
* **File**: [app/views/admin/dashboard.php](file:///Users/wahyutricahya/Web%20Development/KebumenGo/app/views/admin/dashboard.php)
* **Status**: **Hardcoded**
* **Detail**: Total destinations (48), pending (7), and reviews (1.284) along with charts are hardcoded values.

### 📈 Admin Analytics
* **File**: [app/views/admin/analitik.php](file:///Users/wahyutricahya/Web%20Development/KebumenGo/app/views/admin/analitik.php)
* **Status**: **Hardcoded**
* **Detail**: All visitor graphs and statistics are static mockup data.

### ⚙️ Admin Profile & Settings Changes
* **File**: [app/views/admin/pengaturan.php](file:///Users/wahyutricahya/Web%20Development/KebumenGo/app/views/admin/pengaturan.php)
* **Status**: **Demo Alert**
* **Detail**: Submitting the settings form fires an `alert('Perubahan berhasil disimpan (Demo)')` on the client and is not wired to the database. The "Sistem Umum" and "Keamanan" tabs have no forms.

---

## 2. Broken Features (Database Field Mismatches)

### 📂 Admin Category Management [DONE]
* **File**: [app/views/admin/category/index.php](file:///Users/wahyutricahya/Web%20Development/KebumenGo/app/views/admin/category/index.php), [index.php](file:///Users/wahyutricahya/Web%20Development/KebumenGo/index.php)
* **Status**: **Fixed**
* **Detail**:
  * The query sorts by `display_order`, but the column is `sort_order` in the [kebumengo.sql](file:///Users/wahyutricahya/Web%20Development/KebumenGo/database/kebumengo.sql) schema. (Fixed)
  * Creating/updating categories attempts to write `display_order` and `icon_path`, but the DB columns are named `sort_order` and `icon_img`. This causes query failure. (Fixed)

### 🗺️ Destination Details Page [DONE]
* **File**: [app/views/destination/show.php](file:///Users/wahyutricahya/Web%20Development/KebumenGo/app/views/destination/show.php)
* **Status**: **Fixed**
* **Detail**:
  * Tries to access `$destination['days']`, `$destination['open']`, `$destination['close']`, and `$destination['maps']` which are actually named `operational_day`, `open_time`, `close_time`, and `maps_embed` in the DB schema. (Fixed)
  * Tries to access `$destination['location']` which does not exist in the database table. (Fixed with fallback)

### 🎫 Destination Card Component
* **File**: [app/views/partials/destination-card.php](file:///Users/wahyutricahya/Web%20Development/KebumenGo/app/views/partials/destination-card.php)
* **Detail**:
  * Tries to display `$destination['price']` which is named `ticket_price` in the DB query, causing every card to fall back to the default price `Rp 25.000`.
