<?php

namespace Raegmaen\OpenIdConnect\ValueObjects;

use Raegmaen\OpenIdConnect\Exceptions\Exception;
use Raegmaen\OpenIdConnect\Exceptions\InvalidArgumentException;

/**
 * Contains client connection configuration.
 */
class ClientConfiguration
{
    const CLIENT_SECRET_BASIC                = 'client_secret_basic';
    const CLIENT_AUTHORIZATION_RESPONSE_TYPE = 'code';
    const OPEN_ID_SCOPE                      = 'openid';
    const CLIENT_GRANT_TYPE                  = 'authorization_code';
    const SUPPORTED_AUTH_METHODS             = [ClientConfiguration::CLIENT_SECRET_BASIC, ClientConfiguration::CLIENT_SECRET_POST];
    const CLIENT_SECRET_POST                 = 'client_secret_post';

    /**
     * @var string
     */
    private $clientId;

    /**
     * @var string
     */
    private $clientSecret;

    /**
     * @var string
     */
    private $redirectUrl;

    /**
     * @var array
     */
    private $scopes;

    /**
     * @var array
     */
    private $authParams;

    /**
     * @param string $clientId
     * @param string $clientSecret
     * @param string $redirectUrl
     * @param array  $scopes
     * @param array  $authParams
     *
     * @return ClientConfiguration
     * @throws InvalidArgumentException
     */
    public static function create($clientId, $clientSecret, $redirectUrl, $scopes = [], $authParams = [])
    {
        if (!is_string($clientId) || strlen($clientId) < 1) {
            throw new InvalidArgumentException(
                'client_configuration.construct',
                sprintf('Provided client id is invalid: "%s"', (string) $clientId),
                Exception::CODE_CLIENT_ID
            );
        }

        if (!is_string($clientSecret) || strlen($clientSecret) < 1) {
            throw new InvalidArgumentException(
                'client_configuration.construct',
                sprintf('Provided client secret is invalid: "%s"', (string) $clientSecret),
                Exception::CODE_CLIENT_SECRET
            );
        }

        if (!is_string($redirectUrl) || strlen($redirectUrl) < 1) {
            throw new InvalidArgumentException(
                'client_configuration.construct',
                sprintf('Provided redirect url is invalid: "%s"', (string) $redirectUrl),
                Exception::CODE_REDIRECT_URL
            );
        }

        if (!is_array($scopes)) {
            throw new InvalidArgumentException(
                'client_configuration.construct',
                'Provided scopes are invalid!',
                Exception::CODE_SCOPE
            );
        }

        if (!is_array($authParams)) {
            throw new InvalidArgumentException(
                'client_configuration.construct',
                'Provided auth params are invalid!',
                Exception::CODE_AUTH_PARAMS
            );
        }

        return new self($clientId, $clientSecret, $redirectUrl, $scopes, $authParams);
    }

    /**
     * @param string $clientId
     * @param string $clientSecret
     * @param string $redirectUrl
     * @param array  $scopes
     * @param array  $authParams
     */
    private function __construct($clientId, $clientSecret, $redirectUrl, $scopes, $authParams)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUrl = $redirectUrl;
        $this->scopes = $scopes;
        $this->authParams = $authParams;
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @return string
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }

    /**
     * @return array
     */
    public function getScopes()
    {
        return array_merge([self::OPEN_ID_SCOPE], $this->scopes);
    }

    /**
     * @return array
     */
    public function getAuthParams()
    {
        return $this->authParams;
    }
}
