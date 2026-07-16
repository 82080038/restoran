/**
 * Smart Suggestions Engine
 * Provides intelligent suggestions, predictions, and recommendations
 */
class SmartSuggestionsEngine {
    constructor() {
        this.userHistory = [];
        this.popularItems = [];
        this.trendingItems = [];
        this.combinations = new Map();
        this.init();
    }

    init() {
        this.loadUserHistory();
        this.loadPopularItems();
        this.loadTrendingItems();
        this.loadCombinations();
    }

    /**
     * Load user order history
     */
    async loadUserHistory() {
        try {
            const response = await window.apiClient.get('/orders/history');
            if (response && response.success) {
                this.userHistory = response.data || [];
            }
        } catch (error) {
            console.error('Failed to load user history:', error);
        }
    }

    /**
     * Load popular items
     */
    async loadPopularItems() {
        try {
            const response = await window.apiClient.get('/products/popular');
            if (response && response.success) {
                this.popularItems = response.data || [];
            }
        } catch (error) {
            console.error('Failed to load popular items:', error);
        }
    }

    /**
     * Load trending items
     */
    async loadTrendingItems() {
        try {
            const response = await window.apiClient.get('/products/trending');
            if (response && response.success) {
                this.trendingItems = response.data || [];
            }
        } catch (error) {
            console.error('Failed to load trending items:', error);
        }
    }

    /**
     * Load item combinations
     */
    async loadCombinations() {
        try {
            const response = await window.apiClient.get('/products/combinations');
            if (response && response.success) {
                const combinations = response.data || [];
                combinations.forEach(combo => {
                    this.combinations.set(combo.product_id, combo.suggested_products);
                });
            }
        } catch (error) {
            console.error('Failed to load combinations:', error);
        }
    }

    /**
     * Get product suggestions based on context
     * @param {Object} context - Context data
     */
    getSuggestions(context = {}) {
        const suggestions = [];

        // Time-based suggestions
        const timeSuggestions = this.getTimeBasedSuggestions();
        suggestions.push(...timeSuggestions);

        // History-based suggestions
        const historySuggestions = this.getHistoryBasedSuggestions();
        suggestions.push(...historySuggestions);

        // Popular items
        if (this.popularItems.length > 0) {
            suggestions.push({
                type: 'popular',
                items: this.popularItems.slice(0, 3),
                reason: 'Popular with other customers'
            });
        }

        // Trending items
        if (this.trendingItems.length > 0) {
            suggestions.push({
                type: 'trending',
                items: this.trendingItems.slice(0, 3),
                reason: 'Trending now'
            });
        }

        // Complementary items
        if (context.currentItem) {
            const complementary = this.getComplementaryItems(context.currentItem);
            if (complementary.length > 0) {
                suggestions.push({
                    type: 'complementary',
                    items: complementary,
                    reason: 'Often ordered together'
                });
            }
        }

        return suggestions;
    }

    /**
     * Get time-based suggestions
     */
    getTimeBasedSuggestions() {
        const hour = new Date().getHours();
        const suggestions = [];

        if (hour >= 6 && hour < 11) {
            // Breakfast
            suggestions.push({
                type: 'time-based',
                items: this.getItemsByCategory('Breakfast'),
                reason: 'Good for breakfast'
            });
        } else if (hour >= 11 && hour < 14) {
            // Lunch
            suggestions.push({
                type: 'time-based',
                items: this.getItemsByCategory('Main Course'),
                reason: 'Perfect for lunch'
            });
        } else if (hour >= 14 && hour < 17) {
            // Afternoon snack
            suggestions.push({
                type: 'time-based',
                items: this.getItemsByCategory('Snacks'),
                reason: 'Afternoon snack time'
            });
        } else if (hour >= 17 && hour < 21) {
            // Dinner
            suggestions.push({
                type: 'time-based',
                items: this.getItemsByCategory('Main Course'),
                reason: 'Dinner favorites'
            });
        } else {
            // Late night
            suggestions.push({
                type: 'time-based',
                items: this.getItemsByCategory('Beverages'),
                reason: 'Late night options'
            });
        }

        return suggestions;
    }

    /**
     * Get history-based suggestions
     */
    getHistoryBasedSuggestions() {
        if (this.userHistory.length === 0) return [];

        // Count item frequency
        const itemCounts = new Map();
        this.userHistory.forEach(order => {
            order.items.forEach(item => {
                const count = itemCounts.get(item.product_id) || 0;
                itemCounts.set(item.product_id, count + 1);
            });
        });

        // Sort by frequency
        const sortedItems = Array.from(itemCounts.entries())
            .sort((a, b) => b[1] - a[1])
            .slice(0, 5)
            .map(([productId]) => productId);

        // Get product details
        const items = this.getItemsByIds(sortedItems);

        return [{
            type: 'history',
            items: items,
            reason: 'Based on your order history'
        }];
    }

