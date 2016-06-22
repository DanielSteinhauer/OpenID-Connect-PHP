<?php

namespace Raegmaen\OpenIdConnect;

use Raegmaen\OpenIdConnect\Exceptions\ConnectorException;
use Raegmaen\OpenIdConnect\Exceptions\Exception;
use Raegmaen\OpenIdConnect\Exceptions\InvalidArgumentException;

/**
 * Class to connect to openid connect server.
 */
class Connector
{
    /**
     * @var string
     */
    private $providerUrl;

    /**
     * Connector constructor.
     *
     * @param string $providerUrl
     */
    public function __construct($providerUrl)
    {
        $this->providerUrl = $providerUrl;
    }

    /**
     * @param $providerUrl
     *
     * @return Connector
     * @throws InvalidArgumentException
     */
    public static function create($providerUrl)
    {
        if (!is_string($providerUrl) || strlen($providerUrl) < 1) {
            throw new InvalidArgumentException(
                'connector.construct',
                sprintf('Provided "providerUrl" is invalid: "%s"', $providerUrl),
                Exception::CODE_PROVIDER_URL
            );
        }

        return new self($providerUrl);
    }

    /**
     * @return string
     */
    public function getProviderUrl()
    {
        return $this->providerUrl;
    }

    /**
     * @param string $url
     * @param string $post_body
     * @param array  $headers
     * @param null   $certPath
     * @param null   $httpProxy
     *
     * @return array
     * @throws ConnectorException
     */
    public function callProvider(
        $url,
        $post_body = null,
        $headers = [],
        $certPath = null,
        $httpProxy = null
    ) {
        $curlResponse = $this->callCurl($url, $post_body, $headers, $certPath, $httpProxy);

        $jsonDecoded = (array) json_decode($curlResponse, true);
        if (!is_array($jsonDecoded)) {
            throw new ConnectorException(
                'call',
                sprintf('Request: %s, Response could not be decoded.', $url),
                Exception::CODE_CURL
            );
        }

        return $jsonDecoded;
    }

    /**
     * @param string $url
     *
     * @return string
     */
    public function prependProviderUrl($url)
    {
        $completeUrl = rtrim($this->providerUrl, '/') . '/';
        $completeUrl .= ltrim((string) $url, '/');

        return $completeUrl;
    }

    /**
     * @param         $url
     * @param string  $post_body If this is set the post type will be POST
     * @param array() $headers   Extra headers to be send with the request.
     *                           Format as 'NameHeader: ValueHeader'
     * @param string  $certPath
     * @param string  $httpProxy
     *
     * @return string
     * @throws ConnectorException
     */
    private function callCurl(
        $url,
        $post_body = null,
        $headers = [],
        $certPath = null,
        $httpProxy = null
    ) {
        // OK cool - then let's create a new cURL resource handle
        $ch = curl_init();

        // Determine whether this is a GET or POST
        if ($post_body != null) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_body);

            // Default content type is form encoded
            $content_type = 'application/x-www-form-urlencoded';

            // Determine if this is a JSON payload and add the appropriate content type
            if (is_object(json_decode($post_body))) {
                $content_type = 'application/json';
            }

            // Add POST-specific headers
            $headers[] = "Content-Type: {$content_type}";
            $headers[] = 'Content-Length: ' . strlen($post_body);
        }

        // If we set some headers include them
        if (count($headers) > 0) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        // Set URL to download
        curl_setopt($ch, CURLOPT_URL, $url);

        if (isset($httpProxy)) {
            curl_setopt($ch, CURLOPT_PROXY, $httpProxy);
        }

        // Include header in result? (0 = yes, 1 = no)
        curl_setopt($ch, CURLOPT_HEADER, 0);

        /**
         * Set cert
         * Otherwise ignore SSL peer verification
         */
        if (isset($certPath)) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_CAINFO, $certPath);
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        // Should cURL return or print out the data? (true = return, false = print)
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Timeout in seconds
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        // Download the given URL, and return output
        $output = curl_exec($ch);

        if ($output === false) {
            throw new ConnectorException(
                'call',
                sprintf('Curl error: "%s"', curl_error($ch)),
                Exception::CODE_CURL
            );
        }

        // Close the cURL resource, and free system resources
        curl_close($ch);

        return $output;
    }
}
