# Høringsportal – Deskpro

## Configuration

Add configuration in `settings.local.php`:

```php
$settings['hoeringsportal_deskpro.deskpro'] = [
  'deskpro_url' => 'https://example.deskpro.com',
  // https://example.deskpro.com/agent/#admin:/apps/api_keys/go-create
  'api_code_key' => '1:ABCDEFGHIJKLMNOPQRSTUVWXY',
  // Departments (ids) that are available for users.
  'available_departments' => [1],
  // https://example.deskpro.com/agent/#admin:/tickets/fields
  'ticket_custom_fields' => [
    // Field name => field id.
    'hearing_id' => 13,
    'hearing_name' => 14,
    'edoc_id' => 15,
    'pdf_download_url' => 22,
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
hoeringsportal:deskpro:synchronize-data     Synchronizes hearing data with Deskpro.
hoeringsportal:deskpro:synchronize-endpoint Shows information on data synchronization endpoint.
```
