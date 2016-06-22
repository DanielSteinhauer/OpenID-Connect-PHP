<?php

namespace Raegmaen\OpenIdConnect;

use Raegmaen\OpenIdConnect\Exceptions\ClientException;
use Raegmaen\OpenIdConnect\Exceptions\ConfigurationNotFoundException;
use Raegmaen\OpenIdConnect\Exceptions\ConnectorException;
use Raegmaen\OpenIdConnect\Exceptions\Exception;
use Raegmaen\OpenIdConnect\Exceptions\InvalidArgumentException;
use Raegmaen\OpenIdConnect\Exceptions\SessionException;
use Raegmaen\OpenIdConnect\Exceptions\TokenException;
use Raegmaen\OpenIdConnect\Session\SessionHandler;
use Raegmaen\OpenIdConnect\Token\IdTokenFactory;
use Raegmaen\OpenIdConnect\Token\OauthAccessTokenFactory;
use Raegmaen\OpenIdConnect\ValueObjects\AuthorizationCode;
use Raegmaen\OpenIdConnect\ValueObjects\ClientConfiguration;
use Raegmaen\OpenIdConnect\ValueObjects\JsonWebKeySet;
use Raegmaen\OpenIdConnect\Token\OauthAccessToken;
use Raegmaen\OpenIdConnect\ValueObjects\ProviderConfiguration;
use Raegmaen\OpenIdConnect\Token\TokenWrapper;
use Raegmaen\OpenIdConnect\ValueObjects\UserRedirect;

/**
 * Class between client and connector.
 */
class Middleware
{
    /**
     * @var ClientConfiguration
     */
    private $clientConfiguration;

    /**
     * @var ProviderConfiguration
     */
    private $providerConfiguration;

    /**
     * @var Connector
     */
    private $connector;

    /**
     * @var SessionHandler
     */
    private $sessionHandler;

    public static function create(
        ClientConfiguration $clientConfiguration,
        Connector $connector,
        SessionHandler $sessionHandler
    ) {
        $providerConfiguration = self::refreshProviderConfiguration($connector);

        $middleware = new self($clientConfiguration, $providerConfiguration, $connector, $sessionHandler);
        $middleware->checkSupportedScopes($clientConfiguration->getScopes());

        return $middleware;
    }

    /**
     * @param Connector $connector
     *
     * @return ProviderConfiguration
     * @throws ConfigurationNotFoundException
     * @throws ConnectorException
     */
    private static function refreshProviderConfiguration(Connector $connector)
    {
        $wellKnownConfigUrl = ".well-known/openid-configuration";
        $providerConfigurationArray = $connector->callProvider($connector->prependProviderUrl($wellKnownConfigUrl));

        return ProviderConfiguration::createFromArray($providerConfigurationArray);
    }

    /**
     * Middleware constructor.
     *
     * @param ClientConfiguration   $clientConfiguration
     * @param ProviderConfiguration $providerConfiguration
     * @param Connector             $connector
     * @param SessionHandler        $sessionHandler
     */
    private function __construct(
        ClientConfiguration $clientConfiguration,
        ProviderConfiguration $providerConfiguration,
        Connector $connector,
        SessionHandler $sessionHandler
    ) {
        $this->clientConfiguration = $clientConfiguration;
        $this->providerConfiguration = $providerConfiguration;
        $this->connector = $connector;
        $this->sessionHandler = $sessionHandler;
    }

    /**
     * @return UserRedirect
     * @throws SessionException
     * @throws InvalidArgumentException
     */
    public function prepareAuthorizationRequest()
    {
        $this->sessionHandler->initializeSession();

        $params = array_merge(
            $this->clientConfiguration->getAuthParams(),
            [
                'response_type' => ClientConfiguration::CLIENT_AUTHORIZATION_RESPONSE_TYPE,
                'redirect_uri' => $this->clientConfiguration->getRedirectUrl(),
                'client_id' => $this->clientConfiguration->getClientId(),
                'nonce' => $this->sessionHandler->get(SessionHandler::NONCE),
                'state' => $this->sessionHandler->get(SessionHandler::STATE),
                'scope' => $this->clientConfiguration->getScopes(),
            ]
        );

        $params['scope'] = implode(' ', $params['scope']);

        return UserRedirect::create(
            $this->providerConfiguration->getAutorizationEndpoint(),
            UserRedirect::METHOD_GET,
            $params
        );
    }

