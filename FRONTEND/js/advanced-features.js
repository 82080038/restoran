/**
 * Advanced Features Manager
 * Handles Tier 1-4 feature pages in the dashboard
 */
class AdvancedFeaturesManager {
    constructor() {
        this.api = window.apiClient;
        this.data = {
            deposits: [], barCounts: [], depletionLogs: [], batches: [],
            settlements: [], profitability: [], proposals: [],
            tableDeposits: [], bottleInventory: [], promoters: [],
            songs: [], seatMap: [], rainChecks: [], barTabs: [],
            eightSixItems: [], customOrders: [], deliveryRoutes: [],
            pricingRules: [], memberships: [], occupancy: [],
            predictions: [], throttling: [], productionPlans: [],
            coatCheck: [], equipment: [], wines: [],
            waiterStats: [], rotations: []
        };
    }

    // ==================== TIER 1 ====================

    async loadReconciliationPage() {
        try {
            const res = await this.api.request('/pos-reconciliation/deposits');
            if (res && res.success) {
                this.data.deposits = res.data || [];
                return this.renderTable(this.data.deposits, [
                    { key: 'deposit_id', label: 'ID' },
                    { key: 'deposit_date', label: 'Date' },
                    { key: 'pos_total', label: 'POS Total' },
                    { key: 'bank_total', label: 'Bank Total' },
                    { key: 'variance', label: 'Variance' },
                    { key: 'status', label: 'Status' }
                ]);
            }
        } catch (e) { console.error('Reconciliation load error:', e); }
        return '<p class="no-data">No data available</p>';
    }

    async loadBeverageVariancePage() {
        try {
            const res = await this.api.request('/beverage-variance/bar-counts');
            if (res && res.success) {
                this.data.barCounts = res.data || [];
                return this.renderTable(this.data.barCounts, [
                    { key: 'count_id', label: 'ID' },
                    { key: 'count_date', label: 'Date' },
                    { key: 'bar_name', label: 'Bar' },
                    { key: 'count_type', label: 'Type' },
                    { key: 'status', label: 'Status' }
                ]);
            }
        } catch (e) { console.error('Beverage variance load error:', e); }
        return '<p class="no-data">No data available</p>';
    }

    async loadBatchExpiryPage() {
        try {
            const [batchesRes, dashRes] = await Promise.all([
                this.api.request('/batch-expiry/batches'),
                this.api.request('/batch-expiry/dashboard')
            ]);
            let html = '';
            if (dashRes && dashRes.success) {
                const d = dashRes.data;
                html += `<div class="stats-grid">
                    <div class="stat-card"><div class="stat-info"><p class="stat-label">Fresh</p><p class="stat-value">${d.fresh || 0}</p></div></div>
                    <div class="stat-card"><div class="stat-info"><p class="stat-label">Near Expiry</p><p class="stat-value">${d.near_expiry || 0}</p></div></div>
                    <div class="stat-card"><div class="stat-info"><p class="stat-label">Discounted</p><p class="stat-value">${d.discounted || 0}</p></div></div>
                    <div class="stat-card"><div class="stat-info"><p class="stat-label">Expired</p><p class="stat-value">${d.expired || 0}</p></div></div>
                    <div class="stat-card"><div class="stat-info"><p class="stat-label">Value at Risk</p><p class="stat-value">Rp ${this.formatPrice(d.value_at_risk || 0)}</p></div></div>
                </div>`;
            }
            if (batchesRes && batchesRes.success) {
                this.data.batches = batchesRes.data || [];
                html += this.renderTable(this.data.batches, [
                    { key: 'batch_number', label: 'Batch #' },
                    { key: 'product_name', label: 'Product' },
                    { key: 'expiry_date', label: 'Expiry' },
                    { key: 'days_until_expiry', label: 'Days Left' },
                    { key: 'quantity_remaining', label: 'Qty Left' },
                    { key: 'status', label: 'Status' }
                ]);
            }
            return html || '<p class="no-data">No data available</p>';
        } catch (e) { console.error('Batch expiry load error:', e); }
        return '<p class="no-data">No data available</p>';
    }

