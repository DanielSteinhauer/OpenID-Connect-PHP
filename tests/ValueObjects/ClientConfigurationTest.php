<?php

namespace Raegmaen\OpenIdConnect\Tests\ValueObjects;

use Raegmaen\OpenIdConnect\Exceptions\InvalidArgumentException;
use Raegmaen\OpenIdConnect\ValueObjects\ClientConfiguration;

/**
 * @see ClientConfiguration
 */
class ClientConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldCreateClientConfiguration()
    {
        $clientId = '345678905456790543';
        $clientSecret = '3600';
        $redirectUrl = '9898798798798798797';

        $configuration = ClientConfiguration::create($clientId, $clientSecret, $redirectUrl);
        $this->assertEquals($clientId, $configuration->getClientId());
        $this->assertEquals($clientSecret, $configuration->getClientSecret());
        $this->assertEquals($redirectUrl, $configuration->getRedirectUrl());
    }

    /**
     * @test
     * @dataProvider provideInvalidCreationData
     *
     * @param string $clientId
     * @param string $clientSecret
     * @param string $redirectUrl
     *
     * @throws InvalidArgumentException
     */
    public function shouldThrowInvalidArgumentException($clientId, $clientSecret, $redirectUrl)
    {
        $this->setExpectedException(InvalidArgumentException::class);
        ClientConfiguration::create($clientId, $clientSecret, $redirectUrl);
    }

    /**
     * @return \Generator
     */
    public function provideInvalidCreationData()
    {
        yield 'ClientId not a string' => [
            'clientId' => 123,
            'clientSecret' => '123123',
            'redirectUrl' => 'bla',
        ];

        yield 'ClientId too short' => [
            'clientId' => '',
            'clientSecret' => '123123',
            'redirectUrl' => 'bla',
        ];

        yield 'ClientSecret not a string' => [
            'clientId' => '123',
            'clientSecret' => 123123,
            'redirectUrl' => 'bla',
        ];

        yield 'ClientSecret too short' => [
            'clientId' => '123',
            'clientSecret' => '',
            'redirectUrl' => 'bla',
        ];

        yield 'RedirectUrl not a string' => [
            'clientId' => '123',
            'clientSecret' => '123123',
            'redirectUrl' => 123123,
        ];

        yield 'RedirectUrl too short' => [
            'clientId' => '123',
            'clientSecret' => '123',
            'redirectUrl' => '',
        ];
    }
}
