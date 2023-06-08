# Høringsportalen OpenID Connect

Define settings in `settings.local.php`:

```php
$settings['hoeringsportal_openid_connect']['openid_connect'] = [
  'clientId'                 => 'client-id',
  'clientSecret'             => 'client-secret',
  'openIDConnectMetadataUrl' => 'http://idp-citizen.hoeringsportal.local.itkdev.dk/.well-known/openid-configuration',
];
```

Start authentication on `/hoeringsportal-openid-connect/authenticate` (route
name: `hoeringsportal_openid_connect.openid_connect_authenticate`). Set
`target-destination` (`OpenIDConnectController::QUERY_STRING_DESTINATION`) in
the query string to set the destination after authentication.

Example:

```php

Link::createFromRoute(
  'Authenticate',
  'hoeringsportal_openid_connect.openid_connect_authenticate',
  [
    OpenIDConnectController::QUERY_STRING_DESTINATION => Url::fromRoute('<current>')->toString(TRUE)->getGeneratedUrl(),
  ]
);
```

## Getting user data

Calling `getUserData` on the `Drupal\hoeringsportal_openid_connect\Helper`
service will return the current user data if any. `

## Local test

Mock authenticating with local test users can be enabled in `settings.local.php`:

```php
// Enable local test mode
$settings['hoeringsportal_openid_connect']['local_test_mode'] = TRUE;

// Define local test users
//   User id => user info (claims)
$settings['hoeringsportal_openid_connect']['local_test_users'] = [
  '1234567890' => [
    'cpr' => '1234567890',
    'name' => 'John Doe',
  ],
  'another-user' => [
    …
  ],
];
```
