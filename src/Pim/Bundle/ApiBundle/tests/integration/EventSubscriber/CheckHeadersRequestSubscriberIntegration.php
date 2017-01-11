<?php

namespace tests\integration\Pim\Bundle\ApiBundle\EventSubscriber;

use Symfony\Component\HttpFoundation\Response;
use Test\Integration\TestCase;

class CheckHeadersRequestSubscriberIntegration extends TestCase
{
    protected $purgeDatabaseForEachTest = false;

    public function testErrorIfAcceptHeaderIsXml()
    {
        $client = static::createClient();

        $client->request('GET', 'api/rest/v1/categories/master', [], [], ['HTTP_ACCEPT' => 'application/xml']);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_ACCEPTABLE, $response->getStatusCode(), 'Header is not acceptable');
        $content = json_decode($response->getContent(), true);
        $this->assertCount(2, $content, 'Error response contains 2 items');
        $this->assertSame(Response::HTTP_NOT_ACCEPTABLE, $content['code']);
        $this->assertSame('"application/xml" in "Accept" header is not valid. Only "application/json" is allowed.', $content['message']);
    }

    public function testSuccessIfAcceptHeaderIsJson()
    {
        $client = static::createClient();

        $client->request('GET', 'api/rest/v1/categories/master', [], [], ['HTTP_ACCEPT' => 'application/json']);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), 'Header is acceptable');
    }

    public function testSuccessIfAcceptHeaderIsEmpty()
    {
        $client = static::createClient();

        $client->request('GET', 'api/rest/v1/categories/master');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), 'Header is acceptable');
    }

    public function testErrorIfContentTypeHeaderIsXml()
    {
        $client = static::createClient();

        $client->request('PATCH', 'api/rest/v1/categories/master', [], [], [
            'HTTP_CONTENT_TYPE' => 'application/xml'
        ], '{"parent": null}');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_ACCEPTABLE, $response->getStatusCode(), 'Header is not acceptable');
        $content = json_decode($response->getContent(), true);
        $this->assertCount(2, $content, 'Error response contains 2 items');
        $this->assertSame(Response::HTTP_NOT_ACCEPTABLE, $content['code']);
        $this->assertSame('"application/xml" in "Content-Type" header is not valid. Only "application/json" is allowed.', $content['message']);
    }

    public function testSuccessIfContentTypeHeaderIsJson()
    {
        $client = static::createClient();

        $client->request('PATCH', 'api/rest/v1/categories/master', [], [], [
            'HTTP_CONTENT_TYPE' => 'application/json'
        ], '{"parent": null}');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode(), 'Header is acceptable');
    }

    public function testSuccessIfContentTypeHeaderIsEmpty()
    {
        $client = static::createClient();

        $client->request('PATCH', 'api/rest/v1/categories/master', [], [], [
            'HTTP_CONTENT_TYPE' => 'application/json'
        ], '{"parent": null}');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode(), 'Header is acceptable');
    }

    public function testSuccessWhenRouteIsOutsideTheAPI()
    {
        $client = static::createClient();

        $client->request('GET', '/');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), 'Page is accessible without error');
    }
}
