/**
 * Inventory Item Selector Component
 * 
 * Handles selection of individual inventory items (e.g., specific fish with specific weight)
 * for made-to-order products
 * 
 * @version 1.0.0
 * @date 2026-07-08
 */

class InventoryItemSelector {
    constructor() {
        this.selectedItems = [];
        this.availableItems = [];
        this.inventoryId = null;
        this.branchId = null;
    }

    /**
     * Initialize item selector
     * @param {number} inventoryId - Inventory ID
     * @param {number} branchId - Branch ID
     */
    async initialize(inventoryId, branchId) {
        this.inventoryId = inventoryId;
        this.branchId = branchId;
        await this.loadAvailableItems();
        this.showSelectorModal();
    }

    /**
     * Load available inventory items
     */
    async loadAvailableItems() {
        try {
            const response = await fetch(`${Config.api.baseURL}/inventory/items/available/${this.inventoryId}/${this.branchId}`);
            const data = await response.json();
            this.availableItems = data.items || [];
        } catch (error) {
            console.error('Error loading available items:', error);
            this.availableItems = [];
        }
    }

    /**
     * Show item selector modal
     */
    showSelectorModal() {
        const modal = this.createSelectorModal();
        document.body.appendChild(modal);
        this.attachModalEvents(modal);
    }

