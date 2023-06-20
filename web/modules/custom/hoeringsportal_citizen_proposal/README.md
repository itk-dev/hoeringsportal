# Hoeringsportal citizen proposal module

Code related to citizen proposal functionality.

## Cron jobs to run

For the functionality of the module to work properly, certain cron jobs need
to run.

These jobs may need to be modified to match server environment and server
directory naming.

### Cronjob for finishing overdue proposals

```sh
*/5 * * * * (cd /data/www/deltag_aarhus_dk/htdocs && /usr/local/bin/itkdev-docker-compose-server exec --user deploy phpfpm vendor/bin/drush hoeringsportal-citizen-proposal:finish-overdue-proposals) > /dev/null 2>&1; /usr/local/bin/cron-exit-status -c 'deltag.aarhus.dk' -v $?
```

## Settings

The module supports certain settings in settings.php

```php
// The duration of a proposal voting period.
$settings['proposal_period_length'] = '+180 days';

// The required votes for a proposal to pass.
$settings['proposal_support_required'] = '5000';
```