    /**
     * Get complementary items
     * @param {Object} currentItem - Current item
     */
    getComplementaryItems(currentItem) {
        const suggestedIds = this.combinations.get(currentItem.product_id) || [];
        return this.getItemsByIds(suggestedIds);
    }

    /**
     * Get items by category
     * @param {string} category - Category name
     */
    getItemsByCategory(category) {
        // This would typically come from the menu
        // For now, return empty array
        return [];
    }

    /**
     * Get items by IDs
     * @param {Array} ids - Product IDs
     */
    getItemsByIds(ids) {
        // This would typically fetch from API
        // For now, return empty array
        return [];
    }

    /**
     * Predict order completion time
     * @param {Object} order - Order data
     */
    predictOrderCompletion(order) {
        const baseTime = 15; // Base preparation time in minutes
        const itemTime = 3; // Time per item
        const peakMultiplier = this.getPeakHourMultiplier();
        
        const estimatedTime = baseTime + (order.items_count * itemTime) * peakMultiplier;
        
        const completionTime = new Date();
        completionTime.setMinutes(completionTime.getMinutes() + estimatedTime);
        
        return {
            estimated_minutes: Math.round(estimatedTime),
            completion_time: completionTime.toISOString(),
            confidence: this.calculateConfidence(order)
        };
    }

    /**
     * Get peak hour multiplier
     */
    getPeakHourMultiplier() {
        const hour = new Date().getHours();
        
        // Peak hours: 12-14 (lunch), 18-21 (dinner)
        if ((hour >= 12 && hour < 14) || (hour >= 18 && hour < 21)) {
            return 1.5;
        }
        
        // Semi-peak: 11-12, 14-17, 21-22
        if ((hour >= 11 && hour < 12) || (hour >= 14 && hour < 17) || (hour >= 21 && hour < 22)) {
            return 1.2;
        }
        
        return 1.0;
    }

    /**
     * Calculate prediction confidence
     * @param {Object} order - Order data
     */
    calculateConfidence(order) {
        let confidence = 0.8; // Base confidence
        
        // Adjust based on order complexity
        if (order.items_count > 10) {
            confidence -= 0.1;
        }
        
        // Adjust based on peak hour
        const peakMultiplier = this.getPeakHourMultiplier();
        if (peakMultiplier > 1.2) {
            confidence -= 0.1;
        }
        
        return Math.max(0.5, Math.min(0.95, confidence));
    }

    /**
     * Suggest optimal table assignment
     * @param {Object} party - Party information
     */
    suggestTable(party) {
        const suggestions = {
            preferred_tables: [],
            alternatives: [],
            reason: ''
        };

        // Based on party size
        if (party.size <= 2) {
            suggestions.reason = 'Small party - 2-seater tables preferred';
        } else if (party.size <= 4) {
            suggestions.reason = 'Medium party - 4-seater tables preferred';
        } else if (party.size <= 6) {
            suggestions.reason = 'Large party - 6-seater or combined tables';
        } else {
            suggestions.reason = 'Very large party - requires table combination';
        }

        return suggestions;
    }

    /**
     * Detect order patterns
     * @param {Array} orders - Order history
     */
    detectPatterns(orders) {
        const patterns = {
            frequent_combinations: [],
            peak_times: [],
            average_order_value: 0,
            preferred_categories: []
        };

        if (orders.length === 0) return patterns;

        // Calculate average order value
        const totalValue = orders.reduce((sum, order) => sum + (order.total_amount || 0), 0);
        patterns.average_order_value = totalValue / orders.length;

        // Detect peak ordering times
        const hourCounts = new Map();
        orders.forEach(order => {
            const hour = new Date(order.created_at).getHours();
            const count = hourCounts.get(hour) || 0;
            hourCounts.set(hour, count + 1);
        });

        const peakHours = Array.from(hourCounts.entries())
            .sort((a, b) => b[1] - a[1])
            .slice(0, 3)
            .map(([hour]) => hour);

        patterns.peak_times = peakHours;

        return patterns;
    }

    /**
     * Get personalized recommendations
     * @param {string} userId - User ID
     */
    async getPersonalizedRecommendations(userId) {
        try {
            const response = await window.apiClient.get(`/smart/suggestions?user_id=${userId}`);
            if (response && response.success) {
                return response.data || [];
            }
        } catch (error) {
            console.error('Failed to get personalized recommendations:', error);
        }

        // Fallback to local suggestions
        return this.getSuggestions();
    }