    async loadSettlementPage() {
        try {
            const res = await this.api.request('/settlements/deals');
            if (res && res.success) {
                this.data.settlements = res.data || [];
                return this.renderTable(this.data.settlements, [
                    { key: 'deal_id', label: 'ID' },
                    { key: 'artist_name', label: 'Artist' },
                    { key: 'event_date', label: 'Event Date' },
                    { key: 'deal_type', label: 'Type' },
                    { key: 'guarantee_amount', label: 'Guarantee' },
                    { key: 'status', label: 'Status' }
                ]);
            }
        } catch (e) { console.error('Settlement load error:', e); }
        return '<p class="no-data">No data available</p>';
    }

    async loadProfitabilityPage() {
        try {
            const res = await this.api.request('/event-profitability');
            if (res && res.success) {
                this.data.profitability = res.data || [];
                return this.renderTable(this.data.profitability, [
                    { key: 'event_id', label: 'ID' },
                    { key: 'event_name', label: 'Event' },
                    { key: 'event_date', label: 'Date' },
                    { key: 'total_revenue', label: 'Revenue' },
                    { key: 'total_costs', label: 'Costs' },
                    { key: 'net_profit', label: 'Net Profit' },
                    { key: 'status', label: 'Status' }
                ]);
            }
        } catch (e) { console.error('Profitability load error:', e); }
        return '<p class="no-data">No data available</p>';
    }

    async loadProposalsPage() {
        try {
            const res = await this.api.request('/event-proposals');
            if (res && res.success) {
                this.data.proposals = res.data || [];
                return this.renderTable(this.data.proposals, [
                    { key: 'proposal_id', label: 'ID' },
                    { key: 'client_name', label: 'Client' },
                    { key: 'event_date', label: 'Event Date' },
                    { key: 'guest_count', label: 'Guests' },
                    { key: 'total_amount', label: 'Total' },
                    { key: 'status', label: 'Status' }
                ]);
            }
        } catch (e) { console.error('Proposals load error:', e); }
        return '<p class="no-data">No data available</p>';
    }

    // ==================== TIER 2 ====================

    async loadNightclubPage() {
        try {
            const [depositsRes, bottleRes, promotersRes] = await Promise.all([
                this.api.request('/nightclub-advanced/table-deposits'),
                this.api.request('/nightclub-advanced/bottle-inventory'),
                this.api.request('/nightclub-advanced/promoters')
            ]);
            let html = '<h3>Table Deposits</h3>';
            if (depositsRes && depositsRes.success) {
                html += this.renderTable(depositsRes.data || [], [
                    { key: 'table_number', label: 'Table' },
                    { key: 'event_date', label: 'Date' },
                    { key: 'deposit_amount', label: 'Deposit' },
                    { key: 'status', label: 'Status' }
                ]);
            }
            html += '<h3>Bottle Inventory</h3>';
            if (bottleRes && bottleRes.success) {
                html += this.renderTable(bottleRes.data || [], [
                    { key: 'bottle_name', label: 'Bottle' },
                    { key: 'category', label: 'Category' },
                    { key: 'quantity', label: 'Qty' },
                    { key: 'price', label: 'Price' },
                    { key: 'status', label: 'Status' }
                ]);
            }
            html += '<h3>Promoters</h3>';
            if (promotersRes && promotersRes.success) {
                html += this.renderTable(promotersRes.data || [], [
                    { key: 'promoter_name', label: 'Name' },
                    { key: 'commission_rate', label: 'Commission' },
                    { key: 'is_active', label: 'Active' }
                ]);
            }
            return html || '<p class="no-data">No data available</p>';
        } catch (e) { console.error('Nightclub load error:', e); }
        return '<p class="no-data">No data available</p>';
    }

    async loadKaraokePage() {
        try {
            const res = await this.api.request('/karaoke-advanced/songs');
            if (res && res.success) {
                this.data.songs = res.data || [];
                return this.renderTable(this.data.songs, [
                    { key: 'title', label: 'Title' },
                    { key: 'artist', label: 'Artist' },
                    { key: 'genre', label: 'Genre' },
                    { key: 'duration', label: 'Duration' },
                    { key: 'language', label: 'Language' }
                ]);
            }
        } catch (e) { console.error('Karaoke load error:', e); }
        return '<p class="no-data">No data available</p>';
    }

