# OpenID Connect setup

Add this to `settings.local.php` and edit to match your actual setup.

```sh
$config['openid_connect.client.generic']['settings']['client_id'] = '…'; // Get this from your IdP provider
$config['openid_connect.client.generic']['settings']['client_secret'] = '…'; // Get this from your IdP provider
$config['openid_connect.client.generic']['settings']['authorization_endpoint'] = '…'; // Get this from your OpenID Connect Discovery endpoint
$config['openid_connect.client.generic']['settings']['token_endpoint'] = '…'; // Get this from your OpenID Connect Discovery endpoint

$config['openid_connect.settings']['role_mappings']['administrator'] = ['GG-Rolle-B2C-Høringsportalen-Administrator'];
$config['openid_connect.settings']['role_mappings']['editor']        = ['GG-Rolle-B2C-Høringsportalen-Redaktør'];

// Custom label on log in button.
$settings['locale_custom_strings_en'][''] = [
    'Log in with @client_title' => 'Log in with OpenID Connect (employee)',
];

$settings['locale_custom_strings_da'][''] = [
   'Log in with @client_title' => 'Log ind med OpenID Connect (medarbejderlogin)',
];
```

Use `drush` to check your actual configuration (with `--include-overridden` to
include your config from `settings.local.php`):

```sh
docker compose exec phpfpm vendor/bin/drush config:get --include-overridden openid_connect.client.generic
docker compose exec phpfpm vendor/bin/drush config:get --include-overridden openid_connect.settings
```

## Citizen authentification

See the [Høringsportalen OpenID Connect
module](../web/modules/custom/hoeringsportal_openid_connect/README.md) for
details on configuring OpenID Connect authentification for citizens.

For local testing we use [OpenId Connect Server
Mock](https://github.com/Soluto/oidc-server-mock) for (almost) real OpenID
Connect. Users and their claims are defined in
[`docker-compose.override.yml`](../../../../docker-compose.override.yml).

## Employee authentification

```php
# settings.local.php
$config['openid_connect.settings']['role_mappings']['administrator'][] = 'administrator';
$config['openid_connect.settings']['role_mappings']['editor'][] = 'editor';
```

Create department taxonomy terms:

```shell
docker compose exec phpfpm vendor/bin/drush php:eval "\Drupal\taxonomy\Entity\Term::create(['name' => 'Department A', 'vid' => 'department', 'status' => 1])->save();"
docker compose exec phpfpm vendor/bin/drush php:eval "\Drupal\taxonomy\Entity\Term::create(['name' => 'Department B', 'vid' => 'department', 'status' => 1])->save();"
```

## Debugging OpenID Connect authentification

```sh
docker compose --profile oidc up --detach
```

During development it can be useful to see the user info we actually get during
OpenID Connect authentification, and to do this you can apply the patch
[openid_connect-debug-userinfo.patch](../patches/openid_connect-debug-userinfo.patch):

```sh
docker compose exec phpfpm patch --strip=1 --input=patches/openid_connect-debug-userinfo.patch
```

After applying the patch and succesfully logging in, the actual userinfo
received can be inspected with

```sh
docker compose exec phpfpm vendor/bin/drush watchdog:show --type=itkdev-debug --extended
```

Remove (reverse) the patch with

```sh
docker compose exec phpfpm patch --strip=1 --input=patches/openid_connect-debug-userinfo.patch --reverse
```
