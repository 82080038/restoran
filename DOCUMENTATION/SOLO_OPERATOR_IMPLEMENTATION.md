# Solo Operator Implementation Guide

## Overview
Complete implementation of multi-role support and solo operator mode for small businesses where one person handles multiple functions (cashier, waiter, kitchen staff).

## Implementation Summary

### Frontend Components Created

#### 1. Multi-Role Support (`multi-role-support.js`)
**File:** `/opt/lampp/htdocs/restoran/FRONTEND/js/multi-role-support.js`
**CSS:** `/opt/lampp/htdocs/restoran/FRONTEND/css/multi-role-support.css`

**Features:**
- Load user roles from backend
- Display all roles as badges in sidebar
- Role switcher dropdown for quick role switching
- Session-based role persistence
- Auto-reload dashboard on role switch

**Usage:**
```javascript
// Automatically initializes on DOM load
// Access global instance:
multiRoleSupport.getCurrentRole();
multiRoleSupport.getUserRoles();
multiRoleSupport.hasMultipleRoles();
```

#### 2. Solo Operator Mode (`solo-operator-mode.js`)
**File:** `/opt/lampp/htdocs/restoran/FRONTEND/js/solo-operator-mode.js`
**CSS:** `/opt/lampp/htdocs/restoran/FRONTEND/css/solo-operator-mode.css`

**Features:**
- Unified view with 4 components:
  - **POS** - Product catalog and order creation
  - **Kitchen** - Order status management
  - **Tables** - Table status monitoring
  - **Recent Orders** - Order history
- Auto-refresh every 30 seconds
- Manual refresh buttons per component
- Collapsed sidebar in solo mode
- Responsive design for mobile

**Usage:**
```javascript
// Automatically initializes for users with multiple roles
// Toggle solo mode:
soloOperatorMode.toggleSoloMode();
```

### Backend API Endpoints Created

#### SimpleUserController Enhancements
**File:** `/opt/lampp/htdocs/restoran/BACKEND/modules/User/Controllers/SimpleUserController.php`

**New Endpoints:**

1. **GET `/api/v1/public/users/{id}/roles`**
   - Get all roles for a user
   - Returns current role from session
   - Response:
   ```json
   {
       "success": true,
       "roles": [
           {
               "role_id": 1,
               "role_code": "ADMIN",
               "role_name": "Administrator",
               "description": "Full system access"
           }
       ],
       "current_role": "Administrator"
   }
   ```

2. **POST `/api/v1/public/auth/switch-role`**
   - Switch current active role
   - Validates user has the role
   - Updates session
   - Returns new permissions
   - Request Body:
   ```json
   {
       "role": "Cashier"
   }
   ```
   - Response:
   ```json
   {
       "success": true,
       "current_role": "Cashier",
       "permissions": [...]
   }
   ```

3. **GET `/api/v1/public/solo-mode/dashboard`**
   - Get all data for solo operator dashboard
   - Returns POS, kitchen, tables, and recent orders data
   - Response:
   ```json
   {
       "success": true,
       "pos": {
           "products": [...]
       },
       "kitchen": {
           "orders": [...]
       },
       "tables": {
           "tables": [...]
       },
       "recent_orders": {
           "orders": [...]
       }
   }
   ```

### Dashboard Integration

**File Updated:** `/opt/lampp/htdocs/restoran/FRONTEND/dashboard/index.html`

**Changes:**
1. Added CSS files:
   - `multi-role-support.css`
   - `solo-operator-mode.css`

2. Updated sidebar footer:
   - Multi-role badges display
   - Role switcher dropdown
   - Solo mode toggle button

3. Added JavaScript files:
   - `multi-role-support.js`
   - `solo-operator-mode.js`

## Setup Instructions

### 1. Database Setup

Ensure user has multiple roles:
```sql
-- Assign multiple roles to a user
INSERT INTO user_roles (user_id, role_id) VALUES (1, 1); -- Administrator
INSERT INTO user_roles (user_id, role_id) VALUES (1, 2); -- Cashier
INSERT INTO user_roles (user_id, role_id) VALUES (1, 3); -- Kitchen Staff
```

### 2. Frontend Setup

The components are automatically loaded when dashboard is accessed. No additional setup required.

### 3. API Configuration

Update `API_BASE_URL` in `FRONTEND/js/config.js` if needed:
```javascript
const API_BASE_URL = 'http://localhost/restoran/BACKEND';
```

## Usage Guide

### For Solo Operators

1. **Login to Dashboard**
   - User with multiple roles will see role badges in sidebar

2. **Switch Role (Optional)**
   - Use role switcher dropdown to change active role
   - Dashboard reloads with new permissions

3. **Enable Solo Mode**
   - Click "🔄 Solo Mode" button in sidebar
   - Sidebar collapses to icons only
   - Unified view appears with all components

