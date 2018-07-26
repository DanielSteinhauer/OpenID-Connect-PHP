<?php

namespace Raegmaen\OpenIdConnect;

use Raegmaen\OpenIdConnect\Exceptions\ClientException;
use Raegmaen\OpenIdConnect\Token\TokenWrapper;
use Raegmaen\OpenIdConnect\ValueObjects\UserRedirect;

/**
 * Interface ClientInterface
 *
 * This interface contains the following functionalities:
 * 1. **authenticate**: Perform all steps of OpenId Connect code flow.
 * 2. **refreshToken**: Refresh Oauth access token (inside the TokenWrapper) using an Oauth refresh token.
 * 3. **requestUserInfo**: Requests user information from OpenId Connect UserInfo endpoint.
 */
interface ClientInterface
{
    /**
     * Method to perform OpenId Connect Code flow.
     *
     * Parameter $requestData is only needed after the user returns from the Identity Provider (IP). Then it needs to
     * contain the request GET parameters
     *
     * If UserRedirect object is returned, redirect the user to UserRedirect::getRedirectDestination()
     *
     * @param array $requestData
     *
     * @return TokenWrapper|UserRedirect
     *
     * @throws ClientException
     */
    public function authenticate($requestData = []);

    /**
     * Using the User Info endpoint of OpenId Connect, this function returns an associative array of user information.
     * http://openid.net/specs/openid-connect-core-1_0.html#UserInfo
     *
     * @param TokenWrapper $tokenWrapper
     *
     * @return array
     *
     * @throws ClientException
     */
    public function requestUserInfo(TokenWrapper $tokenWrapper);

    /**
     * Provides a new TokenWrapper with refreshed tokens. It MUST refresh the access token and CAN refresh the id token.
     *
     * @param TokenWrapper $tokenWrapper
     *
     * @return TokenWrapper
     *
     * @throws ClientException
     */
    public function refreshToken(TokenWrapper $tokenWrapper);
}