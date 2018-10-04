# Høringsportal – Deskpro

## Configuration

Edit configuration on `/admin/site-setup/deskpro`.

Add configuration in `settings.local.php`:

```php
$settings['hoeringsportal_deskpro.deskpro'] = [
  'deskpro_url' => 'https://example.deskpro.com',
  // https://example.deskpro.com/agent/#admin:/apps/api_keys/go-create
  'api_code_key' => '1:ABCDEFGHIJKLMNOPQRSTUVWXY',
  // Departments (ids) that are available for users.
  'available_departments' => [1],
  'departments' => [
    1 => 'Høringsbidrag',
    2 => 'Teknik og Miljø - Høringsbidrag',
    5 => 'Sundhed og Omsorg - Høringsbidrag',
    6 => 'Børn og Unge - Høringsbidrag',
    7 => 'Kultur og Borgerservice - Høringsbidrag',
    8 => 'Sociale Forhold og Beskæftigelse - Høringsbidrag',
  ],
  // https://example.deskpro.com/agent/#admin:/tickets/fields
  'ticket_custom_fields' => [
    // Field name => field id.
    'hearing_id' => 28,
    'hearing_name' => 30,
    'edoc_id' => 15,
    'pdf_download_url' => 22,
    'representation' => 2,
    'address' => 1,
    'geolocation' => 31,
    'organization' => 7,
  ],
  // Token used for updating Deskpro data in drupal.
  'x-deskpro-token' => 'hat og briller',
  // Cache ttl in seconds
  'cache_ttl' => 60,
];
```

## API

Check out `/hoeringsportal_deskpro/api/docs` for details.

## Drush commands

```
hoeringsportal:deskpro:synchronize-data
hoeringsportal:deskpro:synchronize-endpoint
```
Synchronizes hearing data with Deskpro.
Shows information on data synchronization endpoint.
