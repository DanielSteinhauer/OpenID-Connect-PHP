PHP OpenID Connect Client
========================
PHP library to authenticate users against an identity provider using the [OpenId Connect protocol](http://openid.net/specs/openid-connect-core-1_0.html).  
Use cases implemented:
1. [Authentication using the Authorization Code Flow](http://openid.net/specs/openid-connect-core-1_0.html#CodeFlowAuth)
2. Refreshing access token with refresh token
## Requirements
 1. PHP 5.6 or greater
 2. CURL extension
 3. JSON extension

## Install
Composer
```
composer require raegmaen/openid-connect-php
```

## License
[Apache License, Version 2.0](/LICENSE.txt)

## Example:

```php
$openIdConnectClient = OpenIdConnectFactory::create(
    $providerUrl,
    $clientId,
    $clientSecret,
    $callbackUrl
);

$authenticationResult = $this->openIdConnectClient->authenticate($requestData);
if ($authenticationResult instanceof UserRedirect) {
    // Redirect user to given Url
}

$claims = $authenticationResult->getIdToken()->getClaims();

$name = $claims->get('given_name');
```
