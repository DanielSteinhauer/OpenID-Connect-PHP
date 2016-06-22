<?php

namespace Raegmaen\OpenIdConnect\ValueObjects;

use Raegmaen\OpenIdConnect\Exceptions\Exception;
use Raegmaen\OpenIdConnect\Exceptions\InvalidArgumentException;

class AuthorizationCode
{
    /**
     * @var string
     */
    private $code;

    /**
     * AuthorizationCode constructor.
     *
     * @param string $code
     */
    private function __construct($code)
    {
        $this->code = $code;
    }

    public static function create($code)
    {
        if (!is_string($code)) {
            throw new InvalidArgumentException(
                'authorization_code.construct',
                'Given code is not a string!',
                Exception::CODE_AUTHORIZATION_CODE
            );
        }

        return new self($code);
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    function __toString()
    {
        return $this->code;
    }
}
