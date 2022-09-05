<?php

namespace Mapper\Tests;

use Mapper\Api\APIClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;

use Mapper\Api\Resources\Signal;
use Mapper\Api\Resources\Organization;
use Mapper\Api\Resources\OrganizationSignal;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client;

class ClientTest extends TestCase
{
    public const EMPTY_RESPONSE = 'blank.json';
    public const RESPONSES_PATH = __DIR__ . '/responses/';

    public APIClient $client;

    public function setUp(): void
    {
        $this->client = new APIClient();
    }

    public function setMock(string $filename, $status_code=200, $headers = [])
    {
        $mock = new MockHandler([
            new Response($status_code, $headers, file_get_contents(self::RESPONSES_PATH . $filename))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);

        $this->client = new ApiClient($httpClient);
    }

    public function testCreateSignal()
    {
        $this->setMock("signal_created.json");

        $description = "test_from_php_198";
        $units = "km";

        $signal = $this->client->createSignal($description, $units, null);

        $this->assertInstanceOf(Signal::class, $signal);
        $this->assertSame($description, $signal->description);
    }

    public function testUpdateSignal()
    {
//        $this->setMock("updated_signal.json");

        $signalId = 4;
        $units = "km";

        $updated_signal = $this->client->updateSignal($signalId, null, $units);

        $this->assertInstanceOf(Signal::class, $updated_signal);
        $this->assertSame("km", $updated_signal->units);
    }

    public function testDeleteSignal()
    {
        $this->setMock("deleted.json");

        $deleted = $this->client->deleteSignal(57);

        $this->assertSame(true, $deleted);
    }

    public function testGetSignals()
    {
        $this->setMock("all_signals.json");

        $signals = $this->client->getSignals(null);

        $this->assertIsArray($signals);
        $this->assertInstanceOf(Signal::class, $signals[0]);
        $this->assertSame(4, count($signals));
    }

    public function testGetSignalsWithIds()
    {
        $this->setMock("signals_with_ids.json");

        $ids = [1, 2];

        $signals = $this->client->getSignals($ids);

        $this->assertEquals(2, count($signals));
    }

    public function testGetSignalWithId()
    {
        $this->setMock("one_signal.json");

        $id = 2;

        $signal = $this->client->getSignals($id);

        $this->assertInstanceOf(Signal::class, $signal[0]);
        $this->assertSame(2, $signal[0]->id);
    }

    public function testReadAllOrganizations()
    {
        $this->setMock("organizations.json");

        $organizations = $this->client->readAllOrganizations();

        $this->assertInstanceOf(Organization::class, $organizations[0]);
    }

    public function testReadOneOrganization()
    {
        $this->setMock("one_organization.json");

        $organization = $this->client->readOrganization(10);

        $this->assertInstanceOf(Organization::class, $organization);
        $this->assertSame(10, $organization->prod_id);
    }

    public function testReadOrganizationBasedOnDemoID()
    {
        $this->setMock("one_organization_demo.json");

        $organization = $this->client->readOrganization(26, "demo");

        $this->assertInstanceOf(Organization::class, $organization);
        $this->assertSame(26, $organization->demo_id);
    }

    public function testCreateOrganization()
    {
        $this->setMock("organization_created.json");

        $name = "PHPOrganizationForTest";
        $prod_id = 49;

        $organization = $this->client->createOrganization($name, $prod_id);
        $this->assertSame($name, $organization->name);
    }

    public function testUpdateOrganization()
    {
        $this->setMock("organization_updated.json");

        $updatedOrganization = $this->client->updateOrganization(47, null, null, 34);

        $this->assertSame(34, $updatedOrganization->demo_id);
    }

    public function testDeleteOrganization()
    {
        $this->setMock("deleted.json");

        $deletedOrganization = $this->client->deleteOrganization(43);

        $this->assertSame(true, $deletedOrganization);
    }

    public function testGetOrganizationSignalsWithIds()
    {
        $this->setMock("organization_signals_with_ids.json");

        $ids = [1, 2, 3];

        $organizationSignals = $this->client->getOrganizationSignals($ids);

        $this->assertInstanceOf(OrganizationSignal::class, $organizationSignals[0]);
        $this->assertSame(3, count($organizationSignals));
        $this->assertSame("file_for_testing_1", $organizationSignals[0]->name);
    }

    public function testReadOrganizationSignalsBasedOnOrganizationAndSignalsIds()
    {
        $this->setMock("organization_signal_from_organization.json");

        $organization_id = 3;
        $signal_id = 1;

        $organizationSignals = $this->client->readOrganizationSignalsFromOrganization($organization_id, $signal_id);

        $this->assertInstanceOf(OrganizationSignal::class, $organizationSignals[0]);
        $this->assertSame(3, $organizationSignals[0]->organization_id);
    }

    public function testReadOrganizationSignalsBasedOnDemoOrganizationAndSignalsIds()
    {
        $this->setMock("organization_signal_from_organization.json");

        $organization_demo_id = 6;
        $signal_id = 1;

        $organizationSignals = $this->client->readOrganizationSignalsFromOrganization($organization_demo_id, $signal_id);

        $this->assertInstanceOf(OrganizationSignal::class, $organizationSignals[0]);
        $this->assertSame(3, $organizationSignals[0]->organization_id);
    }

    public function testReadAllOrganizationSignals()
    {
        $this->setMock("all_organization_signals.json");

        $organizationSignals = $this->client->getOrganizationSignals(null);

        $this->assertInstanceOf(OrganizationSignal::class, $organizationSignals[0]);
        $this->assertSame('signal_2', $organizationSignals[1]->signal->description);
    }

    // public function testConstructXaxis() {

    //     $xaxis = $this->client->constructXaxis("label_1", "unit_1", "label_2", "unit_2");
    //     $decoded = json_decode($xaxis);
    //     $this->assertSame('label_1', $decoded[0]->label);
    // }


    public function testCreateOrganizationSignal()
    {
        $this->setMock("organization_signal_created.json");

        $organizationSignal = $this->client->createOrganizationSignal("AnotherTestFromPhp", 3);

        $this->assertInstanceOf(OrganizationSignal::class, $organizationSignal);
        $this->assertSame(1, $organizationSignal->signal->id);
    }

    public function testUpdateOrganizationSignal()
    {
        $this->setMock("organization_signal_updated.json");

        $updatedSignal = $this->client->updateOrganizationSignal(63, null, null, 'test_units', null, null);

        $this->assertSame('test_units', $updatedSignal->units);
    }

    public function testDeleteOrganizationSignal()
    {
        $this->setMock("deleted.json");

        $deleted = $this->client->deleteOrganizationSignal(63);

        $this->assertEquals(true, $deleted);
    }

    public function testGetOrganizationSignalsMap()
    {
        $this->setMock("map.json");

        $organizationFrom = 10;
        $organizationTo=11;

        $map = $this->client->mapOrganizationSignals($organizationFrom, $organizationTo, null);

        $this->assertSame(null, $map->file_for_testing_1);
    }
}
