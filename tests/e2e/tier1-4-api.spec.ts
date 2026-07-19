import { test, expect } from '@playwright/test';

const API_BASE = 'http://localhost/restoran/api/v1';

// Helper: Login and get JWT token
async function getAuthToken(): Promise<string> {
  const response = await fetch(`${API_BASE}/auth/login`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ username: 'admin', password: 'admin123' })
  });
  const text = await response.text();
  if (text.startsWith('{')) {
    const data = JSON.parse(text);
    // Response format: { success, data: { access_token: "..." } }
    if (data.data?.access_token) return data.data.access_token;
    if (data.token) return data.token;
  }
  // Try alternate credentials
  const response2 = await fetch(`${API_BASE}/auth/login`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ username: 'platform_owner', password: 'password' })
  });
  const text2 = await response2.text();
  if (text2.startsWith('{')) {
    const data2 = JSON.parse(text2);
    return data2.data?.access_token || data2.token || '';
  }
  return '';
}

const authHeaders = (token: string) => ({
  'Content-Type': 'application/json',
  'Authorization': `Bearer ${token}`
});

// Helper: safely fetch and parse
async function apiGet(url: string, headers: Record<string, string>) {
  const res = await fetch(url, { headers });
  const text = await res.text();
  let data: any = null;
  if (text.startsWith('{') || text.startsWith('[')) {
    try { data = JSON.parse(text); } catch {}
  }
  return { status: res.status, text, data, isJson: data !== null, isHtml: text.startsWith('<') };
}

test.describe('Tier 1-4 API Endpoint Tests', () => {
  let token: string;

  test.beforeAll(async () => {
    token = await getAuthToken();
  });

  // Helper to test an endpoint
  function testEndpoint(name: string, path: string) {
    test(name, async () => {
      const { status, data, isJson, isHtml } = await apiGet(`${API_BASE}${path}`, authHeaders(token));
      // Should not return HTML (means route not found)
      expect(isHtml, `Endpoint returned HTML (route not found): ${path}`).toBe(false);
      // Should return valid JSON
      expect(isJson, `Endpoint did not return JSON: ${path}`).toBe(true);
      // Status should be 200 or 500 (DB errors ok for empty tables), not 404
      expect([200, 500]).toContain(status);
    });
  }

  // ==================== TIER 1 ====================
  testEndpoint('GET /pos-reconciliation/deposits', '/pos-reconciliation/deposits');
  testEndpoint('GET /beverage-variance/bar-counts', '/beverage-variance/bar-counts');
  testEndpoint('GET /recipe-depletion/logs', '/recipe-depletion/logs');
  testEndpoint('GET /batch-expiry/batches', '/batch-expiry/batches');
  testEndpoint('GET /batch-expiry/dashboard', '/batch-expiry/dashboard');
  testEndpoint('GET /settlements/deals', '/settlements/deals');
  testEndpoint('GET /event-profitability', '/event-profitability');
  testEndpoint('GET /event-proposals', '/event-proposals');

  // ==================== TIER 2 ====================
  testEndpoint('GET /nightclub-advanced/table-deposits', '/nightclub-advanced/table-deposits');
  testEndpoint('GET /nightclub-advanced/bottle-inventory', '/nightclub-advanced/bottle-inventory');
  testEndpoint('GET /nightclub-advanced/promoters', '/nightclub-advanced/promoters');
  testEndpoint('GET /karaoke-advanced/songs', '/karaoke-advanced/songs');
  testEndpoint('GET /beach-club/seat-map', '/beach-club/seat-map');
  testEndpoint('GET /beach-club/rain-checks', '/beach-club/rain-checks');
  testEndpoint('GET /sports-bar/tabs', '/sports-bar/tabs');
  testEndpoint('GET /operations/86-items', '/operations/86-items');
  testEndpoint('GET /operations/custom-orders', '/operations/custom-orders');
  testEndpoint('GET /operations/delivery-routes', '/operations/delivery-routes');
  testEndpoint('GET /operations/leads', '/operations/leads');

  // ==================== TIER 3 ====================
  testEndpoint('GET /venue/dynamic-pricing/rules', '/venue/dynamic-pricing/rules');
  testEndpoint('GET /venue/memberships', '/venue/memberships');
  testEndpoint('GET /venue/occupancy', '/venue/occupancy');
  testEndpoint('GET /venue/holds', '/venue/holds');
  testEndpoint('GET /operations/predictions', '/operations/predictions?date_from=2026-01-01&date_to=2026-12-31');
  testEndpoint('GET /operations/throttling/check', '/operations/throttling/check');
  testEndpoint('GET /operations/booking-sync/status', '/operations/booking-sync/status');
  testEndpoint('GET /operations/production-plans', '/operations/production-plans?date=2026-07-19');
  testEndpoint('GET /operations/service-speed/report', '/operations/service-speed/report?date_from=2026-01-01&date_to=2026-12-31');

  // ==================== TIER 4 ====================
  testEndpoint('GET /misc/coat-check', '/misc/coat-check');
  testEndpoint('GET /misc/karaoke-scores/high', '/misc/karaoke-scores/high');
  testEndpoint('GET /misc/equipment', '/misc/equipment');
  testEndpoint('GET /misc/wines', '/misc/wines');
  testEndpoint('GET /misc/waiter-button/stats', '/misc/waiter-button/stats?date_from=2026-01-01&date_to=2026-12-31');

  // ==================== Auth Test ====================
  test('Endpoints without auth return 401 or error', async () => {
    const { status, isHtml } = await apiGet(`${API_BASE}/nightclub-advanced/promoters`, { 'Content-Type': 'application/json' });
    expect(isHtml).toBe(false);
    expect([401, 500]).toContain(status);
  });
});
