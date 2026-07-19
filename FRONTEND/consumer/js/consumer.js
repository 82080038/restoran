/**
 * Food & Beverages Management System - Consumer App API Integration
 * 
 * This file handles all API integration between the consumer app
 * and the F&B Management System backend
 * 
 * @version 1.0.0
 */

// API Configuration
const API_CONFIG = {
    BASE_URL: '/api/v1',
    TIMEOUT: 30000,
    RETRY_ATTEMPTS: 3
};

// API Client Class
class RestaurantAPIClient {
    constructor(config = API_CONFIG) {
        this.config = config;
        this.token = localStorage.getItem('auth_token') || null;
        this.tenantId = localStorage.getItem('tenant_id') || null;
        this.branchId = localStorage.getItem('branch_id') || null;
    }

    /**
     * Make HTTP request with authentication
     */
    async request(endpoint, options = {}) {
        const url = `${this.config.BASE_URL}${endpoint}`;
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            timeout: this.config.TIMEOUT
        };

        // Add authentication token if available
        if (this.token) {
            defaultOptions.headers['Authorization'] = `Bearer ${this.token}`;
        }

        // Add tenant/branch context if available
        if (this.tenantId) {
            defaultOptions.headers['X-Tenant-ID'] = this.tenantId;
        }
        if (this.branchId) {
            defaultOptions.headers['X-Branch-ID'] = this.branchId;
        }

        const finalOptions = { ...defaultOptions, ...options };

        try {
            const response = await fetch(url, finalOptions);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            return data;
        } catch (error) {
            console.error('API Request failed:', error);
            throw error;
        }
    }

    /**
     * Set authentication token
     */
    setToken(token) {
        this.token = token;
        localStorage.setItem('auth_token', token);
    }

    /**
     * Clear authentication token
     */
    clearToken() {
        this.token = null;
        localStorage.removeItem('auth_token');
    }

    /**
     * Set tenant context
     */
    setTenantContext(tenantId, branchId) {
        this.tenantId = tenantId;
        this.branchId = branchId;
        localStorage.setItem('tenant_id', tenantId);
        localStorage.setItem('branch_id', branchId);
    }

    /**
     * Authentication Methods
     */
    async login(email, password) {
        return this.request('/auth/login', {
            method: 'POST',
            body: JSON.stringify({ email, password })
        });
    }

    async register(userData) {
        return this.request('/auth/register', {
            method: 'POST',
            body: JSON.stringify(userData)
        });
    }

    async logout() {
        const result = await this.request('/auth/logout', {
            method: 'POST'
        });
        this.clearToken();
        return result;
    }

    /**
     * Restaurant Methods
     */
    async getRestaurants(filters = {}) {
        const params = new URLSearchParams(filters);
        return this.request(`/restaurants?${params}`);
    }

    async getRestaurantById(restaurantId) {
        return this.request(`/restaurants/${restaurantId}`);
    }

    async searchRestaurants(query, filters = {}) {
        const params = new URLSearchParams({ query, ...filters });
        return this.request(`/restaurants/search?${params}`);
    }

    async getNearbyRestaurants(latitude, longitude, radius = 5) {
        return this.request(`/restaurants/nearby?lat=${latitude}&lng=${longitude}&radius=${radius}`);
    }

    /**
     * Menu Methods
     */
    async getMenu(restaurantId) {
        return this.request(`/menu/restaurant/${restaurantId}`);
    }

    async getMenuItem(itemId) {
        return this.request(`/menu/items/${itemId}`);
    }

    async searchMenuItems(query, restaurantId) {
        return this.request(`/menu/search?query=${query}&restaurant_id=${restaurantId}`);
    }

    /**
     * Order Methods
     */
    async createOrder(orderData) {
        return this.request('/orders', {
            method: 'POST',
            body: JSON.stringify(orderData)
        });
    }

    async getOrder(orderId) {
        return this.request(`/orders/${orderId}`);
    }

    async getUserOrders() {
        return this.request('/orders/my-orders');
    }

    async updateOrder(orderId, updateData) {
        return this.request(`/orders/${orderId}`, {
            method: 'PUT',
            body: JSON.stringify(updateData)
        });
    }

    async cancelOrder(orderId) {
        return this.request(`/orders/${orderId}/cancel`, {
            method: 'POST'
        });
    }

    /**
     * Reservation Methods
     */
    async createReservation(reservationData) {
        return this.request('/reservations', {
            method: 'POST',
            body: JSON.stringify(reservationData)
        });
    }

    async getReservation(reservationId) {
        return this.request(`/reservations/${reservationId}`);
    }

    async getUserReservations() {
        return this.request('/reservations/my-reservations');
    }

    async updateReservation(reservationId, updateData) {
        return this.request(`/reservations/${reservationId}`, {
            method: 'PUT',
            body: JSON.stringify(updateData)
        });
    }

    async cancelReservation(reservationId) {
        return this.request(`/reservations/${reservationId}/cancel`, {
            method: 'POST'
        });
    }

    /**
     * Cart Methods
     */
    async getCart() {
        return this.request('/cart');
    }

    async addToCart(itemData) {
        return this.request('/cart/items', {
            method: 'POST',
            body: JSON.stringify(itemData)
        });
    }

    async updateCartItem(itemId, quantity) {
        return this.request(`/cart/items/${itemId}`, {
            method: 'PUT',
            body: JSON.stringify({ quantity })
        });
    }

    async removeFromCart(itemId) {
        return this.request(`/cart/items/${itemId}`, {
            method: 'DELETE'
        });
    }

    async clearCart() {
        return this.request('/cart/clear', {
            method: 'POST'
        });
    }

    /**
     * Payment Methods
     */
    async createPayment(paymentData) {
        return this.request('/payments', {
            method: 'POST',
            body: JSON.stringify(paymentData)
        });
    }

    async getPayment(paymentId) {
        return this.request(`/payments/${paymentId}`);
    }

    /**
     * Customer Methods
     */
    async getProfile() {
        return this.request('/customers/profile');
    }

    async updateProfile(profileData) {
        return this.request('/customers/profile', {
            method: 'PUT',
            body: JSON.stringify(profileData)
        });
    }

    /**
     * Loyalty Methods
     */
    async getLoyaltyPoints() {
        return this.request('/loyalty/points');
    }

    async getLoyaltyRewards() {
        return this.request('/loyalty/rewards');
    }

    async redeemReward(rewardId) {
        return this.request(`/loyalty/rewards/${rewardId}/redeem`, {
            method: 'POST'
        });
    }

    /**
     * Review Methods
     */
    async createReview(reviewData) {
        return this.request('/reviews', {
            method: 'POST',
            body: JSON.stringify(reviewData)
        });
    }

    async getRestaurantReviews(restaurantId) {
        return this.request(`/reviews/restaurant/${restaurantId}`);
    }

    /**
     * Favorite Methods
     */
    async getFavorites() {
        return this.request('/favorites');
    }

    async addFavorite(restaurantId) {
        return this.request('/favorites', {
            method: 'POST',
            body: JSON.stringify({ restaurant_id: restaurantId })
        });
    }

    async removeFavorite(restaurantId) {
        return this.request(`/favorites/${restaurantId}`, {
            method: 'DELETE'
        });
    }

    /**
     * Delivery Methods
     */
    async getDeliveryStatus(orderId) {
        return this.request(`/delivery/status/${orderId}`);
    }

    async getDeliveryAddresses() {
        return this.request('/delivery/addresses');
    }

    async addDeliveryAddress(addressData) {
        return this.request('/delivery/addresses', {
            method: 'POST',
            body: JSON.stringify(addressData)
        });
    }
}

