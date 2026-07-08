# Analisis Solo Operator untuk Usaha Kecil

## Ringkasan Kebutuhan

Untuk usaha kecil seperti babi panggang yang hanya memiliki sedikit karyawan (bahkan mungkin hanya pemilik yang berperan ganda sebagai kasir, pelayan, dan bagian dapur), aplikasi perlu mendukung:

1. **Multi-role per user** - Satu user bisa memiliki multiple roles
2. **Quick role switching** - Switch cepat antar role tanpa logout
3. **Solo operator mode** - Interface terpadu untuk semua fungsi
4. **Workflow yang dioptimasi** - Alur kerja yang efisien untuk solo operator

## Analisis Sistem Saat Ini

### Database Structure (SUDAH ADEQUATE)

#### Tabel yang Mendukung Multi-Role:
- **`users`** - User data dengan tenant_id dan branch_id
- **`roles`** - Role definitions dengan tenant_id
- **`user_roles`** - Many-to-many relationship antara users dan roles
  - Satu user bisa memiliki multiple roles
  - Struktur: `user_role_id`, `user_id`, `role_id`, `assigned_at`
- **`permissions`** - Granular permissions per action
- **`role_permissions`** - Link antara roles dan permissions

**Kesimpulan:** Database sudah fully support multi-role per user.

### Frontend Permission System (SUDAH ADEQUATE)

#### File yang Ada:
- **`permission-helpers.js`** - Granular permission checking
  - Support dynamic permissions dari backend
  - Fallback ke static mapping
  - Functions: `canCreate()`, `canEdit()`, `canDelete()`, `canView()`, `canPerform()`
- **`menu-access.js`** - Role-based menu access
  - Define menu tabs per role
  - Functions: `getMenuForUser()`, `hasTabAccess()`, `filterMenuByRole()`

**Kesimpulan:** Permission system sudah granular dan support role-based access.

### UI Dashboard (BELUM ADEQUATE)

#### Masalah yang Ditemukan:
1. **Single role display** - Hanya menampilkan satu role di sidebar
   ```html
   <p class="user-role">Administrator</p>
   ```
   Tidak menampilkan semua roles yang dimiliki user.

2. **No role switcher** - Tidak ada UI untuk switch antar role
   User harus logout/login untuk ganti role (jika diimplementasikan di backend).

3. **Separate tabs for each function** - Setiap fungsi di tab terpisah
   - Orders di tab "Orders"
   - Kitchen di tab "Kitchen"
   - Tables di tab "Tables"
   - Tidak efisien untuk solo operator yang perlu monitor semua sekaligus.

4. **No unified view** - Tidak ada "Solo Mode" yang menggabungkan:
   - POS untuk order
   - Kitchen display untuk status pesanan
   - Table management untuk status meja
   Semua dalam satu view yang mudah dimonitor.

## Gap yang Ditemukan

### 1. UI Multi-Role Display
**Masalah:** User dengan multiple roles tidak melihat semua roles mereka
**Dampak:** Tidak aware bahwa mereka bisa switch role untuk akses berbeda

### 2. Quick Role Switcher
**Masalah:** Tidak ada UI untuk switch role tanpa logout
**Dampak:** Tidak praktis untuk solo operator yang sering ganti peran

### 3. Solo Operator Interface
**Masalah:** Tidak ada unified view untuk monitor semua fungsi
**Dampak:** Solo operator harus navigate antar tabs, tidak efisien

### 4. Optimized Workflow
**Masalah:** Workflow didesain untuk multi-user environment
**Dampak:** Banyak steps yang redundant untuk solo operator

## Solusi yang Direkomendasikan

### Solusi 1: Multi-Role Display di Sidebar

**Implementasi:**
```javascript
// Update sidebar untuk menampilkan semua roles
function displayUserRoles(user) {
    const roles = user.roles || [user.role_name];
    const rolesHtml = roles.map(role => 
        `<span class="role-badge">${role}</span>`
    ).join(' ');
    
    document.getElementById('userRoles').innerHTML = rolesHtml;
}
```

**UI:**
```html
<div class="user-info">
    <div class="user-avatar">A</div>
    <div class="user-details">
        <p class="user-name" id="userName">Admin</p>
        <div class="user-roles" id="userRoles">
            <span class="role-badge">Administrator</span>
            <span class="role-badge">Cashier</span>
            <span class="role-badge">Kitchen Staff</span>
        </div>
    </div>
</div>
```

### Solusi 2: Quick Role Switcher

