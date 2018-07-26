<?php
/**
 *
 * Copyright MITRE 2015
 *
 * OpenIDConnectClient for PHP5
 * Author: Michael Jett <mjett@mitre.org>
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 *
 */

namespace Raegmaen\OpenIdConnect;

use Raegmaen\OpenIdConnect\Exceptions\ConfigurationNotFoundException;
use Raegmaen\OpenIdConnect\Exceptions\ClientException;
use Raegmaen\OpenIdConnect\Exceptions\ConnectorException;
use Raegmaen\OpenIdConnect\Exceptions\Exception;
use Raegmaen\OpenIdConnect\Exceptions\InvalidArgumentException;
use Raegmaen\OpenIdConnect\ValueObjects\ClientConfiguration;
use Raegmaen\OpenIdConnect\Token\TokenWrapper;

/**
 * TODO: Move this note to documentation!
 * Please note this class stores nonce in
 * $_SESSION['openid_connect_nonce']
 */
class Client implements ClientInterface
{
    /**
     * @var Middleware
     */
    private $middleware;

    /**
     * @param Middleware $middleware
     *
     * @return Client
     * @throws ClientException
     */
    public static function create(Middleware $middleware)
    {
        if (!$middleware->checkSupportedResponseTypes(ClientConfiguration::CLIENT_AUTHORIZATION_RESPONSE_TYPE)) {
            throw new ClientException(
                'construct.unsupported_response_type',
                sprintf(
                    'ResponseType "%s" is not supported by the provider!',
                    ClientConfiguration::CLIENT_AUTHORIZATION_RESPONSE_TYPE
                ),
                Exception::CODE_RESPONSE_TYPE
            );
        }

        if (!$middleware->checkSupportedGrantTypes(ClientConfiguration::CLIENT_GRANT_TYPE)) {
            throw new ClientException(
                'construct.unsupported_grant_type',
                sprintf(
                    'GrantType "%s" for token request not supported by provider!',
                    ClientConfiguration::CLIENT_GRANT_TYPE
                ),
                Exception::CODE_GRANT_TYPE
            );
        }

        try {
            $middleware->checkSupportedScopes([ClientConfiguration::OPEN_ID_SCOPE]);
        } catch (InvalidArgumentException $e) {
            throw new ClientException(
                'construct.unsupported_scopes',
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }

        try {
            $middleware->checkSupportedAuthMethods(ClientConfiguration::SUPPORTED_AUTH_METHODS);
        } catch (InvalidArgumentException $e) {
            throw new ClientException(
                'construct.supported_auth_methods',
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }

        return new Client($middleware);
    }

    /**
     * @param Middleware $middleware
     */
    private function __construct(Middleware $middleware)
    {
        $this->middleware = $middleware;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate($requestData = [])
    {
        if (isset($requestData['error'])) {
            throw new ClientException(
                'authenticate.provider_response',
                sprintf(
                    "Error: %s Description: %s",
                    $requestData['error'],
                    $requestData['error_description']
                ),
                Exception::CODE_PROVIDER_ERROR
            );
        }

        /**
         * Step 1: AuthorizationRequest
         */
        if (!isset($requestData['code'])) {
            try {
                // Requests authorization
                return $this->middleware->prepareAuthorizationRequest();
            } catch (Exception $e) {
                $this->middleware->cleanSession();
                throw new ClientException(
                    'authenticate.step1',
                    $e->getMessage(),
                    $e->getCode(),
                    $e
                );
            }
        }

        /**
         * Step 2: Request a token
         */
        if (isset($requestData['code'], $requestData['state'])) {
            try {
                $code = $this->middleware->preRequestTokens($requestData['code'], $requestData['state']);
                $tokenResponseWrapper = $this->middleware->requestTokens($code);
                $this->middleware->cleanSession();

                // Success!
                return $tokenResponseWrapper;
            } catch (InvalidArgumentException $e) {
                $this->middleware->cleanSession();

                throw new ClientException(
                    'authenticate.step2.invalid_argument',
                    $e->getMessage(),
                    $e->getCode(),
                    $e
                );
            } catch (ConnectorException $e) {
                $this->middleware->cleanSession();

                throw new ClientException(
                    'authenticate.step2.connection_error',
                    $e->getMessage(),
                    $e->getCode(),
                    $e
                );
            }
        }

        $this->middleware->cleanSession();

        throw new ClientException(
            'authenticate.internal_error',
            'Unknown error - Authentication failed!',
            Exception::CODE_UNKNOWN_ERROR
        );
    }

    /**
     * {@inheritdoc}
     */
    public function requestUserInfo(TokenWrapper $tokenWrapper)
    {
        try {
            return $this->middleware->callProviderEndpoint('userinfo_endpoint', $tokenWrapper->getAccessToken());
        } catch (ConfigurationNotFoundException $e) {
            throw new ClientException(
                'userinfo.endpoint_not_existing',
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        } catch (ConnectorException $e) {
            throw new ClientException(
                'userinfo.connection_error',
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function refreshToken(TokenWrapper $tokenWrapper)
    {
        try {
            return $this->middleware->refreshAccessToken($tokenWrapper);
        } catch (InvalidArgumentException $e) {
            throw new ClientException(
                'refresh_token.invalid_argument',
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        } catch (ConnectorException $e) {
            throw new ClientException(
                'refresh_token.connection_error',
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }
}