// Initialize API Client
const apiClient = new RestaurantAPIClient();

// Consumer App State Management
class ConsumerAppState {
    constructor() {
        this.state = {
            user: null,
            currentRestaurant: null,
            cart: [],
            reservations: [],
            orders: [],
            favorites: [],
            loyalty: null
        };
        this.listeners = [];
    }

    setState(newState) {
        this.state = { ...this.state, ...newState };
        this.notifyListeners();
    }

    getState() {
        return this.state;
    }

    subscribe(listener) {
        this.listeners.push(listener);
        return () => {
            this.listeners = this.listeners.filter(l => l !== listener);
        };
    }

    notifyListeners() {
        this.listeners.forEach(listener => listener(this.state));
    }
}

// Initialize App State
const appState = new ConsumerAppState();

// Restaurant UI Controller
class RestaurantUIController {
    constructor(apiClient, appState) {
        this.api = apiClient;
        this.state = appState;
        this.init();
    }

    async init() {
        this.loadFeaturedRestaurants();
        this.loadNearbyRestaurants();
        this.loadCuisines();
        this.setupEventListeners();
        this.checkAuthStatus();
    }

    async loadFeaturedRestaurants() {
        try {
            const response = await this.api.getRestaurants({ featured: true });
            this.renderFeaturedRestaurants(response.restaurants || []);
        } catch (error) {
            console.error('Failed to load featured restaurants:', error);
            this.renderFeaturedRestaurants([]);
        }
    }

