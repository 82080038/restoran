/**
 * Solo Operator Mode
 * 
 * Handles unified view for solo operators who need to manage
 * multiple functions (POS, kitchen, tables) in one interface
 * 
 * @version 1.0.0
 * @date 2026-07-08
 */

class SoloOperatorMode {
    constructor() {
        this.isActive = false;
        this.currentUser = null;
        this.refreshInterval = null;
    }

    /**
     * Initialize solo operator mode
     */
    async initialize() {
        // Get current user
        if (window.authManager) {
            this.currentUser = window.authManager.getCurrentUser();
        }
        
        if (this.currentUser && window.multiRoleSupport) {
            // Only show solo mode toggle for users with multiple roles
            if (multiRoleSupport.hasMultipleRoles()) {
                this.setupSoloModeToggle();
            }
        }
    }

    /**
     * Setup solo mode toggle button
     */
    setupSoloModeToggle() {
        const soloModeContainer = document.getElementById('soloModeContainer');
        const soloModeToggle = document.getElementById('soloModeToggle');
        
        if (!soloModeContainer || !soloModeToggle) return;

        soloModeContainer.style.display = 'block';
        
        soloModeToggle.addEventListener('click', () => {
            this.toggleSoloMode();
        });
    }

    /**
     * Toggle solo mode on/off
     */
    toggleSoloMode() {
        this.isActive = !this.isActive;
        document.body.classList.toggle('solo-mode', this.isActive);
        
        const soloModeToggle = document.getElementById('soloModeToggle');
        if (soloModeToggle) {
            soloModeToggle.textContent = this.isActive ? '🔄 Standard Mode' : '🔄 Solo Mode';
        }

        if (this.isActive) {
            this.showSoloOperatorView();
            this.startAutoRefresh();
        } else {
            this.showStandardDashboard();
            this.stopAutoRefresh();
        }
    }

    /**
     * Show solo operator unified view
     */
    showSoloOperatorView() {
        const contentArea = document.querySelector('.content-area');
        if (!contentArea) return;

        // Hide all pages
        document.querySelectorAll('.page').forEach(page => {
            page.classList.remove('active');
        });

        // Create solo operator view
        const soloView = document.createElement('div');
        soloView.className = 'solo-operator-view';
        soloView.id = 'soloOperatorView';
        soloView.innerHTML = `
            <div class="solo-grid">
                <div class="solo-pos">
                    <div class="solo-header">
                        <h3>🛒 Point of Sale</h3>
                        <button class="btn btn-sm btn-refresh" data-refresh="pos">🔄 Refresh</button>
                    </div>
                    <div id="soloPOS" class="solo-content">
                        <p class="loading">Loading POS...</p>
                    </div>
                </div>
                <div class="solo-right">
                    <div class="solo-kitchen">
                        <div class="solo-header">
                            <h3>👨‍🍳 Kitchen Orders</h3>
                            <button class="btn btn-sm btn-refresh" data-refresh="kitchen">🔄 Refresh</button>
                        </div>
                        <div id="soloKitchen" class="solo-content">
                            <p class="loading">Loading kitchen orders...</p>
                        </div>
                    </div>
                    <div class="solo-tables">
                        <div class="solo-header">
                            <h3>🪑 Table Status</h3>
                            <button class="btn btn-sm btn-refresh" data-refresh="tables">🔄 Refresh</button>
                        </div>
                        <div id="soloTables" class="solo-content">
                            <p class="loading">Loading tables...</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="solo-orders">
                <div class="solo-header">
                    <h3>📋 Recent Orders</h3>
                    <button class="btn btn-sm btn-refresh" data-refresh="orders">🔄 Refresh</button>
                </div>
                <div id="soloOrders" class="solo-content">
                    <p class="loading">Loading recent orders...</p>
                </div>
            </div>
        `;

        contentArea.innerHTML = '';
        contentArea.appendChild(soloView);

        // Load components
        this.loadPOSComponent();
        this.loadKitchenComponent();
        this.loadTablesComponent();
        this.loadRecentOrders();

        // Setup refresh buttons
        this.setupRefreshButtons();
    }