    /**
     * Accept suggestion
     * @param {string} suggestionId - Suggestion ID
     */
    async acceptSuggestion(suggestionId) {
        try {
            const response = await window.apiClient.post(`/smart/suggestions/${suggestionId}/accept`);
            if (response && response.success) {
                window.toastManager.success('Suggestion accepted');
                return true;
            }
        } catch (error) {
            console.error('Failed to accept suggestion:', error);
            window.toastManager.error('Failed to accept suggestion');
        }
        return false;
    }

    /**
     * Dismiss suggestion
     * @param {string} suggestionId - Suggestion ID
     */
    async dismissSuggestion(suggestionId) {
        try {
            const response = await window.apiClient.post(`/smart/suggestions/${suggestionId}/dismiss`);
            if (response && response.success) {
                return true;
            }
        } catch (error) {
            console.error('Failed to dismiss suggestion:', error);
        }
        return false;
    }
}

/**
 * Smart Automation
 * Handles automated workflows and triggers
 */
class SmartAutomation {
    constructor() {
        this.workflows = new Map();
        this.triggers = new Map();
        this.init();
    }

    init() {
        this.loadWorkflows();
        this.setupTriggers();
    }

    /**
     * Load automation workflows
     */
    async loadWorkflows() {
        try {
            const response = await window.apiClient.get('/automation/workflows');
            if (response && response.success) {
                const workflows = response.data || [];
                workflows.forEach(workflow => {
                    this.workflows.set(workflow.id, workflow);
                });
            }
        } catch (error) {
            console.error('Failed to load workflows:', error);
        }
    }

    /**
     * Setup automated triggers
     */
    setupTriggers() {
        // Order status change trigger
        if (window.realtimeManager) {
            window.realtimeManager.client.on('channel:orders', (message) => {
                this.handleOrderTrigger(message);
            });
        }

        // Table status change trigger
        if (window.realtimeManager) {
            window.realtimeManager.client.on('channel:tables', (message) => {
                this.handleTableTrigger(message);
            });
        }

        // Peak hour trigger
        this.setupPeakHourTrigger();
    }

    /**
     * Handle order trigger
     * @param {Object} message - Order message
     */
    handleOrderTrigger(message) {
        const order = message.data;

        // Auto-assign table if needed
        if (order.status === 'PENDING' && !order.table_id) {
            this.triggerWorkflow('auto-assign-table', { order });
        }

        // Send notification for order completion
        if (order.status === 'READY') {
            this.triggerWorkflow('order-ready-notification', { order });
        }

        // Log order completion time
        if (order.status === 'COMPLETED') {
            this.triggerWorkflow('log-order-metrics', { order });
        }
    }

    /**
     * Handle table trigger
     * @param {Object} message - Table message
     */
    handleTableTrigger(message) {
        const table = message.data;

        // Auto-cleanup after table is cleared
        if (table.status === 'AVAILABLE') {
            this.triggerWorkflow('table-cleanup', { table });
        }
    }

    /**
     * Setup peak hour trigger
     */
    setupPeakHourTrigger() {
        // Check every 5 minutes
        setInterval(() => {
            const hour = new Date().getHours();
            const isPeakHour = (hour >= 12 && hour < 14) || (hour >= 18 && hour < 21);
            
            if (isPeakHour) {
                this.triggerWorkflow('peak-hour-alert', { hour });
            }
        }, 300000); // 5 minutes
    }

    /**
     * Trigger workflow
     * @param {string} workflowName - Workflow name
     * @param {Object} data - Workflow data
     */
    async triggerWorkflow(workflowName, data) {
        try {
            const response = await window.apiClient.post('/automation/trigger', {
                workflow: workflowName,
                data: data
            });

            if (response && response.success) {
                console.log(`Workflow ${workflowName} triggered successfully`);
            }
        } catch (error) {
            console.error(`Failed to trigger workflow ${workflowName}:`, error);
        }
    }

    /**
     * Get active workflows
     */
    getActiveWorkflows() {
        return Array.from(this.workflows.values()).filter(w => w.is_active);
    }

    /**
     * Create custom workflow
     * @param {Object} workflow - Workflow configuration
     */
    async createWorkflow(workflow) {
        try {
            const response = await window.apiClient.post('/automation/workflows', workflow);
            if (response && response.success) {
                this.workflows.set(response.data.id, response.data);
                window.toastManager.success('Workflow created');
                return response.data;
            }
        } catch (error) {
            console.error('Failed to create workflow:', error);
            window.toastManager.error('Failed to create workflow');
        }
        return null;
    }
}

// Initialize smart features
document.addEventListener('DOMContentLoaded', () => {
    window.smartSuggestions = new SmartSuggestionsEngine();
    window.smartAutomation = new SmartAutomation();
});
