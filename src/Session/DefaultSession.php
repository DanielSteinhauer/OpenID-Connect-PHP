<?php

namespace Raegmaen\OpenIdConnect\Session;

use Raegmaen\OpenIdConnect\Exceptions\SessionException;

class DefaultSession implements SessionInterface
{
    /**
     * Starts a session if necessary.
     */
    public function __construct()
    {
        if (!isset($_SESSION)) {
            session_start();
        }
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * @inheritdoc
     */
    public function get($key)
    {
        if (!$this->has($key)) {
            throw SessionException::createNotFoundException($key);
        }

        return $_SESSION[$key];
    }

    /**
     * @inheritdoc
     */
    public function has($key)
    {
        return isset($_SESSION[$key]);
    }

    /**
     * @inheritdoc
     */
    public function remove($key)
    {
        if (isset($_SESSION)) {
            unset($_SESSION[$key]);
        }
    }
}
