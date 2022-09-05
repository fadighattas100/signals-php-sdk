<?php

namespace Mapper\Api;

use Mapper\Api\Request;
use Mapper\Api\Resources\Organization;
use Mapper\Api\Resources\OrganizationSignal;
use Mapper\Api\Resources\Signal;
use stdClass;

use GuzzleHttp\Client;

class APIClient
{
    public const DEFAULT_BASE_URI     = 'base_uri';
    public const DEFAULT_HEADERS      = 'headers';
    public const DEFAULT_ERROR_HANDLE = 'http_errors';

    private const HEADERS__CONTENT_TYPE = 'Content-Type';
    private const HEADERS__ACCEPT       = 'Accept';
    private const APPLICATION_JSON      = 'application/json';

    /**
     * Request instance.
     *
     * @var Request
     **/
    protected $http;

    /**
     * Server base url
     *
     * @var string
     **/
     protected $baseURL = 'https://signals.compredict.de/api/';

    /**
     * API version
     *
     * @var string
     **/
    protected $APIVersion = 'v1';


    /**
     * Constructor function for Client.
     *
     * @param Client|null instance of HTTP Client from GuzzleHttp
     * @param string|null base url, by default read from baseURL property
     */
    public function __construct(?Client $client = null, ?string $url=null)
    {
        $this->url = $url ?? $this->baseURL;
        $this->httpClient = $client ?? $this->buildDefaultHttpClient($this->url);
        $this->http = new Request($this->httpClient, $this->APIVersion);
    }

    /**
     * Create instance of httpClient.
     */
    private function buildDefaultHttpClient(?string $url): Client
    {
        return new Client([
            self::DEFAULT_BASE_URI => $url,
            self::DEFAULT_ERROR_HANDLE => false,
            self::DEFAULT_HEADERS  => [
                self::HEADERS__CONTENT_TYPE => self::APPLICATION_JSON,
                self::HEADERS__ACCEPT       => self::APPLICATION_JSON,
            ]
        ]);
    }

    /**
     * Configure the API client to throw exceptions when HTTP errors occur.
     *
     * Note that network faults will always cause an exception to be thrown.
     *
     * @param bool $option sets the value of this flag
     */

    public function failOnError($option = true)
    {
        $this->http->failOnError($option);
    }

    /**
     * Get the url to Signals Mapper server
     * @return String URL
     */
    public function getURL(): string
    {
        return $this->baseURL . $this->APIVersion;
    }


    /**
     * Map object from response to single resource or list of resources.
     *
     * @param string $resource name of the resource class
     * @param stdClass|false|string|array $object coming from response
     * @return Signal|Organization|OrganizationSignal|array|false
     */
    private function mapResource(string $resource, $object)
    {
        if ($object === false || is_string($object)) {
            return $object;
        }

        $baseResource = __NAMESPACE__ . '\\' . $resource;
        $resourceClass = (class_exists($baseResource)) ? $baseResource : 'Mapper\\Api\\Resources\\' . $resource;

        if (!is_array($object)) {
            return new $resourceClass($object, $this);
        } else {
            $arrayOfResources = [];
            foreach ($object as $res) {
                $newObject = new $resourceClass($res, $this);
                array_push($arrayOfResources, $newObject);
            }
            return $arrayOfResources;
        }
    }

    /**
     * Read signals based on ids. If no ids passed - read all signals.
     *
     * @param array|int|null $ids ids of signals to read
     * @return mixed list of Signals or one Signal.
     */
    public function getSignals($ids)
    {
        $response = $this->http->GET('/signals/', $ids);
        return $this->mapResource('Signal', $response);
    }

    /**
     * Create signal.
     *
     * @param string $description indicates role of the signal
     * @param string|null $units units of measure
     * @param boolean|null $is_virtual indicates if signal is virtual or not
     * @return Signal created Signal
     */
    public function createSignal($description, $units = null, $is_virtual = null)
    {
        $requestData = ['description' => $description, 'units' => $units, 'is_virtual' => $is_virtual];
        $response =  $this->http->POST('/signals/', $requestData);

        return $this->mapResource('Signal', $response);
    }