4. **Use Unified View**
   - **Left Panel (POS):** Click products to add to order
   - **Top Right (Kitchen):** Click "Start" or "Ready" to update order status
   - **Bottom Right (Tables):** Click tables to see details
   - **Bottom (Recent Orders):** View order history

5. **Disable Solo Mode**
   - Click "🔄 Standard Mode" button
   - Returns to normal dashboard

### For Multi-User Businesses

1. **Standard Dashboard**
   - Each user sees only their assigned role
   - Navigate between tabs as needed

2. **Role Switching**
   - Users with multiple roles can switch as needed
   - Useful for managers who need different access levels

## Component Details

### Multi-Role Display

**Visual:**
```
┌─────────────────────────┐
│  A                      │
│  Admin                  │
│  [Administrator]        │
│  [Cashier] [Kitchen]    │
│  Current Role: [▼]      │
│  └─────────────────────┘
│  [🔄 Solo Mode]         │
│  [Logout]               │
└─────────────────────────┘
```

**Behavior:**
- Active role highlighted in green
- Dropdown only shows if user has multiple roles
- Solo mode toggle only shows if user has multiple roles

### Solo Operator View Layout

```
┌─────────────────────────────────────────────────────────┐
│  [Collapsed Sidebar]  Point of Sale    Kitchen Orders  │
│                       ┌──────────┐    ┌──────────────┐  │
│                       │ Products │    │ Order #123   │  │
│                       │ [Ikan]   │    │ [Start]      │  │
│                       │ [Babi]   │    │ Order #124   │  │
│                       └──────────┘    │ [Ready]      │  │
│                       ┌──────────┐    └──────────────┘  │
│                       │ Order    │    ┌──────────────┐  │
│                       │ Ikan x1  │    │ Table Status  │  │
│                       │ Total:   │    │ [1] [2] [3]  │  │
│                       │ [Create] │    │ [4] [5] [6]  │  │
│                       └──────────┘    └──────────────┘  │
│                                        ┌──────────────┐  │
│                                        │ Recent Orders │  │
│                                        │ #123, #124   │  │
│                                        └──────────────┘  │
└─────────────────────────────────────────────────────────┘
```

## Customization

### Add More Components to Solo Mode

Edit `solo-operator-mode.js`:

```javascript
showSoloOperatorView() {
    // Add new component
    const newComponent = document.createElement('div');
    newComponent.className = 'solo-custom';
    newComponent.innerHTML = `
        <div class="solo-header">
            <h3>Custom Component</h3>
        </div>
        <div id="soloCustom" class="solo-content"></div>
    `;
    
    // Add to grid
    document.querySelector('.solo-grid').appendChild(newComponent);
    
    // Load data
    this.loadCustomComponent();
}
```

### Modify Auto-Refresh Interval

Edit `solo-operator-mode.js`:

```javascript
startAutoRefresh() {
    // Change 30000 to desired interval in milliseconds
    this.refreshInterval = setInterval(() => {
        if (this.isActive) {
            this.loadKitchenComponent();
            this.loadTablesComponent();
            this.loadRecentOrders();
        }
    }, 30000); // 30 seconds
}
```

### Add Audio Notifications

Edit `solo-operator-mode.js`:

```javascript
updateOrderStatus(orderId, newStatus) {
    // Play sound on status change
    if (newStatus === 'READY') {
        const audio = new Audio('/sounds/ready.mp3');
        audio.play();
    }
    
    // ... existing code
}
```

## Troubleshooting

### Issue: Role badges not showing
**Solution:** Ensure user has multiple roles in database. Check `user_roles` table.

### Issue: Role switcher not appearing
**Solution:** Verify user has more than one active role. Check role status is 'ACTIVE'.

### Issue: Solo mode toggle not showing
**Solution:** Only users with multiple roles see the toggle. Assign additional roles to user.

### Issue: Components not loading data
**Solution:** Check API endpoints are accessible. Verify `API_BASE_URL` is correct.

### Issue: Auto-refresh not working
**Solution:** Check browser console for errors. Ensure `setInterval` is not blocked.

## Future Enhancements

1. **WebSocket Integration** - Real-time updates without polling
2. **Audio Notifications** - Sound alerts for new orders and status changes
3. **Keyboard Shortcuts** - Quick actions for common tasks
4. **Drag-and-Drop** - Drag products to order, orders to tables
5. **Mobile App** - Native mobile app for solo operators
6. **Offline Mode** - Work offline with sync when online
7. **Performance Metrics** - Track solo operator efficiency
8. **Quick Actions** - One-click for common workflows

## Support

For issues or questions:
1. Check this documentation
2. Review component source code
3. Check browser console for errors
4. Verify database has correct data
5. Contact development team
