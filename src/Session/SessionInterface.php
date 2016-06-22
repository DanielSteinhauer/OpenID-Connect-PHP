<?php

namespace Raegmaen\OpenIdConnect\Session;

use Raegmaen\OpenIdConnect\Exceptions\SessionException;

interface SessionInterface
{
    /**
     * @param string $key
     *
     * @return bool
     */
    public function has($key);

    /**
     * @param string $key
     *
     * @return string
     * @throws SessionException
     */
    public function get($key);

    /**
     * Sets the $value for the $key.
     *
     * @param string $key
     * @param string $value
     */
    public function set($key, $value);

    /**
     * @param string $key
     *
     * @return string the removed value
     */
    public function remove($key);
}
