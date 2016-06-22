<?php
namespace Raegmaen\OpenIdConnect\ValueObjects;

use Raegmaen\OpenIdConnect\Exceptions\ConfigurationNotFoundException;
use Raegmaen\OpenIdConnect\Exceptions\Exception;

/**
 * Contains provider configuration. Loaded from the server.
 */
class ProviderConfiguration
{
    const ISSUER = 'issuer';
    const AUTHORIZATION_ENDPOINT = 'authorization_endpoint';
    const TOKEN_ENDPOINT = 'token_endpoint';
    const JWKS_URI = 'jwks_uri';
    const RESPONSE_TYPES_SUPPORTED = 'response_types_supported';
    const GRANT_TYPES_SUPPORTED = 'grant_types_supported';
    const SUBJECT_TYPES_SUPPORTED = 'subject_types_supported';
    const ID_TOKEN_SIGNING_ALG_VALUES_SUPPORTED = 'id_token_signing_alg_values_supported';
    const TOKEN_ENDPOINT_AUTH_METHODS_SUPPORTED = 'token_endpoint_auth_methods_supported';
    const SCOPES_SUPPORTED = 'scopes_supported';

    private static $requiredConfigurationKeys = [
        self::ISSUER,
        self::AUTHORIZATION_ENDPOINT,
        self::TOKEN_ENDPOINT,
        self::JWKS_URI,
        self::RESPONSE_TYPES_SUPPORTED,
        self::GRANT_TYPES_SUPPORTED,
        self::SUBJECT_TYPES_SUPPORTED,
        self::ID_TOKEN_SIGNING_ALG_VALUES_SUPPORTED,
        self::TOKEN_ENDPOINT_AUTH_METHODS_SUPPORTED,
    ];

    /**
     * @var array
     */
    private $additionalConfiguration;
    /**
     * @var string
     */
    private $issuer;
    /**
     * @var string
     */
    private $autorizationEndpoint;
    /**
     * @var string
     */
    private $tokenEndpoint;
    /**
     * @var string
     */
    private $jwksUri;
    /**
     * @var array
     */
    private $responseTypesSupported;
    /**
     * @var array
     */
    private $grantTypesSupported;
    /**
     * @var array
     */
    private $subjectTypesSupported;
    /**
     * @var array
     */
    private $idTokenSigningAlgValuesSupported;

    /**
     * @var array
     */
    private $tokenEndpointAuthMethodsSupported;

    /**
     * @param array $configuration
     *
     * @return ProviderConfiguration
     * @throws ConfigurationNotFoundException
     */
    public static function createFromArray($configuration = [])
    {
        // ToDo: Check for other defaults
        if (!isset($configuration[self::GRANT_TYPES_SUPPORTED])) {
            $configuration[self::GRANT_TYPES_SUPPORTED] = ['authorization_code', 'implicit'];
        }

        $constructorArguments = [];
        foreach (self::$requiredConfigurationKeys as $requiredConfigurationKey) {
            if (!isset($configuration[$requiredConfigurationKey])) {
                throw new ConfigurationNotFoundException(
                    'construct',
                    sprintf('Configuration key "%s" not found', $requiredConfigurationKey),
                    Exception::CODE_PROVIDER_CONFIGURATION
                );
            }

            $constructorArguments[] = $configuration[$requiredConfigurationKey];
            unset($configuration[$requiredConfigurationKey]);
        }
        $constructorArguments[] = $configuration;

        return new self(...$constructorArguments);
    }

    private function __construct(
        $issuer,
        $authorizationEndpoint,
        $tokenEndpoint,
        $jwksUri,
        $responseTypesSupported,
        $grantTypesSupported,
        $subjectTypesSupported,
        $idTokenSigningAlgValuesSupported,
        $tokenEndpointAuthMethodsSupported,
        $additionalConfiguration = []
    ) {
        $this->issuer = $issuer;
        $this->autorizationEndpoint = $authorizationEndpoint;
        $this->tokenEndpoint = $tokenEndpoint;
        $this->jwksUri = $jwksUri;
        $this->responseTypesSupported = $responseTypesSupported;
        $this->grantTypesSupported = $grantTypesSupported;
        $this->subjectTypesSupported = $subjectTypesSupported;
        $this->idTokenSigningAlgValuesSupported = $idTokenSigningAlgValuesSupported;
        $this->tokenEndpointAuthMethodsSupported = $tokenEndpointAuthMethodsSupported;
        $this->additionalConfiguration = $additionalConfiguration;
    }

    /**
     * @return string
     */
    public function getIssuer()
    {
        return $this->issuer;
    }

    /**
     * @return string
     */
    public function getAutorizationEndpoint()
    {
        return $this->autorizationEndpoint;
    }

    /**
     * @return string
     */
    public function getTokenEndpoint()
    {
        return $this->tokenEndpoint;
    }

    /**
     * @return string
     */
    public function getJwksUri()
    {
        return $this->jwksUri;
    }

    /**
     * @return array
     */
    public function getResponseTypesSupported()
    {
        return $this->responseTypesSupported;
    }

    /**
     * @return array
     */
    public function getGrantTypesSupported()
    {
        return $this->grantTypesSupported;
    }

    /**
     * @return array
     */
    public function getSubjectTypesSupported()
    {
        return $this->subjectTypesSupported;
    }

    /**
     * @return array
     */
    public function getIdTokenSigningAlgValuesSupported()
    {
        return $this->idTokenSigningAlgValuesSupported;
    }

    /**
     * @return array
     */
    public function getTokenEndpointAuthMethodsSupported()
    {
        return $this->tokenEndpointAuthMethodsSupported;
    }

    /**
     * @param string $param
     *
     * @return mixed
     * @throws ConfigurationNotFoundException
     */
    public function getAdditionalConfigurationParam($param)
    {
        if (!isset($this->additionalConfiguration[$param])) {
            throw new ConfigurationNotFoundException(
                'get_additional_configuration',
                sprintf('Configuration "%s" not found!', $param),
                Exception::CODE_PROVIDER_CONFIGURATION
            );
        }

        return $this->additionalConfiguration[$param];
    }

    /**
     * @param string $grantType
     *
     * @return bool
     */
    public function isGrantTypeSupported($grantType)
    {
        return in_array($grantType, $this->grantTypesSupported);
    }

    /**
     * @param string $responseType
     *
     * @return bool
     */
    public function isResponseTypeSupported($responseType)
    {
        return in_array($responseType, $this->responseTypesSupported);
    }

    /**
     * @param string $authMethod
     *
     * @return bool
     */
    public function isTokenEndpointAuthMethodSupported($authMethod)
    {
        return in_array($authMethod, $this->tokenEndpointAuthMethodsSupported);
    }
}