**Implementasi:**
```javascript
// Add role switcher dropdown
function createRoleSwitcher(user) {
    const roles = user.roles || [user.role_name];
    const currentRole = user.current_role || roles[0];
    
    const switcher = document.createElement('select');
    switcher.className = 'role-switcher';
    switcher.id = 'roleSwitcher';
    
    roles.forEach(role => {
        const option = document.createElement('option');
        option.value = role;
        option.textContent = role;
        option.selected = role === currentRole;
        switcher.appendChild(option);
    });
    
    switcher.addEventListener('change', (e) => {
        switchRole(e.target.value);
    });
    
    return switcher;
}

async function switchRole(newRole) {
    try {
        const response = await fetch(`${API_BASE_URL}/auth/switch-role`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ role: newRole })
        });
        
        if (response.success) {
            // Update current role in session
            currentUser.current_role = newRole;
            // Reload UI with new role permissions
            reloadDashboard();
        }
    } catch (error) {
        console.error('Failed to switch role:', error);
    }
}
```

**UI:**
```html
<div class="role-switcher-container">
    <label>Current Role:</label>
    <select class="role-switcher" id="roleSwitcher">
        <option value="Administrator">Administrator</option>
        <option value="Cashier">Cashier</option>
        <option value="Kitchen Staff">Kitchen Staff</option>
    </select>
</div>
```

### Solusi 3: Solo Operator Mode

**Implementasi:**
```javascript
// Add "Solo Mode" toggle for users with multiple roles
function createSoloModeToggle(user) {
    if (user.roles && user.roles.length > 1) {
        const toggle = document.createElement('button');
        toggle.className = 'solo-mode-toggle';
        toggle.id = 'soloModeToggle';
        toggle.textContent = '🔄 Solo Mode';
        
        toggle.addEventListener('click', () => {
            toggleSoloMode();
        });
        
        return toggle;
    }
    return null;
}

function toggleSoloMode() {
    document.body.classList.toggle('solo-mode');
    const isActive = document.body.classList.contains('solo-mode');
    
    if (isActive) {
        // Show unified solo operator view
        showSoloOperatorView();
    } else {
        // Return to standard dashboard
        showStandardDashboard();
    }
}

function showSoloOperatorView() {
    // Create unified view with:
    // - Left: POS for ordering
    // - Top Right: Kitchen display
    // - Bottom Right: Table management
    // - Bottom: Recent orders summary
    
    const soloView = document.createElement('div');
    soloView.className = 'solo-operator-view';
    soloView.innerHTML = `
        <div class="solo-grid">
            <div class="solo-pos">
                <h3>Point of Sale</h3>
                <div id="soloPOS"></div>
            </div>
            <div class="solo-right">
                <div class="solo-kitchen">
                    <h3>Kitchen Orders</h3>
                    <div id="soloKitchen"></div>
                </div>
                <div class="solo-tables">
                    <h3>Table Status</h3>
                    <div id="soloTables"></div>
                </div>
            </div>
        </div>
        <div class="solo-orders">
            <h3>Recent Orders</h3>
            <div id="soloOrders"></div>
        </div>
    `;
    
    document.querySelector('.content-area').innerHTML = '';
    document.querySelector('.content-area').appendChild(soloView);
    
    // Load components
    loadPOSComponent('soloPOS');
    loadKitchenComponent('soloKitchen');
    loadTablesComponent('soloTables');
    loadRecentOrders('soloOrders');
}
```

**UI:**
```css
/* Solo Mode Styles */
.solo-mode .sidebar {
    width: 60px; /* Collapsed sidebar */
}

.solo-mode .sidebar-nav .nav-item span {
    display: none; /* Hide text, show only icons */
}

.solo-operator-view {
    display: grid;
    grid-template-columns: 2fr 1fr;
    grid-template-rows: auto 1fr;
    gap: 20px;
    height: calc(100vh - 100px);
}

.solo-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
}

.solo-pos {
    background: white;
    border-radius: 8px;
    padding: 20px;
    overflow-y: auto;
}

.solo-right {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.solo-kitchen,
.solo-tables {
    background: white;
    border-radius: 8px;
    padding: 15px;
    flex: 1;
    overflow-y: auto;
}

.solo-orders {
    grid-column: 1 / -1;
    background: white;
    border-radius: 8px;
    padding: 15px;
    max-height: 200px;
    overflow-y: auto;
}
```

### Solusi 4: Optimized Workflow untuk Solo Operator

**Workflow Improvements:**

