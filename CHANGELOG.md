# Changelog for Høringsportal

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic
Versioning](https://semver.org/spec/v2.0.0.html).

## feature/citizen_proposal

* Add node type citizen proposal
* Add fixtures for citizen proposal
* Add form for citizens to add proposal
* Add social links for citizen proposal
* Add form for supporting proposal
* Add Cypress tests
* Add OpenID Connect authentication for citizens
* Add citizen authentication on support proposal form
* Store hashed citizen identifier in database
* Add [Playwright](https://playwright.dev/) tests

## [Unreleased]

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

[Unreleased]: https://github.com/itk-dev/hoeringsportal/compare/3.0.1...HEAD
[3.0.1]: https://github.com/itk-dev/hoeringsportal/compare/3.0.0...3.0.1
[3.0.0]: https://github.com/itk-dev/hoeringsportal/compare/1.4.3...3.0.0
[1.4.3]: https://github.com/itk-dev/hoeringsportal/compare/1.0.0...1.4.3
[1.0.0]: https://github.com/itk-dev/hoeringsportal/releases/tag/1.0.0
