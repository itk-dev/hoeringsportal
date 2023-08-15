# Changelog for Høringsportal

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic
Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]
* [PR-337](https://github.com/itk-dev/hoeringsportal/pull/337)
  Updated drupal core 9.5.10 and contrib modules
  * Upgrading consolidation/output-formatters (4.3.1 => 4.3.2)
  * Upgrading doctrine/reflection (1.2.3 => 1.2.4)
  * Upgrading drupal/better_social_sharing_buttons (4.0.4 => 4.0.6)
  * Upgrading drupal/captcha (1.10.0 => 1.14.0)
  * Upgrading drupal/coder (8.3.20 => 8.3.21)
  * Upgrading drupal/content_fixtures (3.1.3 => 3.1.4)
  * Upgrading drupal/core (9.5.9 => 9.5.10)
  * Upgrading drupal/core-composer-scaffold (9.5.9 => 9.5.10)
  * Upgrading drupal/core-dev (9.5.9 => 9.5.10)
  * Upgrading drupal/core-project-message (9.5.9 => 9.5.10)
  * Upgrading drupal/core-recommended (9.5.9 => 9.5.10)
  * Upgrading drupal/masquerade (2.0.0-rc1 => 2.0.0-rc4)
  * Upgrading drupal/maxlength (2.1.1 => 2.1.2)
  * Upgrading laminas/laminas-escaper (2.9.0 => 2.12.0)
  * Upgrading laminas/laminas-feed (2.17.0 => 2.21.0)
  * Upgrading laminas/laminas-servicemanager (3.20.0 => 3.21.0)
  * Upgrading laminas/laminas-stdlib (3.11.0 => 3.17.0)
  * Upgrading league/csv (9.9.0 => 9.10.0)
  * Upgrading mglaman/phpstan-drupal (1.1.36 => 1.1.37)
  * Upgrading nikic/php-parser (v4.16.0 => v4.17.1)
  * Upgrading phpdocumentor/type-resolver (1.7.2 => 1.7.3)
  * Upgrading phpstan/phpdoc-parser (1.22.1 => 1.23.1)
  * Upgrading phpstan/phpstan (1.10.22 => 1.10.29)
  * Upgrading phpstan/phpstan-deprecation-rules (1.1.3 => 1.1.4)
  * Upgrading phpunit/php-code-coverage (9.2.26 => 9.2.27)
  * Upgrading phpunit/phpunit (9.6.9 => 9.6.10)
  * Upgrading psy/psysh (v0.11.18 => v0.11.20)
  * Upgrading sebastian/global-state (5.0.5 => 5.0.6)
  * Upgrading sirbrillig/phpcs-variable-analysis (v2.11.16 => v2.11.17)
  * Upgrading slevomat/coding-standard (8.13.1 => 8.13.4)
  * Upgrading symfony/http-client (v5.4.25 => v5.4.26)
  * Upgrading symfony/phpunit-bridge (v5.4.25 => v5.4.26)
  * Upgrading symfony/string (v6.3.0 => v6.3.2)
  * Upgrading symfony/var-dumper (v5.4.25 => v5.4.26)
* [PR-332](https://github.com/itk-dev/hoeringsportal/pull/332)
  Added data storage consent checkbox on citizen proposal form
* [PR-333](https://github.com/itk-dev/hoeringsportal/pull/333)
  Removed email from citizen proposal support form
* [PR-335](https://github.com/itk-dev/hoeringsportal/pull/335)
  Hid “Author email display” from display
* [PR-333](https://github.com/itk-dev/hoeringsportal/pull/333)
  Sent notification mails on citizen proposal creation and publication

## [3.1.0] - 2023-08-04 - Citizen proposal

* [PR-331](https://github.com/itk-dev/hoeringsportal/pull/331)
  Bumped `itk-dev/openid-connect` version
* Add node type citizen proposal and fixtures
* Added form for citizens to add proposal
* Added form for supporting proposal
* Added OpenID Connect authentication for citizens
* Added citizen authentication on support proposal form
* Added archiving of citizen proposals

## [3.0.2] - 2023-06-28

* Allow uploading huge files
  [PR-322](https://github.com/itk-dev/hoeringsportal/pull/322)

## [3.0.1] - 2023-06-28

* Applied patch
  <https://www.drupal.org/project/drupal/issues/3222107#comment-15048236> to
  make media stuff work
  [PR-316](https://github.com/itk-dev/hoeringsportal/pull/316)

## [3.0.0] - 2023-06-27

* Upgrade to Drupal 9.5
* Added public meeting content type
* syncronize public meeting dates with [pretix](https://pretix.eu)
* Change backend theme to Claro
* Add fixtures
* Update readme
* Fixed pretix data bug

## [1.4.3]

Updated drupal core 8.6.16

## [1.0.0]

Initial release

[Unreleased]: https://github.com/itk-dev/hoeringsportal/compare/3.1.0...HEAD
[3.1.0]: https://github.com/itk-dev/hoeringsportal/compare/3.0.2...3.1.0
[3.0.2]: https://github.com/itk-dev/hoeringsportal/compare/3.0.1...3.0.2
[3.0.1]: https://github.com/itk-dev/hoeringsportal/compare/3.0.0...3.0.1
[3.0.0]: https://github.com/itk-dev/hoeringsportal/compare/1.4.3...3.0.0
[1.4.3]: https://github.com/itk-dev/hoeringsportal/compare/1.0.0...1.4.3
[1.0.0]: https://github.com/itk-dev/hoeringsportal/releases/tag/1.0.0
