<?php

namespace App\Tests\Unit\Service;

use App\Service\GeoIpService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class GeoIpServiceTest extends TestCase
{
    #[DataProvider('privateIpProvider')]
    public function testGetCountryFromIpReturnsNullForPrivateIps(string $privateIp): void
    {
        $mockHttpClient = new MockHttpClient();
        $service = new GeoIpService($mockHttpClient);

        $result = $service->getCountryFromIp($privateIp);

        $this->assertSame(['code' => null, 'name' => null], $result);
    }

    public static function privateIpProvider(): array
    {
        return [
            'localhost IPv4' => ['127.0.0.1'],
            'private class A' => ['10.0.0.1'],
            'private class B' => ['172.16.0.1'],
            'private class C' => ['192.168.1.1'],
            'localhost hostname' => ['localhost'],
        ];
    }

    public function testGetCountryFromIpReturnsCountryDataForPublicIp(): void
    {
        $mockResponse = new MockResponse(json_encode([
            'status' => 'success',
            'country' => 'United States',
            'countryCode' => 'US',
            'region' => 'CA',
            'city' => 'Mountain View',
        ]));

        $mockHttpClient = new MockHttpClient($mockResponse);
        $service = new GeoIpService($mockHttpClient);

        $result = $service->getCountryFromIp('8.8.8.8');

        $this->assertSame([
            'code' => 'US',
            'name' => 'United States'
        ], $result);
    }

    public function testGetCountryFromIpReturnsNullOnApiFailure(): void
    {
        $mockResponse = new MockResponse(json_encode([
            'status' => 'fail',
            'message' => 'invalid query',
        ]));

        $mockHttpClient = new MockHttpClient($mockResponse);
        $service = new GeoIpService($mockHttpClient);

        $result = $service->getCountryFromIp('8.8.8.8');

        $this->assertSame(['code' => null, 'name' => null], $result);
    }

    public function testGetCountryFromIpHandlesNetworkException(): void
    {
        $mockResponse = new MockResponse('', [
            'error' => 'Network error',
        ]);
        $mockResponse->getStatusCode(); // Trigger the error

        $mockHttpClient = new MockHttpClient(function () {
            throw new class('Network timeout') extends \Exception implements TransportExceptionInterface {};
        });

        $service = new GeoIpService($mockHttpClient);

        $result = $service->getCountryFromIp('8.8.8.8');

        $this->assertSame(['code' => null, 'name' => null], $result);
    }

    public function testGetCountryFromIpHandlesMissingCountryData(): void
    {
        $mockResponse = new MockResponse(json_encode([
            'status' => 'success',
            'region' => 'CA',
            'city' => 'Mountain View',
            // Missing country and countryCode
        ]));

        $mockHttpClient = new MockHttpClient($mockResponse);
        $service = new GeoIpService($mockHttpClient);

        $result = $service->getCountryFromIp('8.8.8.8');

        $this->assertSame([
            'code' => null,
            'name' => null
        ], $result);
    }

    public function testGetCountryFromIpReturnsPartialDataWhenAvailable(): void
    {
        $mockResponse = new MockResponse(json_encode([
            'status' => 'success',
            'countryCode' => 'GB',
            // Missing country name
        ]));

        $mockHttpClient = new MockHttpClient($mockResponse);
        $service = new GeoIpService($mockHttpClient);

        $result = $service->getCountryFromIp('8.8.8.8');

        $this->assertSame([
            'code' => 'GB',
            'name' => null
        ], $result);
    }
}
