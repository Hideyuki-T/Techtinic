<?php

namespace Tests\Browser;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Laravel\Dusk\TestCase as BaseTestCase;
use Tests\CreatesApplication;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Prepare for Dusk test execution.
     *
     * @beforeClass
     */
    public static function prepare(): void
    {
        if (! static::runningInSail()) {
            static::startChromeDriver();
        }
    }

    /**
     * Override getChromeDriverBinary() to force the Chromedriver binary path.
     *
     * @return string
     */
    protected static function getChromeDriverBinary(): string
    {
        // 強制的に /usr/local/bin/chromedriver を返す
        return '/usr/local/bin/chromedriver';
    }

    /**
     * Create the RemoteWebDriver instance.
     */
    protected function driver()
    {
        $options = (new ChromeOptions)->addArguments([
            '--window-size=1920,1080',
            '--disable-gpu',
            '--headless=new',
            '--no-sandbox',                  // コンテナ内では必須な場合が多い
            '--disable-dev-shm-usage',       // /dev/shm の容量不足対策
            '--user-data-dir=' . sys_get_temp_dir() . '/chrome-user-data-' . uniqid(),
        ]);

        return RemoteWebDriver::create(
            'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(ChromeOptions::CAPABILITY, $options)
        );
    }


    /**
     * Determine whether the Dusk command has disabled headless mode.
     */
    protected function hasHeadlessDisabled(): bool
    {
        return isset($_SERVER['DUSK_HEADLESS_DISABLED']) || isset($_ENV['DUSK_HEADLESS_DISABLED']);
    }

    /**
     * Determine if the browser window should start maximized.
     */
    protected function shouldStartMaximized(): bool
    {
        return isset($_SERVER['DUSK_START_MAXIMIZED']) || isset($_ENV['DUSK_START_MAXIMIZED']);
    }
}
