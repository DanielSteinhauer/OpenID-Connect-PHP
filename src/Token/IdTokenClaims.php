<?php

namespace Raegmaen\OpenIdConnect\Token;

use Raegmaen\OpenIdConnect\Exceptions\Exception;
use Raegmaen\OpenIdConnect\Exceptions\InvalidArgumentException;

class IdTokenClaims
{
    /**
     * @var array
     */
    private $claims;

    /**
     * @var array
     */
    private $claimKeys;

    /**
     * IdTokenClaims constructor.
     *
     * @param array $claims
     */
    public function __construct(array $claims = [])
    {
        $this->claims = $claims;
        $this->claimKeys = array_keys($this->claims);
    }

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->claims;
    }

    /**
     * @param string $claim
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    public function has($claim)
    {
        $this->isString($claim);

        return in_array($claim, $this->claimKeys);
    }

    /**
     * @param string $claim
     *
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function get($claim)
    {
        $this->isString($claim);
        if (!$this->has($claim)) {
            throw new InvalidArgumentException(
                'id_token_claims.get',
                sprintf("Claim with key %s doesn't exist.", $claim),
                Exception::CODE_ID_TOKEN_CLAIM
            );
        }

        return $this->claims[$claim];
    }

    /**
     * Returns type of a claim.
     *
     * @param string $claim
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public function type($claim)
    {
        $claimValue = $this->get($claim);

        return gettype($claimValue);
    }

    private function isString($claim)
    {
        if (!is_string($claim)) {
            throw new InvalidArgumentException(
                'id_token_claims.is_string',
                sprintf('Input is not a string! "%s"', (string) $claim),
                Exception::CODE_ID_TOKEN_CLAIM
            );
        }
    }
}
