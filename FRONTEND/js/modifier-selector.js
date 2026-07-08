/**
 * Modifier Selector Component
 * 
 * Handles selection of product modifiers (e.g., seasonings, side dishes, toppings)
 * for made-to-order products
 * 
 * @version 1.0.0
 * @date 2026-07-08
 */

class ModifierSelector {
    constructor() {
        this.selectedModifiers = [];
        this.availableModifiers = [];
        this.productId = null;
        this.modifierGroups = [];
    }

    /**
     * Initialize modifier selector
     * @param {number} productId - Product ID
     */
    async initialize(productId) {
        this.productId = productId;
        await this.loadAvailableModifiers();
        this.showModifierModal();
    }

    /**
     * Load available modifiers for the product
     */
    async loadAvailableModifiers() {
        try {
            const response = await fetch(`${API_BASE_URL}/products/${this.productId}/modifiers`);
            const data = await response.json();
            this.modifierGroups = data.groups || [];
            this.availableModifiers = data.modifiers || [];
        } catch (error) {
            console.error('Error loading modifiers:', error);
            this.modifierGroups = [];
            this.availableModifiers = [];
        }
    }

    /**
     * Show modifier selection modal
     */
    showModifierModal() {
        const modal = this.createModifierModal();
        document.body.appendChild(modal);
        this.attachModalEvents(modal);
    }

