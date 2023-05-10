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
docker-compose exec phpfpm vendor/bin/drush config:get --include-overridden openid_connect.client.generic
docker-compose exec phpfpm vendor/bin/drush config:get --include-overridden openid_connect.settings
```
