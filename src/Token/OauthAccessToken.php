<?php

namespace Raegmaen\OpenIdConnect\Token;

class OauthAccessToken
{
    /**
     * @var string
     */
    private $tokenString;

    /**
     * @var \DateTime
     */
    private $expireAt;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string|null
     */
    private $refreshTokenString;

    /**
     * @param string      $tokenString
     * @param \DateTime   $expireAt
     * @param string      $type
     * @param string|null $refreshTokenString
     */
    public function __construct($tokenString, \DateTime $expireAt, $type, $refreshTokenString = null)
    {
        $this->tokenString = $tokenString;
        $this->expireAt = $expireAt;
        $this->type = $type;
        $this->refreshTokenString = $refreshTokenString;
    }

    /**
     * @return string
     */
    public function getTokenString()
    {
        return $this->tokenString;
    }

    /**
     * @return bool
     */
    public function isExpired()
    {
        return $this->expireAt->getTimestamp() < time();
    }

    /**
     * @return \DateTime
     */
    public function getExpireAt()
    {
        return $this->expireAt;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function canBeRefreshed()
    {
        return null !== $this->refreshTokenString;
    }

    /**
     * @return null|string
     */
    public function getRefreshTokenString()
    {
        return $this->refreshTokenString;
    }
}
