# Audit log in hoeringsportalen

This module allows you to configure when to log events using `os2web-audit`.

You can configure the settings in `/admin/config/audit_log`, where you can add a route name in a textarea field.

In addition to the textarea, classes implementing the `EntityInterface` are displayed with checkboxes. These options are
limited based on the `ENABLED_AUDIT_IDS` const defined in `ConfigHelper`.

The `ControllerListener`listens for events triggered when a route is accessed. Each time a route is visited, it
determines whether the visit should be logged or not, based on the current config.
