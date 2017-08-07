<?php

namespace Raegmaen\OpenIdConnect\Token;

use Raegmaen\OpenIdConnect\Exceptions\Exception;
use Raegmaen\OpenIdConnect\Exceptions\TokenException;
use Raegmaen\OpenIdConnect\Token\OauthAccessToken;

class OauthAccessTokenFactory
{
    const SUPPORTED_TYPE      = 'Bearer';
    const DEFAULT_EXPIRE_TIME = 3600;

    /**
     * @param string      $tokenString
     * @param string      $type
     * @param int|null    $expireIn
     * @param string|null $refreshTokenString
     *
     * @return OauthAccessToken
     * @throws TokenException
     */
    public static function createToken($tokenString, $type, $expireIn = null, $refreshTokenString = null)
    {
        if (null === $expireIn) {
            $expireIn = self::DEFAULT_EXPIRE_TIME;
        }

        if (!isset($responseArray['refresh_token'])) {
            $responseArray['refresh_token'] = null;
        }

        if (!is_string($tokenString) || strlen($tokenString) < 1) {
            throw new TokenException(
                'access.construct.invalid_token_string',
                sprintf('Invalid token string "%s"', $tokenString),
                Exception::CODE_ACCESS_TOKEN
            );
        }

        $expireIn = (int) $expireIn;
        if (0 >= $expireIn) {
            throw new TokenException(
                'access.construct.token_expired',
                "Token already expired!",
                Exception::CODE_ACCESS_TOKEN
            );
        }

        if (!is_string($type) || strlen($type) < 1) {
            throw new TokenException(
                'access.construct.invalid_type',
                sprintf("Invalid type '%s'", $type),
                Exception::CODE_ACCESS_TOKEN
            );
        }

        if (null !== $refreshTokenString && (!is_string($refreshTokenString) || strlen($refreshTokenString) < 1)) {
            throw new TokenException(
                'access.construct.invalid_refresh_token',
                sprintf("Invalid refresh token string '%s'", $refreshTokenString),
                Exception::CODE_ACCESS_TOKEN
            );
        }

        $expiresAt = new \DateTime('@' . (time() + $expireIn));

        return new OauthAccessToken($tokenString, $expiresAt, $type, $refreshTokenString);
    }
}