    async loadBeachClubPage() {
        try {
            const [seatRes, rainRes] = await Promise.all([
                this.api.request('/beach-club/seat-map'),
                this.api.request('/beach-club/rain-checks')
            ]);
            let html = '<h3>Seat Map</h3>';
            if (seatRes && seatRes.success) {
                html += this.renderTable(seatRes.data || [], [
                    { key: 'seat_label', label: 'Seat' },
                    { key: 'seat_type', label: 'Type' },
                    { key: 'capacity', label: 'Capacity' },
                    { key: 'status', label: 'Status' }
                ]);
            }
            html += '<h3>Rain Checks</h3>';
            if (rainRes && rainRes.success) {
                html += this.renderTable(rainRes.data || [], [
                    { key: 'reservation_id', label: 'Reservation' },
                    { key: 'reason', label: 'Reason' },
                    { key: 'refund_amount', label: 'Refund' },
                    { key: 'status', label: 'Status' }
                ]);
            }
            return html;
        } catch (e) { console.error('Beach club load error:', e); }
        return '<p class="no-data">No data available</p>';
    }

    async loadSportsBarPage() {
        try {
            const res = await this.api.request('/sports-bar/tabs');
            if (res && res.success) {
                this.data.barTabs = res.data || [];
                return this.renderTable(this.data.barTabs, [
                    { key: 'tab_id', label: 'ID' },
                    { key: 'customer_name', label: 'Customer' },
                    { key: 'pre_auth_amount', label: 'Pre-Auth' },
                    { key: 'status', label: 'Status' }
                ]);
            }
        } catch (e) { console.error('Sports bar load error:', e); }
        return '<p class="no-data">No data available</p>';
    }

    async loadOpsAdvancedPage() {
        try {
            const [itemsRes, ordersRes, routesRes] = await Promise.all([
                this.api.request('/operations/86-items'),
                this.api.request('/operations/custom-orders'),
                this.api.request('/operations/delivery-routes')
            ]);
            let html = '<h3>86-ing (Unavailable Items)</h3>';
            if (itemsRes && itemsRes.success) {
                html += this.renderTable(itemsRes.data || [], [
                    { key: 'product_name', label: 'Item' },
                    { key: 'reason', label: 'Reason' },
                    { key: 'estimated_return', label: 'Est. Return' },
                    { key: 'status', label: 'Status' }
                ]);
            }
            html += '<h3>Custom Orders</h3>';
            if (ordersRes && ordersRes.success) {
                html += this.renderTable(ordersRes.data || [], [
                    { key: 'order_id', label: 'ID' },
                    { key: 'customer_name', label: 'Customer' },
                    { key: 'description', label: 'Description' },
                    { key: 'status', label: 'Status' }
                ]);
            }
            html += '<h3>Delivery Routes</h3>';
            if (routesRes && routesRes.success) {
                html += this.renderTable(routesRes.data || [], [
                    { key: 'route_name', label: 'Route' },
                    { key: 'driver_name', label: 'Driver' },
                    { key: 'status', label: 'Status' }
                ]);
            }
            return html;
        } catch (e) { console.error('Ops advanced load error:', e); }
        return '<p class="no-data">No data available</p>';
    }

    // ==================== TIER 3 ====================

    async loadDynamicPricingPage() {
        try {
            const res = await this.api.request('/venue/dynamic-pricing/rules');
            if (res && res.success) {
                this.data.pricingRules = res.data || [];
                return this.renderTable(this.data.pricingRules, [
                    { key: 'rule_name', label: 'Rule' },
                    { key: 'condition_type', label: 'Condition' },
                    { key: 'adjustment_type', label: 'Adjustment' },
                    { key: 'adjustment_value', label: 'Value' },
                    { key: 'is_active', label: 'Active' }
                ]);
            }
        } catch (e) { console.error('Dynamic pricing load error:', e); }
        return '<p class="no-data">No data available</p>';
    }

    async loadMembershipPage() {
        try {
            const res = await this.api.request('/venue/memberships');
            if (res && res.success) {
                this.data.memberships = res.data || [];
                return this.renderTable(this.data.memberships, [
                    { key: 'member_name', label: 'Name' },
                    { key: 'tier', label: 'Tier' },
                    { key: 'points_balance', label: 'Points' },
                    { key: 'status', label: 'Status' }
                ]);
            }
        } catch (e) { console.error('Membership load error:', e); }
        return '<p class="no-data">No data available</p>';
    }

