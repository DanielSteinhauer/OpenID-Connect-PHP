<?php

namespace Raegmaen\OpenIdConnect\Token;

class TokenWrapper
{
    /**
     * @var OauthAccessToken
     */
    private $accessToken;

    /**
     * @var IdToken
     */
    private $idToken;

    /**
     * @param OauthAccessToken $accessToken
     * @param IdToken          $idToken
     */
    public function __construct(OauthAccessToken $accessToken, IdToken $idToken)
    {
        $this->accessToken = $accessToken;
        $this->idToken = $idToken;
    }

    /**
     * @return OauthAccessToken
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @return IdToken
     */
    public function getIdToken()
    {
        return $this->idToken;
    }
}
