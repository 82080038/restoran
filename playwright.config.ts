import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: './tests/e2e',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: process.env.CI ? 1 : undefined,
  reporter: 'html',
  use: {
    baseURL: 'http://localhost/restoran/BACKEND/public/api/v1',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
    headless: false, // Run headed on HDMI-0
    viewport: { width: 1920, height: 1080 },
  },

  projects: [
    {
      name: 'chromium-hdmi0',
      use: {
        ...devices['Desktop Chrome'],
        launchOptions: {
          args: [
            '--display=:0',
            '--start-maximized'
          ]
        }
      },
    },
  ],
});
