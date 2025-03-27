# Changelog for Høringsportal

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic
Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [4.8.4] - 2025-03-27

* [PR-485](https://github.com/itk-dev/hoeringsportal/pull/485)
  Cleaned up form helper
* [PR-472](https://github.com/itk-dev/hoeringsportal/pull/472)
  * Translated search page to Danish
  * Updated custom Danish translations (exported from production site)

## [4.8.3] - 2025-03-24

* [PR-473](https://github.com/itk-dev/hoeringsportal/pull/473)
  Allowed citizen proposal editors to view unpublished citizen proposals.

## [4.8.2] - 2025-03-19

* [PR-474](https://github.com/itk-dev/hoeringsportal/pull/474)
  * Patched role permissions
  * Cleaned up frontend admin menu

## [4.8.1] - 2025-03-17

* [PR-471](https://github.com/itk-dev/hoeringsportal/pull/471)
  * Fixed placement of “Social buttons”
  * Updates site name

## [4.8.0] - 2025-03-13

* [PR-467](https://github.com/itk-dev/hoeringsportal/pull/467)
  Added date validation on new hearings
* [PR-458](https://github.com/itk-dev/hoeringsportal/pull/458)
  Cleaned up actions
* [PR-380](https://github.com/itk-dev/hoeringsportal/pull/380)
  Added department access check.
* [PR-449](https://github.com/itk-dev/hoeringsportal/pull/449)
  Set correct Danish pluralization. Remove superfluous docker service.
* [PR-446](https://github.com/itk-dev/hoeringsportal/pull/446)
  Cleaned up archiving code and added web profiler
* [PR-445](https://github.com/itk-dev/hoeringsportal/pull/445)
  Corrected Danish pluralization
* [PR-441](https://github.com/itk-dev/hoeringsportal/pull/441)
  Add and enable [delta sync](https://github.com/itk-dev/azure-ad-delta-sync-drupal)
  Add [mock idp api](https://github.com/dotronglong/faker) and documentation of said
  Add bash script (test-delta-sync) to test delta sync
  Add fixtures with oidc users
* [PR-442](https://github.com/itk-dev/hoeringsportal/pull/442)
  Cleaned up fixtures
* [PR-440](https://github.com/itk-dev/hoeringsportal/pull/440)
  Add missing icon
* [PR-438](https://github.com/itk-dev/hoeringsportal/pull/438)
  Improved search styling
* [PR-436](https://github.com/itk-dev/hoeringsportal/pull/436)
  Improved search
* [PR-439](https://github.com/itk-dev/hoeringsportal/pull/439)
  Updated tasks and map page fixture.
* [PR-437](https://github.com/itk-dev/hoeringsportal/pull/437)
  Cleaned up API and added caching
* [PR-435](https://github.com/itk-dev/hoeringsportal/pull/435)
  Add usable config values for oidc
  Update OIDC documentation
  Rename editor user in group "administrator" to admin user
* [PR-434](https://github.com/itk-dev/hoeringsportal/pull/434)
  Accessibility stuff:
  Font sizes to rem instead of pixels
  Add name to image for screen readers
  Change h5's to h4
  Add label to search icon
* [PR-433](https://github.com/itk-dev/hoeringsportal/pull/433)
  Upgrade node from 18 to 22
* [PR-432](https://github.com/itk-dev/hoeringsportal/pull/432)
  Replace `yarn` with `npm`
  Replace eslint with prettier
  Lint with prettier
  Add audit to actions
* [PR-430](https://github.com/itk-dev/hoeringsportal/pull/430)
  Add build assets to taskfile
* [PR-431](https://github.com/itk-dev/hoeringsportal/pull/431)
  Avoid navbar jumping
* [PR-429](https://github.com/itk-dev/hoeringsportal/pull/429)
  Removed outdated (and unused) API endpoints. Cleaned up.
* [PR-427](https://github.com/itk-dev/hoeringsportal/pull/427)
  Updated docker compose setup. Updated composer packages.
* [PR-425](https://github.com/itk-dev/hoeringsportal/pull/425)
  Publication dates
* [PR-424](https://github.com/itk-dev/hoeringsportal/pull/424)
  2945: Cleaned up form templates. Applied security updates.

## [4.7.3] - 2025-03-14

* [PR-470](https://github.com/itk-dev/hoeringsportal/pull/470)
  Fixed citizen proposal form caching issue

## [4.7.2] - 2025-03-12

* [PR-465](https://github.com/itk-dev/hoeringsportal/pull/465)
  * Handled inactive citizen proposals
  * Disabled cache on citizen proposal support form

## [4.7.1] - 2025-03-12

* [PR-464](https://github.com/itk-dev/hoeringsportal/pull/464)
  Add labels to woodpecker workflow files for deploy to STG and PROD

## [4.7.0] - 2025-03-10

* [PR-455](https://github.com/itk-dev/hoeringsportal/pull/455)
  * Fixed bug in display of "on behalf of” on hearing reply details
  * Added "on behalf of” on hearing reply list
  * Corrected handling of ticket creation time
* [PR-454](https://github.com/itk-dev/hoeringsportal/pull/454)
  Update person name in Deskpro on email match

## [4.6.5] - 2025-03-05

* [PR-456](https://github.com/itk-dev/hoeringsportal/pull/456)
  3949: Made Clamav restart automatically

## [4.6.4] - 2025-02-24

* [PR-450](https://github.com/itk-dev/hoeringsportal/pull/450)
  Fixed styling of placeholders.

## [4.6.3] - 2025-02-20

* [PR-448](https://github.com/itk-dev/hoeringsportal/pull/448)
  3843: Security update

## [4.6.2] - 2025-01-30

* [PR-443](https://github.com/itk-dev/hoeringsportal/pull/443)
  Fixed hearing reply cleanup

## [4.6.1] - 2024-12-04

* Added automatic deployment

## [4.6.0] - 2024-11-11

* [PR-422](https://github.com/itk-dev/hoeringsportal/pull/422)
  Implement feedback on delete hearing tickets feature.
* [PR-421](https://github.com/itk-dev/hoeringsportal/pull/421)
  Security updates
* [PR-420](https://github.com/itk-dev/hoeringsportal/pull/420)
  1606: Made supporter name optional
* [PR-419](https://github.com/itk-dev/hoeringsportal/pull/419)
  Added Drush command to delete hearing replies
* [PR-418](https://github.com/itk-dev/hoeringsportal/pull/418)
  Add message when hearing replies are deleted
* [PR-417](https://github.com/itk-dev/hoeringsportal/pull/417)
  Added more test setup stuff

## [4.5.1] - 2024-09-06

* [PR-416](https://github.com/itk-dev/hoeringsportal/pull/416)
  Increase nginx max body size

## [4.5.0] - 2024-08-19

* [PR-412](https://github.com/itk-dev/hoeringsportal/pull/412)
  * Upgrade drupal core 10.2.7 and contrib modules
  * Disable migrate_subject_data module
  * Remove deprecated webmozart/path-util package
* [PR-411](https://github.com/itk-dev/hoeringsportal/pull/411)
  Fix paragraph spacing
* [PR-409](https://github.com/itk-dev/hoeringsportal/pull/409)
  Enabled and configured log_stdout
* [PR-410](https://github.com/itk-dev/hoeringsportal/pull/410)
  Added ClamAv module and docker containers to run it

## [4.4.1] - 2024-08-05

* [PR-413](https://github.com/itk-dev/hoeringsportal/pull/413)
  Removed `.placeholder` markup

## [4.4.0] - 2024-05-14

* [PR-407](https://github.com/itk-dev/hoeringsportal/pull/407)
  Fix fontawesome issues
* [PR-406](https://github.com/itk-dev/hoeringsportal/pull/406)
  Infobox styling
* [PR-405](https://github.com/itk-dev/hoeringsportal/pull/405)
  Add paragraph for files
* [PR-404](https://github.com/itk-dev/hoeringsportal/pull/404)
  UI fixes after project release

## [4.3.0] - 2024-04-29

* [PR-395](https://github.com/itk-dev/hoeringsportal/pull/395)
  Project fixtures
* [PR-398](https://github.com/itk-dev/hoeringsportal/pull/398)
  Added Deskpro test data
* [pr-399](https://github.com/itk-dev/hoeringsportal/pull/399)
  * Add content type Project subpage
  * Update paragraphs for projects
  * Add list view for project
* [pr-394](https://github.com/itk-dev/hoeringsportal/pull/394)
  Add content type Project page

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

## [3.5.4] -2023-11-10

* [PR-367](https://github.com/itk-dev/hoeringsportal/pull/367)
  Added and used Editor Advanced link

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

## [3.5.1] - 2023-09-01

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

[Unreleased]: https://github.com/itk-dev/hoeringsportal/compare/4.8.4...HEAD
[4.8.4]: https://github.com/itk-dev/hoeringsportal/compare/4.8.3...4.8.4
[4.8.3]: https://github.com/itk-dev/hoeringsportal/compare/4.8.2...4.8.3
[4.8.2]: https://github.com/itk-dev/hoeringsportal/compare/4.8.1...4.8.2
[4.8.1]: https://github.com/itk-dev/hoeringsportal/compare/4.8.0...4.8.1
[4.8.0]: https://github.com/itk-dev/hoeringsportal/compare/4.7.3...4.8.0
[4.7.3]: https://github.com/itk-dev/hoeringsportal/compare/4.7.2...4.7.3
[4.7.2]: https://github.com/itk-dev/hoeringsportal/compare/4.7.1...4.7.2
[4.7.1]: https://github.com/itk-dev/hoeringsportal/compare/4.7.0...4.7.1
[4.7.0]: https://github.com/itk-dev/hoeringsportal/compare/4.6.5...4.7.0
[4.6.5]: https://github.com/itk-dev/hoeringsportal/compare/4.6.4...4.6.5
[4.6.4]: https://github.com/itk-dev/hoeringsportal/compare/4.6.3...4.6.4
[4.6.3]: https://github.com/itk-dev/hoeringsportal/compare/4.6.2...4.6.3
[4.6.2]: https://github.com/itk-dev/hoeringsportal/compare/4.6.1...4.6.2
[4.6.1]: https://github.com/itk-dev/hoeringsportal/compare/4.6.0...4.6.1
[4.6.0]: https://github.com/itk-dev/hoeringsportal/compare/4.5.1...4.6.0
[4.5.1]: https://github.com/itk-dev/hoeringsportal/compare/4.5.0...4.5.1
[4.5.0]: https://github.com/itk-dev/hoeringsportal/compare/4.4.1...4.5.0
[4.4.1]: https://github.com/itk-dev/hoeringsportal/compare/4.4.0...4.4.1
[4.4.0]: https://github.com/itk-dev/hoeringsportal/compare/4.3.0...4.4.0
[4.3.0]: https://github.com/itk-dev/hoeringsportal/compare/4.2.2...4.3.0
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
