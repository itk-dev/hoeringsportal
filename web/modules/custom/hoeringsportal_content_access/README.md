# Content access

## Departments

A user must belong to one or more departments and the user gets `edit` and
`update` access only to content belonging to at least one of the departments.

* Departments are taxonomy terms which can be created and editing on
  `/admin/structure/taxonomy/manage/department/overview`.
* The content list (`/admin/content`) is filtered to only show content that a
  user has edit access to.
* When a user created a new piece of content, all the user's departments are
  initially selected on the node form.
* When a user edits a piece of content, only the user's departments are
  available for selection and at least one of the departments must be selected
  (to prevent the user from accidentally losing access to the content).

## Bypassing department access check

```php
# settings.local.php
$settings['hoeringsportal_content_access']['bypass_department_access_check'] = TRUE;
```
