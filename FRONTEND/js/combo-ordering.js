/**
 * Combo Ordering Component
 * 
 * Handles ordering of combo meals with special pricing
 * 
 * @version 1.0.0
 * @date 2026-07-08
 */

class ComboOrdering {
    constructor() {
        this.currentCombo = null;
        this.selectedItems = {};
        this.calculatedPrice = 0;
        this.availableCombos = [];
    }

    /**
     * Initialize combo ordering
     * @param {Object} combo - Combo data
     */
    async initialize(combo = null) {
        if (combo) {
            this.currentCombo = combo;
            this.showComboSelectionModal();
        } else {
            await this.loadAvailableCombos();
            this.showComboListModal();
        }
        return true;
    }

    /**
     * Load available combos
     */
    async loadAvailableCombos() {
        try {
            const response = await fetch(`${Config.api.baseURL}/sales/combos`);
            const data = await response.json();
            this.availableCombos = data.data || [];
        } catch (error) {
            console.error('Error loading combos:', error);
            this.availableCombos = [];
        }
    }

    /**
     * Show combo list modal
     */
    showComboListModal() {
        const modal = this.createComboListModal();
        document.body.appendChild(modal);
        this.attachComboListEvents(modal);
    }

    /**
     * Show combo selection modal
     */
    showComboSelectionModal() {
        const modal = this.createComboSelectionModal();
        document.body.appendChild(modal);
        this.attachComboSelectionEvents(modal);
    }

    /**
     * Create combo list modal
     */
    createComboListModal() {
        const modal = document.createElement('div');
        modal.className = 'modal combo-list-modal';
        modal.id = 'comboListModal';
        
        let combosHtml = this.availableCombos.map(combo => `
            <div class="combo-card" data-combo-id="${combo.combo_id}">
                <div class="combo-info">
                    <h3 class="combo-name">${combo.combo_name}</h3>
                    <p class="combo-description">${combo.description || ''}</p>
                    <div class="combo-items">
                        ${combo.items.map(item => `
                            <span class="combo-item-tag">${item.menu_name} (${item.quantity})</span>
                        `).join('')}
                    </div>
                </div>
                <div class="combo-pricing">
                    <p class="combo-price">Rp ${combo.combo_price.toLocaleString('id-ID')}</p>
                    ${combo.discount_amount > 0 ? `
                        <p class="combo-savings">
                            Save Rp ${combo.discount_amount.toLocaleString('id-ID')}
                        </p>
                    ` : ''}
                </div>
            </div>
        `).join('');
        
        if (this.availableCombos.length === 0) {
            combosHtml = '<p class="no-combos">No combos available</p>';
        }
        
        modal.innerHTML = `
            <div class="modal-content">
                <button class="close-modal" data-action="close">&times;</button>
                <div class="combo-list-content">
                    <h2>Available Combos</h2>
                    <div class="combos-grid" id="combosGrid">
                        ${combosHtml}
                    </div>
                </div>
            </div>
        `;
        
        return modal;
    }