1. **Quick Order Creation**
   - Shortcut keys untuk common products
   - Auto-select table based on last order
   - One-click modifier selection

2. **Kitchen Status Auto-Update**
   - Auto-move order to "Preparing" when created
   - Auto-move to "Ready" after estimated time
   - Audio notification for status changes

3. **Table Status Auto-Update**
   - Auto-assign table when order created
   - Auto-free table when order paid
   - Color-coded status (green=available, red=occupied)

4. **Payment Quick Flow**
   - One-click payment for cash
   - Quick receipt print
   - Auto-clear table after payment

## Backend API yang Dibutuhkan

### Get User Roles
```php
GET /api/users/{id}/roles
Response: {
    roles: [
        {
            role_id: 1,
            role_name: "Administrator",
            role_code: "ADMIN"
        },
        {
            role_id: 2,
            role_name: "Cashier",
            role_code: "CASHIER"
        }
    ],
    current_role: "Administrator"
}
```

### Switch Role
```php
POST /api/auth/switch-role
Body: {
    role: "Cashier"
}
Response: {
    success: true,
    current_role: "Cashier",
    permissions: [...]
}
```

### Get Solo Mode Data
```php
GET /api/solo-mode/dashboard
Response: {
    pos: {
        products: [...],
        categories: [...]
    },
    kitchen: {
        pending_orders: [...],
        preparing_orders: [...]
    },
    tables: {
        available: [...],
        occupied: [...]
    },
    recent_orders: [...]
}
```

## Prioritas Implementasi

### Prioritas 1: Multi-Role Display (High)
- Update sidebar untuk menampilkan semua roles
- Add role badges di user info section
- Backend: API untuk get user roles

### Prioritas 2: Quick Role Switcher (High)
- Add role switcher dropdown di sidebar
- Implement backend API untuk switch role
- Update UI dynamically saat role switch

### Prioritas 3: Solo Operator Mode (Medium)
- Add "Solo Mode" toggle
- Create unified view layout
- Implement real-time updates untuk semua components

### Prioritas 4: Optimized Workflow (Medium)
- Implement shortcut keys
- Auto-status updates
- Quick payment flow

## Use Case: Babi Panggang Solo Operator

### Scenario:
Pemilik usaha babi panggang bekerja sendiri sebagai:
- Kasir (menerima order)
- Pelayan (melayani pelanggan)
- Dapur (memasak dan menyiapkan)

### Workflow dengan Solo Mode:

1. **Pelanggan datang**
   - Solo operator lihat di "Table Status" - meja 2 available
   - Assign pelanggan ke meja 2 (one-click)

2. **Pelanggan pesan**
   - Solo operator buka "Point of Sale"
   - Select "Babi Panggang" (weight-based)
   - Input weight: 1.2 kg
   - Select bumbu: "Bumbu Padang"
   - Total: Rp 79.000
   - Click "Create Order"

3. **Order otomatis masuk Kitchen**
   - Order muncul di "Kitchen Orders" dengan status "Preparing"
   - Audio notification: "New order #123"
   - Estimated time: 15 menit

4. **Solo operator masak**
   - Lihat di "Kitchen Orders" - order #123
   - Update status ke "Ready" (one-click)
   - Audio notification: "Order #123 ready"

5. **Solo operator antar ke meja**
   - Lihat di "Table Status" - meja 2 occupied
   - Update order status ke "Served" (one-click)

6. **Pelanggan bayar**
   - Solo operator buka "Recent Orders"
   - Select order #123
   - Click "Payment" -> "Cash" (one-click)
   - Print receipt (auto)
   - Meja 2 otomatis free

### Keuntungan:
- Semua fungsi dalam satu view, tidak perlu navigate
- Real-time updates untuk semua status
- Audio notifications untuk tidak miss order
- One-click actions untuk speed
- Auto-workflow untuk reduce manual steps

## Kesimpulan

**Status Sistem Saat Ini:**
- ✅ Database fully support multi-role per user
- ✅ Permission system granular dan adequate
- ❌ UI belum display multiple roles
- ❌ Tidak ada quick role switcher
- ❌ Tidak ada solo operator mode
- ❌ Workflow belum optimized untuk solo operator

**Rekomendasi:**
Implementasi 4 solusi di atas akan membuat aplikasi fully support usaha kecil dengan solo operator yang berperan ganda. Prioritas utama adalah multi-role display dan quick role switcher, diikuti oleh solo operator mode untuk workflow yang lebih efisien.
