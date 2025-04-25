# Audit log in hoeringsportalen

This module allows you to configure when to log events using `os2web-audit`.

You can configure the settings in `/admin/config/audit_log`, where you can add a
route name in a textarea field.

In addition to the textarea, classes implementing the `EntityInterface` are
displayed with checkboxes. These options are limited based on the
`ENABLED_AUDIT_IDS` const defined in `ConfigHelper`.

The `ControllerListener`listens for events triggered when a route is accessed.
Each time a route is visited, it determines whether the visit should be logged
or not, based on the current config.

This module logs when a user visits a page, if this page is configured to be
logged.

## Config structure

The config is structured in two parts.

The first part is `types`, which goes through the defined types in the Drupal
installation (with a hardcoded limitation in the
[ConfigHelper](https://github.com/itk-dev/hoeringsportal/blob/f454ccf38a6e0b8e2d2eb85b0982ab0d2be43623/web/modules/custom/hoeringsportal_audit_log/src/Helpers/ConfigHelper.php#L14)).
Some types have bundles, e.g.
[`Node`](https://api.drupal.org/api/drupal/core%21modules%21node%21src%21Entity%21Node.php/class/Node/8.9.x),
the bundles are then the sub types that can be logged. Some types does not have
bundles, e.g.
[`User`](https://api.drupal.org/api/drupal/core%21modules%21user%21src%21Entity%21User.php/class/User/9),
these are then themselves the subtypes that can be logged.

The second part is `routes_to_audit`, this is YAML and contains `url_pattern` and
`routes`. The routes of pages that should be audit logged. The url pattern use
the current URI, if the expression is matched it creates an audit log.

The second part is

```yml
types:
  node:
    citizen_proposal:
      -
        entity__dot__node__dot__version_history: entity__dot__node__dot__version_history # Enabled
        entity__dot__node__dot__canonical: 0
        entity__dot__node__dot__delete_form: 0
        entity__dot__node__dot__delete_multiple_form: 0
        entity__dot__node__dot__edit_form: 0
        entity__dot__node__dot__revision: 0
        entity__dot__node__dot__display: 0
    hearing:
      -
        entity__dot__node__dot__canonical: 0
        entity__dot__node__dot__delete_form: 0
        entity__dot__node__dot__delete_multiple_form: 0
        entity__dot__node__dot__edit_form: 0
        entity__dot__node__dot__version_history: 0
        entity__dot__node__dot__revision: 0
        entity__dot__node__dot__display: 0
    landing_page:
      -
        entity__dot__node__dot__canonical: 0
        entity__dot__node__dot__delete_form: 0
        entity__dot__node__dot__delete_multiple_form: 0
        entity__dot__node__dot__edit_form: 0
        entity__dot__node__dot__version_history: 0
        entity__dot__node__dot__revision: 0
        entity__dot__node__dot__display: 0
    page_map:
      -
        entity__dot__node__dot__canonical: 0
        entity__dot__node__dot__delete_form: 0
        entity__dot__node__dot__delete_multiple_form: 0
        entity__dot__node__dot__edit_form: 0
        entity__dot__node__dot__version_history: 0
        entity__dot__node__dot__revision: 0
        entity__dot__node__dot__display: 0
    project:
      -
        entity__dot__node__dot__canonical: 0
        entity__dot__node__dot__delete_form: 0
        entity__dot__node__dot__delete_multiple_form: 0
        entity__dot__node__dot__edit_form: 0
        entity__dot__node__dot__version_history: 0
        entity__dot__node__dot__revision: 0
        entity__dot__node__dot__display: 0
    project_main_page:
      -
        entity__dot__node__dot__canonical: 0
        entity__dot__node__dot__delete_form: 0
        entity__dot__node__dot__delete_multiple_form: 0
        entity__dot__node__dot__edit_form: 0
        entity__dot__node__dot__version_history: 0
        entity__dot__node__dot__revision: 0
        entity__dot__node__dot__display: 0
    project_page:
      -
        entity__dot__node__dot__canonical: 0
        entity__dot__node__dot__delete_form: 0
        entity__dot__node__dot__delete_multiple_form: 0
        entity__dot__node__dot__edit_form: 0
        entity__dot__node__dot__version_history: 0
        entity__dot__node__dot__revision: 0
        entity__dot__node__dot__display: 0
    public_meeting:
      -
        entity__dot__node__dot__canonical: 0
        entity__dot__node__dot__delete_form: 0
        entity__dot__node__dot__delete_multiple_form: 0
        entity__dot__node__dot__edit_form: 0
        entity__dot__node__dot__version_history: 0
        entity__dot__node__dot__revision: 0
        entity__dot__node__dot__display: 0
    static_page:
      -
        entity__dot__node__dot__canonical: 0
        entity__dot__node__dot__delete_form: 0
        entity__dot__node__dot__delete_multiple_form: 0
        entity__dot__node__dot__edit_form: 0
        entity__dot__node__dot__version_history: 0
        entity__dot__node__dot__revision: 0
        entity__dot__node__dot__display: 0
  user:
    user:
      -
        entity__dot__user__dot__canonical: entity__dot__user__dot__canonical # Enabled
        entity__dot__user__dot__edit_form: entity__dot__user__dot__edit_form # Enabled
        entity__dot__user__dot__cancel_form: 0
        entity__dot__user__dot__collection: 0
        entity__dot__user__dot__display: 0
routes_to_audit:
  url_pattern:
  # https://www.php.net/manual/en/reference.pcre.pattern.syntax.php
    - '/^\/admin\/content\?title=&type=All&status=1$/'
  routes:
    - node.add
```

The weird `__dot__` are because [Drupal will not accept dots in configuration
keys](https://www.drupal.org/node/2297311).