    /**
     * Show standard dashboard
     */
    showStandardDashboard() {
        const contentArea = document.querySelector('.content-area');
        if (!contentArea) return;

        // Remove solo view
        const soloView = document.getElementById('soloOperatorView');
        if (soloView) {
            soloView.remove();
        }

        // Show overview page
        const overviewPage = document.getElementById('overviewPage');
        if (overviewPage) {
            overviewPage.classList.add('active');
        }

        // Update page title
        const pageTitle = document.getElementById('pageTitle');
        if (pageTitle) {
            pageTitle.textContent = 'Overview';
        }
    }

    /**
     * Load POS component
     */
    async loadPOSComponent() {
        const container = document.getElementById('soloPOS');
        if (!container) return;

        try {
            // Load products
            const response = await fetch(`${API_BASE_URL}/products`);
            const data = await response.json();

            if (data.success && data.products) {
                this.renderPOS(data.products, container);
            } else {
                container.innerHTML = '<p class="error">Failed to load products</p>';
            }
        } catch (error) {
            console.error('Failed to load POS:', error);
            container.innerHTML = '<p class="error">Failed to load products</p>';
        }
    }

    /**
     * Render POS component
     */
    renderPOS(products, container) {
        // Group products by category
        const categories = {};
        products.forEach(product => {
            const category = product.category_name || 'Uncategorized';
            if (!categories[category]) {
                categories[category] = [];
            }
            categories[category].push(product);
        });

        let html = '<div class="pos-categories">';
        
        Object.keys(categories).forEach(category => {
            html += `
                <div class="pos-category">
                    <h4>${category}</h4>
                    <div class="pos-products">
                        ${categories[category].map(product => `
                            <div class="pos-product" data-product-id="${product.product_id}">
                                <div class="product-name">${product.product_name}</div>
                                <div class="product-price">Rp ${product.price.toLocaleString('id-ID')}</div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
        });

        html += '</div>';
        html += `
            <div class="pos-order-summary">
                <h4>Current Order</h4>
                <div class="pos-order-items" id="posOrderItems">
                    <p class="empty">No items</p>
                </div>
                <div class="pos-total">
                    <span>Total:</span>
                    <span id="posTotal">Rp 0</span>
                </div>
                <button class="btn btn-primary btn-block" id="posCreateOrder">Create Order</button>
            </div>
        `;

        container.innerHTML = html;

        // Add click handlers
        container.querySelectorAll('.pos-product').forEach(productEl => {
            productEl.addEventListener('click', () => {
                const productId = parseInt(productEl.dataset.productId);
                this.addToOrder(productId, products);
            });
        });
    }

    /**
     * Add product to order
     */
    addToOrder(productId, products) {
        const product = products.find(p => p.product_id === productId);
        if (!product) return;

        const orderItemsContainer = document.getElementById('posOrderItems');
        const posTotal = document.getElementById('posTotal');

        // Get current order items
        let orderItems = JSON.parse(localStorage.getItem('soloOrderItems') || '[]');

        // Check if product already in order
        const existingItem = orderItems.find(item => item.product_id === productId);
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            orderItems.push({
                product_id: productId,
                product_name: product.product_name,
                price: product.price,
                quantity: 1
            });
        }

        // Save to localStorage
        localStorage.setItem('soloOrderItems', JSON.stringify(orderItems));