    /**
     * @param string $requestCode
     * @param string $requestState
     *
     * @return AuthorizationCode
     * @throws ClientException
     */
    public function preRequestTokens($requestCode, $requestState)
    {
        try {
            $this->sessionHandler->compareValue(SessionHandler::STATE, $requestState);
            $this->sessionHandler->get(SessionHandler::NONCE);

            return AuthorizationCode::create($requestCode);
        } catch (SessionException $e) {
            $this->sessionHandler->cleanSession();
            throw new ClientException(
                'authenticate.pre_step2.missing_session_values',
                'STATE or NONCE are invalid.',
                Exception::CODE_SESSION_ERROR
            );
        } catch (InvalidArgumentException $e) {
            throw new ClientException(
                'authenticate.pre_step2.invalid_argument',
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * @param AuthorizationCode $authCode
     *
     * @return TokenWrapper
     * @throws ClientException
     * @throws ConnectorException
     * @throws InvalidArgumentException
     */
    public function requestTokens(AuthorizationCode $authCode)
    {
        $keys = $this->getJsonWebKeySetFromProvider();

        $tokenEndpoint = $this->providerConfiguration->getTokenEndpoint();

        $tokenParams = [
            'grant_type' => ClientConfiguration::CLIENT_GRANT_TYPE,
            'code' => $authCode->getCode(),
            'redirect_uri' => $this->clientConfiguration->getRedirectUrl(),
        ];
        $tokenHeaders = [];

        $authMethod = $this->findSupportedAuthMethod(ClientConfiguration::SUPPORTED_AUTH_METHODS);
        if ($authMethod === ClientConfiguration::CLIENT_SECRET_POST) {
            $tokenParams['client_id'] = $this->clientConfiguration->getClientId();
            $tokenParams['client_secret'] = $this->clientConfiguration->getClientSecret();
        } elseif ($authMethod === ClientConfiguration::CLIENT_SECRET_BASIC) {
            $tokenHeaders[] = 'Authorization: Basic '
                . base64_encode(
                    $this->clientConfiguration->getClientId() . ':' . $this->clientConfiguration->getClientSecret()
                );
        }

        $tokenParams = http_build_query($tokenParams, null, '&');
        $responseArray = $this->connector->callProvider($tokenEndpoint, $tokenParams, $tokenHeaders);

        if (isset($responseArray['error'])) {
            $errorMessage = isset($responseArray['error_description'])
                ? $responseArray['error_description']
                : $responseArray['error'];
            throw new InvalidArgumentException(
                'authenticate.step2.provider_response.error',
                $errorMessage,
                Exception::CODE_PROVIDER_ERROR
            );
        }

        if (!isset($responseArray['access_token'], $responseArray['id_token'], $responseArray['token_type'])) {
            throw new InvalidArgumentException(
                'authenticate.step2.provider_response.invalid',
                sprintf('Provided data is not complete: %s', json_encode($responseArray)),
                Exception::CODE_PROVIDER_ERROR
            );
        }

        try {
            $accessToken = OauthAccessTokenFactory::createToken(
                $responseArray['access_token'],
                $responseArray['token_type'],
                $responseArray['expires_in'],
                $responseArray['refresh_token']
            );
        } catch (TokenException $e) {
            throw new ClientException(
                'authenticate.step2.invalid_access_token',
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }

        try {
            $idToken = IdTokenFactory::createToken($responseArray['id_token'], $keys);
            IdTokenFactory::verifyToken(
                $idToken,
                $this->clientConfiguration->getClientId(),
                $this->connector->getProviderUrl(),
                $this->sessionHandler->get(SessionHandler::NONCE)
            );
        } catch (TokenException $e) {
            throw new ClientException(
                'authenticate.step2.invalid_id_token',
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        } catch (SessionException $e) {
            throw new ClientException(
                'authenticate.step2.session_invalid',
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }

        return new TokenWrapper($accessToken, $idToken);
    }

    /**
     * @param TokenWrapper $tokenWrapper
     *
     * @return TokenWrapper
     * @throws ClientException
     * @throws ConnectorException
     * @throws InvalidArgumentException
     */
    public function refreshAccessToken(TokenWrapper $tokenWrapper)
    {
        $tokenEndpoint = $this->providerConfiguration->getTokenEndpoint();
        $tokenParams = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $tokenWrapper->getAccessToken()->getRefreshTokenString(),
            'client_id' => $this->clientConfiguration->getClientId(),
            'client_secret' => $this->clientConfiguration->getClientSecret(),
        ];
        $tokenParams = http_build_query($tokenParams, null, '&');
        $responseArray = $this->connector->callProvider($tokenEndpoint, $tokenParams);

        try {
            $accessToken = OauthAccessTokenFactory::createToken(
                $responseArray['access_token'],
                $responseArray['token_type'],
                $responseArray['expires_in'],
                $responseArray['refresh_token']
            );
        } catch (TokenException $e) {
            throw new ClientException(
                'authenticate.step2.invalid_access_token',
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }

        return new TokenWrapper($accessToken, $tokenWrapper->getIdToken());
    }

    /**
     * @param string $responseType
     *
     * @return bool
     * @throws ClientException
     */
    public function checkSupportedResponseTypes($responseType)
    {
        return $this->providerConfiguration->isResponseTypeSupported($responseType);
    }

    /**
     * @param string $grantType
     *
     * @return bool
     * @throws ClientException
     */
    public function checkSupportedGrantTypes($grantType)
    {
        return $this->providerConfiguration->isGrantTypeSupported($grantType);
    }

    /**
     * @param array $authMethods
     *
     * @throws InvalidArgumentException
     */
    public function checkSupportedAuthMethods($authMethods = [])
    {
        $this->findSupportedAuthMethod($authMethods);
    }

    /**
     * @param array $scopes
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    public function checkSupportedScopes(array $scopes)
    {
        try {
            $providerScopes = $this->providerConfiguration->getAdditionalConfigurationParam(
                ProviderConfiguration::SCOPES_SUPPORTED
            );
            foreach ($scopes as $scope) {
                if (!in_array($scope, $providerScopes)) {
                    throw new InvalidArgumentException(
                        'middleware.unsupported_scope',
                        sprintf("The provider doesn't support the scope '%s'.", $scope),
                        Exception::CODE_SCOPE
                    );
                }
            }
        } catch (ConfigurationNotFoundException $e) {
            // Todo: How to handle this use case? Returning true is not correct.
            return true;
        }

        return true;
    }

    /**
     * @param string           $endpoint
     * @param OauthAccessToken $accessToken
     *
     * @return array
     * @throws ConfigurationNotFoundException
     * @throws ConnectorException
     */
    public function callProviderEndpoint($endpoint, OauthAccessToken $accessToken)
    {
        $endpointUrl = $this->providerConfiguration->getAdditionalConfigurationParam($endpoint);
        $schema = ClientConfiguration::OPEN_ID_SCOPE;
        $endpointUrl .= "?schema=" . $schema;

        //The accessToken has to be send in the Authorization header, so we create a new array with only this header.
        $headers = ["Authorization: {$accessToken->getType()} {$accessToken->getTokenString()}"];

        $userJson = $this->connector->callProvider($endpointUrl, null, $headers);

        return $userJson;
    }

    /**
     * @return JsonWebKeySet
     * @throws ConnectorException
     * @throws Exceptions\InvalidArgumentException
     */
    private function getJsonWebKeySetFromProvider()
    {
        $jwks = $this->connector->callProvider($this->providerConfiguration->getJwksUri());

        return JsonWebKeySet::createFromArray($jwks);
    }

    /**
     * Checks client supported with provider supported auth methods
     *
     * @param array $authMethods
     *
     * @return string
     * @throws InvalidArgumentException
     */
    private function findSupportedAuthMethod($authMethods = [])
    {
        foreach ($authMethods as $authMethod) {
            if ($this->providerConfiguration->isTokenEndpointAuthMethodSupported($authMethod)) {
                return $authMethod;
            }
        }

        throw new InvalidArgumentException(
            'middleware.no_supported_auth_method',
            'No supported auth method found.',
            Exception::CODE_AUTH_METHOD
        );
    }

    public function cleanSession()
    {
        $this->sessionHandler->cleanSession();
    }
}
