# Hoeringsportal citizen proposal module.

Code related to citizen proposal functionality.

## Cron jobs to run

Cronjob for finishing overdue proposals.
```
*/5 * * * * (cd /data/www/deltag_aarhus_dk/htdocs && /usr/local/bin/itkdev-docker-compose-server exec --user deploy phpfpm vendor/bin/drush hoeringsportal-citizen-proposal:finish-overdue-proposals) > /dev/null 2>&1; /usr/local/bin/cron-exit-status -c 'deltag.aarhus.dk' -v $?
```