        // Update UI
        this.renderOrderItems(orderItems, orderItemsContainer, posTotal);
    }

    /**
     * Render order items
     */
    renderOrderItems(orderItems, container, totalEl) {
        if (orderItems.length === 0) {
            container.innerHTML = '<p class="empty">No items</p>';
            totalEl.textContent = 'Rp 0';
            return;
        }

        let html = '';
        let total = 0;

        orderItems.forEach((item, index) => {
            const itemTotal = item.price * item.quantity;
            total += itemTotal;
            html += `
                <div class="pos-order-item">
                    <div class="item-info">
                        <span class="item-name">${item.product_name}</span>
                        <span class="item-qty">x${item.quantity}</span>
                    </div>
                    <div class="item-price">Rp ${itemTotal.toLocaleString('id-ID')}</div>
                    <button class="btn-remove" data-index="${index}">&times;</button>
                </div>
            `;
        });

        container.innerHTML = html;
        totalEl.textContent = `Rp ${total.toLocaleString('id-ID')}`;

        // Add remove handlers
        container.querySelectorAll('.btn-remove').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const index = parseInt(e.target.dataset.index);
                this.removeFromOrder(index);
            });
        });
    }

    /**
     * Remove item from order
     */
    removeFromOrder(index) {
        let orderItems = JSON.parse(localStorage.getItem('soloOrderItems') || '[]');
        orderItems.splice(index, 1);
        localStorage.setItem('soloOrderItems', JSON.stringify(orderItems));

        const orderItemsContainer = document.getElementById('posOrderItems');
        const posTotal = document.getElementById('posTotal');
        this.renderOrderItems(orderItems, orderItemsContainer, posTotal);
    }

    /**
     * Load kitchen component
     */
    async loadKitchenComponent() {
        const container = document.getElementById('soloKitchen');
        if (!container) return;

        try {
            const response = await fetch(`${API_BASE_URL}/kitchen/orders`);
            const data = await response.json();

            if (data.success && data.orders) {
                this.renderKitchenOrders(data.orders, container);
            } else {
                container.innerHTML = '<p class="error">Failed to load kitchen orders</p>';
            }
        } catch (error) {
            console.error('Failed to load kitchen:', error);
            container.innerHTML = '<p class="error">Failed to load kitchen orders</p>';
        }
    }

    /**
     * Render kitchen orders
     */
    renderKitchenOrders(orders, container) {
        if (orders.length === 0) {
            container.innerHTML = '<p class="empty">No pending orders</p>';
            return;
        }

        let html = '<div class="kitchen-orders-list">';
        
        orders.forEach(order => {
            const statusClass = order.status.toLowerCase();
            html += `
                <div class="kitchen-order-item status-${statusClass}">
                    <div class="order-header">
                        <span class="order-number">#${order.order_number}</span>
                        <span class="order-status">${order.status}</span>
                    </div>
                    <div class="order-items">
                        ${order.items.map(item => `
                            <div class="order-item">
                                <span class="item-name">${item.product_name}</span>
                                <span class="item-qty">x${item.quantity}</span>
                            </div>
                        `).join('')}
                    </div>
                    <div class="order-actions">
                        ${order.status === 'PENDING' ? `
                            <button class="btn btn-sm btn-start" data-order-id="${order.order_id}">Start</button>
                        ` : ''}
                        ${order.status === 'PREPARING' ? `
                            <button class="btn btn-sm btn-ready" data-order-id="${order.order_id}">Ready</button>
                        ` : ''}
                    </div>
                </div>
            `;
        });

        html += '</div>';
        container.innerHTML = html;

        // Add action handlers
        container.querySelectorAll('.btn-start').forEach(btn => {
            btn.addEventListener('click', () => {
                this.updateOrderStatus(btn.dataset.orderId, 'PREPARING');
            });
        });

        container.querySelectorAll('.btn-ready').forEach(btn => {
            btn.addEventListener('click', () => {
                this.updateOrderStatus(btn.dataset.orderId, 'READY');
            });
        });
    }

    /**
     * Load tables component
     */
    async loadTablesComponent() {
        const container = document.getElementById('soloTables');
        if (!container) return;

        try {
            const response = await fetch(`${API_BASE_URL}/tables`);
            const data = await response.json();

            if (data.success && data.tables) {
                this.renderTables(data.tables, container);
            } else {
                container.innerHTML = '<p class="error">Failed to load tables</p>';
            }
        } catch (error) {
            console.error('Failed to load tables:', error);
            container.innerHTML = '<p class="error">Failed to load tables</p>';
        }
    }

    /**
     * Render tables
     */
    renderTables(tables, container) {
        if (tables.length === 0) {
            container.innerHTML = '<p class="empty">No tables</p>';
            return;
        }

        let html = '<div class="tables-grid">';
        
        tables.forEach(table => {
            const statusClass = table.status.toLowerCase();
            html += `
                <div class="table-card status-${statusClass}" data-table-id="${table.table_id}">
                    <div class="table-number">${table.table_number}</div>
                    <div class="table-status">${table.status}</div>
                    ${table.order_id ? `<div class="table-order">#${table.order_number}</div>` : ''}
                </div>
            `;
        });

        html += '</div>';
        container.innerHTML = html;

        // Add click handlers
        container.querySelectorAll('.table-card').forEach(card => {
            card.addEventListener('click', () => {
                const tableId = card.dataset.tableId;
                this.showTableDetails(tableId);
            });
        });
    }

    /**
     * Load recent orders
     */
    async loadRecentOrders() {
        const container = document.getElementById('soloOrders');
        if (!container) return;

        try {
            const response = await fetch(`${API_BASE_URL}/orders/recent?limit=5`);
            const data = await response.json();

            if (data.success && data.orders) {
                this.renderRecentOrders(data.orders, container);
            } else {
                container.innerHTML = '<p class="error">Failed to load recent orders</p>';
            }
        } catch (error) {
            console.error('Failed to load recent orders:', error);
            container.innerHTML = '<p class="error">Failed to load recent orders</p>';
        }
    }

    /**
     * Render recent orders
     */
    renderRecentOrders(orders, container) {
        if (orders.length === 0) {
            container.innerHTML = '<p class="empty">No recent orders</p>';
            return;
        }

        let html = '<div class="recent-orders-list">';
        
        orders.forEach(order => {
            html += `
                <div class="recent-order-item">
                    <div class="order-info">
                        <span class="order-number">#${order.order_number}</span>
                        <span class="order-time">${new Date(order.created_at).toLocaleTimeString('id-ID')}</span>
                    </div>
                    <div class="order-total">Rp ${order.total_amount.toLocaleString('id-ID')}</div>
                    <div class="order-status status-${order.status.toLowerCase()}">${order.status}</div>
                </div>
            `;
        });

        html += '</div>';
        container.innerHTML = html;
    }

    /**
     * Update order status
     */
    async updateOrderStatus(orderId, newStatus) {
        try {
            const response = await fetch(`${API_BASE_URL}/orders/${orderId}/status`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ status: newStatus })
            });

            if (response.success) {
                // Refresh kitchen component
                this.loadKitchenComponent();
            }
        } catch (error) {
            console.error('Failed to update order status:', error);
        }
    }

    /**
     * Setup refresh buttons
     */
    setupRefreshButtons() {
        document.querySelectorAll('[data-refresh]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const type = e.target.dataset.refresh;
                this.refreshComponent(type);
            });
        });
    }

    /**
     * Refresh specific component
     */
    refreshComponent(type) {
        switch (type) {
            case 'pos':
                this.loadPOSComponent();
                break;
            case 'kitchen':
                this.loadKitchenComponent();
                break;
            case 'tables':
                this.loadTablesComponent();
                break;
            case 'orders':
                this.loadRecentOrders();
                break;
        }
    }

    /**
     * Start auto refresh
     */
    startAutoRefresh() {
        // Refresh every 30 seconds
        this.refreshInterval = setInterval(() => {
            if (this.isActive) {
                this.loadKitchenComponent();
                this.loadTablesComponent();
                this.loadRecentOrders();
            }
        }, 30000);
    }

    /**
     * Stop auto refresh
     */
    stopAutoRefresh() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
            this.refreshInterval = null;
        }
    }

    /**
     * Show table details
     */
    showTableDetails(tableId) {
        // Implement table details modal
        console.log('Show details for table:', tableId);
    }
}

// Initialize global instance
const soloOperatorMode = new SoloOperatorMode();

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    soloOperatorMode.initialize();
});
