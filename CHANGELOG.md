# Changelog for Høringsportal

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic
Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [4.2.2] - 2024-04-10

* [PR-392](https://github.com/itk-dev/hoeringsportal/pull/392)
  1152: Fixed rendering of headers

## [4.2.1] - 2024-04-04

* [pr-390](https://github.com/itk-dev/hoeringsportal/pull/390)
  Fix logout and newsletter banner

## [4.2.0] - 2024-04-04

* [pr-386](https://github.com/itk-dev/hoeringsportal/pull/386)
  Design fixes:
  * Main nav visuals
  * Search remove repeated icons
  * Make search available on mobile
  * Mobile nav tweaks
  * Upgrade to Bootstrap v5
  * Add and apply twig coding standards [pr-388]
  * Upgrade all frontend dependencies [pr-387]

## [4.1.1] - 2024-03-21

* [pr-385](https://github.com/itk-dev/hoeringsportal/pull/385)
  Fixed initialization error

## [4.1.0] - 2024-03-11

* [PR-383](https://github.com/itk-dev/hoeringsportal/pull/383)
  Added check for numbers in person name
* [PR-379](https://github.com/itk-dev/hoeringsportal/pull/379)
  Disabled eDoc casefile ID field on new hearings
* [PR-378](https://github.com/itk-dev/hoeringsportal/pull/378)
  New main menu, new search.

## [4.0.1] - 2024-01-31

* [PR-382](https://github.com/itk-dev/hoeringsportal/pull/382)
  Updated custom Drush commands to work with Drush 12.

## [4.0.0] - 2024-01-24

* [PR-375](https://github.com/itk-dev/hoeringsportal/pull/375)
  Updated to Drupal 10
* [PR-343](https://github.com/itk-dev/hoeringsportal/pull/343)
  Make custom modules and themes pass code analysis and compatible with d10
  Update contrib modules (Major versions):
  * better_exposed_filters
  * captcha
  * color_field
  * openid_connect
  * search_autocomplete
  * toolbar_visibility
  * twig_tweak
  * viewsreference

## [3.6.0] - 2023-12-20

* [PR-376](https://github.com/itk-dev/hoeringsportal/pull/376)
  Added GIS map token

## [3.5.3] - 2023-11-08

* [PR-370](https://github.com/itk-dev/hoeringsportal/pull/370)
  Add additional fixtures
* [PR-374](https://github.com/itk-dev/hoeringsportal/pull/374)
  Fix hidden mobile filters
* [PR-367](https://github.com/itk-dev/hoeringsportal/pull/367)
  Added and used Editor Advanced link

## [3.5.2] - 2023-11-07

* [PR-372](https://github.com/itk-dev/hoeringsportal/pull/372)
  Limited text formats on content
* [PR-371](https://github.com/itk-dev/hoeringsportal/pull/371)
  Cleaned up archiving of citizen proposals

## [3.5.0] - 2023-08-30

* [PR-363](https://github.com/itk-dev/hoeringsportal/pull/363)
  Fixed issue with storing names containing non-ascii characters
* [PR-364](https://github.com/itk-dev/hoeringsportal/pull/364)
  Fixed sorting of citizen proposals
* [PR-365](https://github.com/itk-dev/hoeringsportal/pull/365)
  Fixed use of private temp store

## [3.4.0] - 2023-08-29

* [PR-361](https://github.com/itk-dev/hoeringsportal/pull/361)
  Added cancel proposal redirect URL
* [PR-360](https://github.com/itk-dev/hoeringsportal/pull/360)
  Cleaned up display of checkboxes
* [PR-359](https://github.com/itk-dev/hoeringsportal/pull/359)
  Generalized handling of form surveys. Added survey to support form
* [PR-358](https://github.com/itk-dev/hoeringsportal/pull/358)
  Fixed display of description and counter

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

[Unreleased]: https://github.com/itk-dev/hoeringsportal/compare/4.2.2...HEAD
[4.2.2]: https://github.com/itk-dev/hoeringsportal/compare/4.2.1...4.2.2
[4.2.1]: https://github.com/itk-dev/hoeringsportal/compare/4.2.0...4.2.1
[4.2.0]: https://github.com/itk-dev/hoeringsportal/compare/4.1.1...4.2.0
[4.1.1]: https://github.com/itk-dev/hoeringsportal/compare/4.1.0...4.1.1
[4.1.0]: https://github.com/itk-dev/hoeringsportal/compare/4.0.1...4.1.0
[4.0.1]: https://github.com/itk-dev/hoeringsportal/compare/4.0.0...4.0.1
[4.0.0]: https://github.com/itk-dev/hoeringsportal/compare/3.6.0...4.0.0
[3.6.0]: https://github.com/itk-dev/hoeringsportal/compare/3.5.3...3.6.0
[3.5.3]: https://github.com/itk-dev/hoeringsportal/compare/3.5.2...3.5.3
[3.5.2]: https://github.com/itk-dev/hoeringsportal/compare/3.5.1...3.5.2
[3.5.1]: https://github.com/itk-dev/hoeringsportal/compare/3.5.0...3.5.1
[3.5.0]: https://github.com/itk-dev/hoeringsportal/compare/3.4.0...3.5.0
[3.4.0]: https://github.com/itk-dev/hoeringsportal/compare/3.3.0...3.4.0
[3.3.0]: https://github.com/itk-dev/hoeringsportal/compare/3.2.0...3.3.0
[3.2.0]: https://github.com/itk-dev/hoeringsportal/compare/3.1.0...3.2.0
[3.1.0]: https://github.com/itk-dev/hoeringsportal/compare/3.0.2...3.1.0
[3.0.2]: https://github.com/itk-dev/hoeringsportal/compare/3.0.1...3.0.2
[3.0.1]: https://github.com/itk-dev/hoeringsportal/compare/3.0.0...3.0.1
[3.0.0]: https://github.com/itk-dev/hoeringsportal/compare/1.4.3...3.0.0
[1.4.3]: https://github.com/itk-dev/hoeringsportal/compare/1.0.0...1.4.3
[1.0.0]: https://github.com/itk-dev/hoeringsportal/releases/tag/1.0.0
