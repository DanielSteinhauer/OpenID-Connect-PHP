<?php

namespace Raegmaen\OpenIdConnect\ValueObjects;

use Raegmaen\OpenIdConnect\Exceptions\Exception;
use Raegmaen\OpenIdConnect\Exceptions\InvalidArgumentException;

class UserRedirect
{
    const METHOD_POST = 'POST';
    const METHOD_GET  = 'GET';

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $method;

    /**
     * @var array
     */
    private $queryParameters;

    /**
     * Closed UserRedirect constructor.
     *
     * @param string $url
     * @param string $method
     * @param array  $queryParameters
     */
    private function __construct($url, $method, array $queryParameters)
    {
        $this->url = $url;
        $this->method = $method;
        $this->queryParameters = $queryParameters;
    }

    /**
     * @param string $url
     * @param string $method
     * @param array  $queryParameters
     *
     * @return UserRedirect
     * @throws InvalidArgumentException
     */
    public static function create($url, $method, array $queryParameters)
    {
        if (!is_string($method) || !in_array($method, [self::METHOD_GET, self::METHOD_POST])) {
            throw new InvalidArgumentException(
                'user_redirect.construct',
                sprintf('Method "%s" is not a valid method!', var_export($method)),
                Exception::CODE_USER_REDIRECT
            );
        }

        if (!is_string($url)) {
            throw new InvalidArgumentException(
                'user_redirect.construct',
                'Url is not a string!',
                Exception::CODE_USER_REDIRECT
            );
        }

        return new self($url, $method, $queryParameters);
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return array
     */
    public function getQueryParameters()
    {
        return $this->queryParameters;
    }

    public function getRedirectDestination()
    {
        return $this->url . '?' . http_build_query($this->queryParameters, null, '&');
    }
}
