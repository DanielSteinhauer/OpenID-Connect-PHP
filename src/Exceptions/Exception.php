<?php

namespace Raegmaen\OpenIdConnect\Exceptions;

/**
 * Exception to group library exceptions.
 *
 * $context     WHERE?  Descriptive, showing path to source of the error.
 * $code        WHAT?   Groups error by affected object/ error reason. E.g. IdToken
 */
class Exception extends \Exception
{
    const CONTEXT_PREFIX = 'oidc.';

    const CODE_DEFAULT_ERROR  = 0;
    const CODE_UNKNOWN_ERROR  = 500;
    const CODE_PROVIDER_ERROR = 510;
    const CODE_SESSION_ERROR  = 520;
    const CODE_PREREQUISITE   = 530;

    // Todo: Group errors somehow. E.g. By process (Input, Process, Output)?
    const CODE_RESPONSE_TYPE          = 100;
    const CODE_GRANT_TYPE             = 110;
    const CODE_STATE                  = 120;
    const CODE_PROVIDER_URL           = 130;
    const CODE_CURL                   = 140;
    const CODE_AUTH_METHOD            = 150;
    const CODE_SCOPE                  = 160;
    const CODE_AUTHORIZATION_CODE     = 170;
    const CODE_CLIENT_ID              = 180;
    const CODE_CLIENT_SECRET          = 190;
    const CODE_REDIRECT_URL           = 200;
    const CODE_AUTH_PARAMS            = 210;
    const CODE_ID_TOKEN               = 220;
    const CODE_ID_TOKEN_CLAIM         = 230;
    const CODE_JWK_SET                = 240;
    const CODE_ACCESS_TOKEN           = 250;
    const CODE_PROVIDER_CONFIGURATION = 260;
    const CODE_TOKEN_WRAPPER          = 270;
    const CODE_USER_REDIRECT          = 280;
    const CODE_PROMPT                 = 290;

    /**
     * Structured with "."
     *
     * @var string
     */
    protected $context;

    /**
     * Exception constructor.
     *
     * @param string         $context
     * @param string         $message
     * @param int            $code
     * @param Exception|null $previous
     */
    public function __construct($context, $message, $code = self::CODE_DEFAULT_ERROR, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * @return string
     */
    public function getContext()
    {
        return static::CONTEXT_PREFIX . $this->context;
    }

    /**
     * @return string
     */
    public function getCompleteMessage()
    {
        return $this->getMessage() . ' (' . $this->getContext() . ' ' . $this->getCode() . ')';
    }
}
