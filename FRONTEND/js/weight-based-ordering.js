/**
 * Weight-Based Ordering Component
 * 
 * Handles ordering of products with weight-based pricing (e.g., grilled fish, roasted pork)
 * 
 * @version 1.0.0
 * @date 2026-07-08
 */

class WeightBasedOrdering {
    constructor() {
        this.currentProduct = null;
        this.selectedWeight = 0;
        this.selectedUnit = null;
        this.calculatedPrice = 0;
        this.availableItems = [];
    }

    /**
     * Initialize weight-based ordering for a product
     * @param {Object} product - Product data
     */
    async initialize(product) {
        this.currentProduct = product;
        
        if (product.pricing_type === 'WEIGHT_BASED') {
            await this.loadAvailableItems();
            this.showWeightInputModal();
        } else if (product.pricing_type === 'UNIT_BASED') {
            await this.loadAvailableItems();
            this.showUnitSelectionModal();
        } else {
            // Fixed price, use standard ordering
            return false;
        }
        
        return true;
    }

    /**
     * Load available inventory items for the product
     */
    async loadAvailableItems() {
        try {
            const response = await fetch(`${Config.api.baseURL}/inventory/available-items/${this.currentProduct.product_id}`);
            const data = await response.json();
            this.availableItems = data.items || [];
        } catch (error) {
            console.error('Error loading available items:', error);
            this.availableItems = [];
        }
    }

    /**
     * Show modal for weight-based input
     */
    showWeightInputModal() {
        const modal = this.createWeightModal();
        document.body.appendChild(modal);
        this.attachWeightModalEvents(modal);
    }

    /**
     * Show modal for unit-based selection
     */
    showUnitSelectionModal() {
        const modal = this.createUnitModal();
        document.body.appendChild(modal);
        this.attachUnitModalEvents(modal);
    }

    /**
     * Create weight input modal
     */
    createWeightModal() {
        const modal = document.createElement('div');
        modal.className = 'modal weight-order-modal';
        modal.id = 'weightOrderModal';
        
        const unitPrice = this.currentProduct.unit_price_per_kg || 0;
        
        modal.innerHTML = `
            <div class="modal-content">
                <button class="close-modal" data-action="close">&times;</button>
                <div class="weight-order-content">
                    <h2>${this.currentProduct.product_name}</h2>
                    <p class="price-info">Rp ${unitPrice.toLocaleString('id-ID')} / kg</p>
                    
                    <div class="weight-input-section">
                        <label for="weightInput">Weight (kg):</label>
                        <div class="weight-input-wrapper">
                            <input type="number" 
                                   id="weightInput" 
                                   class="weight-input" 
                                   step="0.1" 
                                   min="0.1" 
                                   max="10" 
                                   placeholder="0.0"
                                   value="0.5">
                            <span class="unit-label">kg</span>
                        </div>
                        <div class="weight-presets">
                            <button class="weight-preset" data-weight="0.3">0.3 kg</button>
                            <button class="weight-preset" data-weight="0.5">0.5 kg</button>
                            <button class="weight-preset" data-weight="0.7">0.7 kg</button>
                            <button class="weight-preset" data-weight="1.0">1.0 kg</button>
                        </div>
                    </div>
                    
                    <div class="calculated-price-section">
                        <label>Calculated Price:</label>
                        <p class="calculated-price" id="calculatedPrice">Rp ${(unitPrice * 0.5).toLocaleString('id-ID')}</p>
                    </div>
                    
                    <div class="availability-info">
                        <p class="available-count">Available items: ${this.availableItems.length}</p>
                        ${this.availableItems.length === 0 ? '<p class="warning">No items available</p>' : ''}
                    </div>
                    
                    <div class="order-actions">
                        <button class="btn btn-secondary" data-action="close">Cancel</button>
                        <button class="btn btn-primary" id="confirmWeightOrder" ${this.availableItems.length === 0 ? 'disabled' : ''}>
                            Add to Order
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        return modal;
    }

    /**
     * Create unit selection modal
     */
    createUnitModal() {
        const modal = document.createElement('div');
        modal.className = 'modal unit-order-modal';
        modal.id = 'unitOrderModal';
        
        const unitPrice = this.currentProduct.unit_price_per_unit || 0;
        
        let itemsHtml = this.availableItems.map(item => `
            <div class="unit-item" data-item-id="${item.item_id}" data-weight="${item.weight}">
                <div class="unit-item-info">
                    <span class="item-code">${item.item_code}</span>
                    <span class="item-weight">${item.weight} kg</span>
                </div>
                <div class="unit-item-price">
                    Rp ${(item.weight * unitPrice).toLocaleString('id-ID')}
                </div>
            </div>
        `).join('');
        
        if (this.availableItems.length === 0) {
            itemsHtml = '<p class="no-items">No items available</p>';
        }
        
        modal.innerHTML = `
            <div class="modal-content">
                <button class="close-modal" data-action="close">&times;</button>
                <div class="unit-order-content">
                    <h2>${this.currentProduct.product_name}</h2>
                    <p class="price-info">Rp ${unitPrice.toLocaleString('id-ID')} / kg</p>
                    
                    <div class="unit-selection-section">
                        <label>Select an item:</label>
                        <div class="unit-items-list" id="unitItemsList">
                            ${itemsHtml}
                        </div>
                    </div>
                    
                    <div class="selected-item-info" id="selectedItemInfo" style="display: none;">
                        <label>Selected:</label>
                        <p class="selected-item" id="selectedItem">-</p>
                        <p class="selected-price" id="selectedPrice">Rp 0</p>
                    </div>
                    
                    <div class="order-actions">
                        <button class="btn btn-secondary" data-action="close">Cancel</button>
                        <button class="btn btn-primary" id="confirmUnitOrder" disabled>
                            Add to Order
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        return modal;
    }

