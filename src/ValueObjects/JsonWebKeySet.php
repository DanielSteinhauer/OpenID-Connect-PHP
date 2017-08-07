<?php

namespace Raegmaen\OpenIdConnect\ValueObjects;

use Raegmaen\OpenIdConnect\Exceptions\Exception;
use Raegmaen\OpenIdConnect\Exceptions\InvalidArgumentException;

class JsonWebKeySet
{
    /**
     * @var array
     */
    private $keySet;

    /**
     * @param array $input
     *
     * @return JsonWebKeySet
     * @throws InvalidArgumentException
     */
    public static function createFromArray(array $input)
    {
        if (!isset($input['keys'])) {
            throw new InvalidArgumentException(
                'jwk_set.construct',
                'Invalid key set!',
                Exception::CODE_JWK_SET
            );
        }

        foreach ($input['keys'] as $jwk) {
            if (!isset($jwk['kty'])) {
                throw new InvalidArgumentException(
                    'jwk_set.construct',
                    'Invalid JsonWebKey!',
                    Exception::CODE_JWK_SET
                );
            }

            // Todo: Where are supported JsonWebKeys defined?
            if (!'RSA' == $jwk['kty']) {
                throw new InvalidArgumentException(
                    'jwk_set.construct',
                    'Not supported JsonWebToken type!',
                    Exception::CODE_JWK_SET
                );
            }
        }

        return new self($input);
    }

    /**
     * JsonWebKeySet constructor.
     *
     * @param array $keySet
     */
    private function __construct(array $keySet)
    {
        $this->keySet = $keySet;
    }

    /**
     * @return array
     */
    public function getKeys()
    {
        return $this->keySet['keys'];
    }

    /**
     * @return array
     */
    public function getKeySet()
    {
        return $this->keySet;
    }
}
