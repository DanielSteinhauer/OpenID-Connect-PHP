<?php

namespace Raegmaen\OpenIdConnect\Helper;

use Raegmaen\OpenIdConnect\Exceptions\Exception;

/**
 * Helper functions to be used in OpenIdConnect flow.
 */
class OpenIDConnectHelper
{
    /**
     * Checks prerequisites to use OpenIdConnectClient
     *
     * @throws Exception
     */
    public static function checkPrerequisites()
    {
        /**
         * Require the CURL and JSON PHP extensions to be installed
         */
        if (!function_exists('curl_init')) {
            throw new Exception(
                'helper.prerequisite_check',
                'OpenIDConnect needs the CURL PHP extension.',
                Exception::CODE_PREREQUISITE
            );
        }
        if (!function_exists('json_decode')) {
            throw new Exception(
                'helper.prerequisite_check',
                'OpenIDConnect needs the JSON PHP extension.',
                Exception::CODE_PREREQUISITE
            );
        }
    }

    /**
     * Used for arbitrary value generation for nonce and state
     *
     * @return string
     */
    public static function generateRandString()
    {
        return md5(uniqid(rand(), true));
    }
}
