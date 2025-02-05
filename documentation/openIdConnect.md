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

Use `drush` to check your actual configuration (with `--include-overridden` to include your config from
`settings.local.php`):

```sh
docker compose exec phpfpm vendor/bin/drush config:get --include-overridden openid_connect.client.generic
docker compose exec phpfpm vendor/bin/drush config:get --include-overridden openid_connect.settings
```

## Citizen authentification

See the [Høringsportalen OpenID Connect module](../web/modules/custom/hoeringsportal_openid_connect/README.md) for
details on configuring OpenID Connect authentification for citizens.

For local testing we use [OpenId Connect Server Mock](https://github.com/Soluto/oidc-server-mock) for (almost) real
OpenID Connect. Users and their claims are defined in
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

### Local OIDC test

During (local) development we use [OpenId Connect Server Mock](https://github.com/Soluto/oidc-server-mock) (cf.
[`docker-compose.oidc.yml`](docker-compose.oidc.yml) which is
[included](https://docs.docker.com/compose/how-tos/multiple-compose-files/include/) in
[`docker-compose.override.yml`](docker-compose.override.yml)).

#### Employees

| Username            | Password             | Groups        |
|---------------------|----------------------|---------------|
| department1-admin   | department1-admin    | administrator |
| department2-editor  | department2-editor   | editor        |
| department3-editor  | department3-editor   | editor        |

## Debug OIDC

During development it can be useful to see the user info we actually get during OpenID Connect authentification, and to
do this you can apply the patch [openid_connect-debug-userinfo.patch](../patches/openid_connect-debug-userinfo.patch):

```sh
docker compose exec phpfpm patch --strip=1 --input=patches/openid_connect-debug-userinfo.patch
```

After applying the patch and succesfully logging in, the actual userinfo received can be inspected with

```sh
docker compose exec phpfpm vendor/bin/drush watchdog:show --type=itkdev-debug --extended
```

Remove (reverse) the patch with

```sh
docker compose exec phpfpm patch --strip=1 --input=patches/openid_connect-debug-userinfo.patch --reverse
```

## Mock idp api

To mock the api we are using [dotronglong/faker](https://github.com/dotronglong/faker/)s [docker
setup](https://github.com/dotronglong/faker/wiki/Getting-Started-%5BDocker%5D). ipd_mock_api in
[`docker-compose.oidc.yml`](docker-compose.oidc.yml).

The json files with mock returns are located in the `mocks` folder in the root of the project.

To test if this works, patiently wait for:

```sh
docker compose --profile oidc up --detach
```

To test if it works, run (should return something starting with `HTTP/1.1 200 OK`)

```sh
curl -d '{}' "http://$(docker compose --profile oidc port idp_mock_api 3030)/users"
```

or

```sh
docker compose exec phpfpm curl http://idp_mock_api:3030/users --include --request POST
```

Now delta sync can be tested. The config:

```yaml
drupal:
  # Built in drupal user deletion procedures
  # https://www.drush.org/12.x/commands/user_cancel/ 
  user_cancel_method: user_cancel_reassign
  # user_id_field is the field that determines which user to block/delete
  # If mail is chosen, the deletion match is on the drupal user mail.
  user_id_field: mail
azure:
  # Azure security key
  security_key: security_key
  # Azure Client secret
  client_secret: client_secret
  # Uri should be set to above mention mock uri (http://idp_mock_api:3030/users)
  uri: ''
  # This is the field that will be compared ot the above user_id_field
  user_id_claim: userprincipalname
include:
  providers:
    # Determines if the deletion of users is run on all users (0) or users connected to a provider (openid_connect__dot__generic)
    'openid_connect__dot__generic': 'openid_connect.generic'
exclude:
  roles:
    citizen_proposal_editor: 0
    editor: 0
    administrator: 0
    project_editor: 0
  users:
    - '1'
```

run to test:

```sh
task drush -- azure_ad_delta_sync:run --dry-run
```

### Mocks

The mocks can be found in the directory `mocks`, the response should contain the field mentioned in `user_id_claim` in
above config file (here, `userprincipalname`).

```json
{
  "request": {
    "method": "POST",
    "path": "/users"
  },
  "response": {
    "body": [
      {
        "userprincipalname": "department3-editor@example.com"
      },
    ]
  }
}
```

### Test delta sync

```sh
docker compose --profile oidc up --detach
./test-delta-sync
```