    /**
     * Update signal.
     *
     * @param int $id of the signal to update
     * @param string|null $description indicates role of the signal
     * @param string|null $units units of measure
     * @param boolean|null $is_virtual indicates if signal is virtual or not
     * @return Signal updated Signal
     */
    public function updateSignal($id, ?string $description=null, ?string $units=null, ?bool $is_virtual=null)
    {
        if (isset($units)) {
            $requestData['units'] = $units;
        }
        if (isset($is_virtual)) {
            $requestData['is_virtual'] = $is_virtual;
        }
        if (isset($description)) {
            $requestData['description'] = $description;
        }

        $response = $this->http->PUT('/signals/' . $id, $requestData);

        return $this->mapResource('Signal', $response);
    }

    /**
     * Delete signal.
     *
     * @param int $id of the signal to delete
     * @return boolean true if deleted
     */
    public function deleteSignal(int $id)
    {
        $response = $this->http->DELETE('/signals/' . $id);
        var_dump($response);
        if ($response === false || is_string($response)) {
            return $response;
        } else {
            return true;
        }
    }

    /**
     * Create Organization.
     *
     * @param string $name name of Organization
     * @param int|null $prod_id id of organization in prod version of AI CORE
     * @param int|null $demo_id id of organization in demo version of AI CORE
     * @return Organization create Organization
     */
    public function createOrganization(string $name, ?int $prod_id = null, ?int $demo_id = null)
    {
        $requestData = ['name' => $name, 'prod_id' => $prod_id, 'demo_id' => $demo_id];
        $response = $this->http->POST('/organizations/', $requestData);

        return $this->mapResource('Organization', $response);
    }

    /**
     * Update Organization.
     *
     * @param string|null $name name of Organization
     * @param int|null $prod_id id of organization in prod version of AI CORE
     * @param int|null $demo_id id of organization in demo version of AI CORE
     * @return Organization updated Organization
     */
    public function updateOrganization(int $id, ?string $name=null, ?int $prod_id=null, ?int $demo_id=null)
    {
        $requestData = [];

        if (isset($name)) {
            $requestData['name'] = $name;
        }
        if (isset($prod_id)) {
            $requestData['prod_id'] = $prod_id;
        }
        if (isset($demo_id)) {
            $requestData['demo_id'] = $demo_id;
        }
        $response = $this->http->PUT('/organizations/' . $id, $requestData);

        return $this->mapResource('Organization', $response);
    }

    /**
     * Delete organization.
     *
     * @param int $id of the organization to delete
     * @return boolean true if deleted
     */
    public function deleteOrganization($id): bool
    {
        $response = $this->http->DELETE('/organizations/' . $id);
        if ($response === false || is_string($response)) {
            return $response;
        } else {
            return true;
        }
    }

    /**
     * Read one organization, based on ID.
     *
     * @param int $id of the organization to read
     * @return Organization|bool|string read Organization
     */
    public function readOrganization($id, $version = "prod")
    {
        $response = $this->http->GET('/organizations/' . $id, null, $version);

        return $this->mapResource('Organization', $response);
    }

    /**
     * Read all organizations.
     *
     * @return array|bool|string|Organization Organization or list of Organizations
     */
    public function readAllOrganizations()
    {
        $response = $this->http->GET('/organizations/');

        return $this->mapResource('Organization', $response);
    }

    /**
     * Read OrganizationSignals that belong to specified organization.
     *
     * @param int $organizationId of the organization to read from
     * @param int|array|null $signalIds ids of signals based on which OrganizationSignals should be retrieved
     * @param string|null version of organizationId (or Id coming from demo or prod version of AICore)
     * @return OrganizationSignal|string|bool|array OrganizationSignals retrieved
     */
    public function readOrganizationSignalsFromOrganization(int $organizationId, $signalIds, $version = "prod")
    {
        $response = $this->http->GET('/organizations/' . $organizationId . "/signals/", $signalIds, $version);
        return $this->mapResource('OrganizationSignal', $response);
    }

