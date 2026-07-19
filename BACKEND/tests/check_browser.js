const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  
  const pages = [
    { url: 'http://localhost:8000/consumer/index.html', name: 'Consumer App' },
    { url: 'http://localhost:8000/dashboard/', name: 'Dashboard' },
    { url: 'http://localhost:8000/kiosk/index.html', name: 'Kiosk' },
    { url: 'http://localhost:8000/mobile/index.html', name: 'Mobile' },
    { url: 'http://localhost:8000/landing.html', name: 'Landing' },
    { url: 'http://localhost:8000/login.html', name: 'Login' },
    { url: 'http://localhost:8000/bill-split/index.html', name: 'Bill Split' },
    { url: 'http://localhost:8000/floor-plan/index.html', name: 'Floor Plan' },
    { url: 'http://localhost:8000/floor-status/index.html', name: 'Floor Status' },
    { url: 'http://localhost:8000/qr-order/index.html', name: 'QR Order' },
  ];

  for (const pageInfo of pages) {
    const context = await browser.newContext();
    const page = await context.newPage();
    const errors = [];
    const consoleMsgs = [];
    const failedRequests = [];

    page.on('console', msg => {
      if (msg.type() === 'error' || msg.type() === 'warning') {
        consoleMsgs.push(`[${msg.type()}] ${msg.text()}`);
      }
    });
    page.on('pageerror', err => errors.push(err.message));
    page.on('requestfailed', req => {
      failedRequests.push(`${req.method()} ${req.url()} - ${req.failure()?.errorText || 'failed'}`);
    });

    try {
      await page.goto(pageInfo.url, { waitUntil: 'networkidle', timeout: 10000 });
      await page.waitForTimeout(2000);
    } catch (e) {
      console.log(`\n=== ${pageInfo.name} (${pageInfo.url}) ===`);
      console.log(`  NAVIGATION ERROR: ${e.message}`);
      await context.close();
      continue;
    }

    console.log(`\n=== ${pageInfo.name} (${pageInfo.url}) ===`);
    
    if (errors.length > 0) {
      console.log('  JS ERRORS:');
      errors.forEach(e => console.log(`    - ${e}`));
    }
    
    if (consoleMsgs.length > 0) {
      console.log('  CONSOLE WARNINGS/ERRORS:');
      consoleMsgs.forEach(m => console.log(`    - ${m}`));
    }
    
    if (failedRequests.length > 0) {
      console.log('  FAILED REQUESTS:');
      failedRequests.forEach(r => console.log(`    - ${r}`));
    }
    
    if (errors.length === 0 && consoleMsgs.length === 0 && failedRequests.length === 0) {
      console.log('  No issues found');
    }

    await context.close();
  }

  await browser.close();
})();
