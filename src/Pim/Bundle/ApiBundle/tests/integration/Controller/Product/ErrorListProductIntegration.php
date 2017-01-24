<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Controller\Product;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Symfony\Component\HttpFoundation\Response;

class ErrorListProductIntegration extends TestCase
{
    public function testNotFoundChannel()
    {
        $client = static::createClient();

        $client->request('GET', 'api/rest/v1/products?channel=not_found');
        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertCount(2, $content);
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $content['code']);
        $this->assertSame('Channel "not_found" does not exist.', $content['message']);
    }

    public function testNotFoundLocale()
    {
        $client = static::createClient();

        $client->request('GET', 'api/rest/v1/products?locales=not_found');
        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertCount(2, $content);
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $content['code']);
        $this->assertSame('Locale "not_found" does not exist.', $content['message']);
    }

    public function testNotFoundLocales()
    {
        $client = static::createClient();

        $client->request('GET', 'api/rest/v1/products?locales=not_found,jambon');
        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertCount(2, $content);
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $content['code']);
        $this->assertSame('Locales "not_found, jambon" do not exist.', $content['message']);
    }

    public function testInactiveLocale()
    {
        $client = static::createClient();

        $client->request('GET', 'api/rest/v1/products?channel=ecommerce&locales=de_DE');
        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertCount(2, $content);
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $content['code']);
        $this->assertSame('Locale "de_DE" is not activated for the channel "ecommerce".', $content['message']);
    }

    public function testInactiveLocales()
    {
        $client = static::createClient();

        $client->request('GET', 'api/rest/v1/products?channel=ecommerce&locales=de_DE,fr_FR');
        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertCount(2, $content);
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $content['code']);
        $this->assertSame('Locales "de_DE, fr_FR" are not activated for the channel "ecommerce".', $content['message']);
    }

    public function testNotFoundAttribute()
    {
        $client = static::createClient();

        $client->request('GET', 'api/rest/v1/products?attributes=not_found');
        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertCount(2, $content);
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $content['code']);
        $this->assertSame('Attribute "not_found" does not exist.', $content['message']);
    }

    public function testNotFoundAttributes()
    {
        $client = static::createClient();

        $client->request('GET', 'api/rest/v1/products?attributes=not_found,jambon');
        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertCount(2, $content);
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $content['code']);
        $this->assertSame('Attributes "not_found, jambon" do not exist.', $content['message']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration(
            [Configuration::getTechnicalCatalogPath()],
            false
        );
    }
}
