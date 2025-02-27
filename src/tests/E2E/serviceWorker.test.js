// serviceWorker.test.js
const puppeteer = require('puppeteer');

describe('Service Worker Tests', () => {
    let browser;
    let page;

    beforeAll(async () => {
        browser = await puppeteer.launch({
            headless: true,
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--ignore-certificate-errors', // 自己署名証明書のエラーを無視
            ]
        });
        page = await browser.newPage();

        // サービスワーカーのログをキャプチャ
        page.on('console', msg => {
            console.log('PAGE LOG:', msg.text());
        });
    });

    afterAll(async () => {
        await browser.close();
    });

    test('対象URLにアクセスして200が返るか確認する', async () => {
        const urls = ['/chat', '/css/style.css', '/manifest.json'];
        for (const path of urls) {
            const response = await page.goto(`https://localhost:8080${path}`, { waitUntil: 'networkidle0' });
            console.log(`Accessing ${path}: status ${response.status()}`);
            expect(response.status()).toBe(200);
        }
    });

    test('サービスワーカーがインストールされ、ページを制御しているか確認する', async () => {
        // ルートページにアクセス（サービスワーカー登録の対象）
        await page.goto('https://localhost:8080', { waitUntil: 'networkidle0' });
        // サービスワーカーがコントローラーとして設定されるまで待つ
        await page.waitForFunction(() => navigator.serviceWorker.controller !== null, { timeout: 5000 });
        const registration = await page.evaluate(() => navigator.serviceWorker.getRegistration());
        expect(registration).not.toBeNull();
        console.log('Service Worker Registration:', registration);
    });

    test('キャッシュ時の fetch エラー発生時に詳細ログを出力する', async () => {
        // リクエストをインターセプトして、存在しないリソースへのアクセスをシミュレーション
        await page.setRequestInterception(true);
        page.on('request', request => {
            if (request.url().includes('/non-existent-resource')) {
                console.log(`Simulating error for: ${request.url()}`);
                request.abort();
            } else {
                request.continue();
            }
        });

        // 存在しないリソースへのアクセス
        let errorOccurred = false;
        try {
            await page.goto('https://localhost:8080/non-existent-resource', { waitUntil: 'networkidle0' });
        } catch (error) {
            console.log('Expected fetch error caught:', error.message);
            errorOccurred = true;
        }
        expect(errorOccurred).toBe(true);

        // インターセプションを解除
        await page.setRequestInterception(false);
    });
});
