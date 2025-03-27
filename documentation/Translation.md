# Translation

We use [Translation template extractor](https://www.drupal.org/project/potx) to extract translations from custom modules
and themes.

We use the `interface translation` stuff defined in
[locale.api.php](https://git.drupalcode.org/project/drupal/-/blob/11.x/core/modules/locale/locale.api.php).

Extract translations by running

``` shell name=translations:extract
task translations:extract
```

Import (extracted) translations by running

``` shell name=translations:import:all
task translations:import:all
```