    async loadOccupancyPage() {
        try {
            const res = await this.api.request('/venue/occupancy');
            if (res && res.success) {
                this.data.occupancy = res.data || [];
                return this.renderTable(this.data.occupancy, [
                    { key: 'area_name', label: 'Area' },
                    { key: 'current_count', label: 'Current' },
                    { key: 'capacity', label: 'Capacity' },
                    { key: 'occupancy_pct', label: '%' }
                ]);
            }
        } catch (e) { console.error('Occupancy load error:', e); }
        return '<p class="no-data">No data available</p>';
    }

    async loadPredictionsPage() {
        try {
            const res = await this.api.request('/operations/predictions');
            if (res && res.success) {
                this.data.predictions = res.data || [];
                return this.renderTable(this.data.predictions, [
                    { key: 'prediction_date', label: 'Date' },
                    { key: 'predicted_revenue', label: 'Predicted Revenue' },
                    { key: 'confidence', label: 'Confidence' }
                ]);
            }
        } catch (e) { console.error('Predictions load error:', e); }
        return '<p class="no-data">No data available</p>';
    }

    async loadProductionPlansPage() {
        try {
            const res = await this.api.request('/operations/production-plans');
            if (res && res.success) {
                this.data.productionPlans = res.data || [];
                return this.renderTable(this.data.productionPlans, [
                    { key: 'plan_date', label: 'Date' },
                    { key: 'product_name', label: 'Product' },
                    { key: 'planned_qty', label: 'Planned Qty' },
                    { key: 'status', label: 'Status' }
                ]);
            }
        } catch (e) { console.error('Production plans load error:', e); }
        return '<p class="no-data">No data available</p>';
    }

    // ==================== TIER 4 ====================

    async loadCoatCheckPage() {
        try {
            const res = await this.api.request('/misc/coat-check');
            if (res && res.success) {
                this.data.coatCheck = res.data || [];
                return this.renderTable(this.data.coatCheck, [
                    { key: 'ticket_number', label: 'Ticket #' },
                    { key: 'item_description', label: 'Item' },
                    { key: 'check_in_time', label: 'Check In' },
                    { key: 'status', label: 'Status' }
                ]);
            }
        } catch (e) { console.error('Coat check load error:', e); }
        return '<p class="no-data">No data available</p>';
    }

    async loadEquipmentPage() {
        try {
            const res = await this.api.request('/misc/equipment');
            if (res && res.success) {
                this.data.equipment = res.data || [];
                return this.renderTable(this.data.equipment, [
                    { key: 'equipment_name', label: 'Name' },
                    { key: 'equipment_type', label: 'Type' },
                    { key: 'serial_number', label: 'Serial' },
                    { key: 'status', label: 'Status' }
                ]);
            }
        } catch (e) { console.error('Equipment load error:', e); }
        return '<p class="no-data">No data available</p>';
    }

    async loadWinePage() {
        try {
            const res = await this.api.request('/misc/wines');
            if (res && res.success) {
                this.data.wines = res.data || [];
                return this.renderTable(this.data.wines, [
                    { key: 'wine_name', label: 'Name' },
                    { key: 'wine_type', label: 'Type' },
                    { key: 'vintage', label: 'Vintage' },
                    { key: 'region', label: 'Region' },
                    { key: 'price', label: 'Price' }
                ]);
            }
        } catch (e) { console.error('Wine load error:', e); }
        return '<p class="no-data">No data available</p>';
    }

    // ==================== HELPERS ====================

    formatPrice(val) {
        return new Intl.NumberFormat('id-ID').format(val || 0);
    }

    renderTable(data, columns) {
        if (!data || data.length === 0) {
            return '<p class="no-data">No records found</p>';
        }
        let html = '<table class="data-table"><thead><tr>';
        columns.forEach(c => { html += `<th>${c.label}</th>`; });
        html += '</tr></thead><tbody>';
        data.forEach(row => {
            html += '<tr>';
            columns.forEach(c => {
                let val = row[c.key] ?? '';
                if (typeof val === 'boolean') val = val ? 'Yes' : 'No';
                html += `<td>${val}</td>`;
            });
            html += '</tr>';
        });
        html += '</tbody></table>';
        return html;
    }
}

if (typeof window !== 'undefined') {
    window.advancedFeaturesManager = new AdvancedFeaturesManager();
}
