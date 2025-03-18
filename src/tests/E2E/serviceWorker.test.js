import puppeteer from 'puppeteer';

jest.setTimeout(30000); // テストタイムアウトを30秒に延長

let browser;
let page;

beforeAll(async () => {
    browser = await puppeteer.launch({
        headless: true,
        executablePath: '/usr/bin/google-chrome-stable',
        args: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-dev-shm-usage',
            '--ignore-certificate-errors'
        ]
    });
    page = await browser.newPage();

    // 対象のURLへアクセス
    await page.goto('https://techtinic-nginx', { waitUntil: 'networkidle2' });

    // サービスワーカーを登録し、ready 状態を待つ
    await page.evaluate(async () => {
        try {
            await navigator.serviceWorker.register('/service-worker.js', { scope: '/' });
            // 登録したサービスワーカーがコントローラーになるのを待つ
            await navigator.serviceWorker.ready;
        } catch (error) {
            console.error('Service Worker registration failed:', error);
        }
    });
});

afterAll(async () => {
    await browser.close();
});

test('Service Worker Registration Test', async () => {
    // サービスワーカーがページのコントローラーとして反映されるのを待つ
    await page.waitForFunction(() => navigator.serviceWorker.controller !== null, { timeout: 15000 });

    // 登録状態を取得
    const registrations = await page.evaluate(async () => {
        const regs = await navigator.serviceWorker.getRegistrations();
        return regs.map(reg => reg.scope);
    });

    console.log('Registrations:', registrations);
    expect(registrations.length).toBeGreaterThan(0);
});
