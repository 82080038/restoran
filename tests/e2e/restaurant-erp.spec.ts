import { test, expect } from '@playwright/test';

test.describe('RESTAURANT ERP System Tests', () => {
  test.beforeAll(async () => {
    // Set DISPLAY to use HDMI-0
    process.env.DISPLAY = ':0';
  });

  test('Dashboard loads correctly', async ({ page }) => {
    await page.goto('http://localhost/restoran/FRONTEND/dashboard/index.html');
    
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
    await page.goto('http://localhost/restoran/FRONTEND/consumer/index.html');
    
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
    // Test authentication endpoint
    const authResponse = await request.get('http://localhost/restoran/BACKEND/public/api/v1/auth/health');
    expect(authResponse.ok()).toBeTruthy();
    
    // Test menu endpoint
    const menuResponse = await request.get('http://localhost/restoran/BACKEND/public/api/v1/menu/items?tenant_id=1');
    expect(menuResponse.ok()).toBeTruthy();
  });
});