    /**
     * Attach events for weight modal
     */
    attachWeightModalEvents(modal) {
        const weightInput = modal.querySelector('#weightInput');
        const calculatedPrice = modal.querySelector('#calculatedPrice');
        const confirmBtn = modal.querySelector('#confirmWeightOrder');
        const unitPrice = this.currentProduct.unit_price_per_kg || 0;
        
        // Weight input change
        weightInput.addEventListener('input', (e) => {
            const weight = parseFloat(e.target.value) || 0;
            this.selectedWeight = weight;
            calculatedPrice.textContent = `Rp ${(weight * unitPrice).toLocaleString('id-ID')}`;
        });
        
        // Weight preset buttons
        modal.querySelectorAll('.weight-preset').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const weight = parseFloat(e.target.dataset.weight);
                weightInput.value = weight;
                this.selectedWeight = weight;
                calculatedPrice.textContent = `Rp ${(weight * unitPrice).toLocaleString('id-ID')}`;
            });
        });
        
        // Confirm button
        confirmBtn.addEventListener('click', () => {
            if (this.selectedWeight > 0) {
                this.calculatedPrice = this.selectedWeight * unitPrice;
                this.addToOrder({
                    product_id: this.currentProduct.product_id,
                    product_name: this.currentProduct.product_name,
                    quantity: 1,
                    actual_weight: this.selectedWeight,
                    calculated_price: this.calculatedPrice,
                    unit_price: unitPrice
                });
                this.closeModal(modal);
            }
        });
        
        // Close buttons
        modal.querySelectorAll('[data-action="close"]').forEach(btn => {
            btn.addEventListener('click', () => this.closeModal(modal));
        });
    }

    /**
     * Attach events for unit modal
     */
    attachUnitModalEvents(modal) {
        const unitItems = modal.querySelectorAll('.unit-item');
        const confirmBtn = modal.querySelector('#confirmUnitOrder');
        const selectedItemInfo = modal.querySelector('#selectedItemInfo');
        const selectedItem = modal.querySelector('#selectedItem');
        const selectedPrice = modal.querySelector('#selectedPrice');
        const unitPrice = this.currentProduct.unit_price_per_unit || 0;
        
        // Unit item selection
        unitItems.forEach(item => {
            item.addEventListener('click', (e) => {
                // Remove active class from all items
                unitItems.forEach(i => i.classList.remove('active'));
                
                // Add active class to clicked item
                item.classList.add('active');
                
                // Update selected info
                const itemId = item.dataset.itemId;
                const weight = parseFloat(item.dataset.weight);
                const itemCode = item.querySelector('.item-code').textContent;
                
                this.selectedUnit = {
                    item_id: itemId,
                    weight: weight,
                    item_code: itemCode
                };
                
                selectedItem.textContent = `${itemCode} (${weight} kg)`;
                selectedPrice.textContent = `Rp ${(weight * unitPrice).toLocaleString('id-ID')}`;
                selectedItemInfo.style.display = 'block';
                confirmBtn.disabled = false;
            });
        });
        
        // Confirm button
        confirmBtn.addEventListener('click', () => {
            if (this.selectedUnit) {
                this.calculatedPrice = this.selectedUnit.weight * unitPrice;
                this.addToOrder({
                    product_id: this.currentProduct.product_id,
                    product_name: this.currentProduct.product_name,
                    quantity: 1,
                    actual_weight: this.selectedUnit.weight,
                    actual_unit_id: this.selectedUnit.item_id,
                    calculated_price: this.calculatedPrice,
                    unit_price: unitPrice
                });
                this.closeModal(modal);
            }
        });
        
        // Close buttons
        modal.querySelectorAll('[data-action="close"]').forEach(btn => {
            btn.addEventListener('click', () => this.closeModal(modal));
        });
    }

    /**
     * Add item to order
     * @param {Object} orderItem - Order item data
     */
    addToOrder(orderItem) {
        // Dispatch custom event for order management
        const event = new CustomEvent('weightBasedOrderAdded', {
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
        this.currentProduct = null;
        this.selectedWeight = 0;
        this.selectedUnit = null;
        this.calculatedPrice = 0;
        this.availableItems = [];
    }
}

// Initialize global instance
const weightBasedOrdering = new WeightBasedOrdering();
