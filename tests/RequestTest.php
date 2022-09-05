<?php

namespace Mapper\Tests;

use Mapper\Api\Request;

use PHPUnit\Framework\TestCase;

use GuzzleHttp\Client;

class RequestTest extends TestCase
{
    public Request $request;

    protected function setUp(): void
    {
        $httpClient = new Client(['base_uri' => "test/"]);
        $version = "v1";
        $this->request = new Request($httpClient, $version);
    }

    public function testBuildGetParametersWithIdsAndVersion()
    {
        $ids = [1,2,3,4];
        $version = "demo";

        $actual = $this->request->buildGetParameters($ids, $version);
        $expected = "?id=1&id=2&id=3&id=4&version=demo";

        $this->assertSame($actual, $expected);
    }

    public function testBuildGetParametersWithId()
    {
        $id = 5;

        $actual = $this->request->buildGetParameters($id);
        $expected = "?id=5";

        $this->assertSame($actual, $expected);
    }

    public function testBuildParametersWithVersion()
    {
        $version = "demo";

        $actual = $this->request->buildGetParameters(null, $version);
        $expected = "?version=demo";

        $this->assertSame($actual, $expected);
    }
}
