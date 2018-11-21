Changelog
=========

## Version 0.2.1

* Update `kelvinmo/simplejwt` to version 0.1.6 supporting newer versions of OpenSSL > 1.0

## Version 0.2.0

* Added support for oidc code flow option `prompt`
* Add support for PSR-16 SimpleCache, to cache provider configuration.
* Fix issue: [Previous exception needs to be from library namespace](https://github.com/raegmaen/OpenID-Connect-PHP/issues/1)
* Introduce ClientInterface incl. more documentation of exposed methods.
* Add session cleaning in case of error during authentication.

## Version 0.1.1

* Composer.json also contains license key

## Version 0.1.0

* Initial release
* Implemented use cases in openid connect protocol:
  * [Authentication using the Authorization Code Flow](http://openid.net/specs/openid-connect-core-1_0.html#CodeFlowAuth)