    /**
     * Create selector modal
     */
    createSelectorModal() {
        const modal = document.createElement('div');
        modal.className = 'modal inventory-item-selector-modal';
        modal.id = 'inventoryItemSelectorModal';
        
        let itemsHtml = this.availableItems.map(item => `
            <div class="inventory-item-card" data-item-id="${item.item_id}">
                <div class="item-header">
                    <span class="item-code">${item.item_code}</span>
                    <span class="item-status status-${item.status.toLowerCase()}">${item.status}</span>
                </div>
                <div class="item-details">
                    <div class="detail-row">
                        <span class="label">Weight:</span>
                        <span class="value">${item.weight} kg</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Cost:</span>
                        <span class="value">Rp ${item.unit_cost?.toLocaleString('id-ID') || 'N/A'}</span>
                    </div>
                    ${item.received_date ? `
                    <div class="detail-row">
                        <span class="label">Received:</span>
                        <span class="value">${new Date(item.received_date).toLocaleDateString('id-ID')}</span>
                    </div>
                    ` : ''}
                    ${item.expiry_date ? `
                    <div class="detail-row">
                        <span class="label">Expiry:</span>
                        <span class="value ${this.isExpiringSoon(item.expiry_date) ? 'warning' : ''}">${new Date(item.expiry_date).toLocaleDateString('id-ID')}</span>
                    </div>
                    ` : ''}
                </div>
                <div class="item-actions">
                    <button class="btn btn-sm btn-select" data-action="select" data-item-id="${item.item_id}" 
                            ${item.status !== 'AVAILABLE' ? 'disabled' : ''}>
                        Select
                    </button>
                </div>
            </div>
        `).join('');
        
        if (this.availableItems.length === 0) {
            itemsHtml = `
                <div class="no-items-message">
                    <p>No available items found</p>
                    <p class="hint">Items with status 'AVAILABLE' will appear here</p>
                </div>
            `;
        }
        
        modal.innerHTML = `
            <div class="modal-content large">
                <button class="close-modal" data-action="close">&times;</button>
                <div class="inventory-selector-content">
                    <div class="selector-header">
                        <h2>Select Inventory Item</h2>
                        <div class="filter-controls">
                            <input type="text" 
                                   id="itemSearch" 
                                   class="search-input" 
                                   placeholder="Search by code...">
                            <select id="statusFilter" class="filter-select">
                                <option value="all">All Status</option>
                                <option value="AVAILABLE" selected>Available</option>
                                <option value="RESERVED">Reserved</option>
                                <option value="SOLD">Sold</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="items-grid" id="itemsGrid">
                        ${itemsHtml}
                    </div>
                    
                    <div class="selected-items-summary" id="selectedSummary" style="display: none;">
                        <h3>Selected Items (${this.selectedItems.length})</h3>
                        <div class="selected-items-list" id="selectedItemsList"></div>
                    </div>
                    
                    <div class="selector-actions">
                        <button class="btn btn-secondary" data-action="close">Cancel</button>
                        <button class="btn btn-primary" id="confirmSelection" disabled>
                            Confirm Selection (${this.selectedItems.length})
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        return modal;
    }

    /**
     * Check if item is expiring soon (within 7 days)
     * @param {string} expiryDate - Expiry date string
     * @returns {boolean}
     */
    isExpiringSoon(expiryDate) {
        const expiry = new Date(expiryDate);
        const now = new Date();
        const daysUntilExpiry = Math.ceil((expiry - now) / (1000 * 60 * 60 * 24));
        return daysUntilExpiry <= 7 && daysUntilExpiry > 0;
    }

    /**
     * Attach modal events
     * @param {HTMLElement} modal - Modal element
     */
    attachModalEvents(modal) {
        const searchInput = modal.querySelector('#itemSearch');
        const statusFilter = modal.querySelector('#statusFilter');
        const itemsGrid = modal.querySelector('#itemsGrid');
        const confirmBtn = modal.querySelector('#confirmSelection');
        const selectedSummary = modal.querySelector('#selectedSummary');
        const selectedItemsList = modal.querySelector('#selectedItemsList');
        
        // Search functionality
        searchInput.addEventListener('input', (e) => {
            this.filterItems(e.target.value, statusFilter.value, itemsGrid);
        });
        
        // Status filter
        statusFilter.addEventListener('change', (e) => {
            this.filterItems(searchInput.value, e.target.value, itemsGrid);
        });
        
        // Item selection
        itemsGrid.addEventListener('click', (e) => {
            const selectBtn = e.target.closest('[data-action="select"]');
            if (selectBtn && !selectBtn.disabled) {
                const itemId = parseInt(selectBtn.dataset.itemId);
                this.toggleItemSelection(itemId, modal);
            }
        });
        
        // Confirm selection
        confirmBtn.addEventListener('click', () => {
            this.confirmSelection();
            this.closeModal(modal);
        });
        
        // Close buttons
        modal.querySelectorAll('[data-action="close"]').forEach(btn => {
            btn.addEventListener('click', () => this.closeModal(modal));
        });
    }

    /**
     * Filter items based on search and status
     * @param {string} searchTerm - Search term
     * @param {string} status - Status filter
     * @param {HTMLElement} container - Container element
     */
    filterItems(searchTerm, status, container) {
        const term = searchTerm.toLowerCase();
        const filteredItems = this.availableItems.filter(item => {
            const matchesSearch = item.item_code.toLowerCase().includes(term);
            const matchesStatus = status === 'all' || item.status === status;
            return matchesSearch && matchesStatus;
        });
        
        this.renderItems(filteredItems, container);
    }

    /**
     * Render items to container
     * @param {Array} items - Items to render
     * @param {HTMLElement} container - Container element
     */
    renderItems(items, container) {
        let html = items.map(item => `
            <div class="inventory-item-card ${this.selectedItems.includes(item.item_id) ? 'selected' : ''}" 
                 data-item-id="${item.item_id}">
                <div class="item-header">
                    <span class="item-code">${item.item_code}</span>
                    <span class="item-status status-${item.status.toLowerCase()}">${item.status}</span>
                </div>
                <div class="item-details">
                    <div class="detail-row">
                        <span class="label">Weight:</span>
                        <span class="value">${item.weight} kg</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Cost:</span>
                        <span class="value">Rp ${item.unit_cost?.toLocaleString('id-ID') || 'N/A'}</span>
                    </div>
                </div>
                <div class="item-actions">
                    <button class="btn btn-sm ${this.selectedItems.includes(item.item_id) ? 'btn-deselect' : 'btn-select'}" 
                            data-action="select" 
                            data-item-id="${item.item_id}"
                            ${item.status !== 'AVAILABLE' ? 'disabled' : ''}>
                        ${this.selectedItems.includes(item.item_id) ? 'Deselect' : 'Select'}
                    </button>
                </div>
            </div>
        `).join('');
        
        if (items.length === 0) {
            html = `
                <div class="no-items-message">
                    <p>No items match your criteria</p>
                </div>
            `;
        }
        
        container.innerHTML = html;
    }

    /**
     * Toggle item selection
     * @param {number} itemId - Item ID
     * @param {HTMLElement} modal - Modal element
     */
    toggleItemSelection(itemId, modal) {
        const index = this.selectedItems.indexOf(itemId);
        
        if (index > -1) {
            this.selectedItems.splice(index, 1);
        } else {
            this.selectedItems.push(itemId);
        }
        
        // Update UI
        this.updateSelectionUI(modal);
    }

    /**
     * Update selection UI
     * @param {HTMLElement} modal - Modal element
     */
    updateSelectionUI(modal) {
        const confirmBtn = modal.querySelector('#confirmSelection');
        const selectedSummary = modal.querySelector('#selectedSummary');
        const selectedItemsList = modal.querySelector('#selectedItemsList');
        const itemsGrid = modal.querySelector('#itemsGrid');
        
        // Update confirm button
        confirmBtn.textContent = `Confirm Selection (${this.selectedItems.length})`;
        confirmBtn.disabled = this.selectedItems.length === 0;
        
        // Update selected summary
        if (this.selectedItems.length > 0) {
            selectedSummary.style.display = 'block';
            
            const selectedItemsData = this.availableItems.filter(item => 
                this.selectedItems.includes(item.item_id)
            );
            
            selectedItemsList.innerHTML = selectedItemsData.map(item => `
                <div class="selected-item-row">
                    <span class="item-code">${item.item_code}</span>
                    <span class="item-weight">${item.weight} kg</span>
                    <button class="btn-remove" data-item-id="${item.item_id}">&times;</button>
                </div>
            `).join('');
            
            // Add remove handlers
            selectedItemsList.querySelectorAll('.btn-remove').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const itemId = parseInt(e.target.dataset.itemId);
                    this.toggleItemSelection(itemId, modal);
                });
            });
        } else {
            selectedSummary.style.display = 'none';
        }
        
        // Re-render items grid to show selection state
        this.renderItems(this.availableItems, itemsGrid);
    }

    /**
     * Confirm selection
     */
    confirmSelection() {
        const selectedItemsData = this.availableItems.filter(item => 
            this.selectedItems.includes(item.item_id)
        );
        
        // Dispatch custom event
        const event = new CustomEvent('inventoryItemsSelected', {
            detail: {
                items: selectedItemsData,
                selectedIds: this.selectedItems
            }
        });
        document.dispatchEvent(event);
    }

    /**
     * Close modal
     * @param {HTMLElement} modal - Modal element
     */
    closeModal(modal) {
        modal.remove();
        this.selectedItems = [];
        this.availableItems = [];
        this.inventoryId = null;
        this.branchId = null;
    }
}

// Initialize global instance
const inventoryItemSelector = new InventoryItemSelector();