    async loadNearbyRestaurants() {
        try {
            // Get user location
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    async (position) => {
                        const { latitude, longitude } = position.coords;
                        const response = await this.api.getNearbyRestaurants(latitude, longitude);
                        this.renderNearbyRestaurants(response.restaurants || []);
                    },
                    (error) => {
                        console.error('Geolocation error:', error);
                        // Load default nearby restaurants
                        this.loadDefaultNearbyRestaurants();
                    }
                );
            } else {
                this.loadDefaultNearbyRestaurants();
            }
        } catch (error) {
            console.error('Failed to load nearby restaurants:', error);
            this.renderNearbyRestaurants([]);
        }
    }

    async loadDefaultNearbyRestaurants() {
        try {
            const response = await this.api.getRestaurants({ limit: 10 });
            this.renderNearbyRestaurants(response.restaurants || []);
        } catch (error) {
            this.renderNearbyRestaurants([]);
        }
    }

    async loadCuisines() {
        try {
            const response = await this.api.getRestaurants();
            const cuisines = this.extractCuisines(response.restaurants || []);
            this.renderCuisines(cuisines);
        } catch (error) {
            console.error('Failed to load cuisines:', error);
            this.renderCuisines([]);
        }
    }

    extractCuisines(restaurants) {
        const cuisineMap = new Map();
        restaurants.forEach(restaurant => {
            if (restaurant.cuisine_type) {
                cuisineMap.set(restaurant.cuisine_type, {
                    name: restaurant.cuisine_type,
                    count: (cuisineMap.get(restaurant.cuisine_type)?.count || 0) + 1
                });
            }
        });
        return Array.from(cuisineMap.values());
    }

    renderFeaturedRestaurants(restaurants) {
        const container = document.getElementById('featuredRestaurants');
        if (!container) return;

        if (restaurants.length === 0) {
            container.innerHTML = '<p class="no-data">No featured restaurants available</p>';
            return;
        }

        container.innerHTML = restaurants.map(restaurant => `
            <div class="restaurant-card" data-restaurant-id="${restaurant.restaurant_id}">
                <img src="${restaurant.image_url || '/images/placeholder-restaurant.jpg'}" 
                     alt="${restaurant.name}" class="restaurant-image">
                <div class="restaurant-info">
                    <h4 class="restaurant-name">${restaurant.name}</h4>
                    <p class="restaurant-cuisine">${restaurant.cuisine_type || 'Various'}</p>
                    <div class="restaurant-meta">
                        <span class="rating">⭐ ${restaurant.rating || '4.0'}</span>
                        <span class="delivery-time">🕐 ${restaurant.delivery_time || '30-45 min'}</span>
                    </div>
                    <button class="view-menu-btn" data-restaurant-id="${restaurant.restaurant_id}">
                        View Menu
                    </button>
                </div>
            </div>
        `).join('');

        // Add click handlers
        container.querySelectorAll('.view-menu-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const restaurantId = e.target.dataset.restaurantId;
                this.viewRestaurantMenu(restaurantId);
            });
        });
    }

    renderNearbyRestaurants(restaurants) {
        const container = document.getElementById('nearbyRestaurants');
        if (!container) return;

        if (restaurants.length === 0) {
            container.innerHTML = '<p class="no-data">No nearby restaurants found</p>';
            return;
        }

        container.innerHTML = restaurants.map(restaurant => `
            <div class="restaurant-list-item" data-restaurant-id="${restaurant.restaurant_id}">
                <div class="restaurant-thumbnail">
                    <img src="${restaurant.image_url || '/images/placeholder-restaurant.jpg'}" 
                         alt="${restaurant.name}">
                </div>
                <div class="restaurant-details">
                    <h4 class="restaurant-name">${restaurant.name}</h4>
                    <p class="restaurant-cuisine">${restaurant.cuisine_type || 'Various'}</p>
                    <p class="restaurant-address">${restaurant.address || 'Address not available'}</p>
                    <div class="restaurant-meta">
                        <span class="rating">⭐ ${restaurant.rating || '4.0'}</span>
                        <span class="distance">📍 ${restaurant.distance || '1.2 km'}</span>
                    </div>
                </div>
                <button class="order-btn" data-restaurant-id="${restaurant.restaurant_id}">
                    Order Now
                </button>
            </div>
        `).join('');

        // Add click handlers
        container.querySelectorAll('.order-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const restaurantId = e.target.dataset.restaurantId;
                this.viewRestaurantMenu(restaurantId);
            });
        });
    }

    renderCuisines(cuisines) {
        const container = document.getElementById('cuisineGrid');
        if (!container) return;

        if (cuisines.length === 0) {
            container.innerHTML = '<p class="no-data">No cuisines available</p>';
            return;
        }

        container.innerHTML = cuisines.map(cuisine => `
            <div class="cuisine-card" data-cuisine="${cuisine.name}">
                <div class="cuisine-icon">🍽️</div>
                <h4 class="cuisine-name">${cuisine.name}</h4>
                <p class="cuisine-count">${cuisine.count} restaurants</p>
            </div>
        `).join('');

        // Add click handlers
        container.querySelectorAll('.cuisine-card').forEach(card => {
            card.addEventListener('click', (e) => {
                const cuisine = e.currentTarget.dataset.cuisine;
                this.filterByCuisine(cuisine);
            });
        });
    }

    async viewRestaurantMenu(restaurantId) {
        try {
            const response = await this.api.getRestaurantById(restaurantId);
            const menuResponse = await this.api.getMenu(restaurantId);
            
            this.state.setState({
                currentRestaurant: response.restaurant,
                menuItems: menuResponse.menu_items || []
            });
            
            this.showMenuPage(response.restaurant, menuResponse.menu_items || []);
        } catch (error) {
            console.error('Failed to load restaurant menu:', error);
            alert('Failed to load menu. Please try again.');
        }
    }

    showMenuPage(restaurant, menuItems) {
        // Implementation for showing menu page
        console.log('Showing menu for:', restaurant.name);
        // This would navigate to a menu page or show a modal
    }

    async filterByCuisine(cuisine) {
        try {
            const response = await this.api.searchRestaurants('', { cuisine_type: cuisine });
            this.renderNearbyRestaurants(response.restaurants || []);
        } catch (error) {
            console.error('Failed to filter by cuisine:', error);
        }
    }

    setupEventListeners() {
        // Search functionality
        const searchInput = document.getElementById('homeSearch');
        if (searchInput) {
            searchInput.addEventListener('input', this.debounce((e) => {
                this.handleSearch(e.target.value);
            }, 300));
        }

        // Filter chips
        document.querySelectorAll('.filter-chip').forEach(chip => {
            chip.addEventListener('click', (e) => {
                document.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active'));
                e.target.classList.add('active');
                this.handleFilter(e.target.dataset.filter);
            });
        });

        // Sidebar toggle
        const menuBtn = document.getElementById('menuBtn');
        const closeSidebar = document.getElementById('closeSidebar');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');

        if (menuBtn && sidebar) {
            menuBtn.addEventListener('click', () => {
                sidebar.classList.add('active');
                overlay.classList.add('active');
            });
        }

        if (closeSidebar && sidebar) {
            closeSidebar.addEventListener('click', () => {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            });
        }

        if (overlay) {
            overlay.addEventListener('click', () => {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            });
        }
    }

    async handleSearch(query) {
        if (query.length < 2) {
            this.loadNearbyRestaurants();
            return;
        }

        try {
            const response = await this.api.searchRestaurants(query);
            this.renderNearbyRestaurants(response.restaurants || []);
        } catch (error) {
            console.error('Search failed:', error);
        }
    }

    async handleFilter(filter) {
        try {
            let filters = {};
            switch (filter) {
                case 'nearby':
                    this.loadNearbyRestaurants();
                    return;
                case 'top-rated':
                    filters.min_rating = 4.5;
                    break;
                case 'halal':
                    filters.halal = true;
                    break;
                case 'delivery':
                    filters.delivery_available = true;
                    break;
                default:
                    this.loadNearbyRestaurants();
                    return;
            }

            const response = await this.api.getRestaurants(filters);
            this.renderNearbyRestaurants(response.restaurants || []);
        } catch (error) {
            console.error('Filter failed:', error);
        }
    }

    async checkAuthStatus() {
        const token = localStorage.getItem('auth_token');
        if (token) {
            try {
                const response = await this.api.getProfile();
                this.state.setState({ user: response.customer });
                this.updateUserUI(response.customer);
            } catch (error) {
                console.error('Failed to get user profile:', error);
                this.api.clearToken();
            }
        }
    }

    updateUserUI(user) {
        const userInfo = document.getElementById('userInfo');
        if (userInfo && user) {
            userInfo.innerHTML = `
                <div class="user-avatar">${user.name.charAt(0).toUpperCase()}</div>
                <div class="user-details">
                    <p class="user-name">${user.name}</p>
                    <p class="user-role">${user.email}</p>
                </div>
            `;
        }
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
}

// Initialize UI Controller when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.restaurantUI = new RestaurantUIController(apiClient, appState);
});

// Export for use in other modules
window.RestaurantAPIClient = RestaurantAPIClient;
window.apiClient = apiClient;
window.appState = appState;
