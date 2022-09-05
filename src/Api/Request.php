<?php

/**
 * The Request class provides a simple HTTP request interface.
 */

namespace Mapper\Api;

use stdClass;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;

class Request
{
    /**
     * Last error from the server.
     *
     * @var StdClass
     **/
    private $lastError;

    /**
     * Determine whether to throw error or store the error in $lastError.
     *
     * @var bool
     **/
    private $failOnError = false;


    /**
     * Called when the Request object is created.
     *
     * @param Client $httpClient
     */
    public function __construct(Client $httpClient, string $apiVersion)
    {
        $this->lastError = false;
        $this->httpClient = $httpClient;
        $this->apiVersion = $apiVersion;
    }

    /**
     * Throw an exception if the request encounters an HTTP error condition.
     *
     * @param bool $option the new state of this feature
     */
    public function failOnError(bool $option = true)
    {
        $this->failOnError = $option;
    }

    /**
     * Return an representation of an error returned by the last request, or false
     * if the last request was not an error.
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * Build the query string based on the parameters sent.
     * @param array|integer|null $ids ids of the objects user would like to read
     * @param string|null $version "prod" or "demo", ID version of organization in AI Core
     * @return string query string
     */
    public function buildGetParameters($ids, ?string $version=null): string
    {
        if (is_null($ids) && is_null($version)) {
            return "";
        }

        $query = "?";
        $queryID = "id=";
        $queryVersion = "version=";
        $idsString = "";
        $fullQuery = "";

        if (is_array($ids)) {
            foreach ($ids as $value) {
                $idsString = $idsString . $queryID . $value . "&";
            }
            $fullQuery = $query . $idsString;
        } elseif (is_integer($ids) || is_string($ids)) {
            $idsString = $ids;
            $fullQuery = $query . $queryID . $idsString;
        }

        if (is_null($version)) {
            return $fullQuery;
        } elseif (is_string($version) && is_null($ids)) {
            $fullQuery = $fullQuery . $query . $queryVersion . $version;
        } elseif (is_string($version) && is_integer($ids)) {
            $fullQuery = $fullQuery . "&" . $queryVersion . $version;
        } else {
            $fullQuery = $fullQuery . $queryVersion . $version;
        }

        return $fullQuery;
    }

    /**
    * Pipeline for POST request.
    *
    * @param string $endpoint completes the url
    * @param string | array $data json encoded.
    *
    * @return stdClass|false the result from the endpoint
    */
    public function POST(string $endpoint, $data)
    {
        $address = $this->apiVersion . $endpoint;
        $response = $this->httpClient->post($address, ['json'=>$data]);

        return $this->handleResponse($response);
    }

    /**
     * Pipeline for PUT request.
     *
     * @param string $endpoint completes the url.
     * @param string | array $data json encoded.
     *
     * @return stdClass|false the result from the endpoint
     */
    public function PUT(string $endpoint, $data)
    {
        $address = $this->apiVersion . $endpoint;
        $response = $this->httpClient->put($address, ['json'=>$data]);

        return $this->handleResponse($response);
    }

    /**
     * Pipeline for GET request.
     *
     * @param string $endpoint completes the url.
     * @param array | int | null $ids
     * @return stdClass|array|false the result from the endpoint
     */
    public function GET(string $endpoint, $ids = null, $version=null)
    {
        $query = $this->buildGetParameters($ids, $version);
        $address = $this->apiVersion . $endpoint . $query;
        $response = $this->httpClient->get($address);

        return $this->handleResponse($response);
    }

    /**
     * Pipeline for DELETE request.
     *
     * @param string $endpoint completes the url.
     *
     * @return stdClass|string|false the result from the endpoint
     */
    public function DELETE(string $endpoint)
    {
        $address = $this->apiVersion . $endpoint;
        $response = $this->httpClient->delete($address);

        return $this->handleResponse($response);
    }

    /**
     * Check the response for possible errors and handle the response body returned.
     *
     * If failOnError is true, a client or server error is raised, otherwise returns false
     * on error.
     */
    private function handleResponse(Psr7\Response $response)
    {
        $statusCode = $response->getStatusCode();
        $body =  (string) $response->getBody();

        if ($statusCode >= 500 && $statusCode <= 599) {
            if ($this->failOnError) {
                throw new ClientError($body, $statusCode);
            } else {
                $this->lastError = $body;

                return false;
            }
        } elseif ($statusCode>= 400 && $statusCode <= 499) {
            if ($this->failOnError) {
                throw new ServerError($body, $statusCode);
            } else {
                $this->lastError = $body;

                return false;
            }
        }

        return json_decode($body);
    }
}