    /**
     * Create combo selection modal
     */
    createComboSelectionModal() {
        const modal = document.createElement('div');
        modal.className = 'modal combo-selection-modal';
        modal.id = 'comboSelectionModal';
        
        const combo = this.currentCombo;
        
        let itemsHtml = combo.items.map(item => `
            <div class="combo-item-selector" data-menu-id="${item.menu_id}" data-quantity="${item.quantity}">
                <div class="item-info">
                    <span class="item-name">${item.menu_name}</span>
                    <span class="item-quantity">Required: ${item.quantity}</span>
                </div>
                <div class="item-price">
                    Rp ${item.menu_price.toLocaleString('id-ID')}
                </div>
            </div>
        `).join('');
        
        modal.innerHTML = `
            <div class="modal-content">
                <button class="close-modal" data-action="close">&times;</button>
                <div class="combo-selection-content">
                    <h2>${combo.combo_name}</h2>
                    <p class="combo-description">${combo.description || ''}</p>
                    
                    <div class="combo-items-section">
                        <h3>Combo Items</h3>
                        <div class="combo-items-list">
                            ${itemsHtml}
                        </div>
                    </div>
                    
                    <div class="combo-pricing-section">
                        <div class="regular-price">
                            <label>Regular Price:</label>
                            <p id="regularPrice">Rp 0</p>
                        </div>
                        <div class="combo-price-display">
                            <label>Combo Price:</label>
                            <p class="combo-price" id="comboPrice">Rp ${combo.combo_price.toLocaleString('id-ID')}</p>
                        </div>
                        <div class="savings-display">
                            <label>You Save:</label>
                            <p class="savings-amount" id="savingsAmount">Rp 0</p>
                        </div>
                    </div>
                    
                    <div class="order-actions">
                        <button class="btn btn-secondary" data-action="close">Cancel</button>
                        <button class="btn btn-primary" id="confirmComboOrder">
                            Add Combo to Order
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        return modal;
    }

    /**
     * Attach events for combo list modal
     */
    attachComboListEvents(modal) {
        const comboCards = modal.querySelectorAll('.combo-card');
        
        comboCards.forEach(card => {
            card.addEventListener('click', () => {
                const comboId = parseInt(card.dataset.comboId);
                const combo = this.availableCombos.find(c => c.combo_id === comboId);
                if (combo) {
                    this.closeModal(modal);
                    this.currentCombo = combo;
                    this.showComboSelectionModal();
                }
            });
        });
        
        // Close buttons
        modal.querySelectorAll('[data-action="close"]').forEach(btn => {
            btn.addEventListener('click', () => this.closeModal(modal));
        });
    }

    /**
     * Attach events for combo selection modal
     */
    attachComboSelectionEvents(modal) {
        const confirmBtn = modal.querySelector('#confirmComboOrder');
        const regularPriceEl = modal.querySelector('#regularPrice');
        const savingsAmountEl = modal.querySelector('#savingsAmount');
        
        // Calculate regular price and savings
        this.calculateComboPricing();
        regularPriceEl.textContent = `Rp ${this.currentCombo.regular_price?.toLocaleString('id-ID') || 0}`;
        savingsAmountEl.textContent = `Rp ${this.currentCombo.discount_amount?.toLocaleString('id-ID') || 0}`;
        
        // Confirm button
        confirmBtn.addEventListener('click', () => {
            this.addToOrder({
                combo_id: this.currentCombo.combo_id,
                combo_name: this.currentCombo.combo_name,
                quantity: 1,
                unit_price: this.currentCombo.combo_price,
                calculated_price: this.currentCombo.combo_price,
                items: this.currentCombo.items
            });
            this.closeModal(modal);
        });
        
        // Close buttons
        modal.querySelectorAll('[data-action="close"]').forEach(btn => {
            btn.addEventListener('click', () => this.closeModal(modal));
        });
    }

    /**
     * Calculate combo pricing
     */
    async calculateComboPricing() {
        try {
            const quantities = {};
            this.currentCombo.items.forEach(item => {
                quantities[item.menu_id] = item.quantity;
            });
            
            const response = await fetch(`${Config.api.baseURL}/sales/combos/calculate-price`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    combo_id: this.currentCombo.combo_id,
                    quantities: quantities
                })
            });
            
            const data = await response.json();
            if (data.success) {
                this.currentCombo.regular_price = data.data.regular_price;
                this.currentCombo.discount_amount = data.data.discount_amount;
                this.currentCombo.discount_percentage = data.data.discount_percentage;
            }
        } catch (error) {
            console.error('Error calculating combo price:', error);
        }
    }

    /**
     * Add combo to order
     * @param {Object} orderItem - Order item data
     */
    addToOrder(orderItem) {
        // Dispatch custom event for order management
        const event = new CustomEvent('comboOrderAdded', {
            detail: orderItem
        });
        document.dispatchEvent(event);
    }

    /**
     * Close modal
     * @param {HTMLElement} modal - Modal element
     */
    closeModal(modal) {
        modal.remove();
        this.currentCombo = null;
        this.selectedItems = {};
        this.calculatedPrice = 0;
    }
}

// Initialize global instance
const comboOrdering = new ComboOrdering();
