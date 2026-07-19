/**
 * Nightclub Manager - Handles nightclub/discotheque module UI
 */
class NightclubManager {
    constructor() {
        this.apiBase = (window.API_BASE || '/restoran/BACKEND/public/api/v1') + '/nightclub';
        this.token = localStorage.getItem('auth_token') || localStorage.getItem('token') || '';
    }

    async apiCall(endpoint, method = 'GET', body = null) {
        const options = {
            method,
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + this.token
            }
        };
        if (body) options.body = JSON.stringify(body);
        const res = await fetch(this.apiBase + endpoint, options);
        return res.json();
    }

    async init() {
        await this.loadDashboardStats();
        await this.loadEvents();
        await this.loadGuestList();
        await this.loadTableReservations();
        await this.loadRevenueReport();
        this.bindEvents();
    }

    formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount || 0);
    }

    async loadRevenueReport() {
        try {
            const res = await this.apiCall('/revenue-report');
            const container = document.getElementById('ncRevenueReport');
            if (!container) return;
            if (res.success && res.data) {
                const d = res.data;
                const s = d.summary || {};
                const tickets = d.entrance_tickets || {};
                const bottle = d.bottle_service || {};
                const tables = d.table_reservations || {};
                const guests = d.guest_list || {};

                let html = '<div class="stats-grid" style="margin-bottom:15px">';
                html += `<div class="stat-card"><div class="stat-icon">💰</div><div class="stat-info"><h4>${this.formatCurrency(s.total_revenue)}</h4><p>Total Revenue (Paid)</p></div></div>`;
                html += `<div class="stat-card"><div class="stat-icon">🎟️</div><div class="stat-info"><h4>${tickets.total_tickets || 0}</h4><p>Tickets Sold</p></div></div>`;
                html += `<div class="stat-card"><div class="stat-icon">🍾</div><div class="stat-info"><h4>${bottle.total_reservations || 0}</h4><p>Bottle Reservations</p></div></div>`;
                html += `<div class="stat-card"><div class="stat-icon">🪑</div><div class="stat-info"><h4>${tables.total_reservations || 0}</h4><p>Table Reservations</p></div></div>`;
                html += '</div>';

                html += '<table style="width:100%;border-collapse:collapse;margin-top:10px">';
                html += '<tr><th style="text-align:left;padding:8px;border-bottom:2px solid #eee">Revenue Stream</th><th style="text-align:right;padding:8px;border-bottom:2px solid #eee">Total</th><th style="text-align:right;padding:8px;border-bottom:2px solid #eee">Paid</th><th style="text-align:right;padding:8px;border-bottom:2px solid #eee">Count</th></tr>';
                html += `<tr><td style="padding:8px;border-bottom:1px solid #eee">Entrance Tickets</td><td style="text-align:right;padding:8px;border-bottom:1px solid #eee">${this.formatCurrency(tickets.total_revenue)}</td><td style="text-align:right;padding:8px;border-bottom:1px solid #eee">${this.formatCurrency(tickets.paid_revenue)}</td><td style="text-align:right;padding:8px;border-bottom:1px solid #eee">${tickets.total_tickets || 0}</td></tr>`;
                html += `<tr><td style="padding:8px;border-bottom:1px solid #eee">Bottle Service</td><td style="text-align:right;padding:8px;border-bottom:1px solid #eee">${this.formatCurrency(bottle.total_revenue)}</td><td style="text-align:right;padding:8px;border-bottom:1px solid #eee">${this.formatCurrency(bottle.paid_revenue)}</td><td style="text-align:right;padding:8px;border-bottom:1px solid #eee">${bottle.total_reservations || 0}</td></tr>`;
                html += `<tr><td style="padding:8px;border-bottom:1px solid #eee">Table Min Spend</td><td style="text-align:right;padding:8px;border-bottom:1px solid #eee">${this.formatCurrency(tables.total_minimum_spend)}</td><td style="text-align:right;padding:8px;border-bottom:1px solid #eee">-</td><td style="text-align:right;padding:8px;border-bottom:1px solid #eee">${tables.total_reservations || 0}</td></tr>`;
                html += `<tr><td style="padding:8px;border-bottom:1px solid #eee">Guest List</td><td style="text-align:right;padding:8px;border-bottom:1px solid #eee">-</td><td style="text-align:right;padding:8px;border-bottom:1px solid #eee">-</td><td style="text-align:right;padding:8px;border-bottom:1px solid #eee">${guests.total_guests || 0}</td></tr>`;
                html += '</table>';

                if (d.per_event && d.per_event.length > 0) {
                    html += '<h4 style="margin-top:20px">Per-Event Breakdown</h4>';
                    html += '<table style="width:100%;border-collapse:collapse;margin-top:10px">';
                    html += '<tr><th style="text-align:left;padding:8px;border-bottom:2px solid #eee">Event</th><th style="text-align:left;padding:8px;border-bottom:2px solid #eee">Date</th><th style="text-align:right;padding:8px;border-bottom:2px solid #eee">Tickets</th><th style="text-align:right;padding:8px;border-bottom:2px solid #eee">Bottle Rev</th><th style="text-align:right;padding:8px;border-bottom:2px solid #eee">Guests</th></tr>';
                    d.per_event.forEach(e => {
                        html += `<tr><td style="padding:8px;border-bottom:1px solid #eee">${this.escape(e.event_name)}</td><td style="padding:8px;border-bottom:1px solid #eee">${e.event_date}</td><td style="text-align:right;padding:8px;border-bottom:1px solid #eee">${e.ticket_count}</td><td style="text-align:right;padding:8px;border-bottom:1px solid #eee">${this.formatCurrency(e.bottle_revenue)}</td><td style="text-align:right;padding:8px;border-bottom:1px solid #eee">${e.guest_count}</td></tr>`;
                    });
                    html += '</table>';
                }

                container.innerHTML = html;
            } else {
                container.innerHTML = '<p class="text-muted">No revenue data available</p>';
            }
        } catch (e) {
            console.error('Nightclub revenue report error:', e);
        }
    }

    async loadDashboardStats() {
        try {
            const res = await this.apiCall('/dashboard');
            if (res.success && res.data) {
                const d = res.data;
                document.getElementById('ncTotalEvents').textContent = d.events?.total_events || 0;
                document.getElementById('ncTotalTickets').textContent = d.tickets?.total_tickets || 0;
                document.getElementById('ncTotalGuests').textContent = d.guest_list?.total_guests || 0;
                document.getElementById('ncTotalBottle').textContent = d.bottle_service?.total_reservations || 0;
            }
        } catch (e) {
            console.error('Nightclub stats error:', e);
        }
    }

    async loadEvents() {
        try {
            const res = await this.apiCall('/events');
            const container = document.getElementById('ncEventsList');
            if (!container) return;
            if (res.success && res.data && res.data.length > 0) {
                container.innerHTML = res.data.slice(0, 6).map(e => `
                    <div class="report-card">
                        <h5>${this.escape(e.event_name)}</h5>
                        <p><strong>Date:</strong> ${e.event_date}</p>
                        <p><strong>Time:</strong> ${e.start_time} - ${e.end_time}</p>
                        ${e.dj_name ? `<p><strong>DJ:</strong> ${this.escape(e.dj_name)}</p>` : ''}
                        ${e.theme ? `<p><strong>Theme:</strong> ${this.escape(e.theme)}</p>` : ''}
                        <p><strong>Status:</strong> <span class="badge">${e.status}</span></p>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<p class="text-muted">No events scheduled</p>';
            }
        } catch (e) {
            console.error('Nightclub events error:', e);
        }
    }

    async loadGuestList() {
        try {
            const res = await this.apiCall('/guest-list');
            const container = document.getElementById('ncGuestList');
            if (!container) return;
            if (res.success && res.data && res.data.length > 0) {
                container.innerHTML = res.data.slice(0, 6).map(g => `
                    <div class="report-card">
                        <h5>${this.escape(g.guest_name)}</h5>
                        <p><strong>Party Size:</strong> ${g.party_size}</p>
                        <p><strong>Entry:</strong> ${g.entry_type}</p>
                        ${g.discount_percentage > 0 ? `<p><strong>Discount:</strong> ${g.discount_percentage}%</p>` : ''}
                        <p><strong>Check-in:</strong> ${g.check_in_status == 1 ? '✅ Checked In' : '⏳ Pending'}</p>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<p class="text-muted">No guests on the list</p>';
            }
        } catch (e) {
            console.error('Nightclub guest list error:', e);
        }
    }

    async loadTableReservations() {
        try {
            const res = await this.apiCall('/table-reservations');
            const container = document.getElementById('ncTableReservations');
            if (!container) return;
            if (res.success && res.data && res.data.length > 0) {
                container.innerHTML = res.data.slice(0, 6).map(r => `
                    <div class="report-card">
                        <h5>${this.escape(r.customer_name)}</h5>
                        <p><strong>Date:</strong> ${r.reservation_date}</p>
                        <p><strong>Party Size:</strong> ${r.party_size}</p>
                        ${r.table_type ? `<p><strong>Type:</strong> ${this.escape(r.table_type)}</p>` : ''}
                        ${r.minimum_spend > 0 ? `<p><strong>Min Spend:</strong> ${r.minimum_spend}</p>` : ''}
                        <p><strong>Status:</strong> <span class="badge">${r.status}</span></p>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<p class="text-muted">No table reservations</p>';
            }
        } catch (e) {
            console.error('Nightclub table reservations error:', e);
        }
    }

    bindEvents() {
        const newEventBtn = document.getElementById('ncNewEventBtn');
        if (newEventBtn) {
            newEventBtn.addEventListener('click', () => this.showNewEventModal());
        }
    }

    showNewEventModal() {
        const eventName = prompt('Event Name:');
        if (!eventName) return;
        const eventDate = prompt('Event Date (YYYY-MM-DD):');
        if (!eventDate) return;
        const djName = prompt('DJ Name (optional):') || '';
        const theme = prompt('Theme (optional):') || '';

        this.apiCall('/events', 'POST', {
            event_name: eventName,
            event_date: eventDate,
            dj_name: djName,
            theme: theme
        }).then(res => {
            if (res.success) {
                alert('Event created successfully!');
                this.loadEvents();
                this.loadDashboardStats();
            } else {
                alert('Failed: ' + (res.message || 'Unknown error'));
            }
        }).catch(e => alert('Error: ' + e.message));
    }

    escape(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
}

const nightclubManager = new NightclubManager();

document.addEventListener('DOMContentLoaded', () => {
    const navItems = document.querySelectorAll('.nav-item[data-page="nightclub"]');
    navItems.forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            nightclubManager.init();
        });
    });
});
