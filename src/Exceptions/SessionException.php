<?php

namespace Raegmaen\OpenIdConnect\Exceptions;

class SessionException extends Exception
{
    const CONTEXT_PREFIX = 'oidc.session.';

    /**
     * @param string $key Key of the attribute which couldn't be found.
     *
     * @return SessionException
     */
    public static function createNotFoundException($key)
    {
        return new self(
            'get',
            sprintf('Attribute with key "%s" not found in session.', $key),
            Exception::CODE_SESSION_ERROR
        );
    }
}
