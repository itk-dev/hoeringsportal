# Høringsportalen OpenID Connect

This module implements OpenId Connect authentification as a plugin in the
[OpenID Connect](https://www.drupal.org/project/openid_connect) module. Users
are not really authorized and created in Drupal, but their claims are stored in
the session for later retrieval.

## The hack

This module adds a regular OpenID Connect plugin,
`hoeringsportal_openid_connect_citizen` and implements
`hook_openid_connect_pre_authorize` to make sure that no users are ever actually
authorized.

Authorization is started by sending the user to the
`hoeringsportal_openid_connect.redirect_controller.authorize` endpoint
specifying the id of an instance of the `hoeringsportal_openid_connect_citizen`
plugin and the final destination (after authorizing), e.g.

```sh
/hoeringsportal-openid-connect/authorize/my_client?destination=/my-page
```

This will start a proper OpenID Connect authorization cycle during which the
`hook_openid_connect_pre_authorize` implementation stores the user's claims in
the session. In the end the user ends up on
`/hoeringsportal-openid-connect/authorize/?destination=/my-page` where *all*
[Messenger
messages](https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Messenger%21Messenger.php/9)
are deleted[^1] before the user is sent to the final destination (`/my-page`).

## Getting user data

Calling `getUserData` on the `Drupal\hoeringsportal_openid_connect\Helper`
service will return the current user data if any. `Helper::removeUserData` will
remove user data; in effect performing a poor man's sign out.

This module does not (yet?) support properly signing out from the OIDC identity provider.

[^1]: The OpenID Connect module sets some messages complaining about the user
    not being authorized, but we suppress these. And all other messages.
