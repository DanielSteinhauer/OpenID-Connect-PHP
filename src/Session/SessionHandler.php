<?php

namespace Raegmaen\OpenIdConnect\Session;

use Raegmaen\OpenIdConnect\Exceptions\SessionException;
use Raegmaen\OpenIdConnect\Helper\OpenIDConnectHelper;

class SessionHandler
{
    const NONCE = 'openid_connect_nonce';
    const STATE = 'openid_connect_state';
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @param $key
     *
     * @return string
     * @throws SessionException
     */
    public function get($key)
    {
        return $this->session->get($key);
    }

    /**
     * Initializing the session with random
     * values for NONCE and STATE.
     */
    public function initializeSession()
    {
        $this->session->set(self::NONCE, OpenIDConnectHelper::generateRandString());
        $this->session->set(self::STATE, OpenIDConnectHelper::generateRandString());
    }

    /**
     * Removes NONCE and STATE from session.
     */
    public function cleanSession()
    {
        $this->session->remove(self::NONCE);
        $this->session->remove(self::STATE);
    }

    /**
     * @param string $key
     * @param string $value
     *
     * @return bool
     * @throws SessionException
     */
    public function compareValue($key, $value)
    {
        return $this->session->get($key) === $value;
    }
}
