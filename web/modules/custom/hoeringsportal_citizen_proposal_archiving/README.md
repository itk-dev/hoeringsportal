# Citizen proposal archiving

Set up a [cron](https://en.wikipedia.org/wiki/Cron) job to process the Citizen
proposal archiving (`hoeringsportal_citizen_proposal_archiving`) queue
regularly, e.g. [daily](https://crontab.guru/daily)

```shell
0 0 * * * docker compose exec phpfpm vendor/bin/drush advancedqueue:queue:process hoeringsportal_citizen_proposal_archiving
```

## Settings

```php
# settings.local.php
$settings['hoeringsportal_citizen_proposal_archiving'] = [
  'archiver' => [
    'get_organized' => [
      'api_url' => …,
      'api_username' => …,
      'api_password' => …,
      // Optional.
      'admin_url' => …,
    ],
  ],
];
];
```