    /**
     * Create OrganizationSignal.
     *
     * @param string $name name of OrganizationSignal
     * @param int $organizationId id of organization that signal should belong to
     * @param int|null $signalId id of the signal that organization signal is mapped to
     * @param string|null $units units of measurement for organization's signal
     * @param string|null $label label used for visualization of organization's signal
     * @param string|null $xaxis json used for visualization of signal data; needs to contain label,
     * unit, filter for x and y axis.
     *
     * @return OrganizationSignal|bool|string OrganziationSignal created
     */
    public function createOrganizationSignal($name, $organizationId, $signalId = null, $units = null, $label = null, $xaxis = null)
    {
        $requestData = [
            'name' => $name, 'organization_id' => $organizationId, 'units' => $units, 'label' => $label,
            'xaxis' => $xaxis, 'signal_id' => $signalId
        ];

        $response = $this->http->POST('/organization_signals/', $requestData);
        return $this->mapResource('OrganizationSignal', $response);
    }

    /**
     * Update OrganizationSignal.
     *
     * @param int}null $organizationId id of organization that signal should belong to
     * @param int|null $signalId id of the signal that organization signal is mapped to
     * @param string|null $units units of measurement for organization's signal
     * @param string|null $label label used for visualization of organization's signal
     * @param string|null $xaxis json used for visualization of signal data; needs to contain label,
     * unit, filter for x and y axis.
     *
     * @return OrganizationSignal|bool|string OrganizationSignal updated
     */
    public function updateOrganizationSignal(
        $id,
        ?int $organizationId=null,
        ?int $signalId=null,
        ?string $units=null,
        ?string $label=null,
        ?string $xaxis=null
    ) {
        $requestData = [];

        if (isset($organizationId)) {
            $requestData['organization_id'] = $organizationId;
        }
        if (isset($signalId)) {
            $requestData['signal_id'] = $signalId;
        }
        if (isset($units)) {
            $requestData['units'] = $units;
        }
        if (isset($label)) {
            $requestData['label'] = $label;
        }
        if (isset($xaxis)) {
            $requestData['xaxis'] = $xaxis;
        }

        $response = $this->http->PUT("/organization_signals/" . $id, $requestData);
        return $this->mapResource("OrganizationSignal", $response);
    }

    /**
     * Delete OrganizationSignal.
     *
     * @param int $id of the OrganizationSignal to delete
     * @return boolean true if deleted
     */
    public function deleteOrganizationSignal($id)
    {
        $response = $this->http->DELETE("/organization_signals/" . $id);
        if ($response === false || is_string($response)) {
            return $response;
        } else {
            return true;
        }
    }

    /**
     * Read OrganizationSignals based on ids. If no ids passed - read all OrganizationSignals.
     *
     * @param array|int|null $ids of OrganizationSignals to read
     * @return array|Signal list of Signals or one Signal.
     */
    public function getOrganizationSignals($ids=null)
    {
        $response = $this->http->GET("/organization_signals/", $ids);
        return $this->mapResource("OrganizationSignal", $response);
    }

    /**
     * Map OrganizationSignals names coming from two different Organizations.
     * Option: Can map only OrganizationSignals based on specific Signals ids if they are passed.
     *
     * @param int $fromOrganization Organization id that OrganizationSignals should be mapped from
     * @param int $toOrganization Organization id that OrganizationSignals should be mapped to
     * @param int|array $ids Ids of signals that OrganizationSignals should be based on
     * @param string|null $version version of AICore that ID of organization is coming from
     * @return  array|bool|string map of OrganizationSignals names
     */
    public function mapOrganizationSignals(int $fromOrganization, int $toOrganization, $ids=null, $version =
    "prod")
    {
        $map = $this->http->GET(
            "/organization_signals/map/" . $fromOrganization . "/" . $toOrganization,
            $ids,
            $version
        );
        return $map;
    }

    /**
     * Xaxis is a value that can be passed to organization signal while creating or updating.
     * Since Signal's Mapper API accepts only JSON value of xaxis, this is a helper function to create json out
     * of passed values. User need to provide label and units for two filters: epoch and default one.
     * This data is used later for visualization.
     *
     * @param string $labelForEpoch
     * @param string $unitForEpoch
     * @param string $labelForDefault
     * @param string $unitForDefault
     * @return string wih xaxis
     */
    public function constructXaxis($labelForEpoch, $unitForEpoch, $labelForDefault, $unitForDefault)
    {
        $xaxis = [
            ["label" => $labelForEpoch, "unit" => $unitForEpoch, "filter" => "epoch"],
            ["label" => $labelForDefault, "unit" => $unitForDefault, "filter" => "default"]
        ];
        return json_encode($xaxis);
    }
}
