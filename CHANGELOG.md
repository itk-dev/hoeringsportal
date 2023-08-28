# Changelog for Høringsportal

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic
Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

* [PR-359](https://github.com/itk-dev/hoeringsportal/pull/359)
  Generalized handling of form surveys. Added survey to support form

## [3.3.0] - 2023-08-25

* [PR-355](https://github.com/itk-dev/hoeringsportal/pull/355)
  Added citizen access check.
* [PR-356](https://github.com/itk-dev/hoeringsportal/pull/356)
  Miscellaneous fixes

## [3.2.0] - 2023-08-23

* [PR-353](https://github.com/itk-dev/hoeringsportal/pull/353)
  Added “Allow email” checkboxes
* [PR-351](https://github.com/itk-dev/hoeringsportal/pull/351)
  Added and used citizen proposal text format
* [PR-350](https://github.com/itk-dev/hoeringsportal/pull/350)
  Added superfluous support proposal button
* [PR-347](https://github.com/itk-dev/hoeringsportal/pull/347)
  Added citizen proposal survey
* [PR-342](https://github.com/itk-dev/hoeringsportal/pull/342)
  Added email and storage consent checkbox on citizen proposal support form.
  Added supporters view.
* [PR-346](https://github.com/itk-dev/hoeringsportal/pull/346)
  Made filter and sort labels visually hidden
* [PR-344](https://github.com/itk-dev/hoeringsportal/pull/344)
  Hid changed date on static page
* [PR-345](https://github.com/itk-dev/hoeringsportal/pull/345)
  Added support redirect URL
* [PR-341](https://github.com/itk-dev/hoeringsportal/pull/341)
  Removed superfluous support proposal button
* [PR-340](https://github.com/itk-dev/hoeringsportal/pull/340)
  Fixed proposal progress bar display
* [PR-337](https://github.com/itk-dev/hoeringsportal/pull/337)
  Updated drupal core 9.5.10 and contrib modules
* [PR-332](https://github.com/itk-dev/hoeringsportal/pull/332)
  Added data storage consent checkbox on citizen proposal form
* [PR-333](https://github.com/itk-dev/hoeringsportal/pull/333)
  Removed email from citizen proposal support form
* [PR-335](https://github.com/itk-dev/hoeringsportal/pull/335)
  Hid “Author email display” from display
* [PR-334](https://github.com/itk-dev/hoeringsportal/pull/334)
  Sent notification mails on citizen proposal creation and publication
* [PR-336](https://github.com/itk-dev/hoeringsportal/pull/336)
  De-authenticated citizens after creating or supporting proposals

## [3.1.0] - 2023-08-04

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

[Unreleased]: https://github.com/itk-dev/hoeringsportal/compare/3.3.0...HEAD
[3.3.0]: https://github.com/itk-dev/hoeringsportal/compare/3.2.0...3.3.0
[3.2.0]: https://github.com/itk-dev/hoeringsportal/compare/3.1.0...3.2.0
[3.1.0]: https://github.com/itk-dev/hoeringsportal/compare/3.0.2...3.1.0
[3.0.2]: https://github.com/itk-dev/hoeringsportal/compare/3.0.1...3.0.2
[3.0.1]: https://github.com/itk-dev/hoeringsportal/compare/3.0.0...3.0.1
[3.0.0]: https://github.com/itk-dev/hoeringsportal/compare/1.4.3...3.0.0
[1.4.3]: https://github.com/itk-dev/hoeringsportal/compare/1.0.0...1.4.3
[1.0.0]: https://github.com/itk-dev/hoeringsportal/releases/tag/1.0.0
