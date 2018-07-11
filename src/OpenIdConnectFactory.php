<?php

namespace Raegmaen\OpenIdConnect;

use Raegmaen\OpenIdConnect\Exceptions\Exception;
use Raegmaen\OpenIdConnect\Helper\OpenIDConnectHelper;
use Raegmaen\OpenIdConnect\Session\DefaultSession;
use Raegmaen\OpenIdConnect\Session\SessionHandler;
use Raegmaen\OpenIdConnect\Session\SessionInterface;
use Raegmaen\OpenIdConnect\ValueObjects\ClientConfiguration;

class OpenIdConnectFactory
{
    /**
     * OpenIdConnectFactory constructor.
     */
    private function __construct()
    {
        // closed constructor
    }

    /**
     * @param string           $providerUrl
     * @param string           $clientId
     * @param string           $clientSecret
     * @param string           $redirectUrl
     * @param SessionInterface $session
     * @param array            $scopes
     * @param array            $authParameters
     * @param string|null      $prompt
     *
     * @return Client
     * @throws Exception
     */
    public static function create(
        $providerUrl,
        $clientId,
        $clientSecret,
        $redirectUrl,
        SessionInterface $session = null,
        $scopes = [],
        $authParameters = [],
        $prompt = null
    ){
        try {
            OpenIDConnectHelper::checkPrerequisites();

            if (null === $session) {
                $session = new DefaultSession();
            }

            $sessionHandler = new SessionHandler($session);

            $clientConfiguration = ClientConfiguration::create(
                $clientId,
                $clientSecret,
                $redirectUrl,
                $scopes,
                $authParameters,
                $prompt
            );

            $connector = Connector::create($providerUrl);
            $middleware = Middleware::create($clientConfiguration, $connector, $sessionHandler);
            $client = Client::create($middleware);

            return $client;
        } catch (Exception $e) {
            throw new Exception(
                'factory.create',
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }
}
