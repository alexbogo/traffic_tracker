<?php

namespace App\Tests\Unit\Service;

use App\Service\DeviceDetectionService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DeviceDetectionServiceTest extends TestCase
{
    private DeviceDetectionService $service;

    protected function setUp(): void
    {
        $this->service = new DeviceDetectionService();
    }

    #[DataProvider('deviceUserAgentProvider')]
    public function testDetectDevice(string $userAgent, string $expectedDevice): void
    {
        $result = $this->service->detectDevice($userAgent);
        
        $this->assertSame($expectedDevice, $result);
    }

    public static function deviceUserAgentProvider(): array
    {
        return [
            'iPhone mobile' => [
                'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15',
                'mobile'
            ],
            'Android mobile' => [
                'Mozilla/5.0 (Linux; Android 11; SM-G991B) AppleWebKit/537.36',
                'mobile'
            ],
            'iPad tablet' => [
                'Mozilla/5.0 (iPad; CPU OS 14_6 like Mac OS X) AppleWebKit/605.1.15',
                'tablet'
            ],
            'Generic tablet' => [
                'Mozilla/5.0 (Linux; U; en-us; KFAPWI Build/JDQ39) AppleWebKit/535.19 (KHTML, like Gecko) Silk/3.13 Safari/535.19 Silk-Accelerated=true tablet',
                'tablet'
            ],
            'Desktop Chrome' => [
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'desktop'
            ],
            'Desktop Firefox' => [
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0',
                'desktop'
            ],
            'MacOS Safari' => [
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15',
                'desktop'
            ],
        ];
    }

    #[DataProvider('browserUserAgentProvider')]
    public function testDetectBrowser(string $userAgent, string $expectedBrowser): void
    {
        $result = $this->service->detectBrowser($userAgent);
        
        $this->assertSame($expectedBrowser, $result);
    }

    public static function browserUserAgentProvider(): array
    {
        return [
            'Chrome' => [
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'Chrome'
            ],
            'Firefox' => [
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0',
                'Firefox'
            ],
            'Safari (not Chrome)' => [
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15',
                'Safari'
            ],
            'Edge (contains Chrome string)' => [
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36 Edg/91.0.864.59',
                'Chrome'
            ],
            'Unknown browser' => [
                'SomeCustomBrowser/1.0',
                'Unknown'
            ],
        ];
    }

    #[DataProvider('botUserAgentProvider')]
    public function testIsBot(string $userAgent, bool $expectedIsBot): void
    {
        $result = $this->service->isBot($userAgent);
        
        $this->assertSame($expectedIsBot, $result);
    }

    public static function botUserAgentProvider(): array
    {
        return [
            'Googlebot' => [
                'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
                true
            ],
            'Bingbot' => [
                'Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)',
                true
            ],
            'Generic spider' => [
                'Mozilla/5.0 (compatible; spider/1.0)',
                true
            ],
            'Crawler' => [
                'Mozilla/5.0 (compatible; MJ12bot/v1.4.8; http://mj12bot.com/) crawler',
                true
            ],
            'Headless Chrome' => [
                'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/91.0.4472.124 Safari/537.36',
                true
            ],
            'cURL' => [
                'curl/7.68.0',
                true
            ],
            'wget' => [
                'Wget/1.20.3 (linux-gnu)',
                true
            ],
            'Python requests' => [
                'python-requests/2.25.1',
                true
            ],
            'Scraper' => [
                'MyCustomScraper/1.0',
                true
            ],
            'Regular Chrome user' => [
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                false
            ],
            'Regular Firefox user' => [
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0',
                false
            ],
            'Regular Safari user' => [
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15',
                false
            ],
        ];
    }
}
