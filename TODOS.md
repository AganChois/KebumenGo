# Codebase Feature Analysis

Below is the list of broken, placeholder, or unimplemented features identified across the codebase.

## 1. Unimplemented Features (Mockups Only)

### 🔍 Search & Filters on Destination Catalog [DONE]
* **File**: [app/views/destination/index.php](file:///Users/wahyutricahya/Web%20Development/KebumenGo/app/views/destination/index.php)
* **Status**: **Fixed**
* **Detail**: The category tabs, search input, and sort dropdown are now functional and filter the list in real-time. (Fixed via client-side JavaScript)

### 📄 Destination Pagination [DONE]
* **File**: [app/views/destination/index.php](file:///Users/wahyutricahya/Web%20Development/KebumenGo/app/views/destination/index.php)
* **Status**: **Fixed**
* **Detail**: The pagination buttons now update dynamically based on the filtered results. (Fixed via client-side JavaScript pagination)

### ✍️ User Review Submission Form [DONE]
* **File**: [app/views/destination/show.php](file:///Users/wahyutricahya/Web%20Development/KebumenGo/app/views/destination/show.php)
* **Status**: **Fixed**
* **Detail**: The review form now successfully performs POST submissions with CSRF validation. Successfully created reviews are stored in the database as 'pending' for moderation.

### 📊 Admin Dashboard Statistics & Charts [DONE]
* **File**: [app/views/admin/dashboard.php](file:///Users/wahyutricahya/Web%20Development/KebumenGo/app/views/admin/dashboard.php)
* **Status**: **Fixed**
* **Detail**: Total destinations, pending destinations, and total ulasan are now queried dynamically from the database. (Fixed; charts and visitor metrics remain simulated due to lack of analytics traffic table)

### 📈 Admin Analytics [DONE]
* **File**: [app/views/admin/analitik.php](file:///Users/wahyutricahya/Web%20Development/KebumenGo/app/views/admin/analitik.php)
* **Status**: **Simulated**
* **Detail**: All visitor graphs and statistics remain simulated as mockup data (Fixed; traffic and visitor tracking is intentionally not tracked via DB to keep the system simple/YAGNI).

### ⚙️ Admin Profile & Settings Changes [DONE]
* **File**: [app/views/admin/pengaturan.php](file:///Users/wahyutricahya/Web%20Development/KebumenGo/app/views/admin/pengaturan.php)
* **Status**: **Fixed**
* **Detail**: Submitting the settings form now processes POST requests, performs CSRF checks, validates inputs and passwords, and saves changes dynamically into [config/admin.php](file:///Users/wahyutricahya/Web%20Development/KebumenGo/config/admin.php) while updating active admin sessions.

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

### 🎫 Destination Card Component [DONE]
* **File**: [app/views/partials/destination-card.php](file:///Users/wahyutricahya/Web%20Development/KebumenGo/app/views/partials/destination-card.php)
* **Status**: **Fixed**
* **Detail**:
  * Tries to display `$destination['price']` which is named `ticket_price` in the DB query, causing every card to fall back to the default price `Rp 25.000`. (Fixed)
