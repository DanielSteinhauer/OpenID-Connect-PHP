<?php

namespace Raegmaen\OpenIdConnect\Token;

use Raegmaen\OpenIdConnect\Exceptions\Exception;
use Raegmaen\OpenIdConnect\Exceptions\InvalidArgumentException;
use Raegmaen\OpenIdConnect\Exceptions\TokenException;
use Raegmaen\OpenIdConnect\ValueObjects\JsonWebKeySet;
use SimpleJWT\InvalidTokenException;
use SimpleJWT\JWT;
use SimpleJWT\Keys\KeySet;

class IdTokenFactory
{
    const SUPPORTED_ENCODING_RS256 = 'RS256';

    /**
     * @param string        $jwtString
     * @param JsonWebKeySet $jwkSet
     *
     * @return IdToken
     * @throws TokenException
     */
    public static function createToken($jwtString, JsonWebKeySet $jwkSet)
    {
        // Use external library to handle decoding
        $simpleJwtKeySet = new KeySet();
        $simpleJwtKeySet->load(json_encode($jwkSet->getKeySet()));
        try {
            // Todo: Check to get expected alg from provider
            $simpleJwtJWT = JWT::decode($jwtString, $simpleJwtKeySet, self::SUPPORTED_ENCODING_RS256);
        } catch (InvalidTokenException $e) {
            throw new TokenException(
                'construct.invalid_id_token',
                $e->getMessage(),
                Exception::CODE_ID_TOKEN,
                $e
            );
        }

        $claims = new IdTokenClaims($simpleJwtJWT->getClaims());

        return new IdToken($jwtString, $claims);
    }

    /**
     * http://openid.net/specs/openid-connect-core-1_0.html#IDTokenValidation
     *
     * @param IdToken $idToken
     * @param         $clientId
     * @param         $providerUrl
     * @param         $sessionNonce
     *
     * @throws TokenException
     */
    public static function verifyToken(IdToken $idToken, $clientId, $providerUrl, $sessionNonce)
    {
        try {
            if (!self::isValidToken($idToken, $clientId, $providerUrl, $sessionNonce)) {
                throw new TokenException(
                    'verify.invalid_id_token',
                    'The received IdToken is invalid.',
                    Exception::CODE_ID_TOKEN
                );
            }
        } catch (InvalidArgumentException $e) {
            throw new TokenException(
                'verify.invalid_id_token',
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * @param IdToken $idToken
     * @param         $clientId
     * @param         $providerUrl
     * @param         $sessionNonce
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    private
    static function isValidToken(
        IdToken $idToken,
        $clientId,
        $providerUrl,
        $sessionNonce
    ) {
        $claims = $idToken->getClaims();

        if ($claims->get('iss') !== $providerUrl) {
            return false;
        }

        $aud = $claims->get('aud');
        if (is_array($aud)) {
            if (!in_array($clientId, $aud)) {
                return false;
            }

            $azp = $claims->get('azp');
            if (isset($azp) && $azp !== $clientId) {
                return false;
            }
        } else {
            if ($aud !== $clientId) {
                return false;
            }
        }

        if (time() >= $claims->get('exp')) {
            return false;
        }

        if ($claims->get('nonce') !== $sessionNonce) {
            return false;
        }

        return true;
    }
}
