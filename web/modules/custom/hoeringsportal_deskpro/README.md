# Høringsportal – Deskpro

## Configuration

Add configuration in `settings.local.php`:

```php
$settings['hoeringsportal_deskpro.deskpro'] = [
	'deskpro_url' => 'https://example.deskpro.com',
	// https://example.deskpro.com/agent/#admin:/apps/api_keys/go-create
	'api_code_key' => '1:ABCDEFGHIJKLMNOPQRSTUVWXY',
	// https://example.deskpro.com/agent/#admin:/tickets/fields
	'hearing_field_id' => 13,
	// https://example.deskpro.com/agent/#admin:/tickets/ticket_deps
	'hearing_department_id' => 1,
	// Cache ttl in seconds
	'cache_ttl' => 60,
];
```
