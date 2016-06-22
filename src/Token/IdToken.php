<?php

namespace Raegmaen\OpenIdConnect\Token;

use Raegmaen\OpenIdConnect\Token\IdTokenClaims;

class IdToken
{
    /**
     * @var IdTokenClaims
     */
    private $claims;

    /**
     * @var string
     */
    private $tokenString;

    /**
     * IdToken constructor.
     *
     * @param IdTokenClaims $claims
     * @param string        $tokenString
     */
    public function __construct($tokenString, IdTokenClaims $claims)
    {
        $this->claims = $claims;
        $this->tokenString = $tokenString;
    }

    /**
     * @return IdTokenClaims
     */
    public function getClaims()
    {
        return $this->claims;
    }

    /**
     * @return string
     */
    public function getTokenString()
    {
        return $this->tokenString;
    }
}
