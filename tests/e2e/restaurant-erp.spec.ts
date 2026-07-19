import { test, expect } from '@playwright/test';

test.describe('RESTAURANT ERP System Tests', () => {
  test.beforeAll(async () => {
    // Set DISPLAY to use HDMI-0
    process.env.DISPLAY = ':0';
  });

  test('Dashboard loads correctly', async ({ page }) => {
    await page.goto('http://localhost/restauran/FRONTEND/dashboard/index.html');
    
    // Wait for page to load
    await page.waitForLoadState('networkidle');
    
    // Check if dashboard elements are present
    await expect(page.locator('body')).toBeVisible();
    
    // Check for console errors
    const errors: string[] = [];
    page.on('console', msg => {
      if (msg.type() === 'error') {
        errors.push(msg.text());
      }
    });
    
    // Screenshot for visual verification
    await page.screenshot({ path: 'test-results/dashboard-load.png' });
    
    if (errors.length > 0) {
      console.error('Console errors found:', errors);
    }
    
    expect(errors.length).toBe(0);
  });

  test('Consumer app loads correctly', async ({ page }) => {
    await page.goto('http://localhost/restauran/FRONTEND/consumer/index.html');
    
    await page.waitForLoadState('networkidle');
    
    // Check if consumer app elements are present
    await expect(page.locator('body')).toBeVisible();
    
    // Monitor console for errors
    const errors: string[] = [];
    page.on('console', msg => {
      if (msg.type() === 'error') {
        errors.push(msg.text());
      }
    });
    
    await page.screenshot({ path: 'test-results/consumer-app-load.png' });
    
    if (errors.length > 0) {
      console.error('Console errors found:', errors);
    }
    
    expect(errors.length).toBe(0);
  });

  test('API endpoints are accessible', async ({ request }) => {
    // Test login endpoint (POST)
    const loginResponse = await request.post('http://localhost/restauran/api/v1/auth/login', {
      data: { username: 'admin', password: 'admin123' },
      headers: { 'Content-Type': 'application/json' }
    });
    expect(loginResponse.ok()).toBeTruthy();
    const loginData = await loginResponse.json();
    const token = loginData.data?.access_token || loginData.token;
    expect(token).toBeTruthy();
    
    // Test an authenticated endpoint
    const miscResponse = await request.get('http://localhost/restauran/api/v1/misc/coat-check', {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    expect(miscResponse.ok()).toBeTruthy();
  });
});