    /**
     * Create modifier modal
     */
    createModifierModal() {
        const modal = document.createElement('div');
        modal.className = 'modal modifier-selector-modal';
        modal.id = 'modifierSelectorModal';
        
        let groupsHtml = this.modifierGroups.map(group => {
            const groupModifiers = this.availableModifiers.filter(m => m.modifier_group_id === group.modifier_group_id);
            
            if (groupModifiers.length === 0) return '';
            
            return `
                <div class="modifier-group" data-group-id="${group.modifier_group_id}">
                    <div class="group-header">
                        <h3>${group.modifier_group_name}</h3>
                        ${group.description ? `<p class="group-description">${group.description}</p>` : ''}
                    </div>
                    <div class="group-modifiers">
                        ${groupModifiers.map(modifier => `
                            <div class="modifier-item" data-modifier-id="${modifier.modifier_id}">
                                <div class="modifier-info">
                                    <div class="modifier-name">${modifier.modifier_name}</div>
                                    ${modifier.price_adjustment > 0 ? `
                                    <div class="modifier-price">+Rp ${modifier.price_adjustment.toLocaleString('id-ID')}</div>
                                    ` : ''}
                                </div>
                                <div class="modifier-control">
                                    <input type="checkbox" 
                                           class="modifier-checkbox" 
                                           id="modifier_${modifier.modifier_id}"
                                           data-modifier-id="${modifier.modifier_id}"
                                           data-price="${modifier.price_adjustment}"
                                           ${!modifier.is_available ? 'disabled' : ''}>
                                    <label for="modifier_${modifier.modifier_id}"></label>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
        }).join('');
        
        if (this.modifierGroups.length === 0 || this.availableModifiers.length === 0) {
            groupsHtml = `
                <div class="no-modifiers-message">
                    <p>No modifiers available for this product</p>
                </div>
            `;
        }
        
        modal.innerHTML = `
            <div class="modal-content">
                <button class="close-modal" data-action="close">&times;</button>
                <div class="modifier-selector-content">
                    <div class="selector-header">
                        <h2>Select Modifiers</h2>
                        <p class="header-description">Add seasonings, side dishes, or other options</p>
                    </div>
                    
                    <div class="modifier-groups" id="modifierGroups">
                        ${groupsHtml}
                    </div>
                    
                    <div class="selected-modifiers-summary" id="selectedSummary" style="display: none;">
                        <h3>Selected Modifiers (${this.selectedModifiers.length})</h3>
                        <div class="selected-modifiers-list" id="selectedModifiersList"></div>
                        <div class="total-modifier-price">
                            <span>Modifier Total:</span>
                            <span id="totalModifierPrice">Rp 0</span>
                        </div>
                    </div>
                    
                    <div class="selector-actions">
                        <button class="btn btn-secondary" data-action="close">Cancel</button>
                        <button class="btn btn-primary" id="confirmModifiers">
                            Confirm (${this.selectedModifiers.length})
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        return modal;
    }

    /**
     * Attach modal events
     * @param {HTMLElement} modal - Modal element
     */
    attachModalEvents(modal) {
        const modifierCheckboxes = modal.querySelectorAll('.modifier-checkbox');
        const confirmBtn = modal.querySelector('#confirmModifiers');
        const selectedSummary = modal.querySelector('#selectedSummary');
        const selectedModifiersList = modal.querySelector('#selectedModifiersList');
        const totalModifierPrice = modal.querySelector('#totalModifierPrice');
        
        // Modifier checkbox changes
        modifierCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', (e) => {
                const modifierId = parseInt(e.target.dataset.modifierId);
                const price = parseFloat(e.target.dataset.price);
                
                if (e.target.checked) {
                    this.selectedModifiers.push({
                        modifier_id: modifierId,
                        price_adjustment: price
                    });
                } else {
                    this.selectedModifiers = this.selectedModifiers.filter(m => m.modifier_id !== modifierId);
                }
                
                this.updateSelectionUI(modal);
            });
        });
        
        // Confirm button
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
     * Update selection UI
     * @param {HTMLElement} modal - Modal element
     */
    updateSelectionUI(modal) {
        const confirmBtn = modal.querySelector('#confirmModifiers');
        const selectedSummary = modal.querySelector('#selectedSummary');
        const selectedModifiersList = modal.querySelector('#selectedModifiersList');
        const totalModifierPrice = modal.querySelector('#totalModifierPrice');
        
        // Update confirm button
        confirmBtn.textContent = `Confirm (${this.selectedModifiers.length})`;
        
        // Update selected summary
        if (this.selectedModifiers.length > 0) {
            selectedSummary.style.display = 'block';
            
            const selectedModifiersData = this.availableModifiers.filter(modifier => 
                this.selectedModifiers.some(m => m.modifier_id === modifier.modifier_id)
            );
            
            selectedModifiersList.innerHTML = selectedModifiersData.map(modifier => `
                <div class="selected-modifier-row">
                    <span class="modifier-name">${modifier.modifier_name}</span>
                    <span class="modifier-price">+Rp ${modifier.price_adjustment.toLocaleString('id-ID')}</span>
                </div>
            `).join('');
            
            // Calculate total
            const total = this.selectedModifiers.reduce((sum, m) => sum + m.price_adjustment, 0);
            totalModifierPrice.textContent = `Rp ${total.toLocaleString('id-ID')}`;
        } else {
            selectedSummary.style.display = 'none';
        }
    }

    /**
     * Confirm selection
     */
    confirmSelection() {
        const selectedModifiersData = this.availableModifiers.filter(modifier => 
            this.selectedModifiers.some(m => m.modifier_id === modifier.modifier_id)
        );
        
        const total = this.selectedModifiers.reduce((sum, m) => sum + m.price_adjustment, 0);
        
        // Dispatch custom event
        const event = new CustomEvent('modifiersSelected', {
            detail: {
                modifiers: selectedModifiersData,
                selectedIds: this.selectedModifiers,
                totalAdjustment: total
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
        this.selectedModifiers = [];
        this.availableModifiers = [];
        this.modifierGroups = [];
        this.productId = null;
    }

    /**
     * Quick add single modifier (for inline usage)
     * @param {Object} modifier - Modifier data
     */
    quickAddModifier(modifier) {
        this.selectedModifiers.push({
            modifier_id: modifier.modifier_id,
            price_adjustment: modifier.price_adjustment
        });
        
        const event = new CustomEvent('modifierQuickAdded', {
            detail: modifier
        });
        document.dispatchEvent(event);
    }

    /**
     * Quick remove single modifier (for inline usage)
     * @param {number} modifierId - Modifier ID
     */
    quickRemoveModifier(modifierId) {
        this.selectedModifiers = this.selectedModifiers.filter(m => m.modifier_id !== modifierId);
        
        const event = new CustomEvent('modifierQuickRemoved', {
            detail: { modifier_id: modifierId }
        });
        document.dispatchEvent(event);
    }
}

// Initialize global instance
const modifierSelector = new ModifierSelector();
