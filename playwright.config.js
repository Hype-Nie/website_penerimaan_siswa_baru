const { defineConfig, devices } = require('@playwright/test');

const host = process.env.BLACKBOX_HOST || '127.0.0.1';
const port = process.env.BLACKBOX_PORT || '8010';
const baseURL = process.env.BLACKBOX_BASE_URL || `http://${host}:${port}`;

module.exports = defineConfig({
  testDir: './tests/blackbox',
  fullyParallel: false,
  workers: 1,
  timeout: 60 * 1000,
  expect: {
    timeout: 10 * 1000,
  },
  reporter: [
    ['list'],
    ['html', { outputFolder: 'playwright-report', open: 'never' }],
  ],
  use: {
    ...devices['Desktop Chrome'],
    baseURL,
    actionTimeout: 15 * 1000,
    navigationTimeout: 30 * 1000,
    screenshot: 'only-on-failure',
    trace: 'retain-on-failure',
    video: 'retain-on-failure',
  },
  webServer: {
    command: 'node tests/scripts/start-blackbox-server.js',
    url: baseURL,
    reuseExistingServer: !process.env.CI,
    timeout: 120 * 1000,
  },
});
