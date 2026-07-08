/**
 * Multi-Role Support
 * 
 * Handles display and switching of multiple user roles
 * 
 * @version 1.0.0
 * @date 2026-07-08
 */

class MultiRoleSupport {
    constructor() {
        this.currentUser = null;
        this.userRoles = [];
        this.currentRole = null;
    }

    /**
     * Initialize multi-role support
     */
    async initialize() {
        // Get current user from auth manager
        if (window.authManager) {
            this.currentUser = window.authManager.getCurrentUser();
        }
        
        if (this.currentUser) {
            await this.loadUserRoles();
            this.displayUserRoles();
            this.setupRoleSwitcher();
        }
    }

    /**
     * Load user roles from backend
     */
    async loadUserRoles() {
        try {
            const response = await fetch(`${API_BASE_URL}/users/${this.currentUser.user_id}/roles`);
            const data = await response.json();
            
            if (data.success && data.roles) {
                this.userRoles = data.roles;
                this.currentRole = data.current_role || (this.userRoles.length > 0 ? this.userRoles[0].role_name : null);
            } else {
                // Fallback to single role from user object
                this.userRoles = [{ role_name: this.currentUser.role_name }];
                this.currentRole = this.currentUser.role_name;
            }
        } catch (error) {
            console.error('Failed to load user roles:', error);
            // Fallback to single role
            this.userRoles = [{ role_name: this.currentUser.role_name }];
            this.currentRole = this.currentUser.role_name;
        }
    }

    /**
     * Display user roles in sidebar
     */
    displayUserRoles() {
        const userRolesContainer = document.getElementById('userRoles');
        if (!userRolesContainer) return;

        if (this.userRoles.length > 1) {
            // Display multiple roles as badges
            const rolesHtml = this.userRoles.map(role => 
                `<span class="role-badge ${role.role_name === this.currentRole ? 'active' : ''}">${role.role_name}</span>`
            ).join('');
            userRolesContainer.innerHTML = rolesHtml;
        } else {
            // Display single role as text
            userRolesContainer.innerHTML = `<span class="role-badge active">${this.userRoles[0].role_name}</span>`;
        }
    }

    /**
     * Setup role switcher dropdown
     */
    setupRoleSwitcher() {
        const roleSwitcherContainer = document.getElementById('roleSwitcherContainer');
        const roleSwitcher = document.getElementById('roleSwitcher');
        
        if (!roleSwitcherContainer || !roleSwitcher) return;

        // Only show switcher if user has multiple roles
        if (this.userRoles.length > 1) {
            roleSwitcherContainer.style.display = 'block';
            
            // Populate dropdown
            roleSwitcher.innerHTML = '';
            this.userRoles.forEach(role => {
                const option = document.createElement('option');
                option.value = role.role_name;
                option.textContent = role.role_name;
                option.selected = role.role_name === this.currentRole;
                roleSwitcher.appendChild(option);
            });

            // Add change event listener
            roleSwitcher.addEventListener('change', (e) => {
                this.switchRole(e.target.value);
            });
        }
    }

    /**
     * Switch to a different role
     * @param {string} newRole - New role name
     */
    async switchRole(newRole) {
        try {
            const response = await fetch(`${API_BASE_URL}/auth/switch-role`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ role: newRole })
            });

            const data = await response.json();

            if (data.success) {
                this.currentRole = newRole;
                
                // Update UI
                this.displayUserRoles();
                document.getElementById('roleSwitcher').value = newRole;
                
                // Update current user object
                if (this.currentUser) {
                    this.currentUser.current_role = newRole;
                }

                // Reload dashboard with new role permissions
                this.reloadDashboard();
                
                // Show success notification
                this.showNotification(`Switched to ${newRole} role`, 'success');
            } else {
                this.showNotification('Failed to switch role', 'error');
            }
        } catch (error) {
            console.error('Failed to switch role:', error);
            this.showNotification('Failed to switch role', 'error');
        }
    }

    /**
     * Reload dashboard with new role permissions
     */
    reloadDashboard() {
        // Reload page to apply new permissions
        window.location.reload();
    }

    /**
     * Show notification
     * @param {string} message - Notification message
     * @param {string} type - Notification type (success, error, warning)
     */
    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        
        // Add to page
        document.body.appendChild(notification);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    /**
     * Check if user has multiple roles
     * @returns {boolean}
     */
    hasMultipleRoles() {
        return this.userRoles.length > 1;
    }

    /**
     * Get current role
     * @returns {string|null}
     */
    getCurrentRole() {
        return this.currentRole;
    }

    /**
     * Get all user roles
     * @returns {Array}
     */
    getUserRoles() {
        return this.userRoles;
    }
}

// Initialize global instance
const multiRoleSupport = new MultiRoleSupport();

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    multiRoleSupport.initialize();
});
