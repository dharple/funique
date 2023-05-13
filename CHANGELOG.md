# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

# [Unreleased]
## Added
- Added rector support.

## Changed
- Updated code to rector's PHP 8.1 standards (with several exceptions).

# [0.4.0] - 2023-01-21
## Added
- When viewing debug output, a line will show how many unique files and
  checksums exist on both sides.

## Changed
- The main review loop looks for hardlinks first, then compares sizes and
  checksums when at least one non-unique pair exists.
- The minimum PHP version is now 8.1.
- The output now includes 'L:' and 'R:' to distinguish sides.

## Fixed
- When viewing debug output, the size groups are now shown correctly.

# [0.3.0] - 2023-01-14
## Added
- Added support for considering a list of checksums as being on one side or the
  other.

## Changed
- Add Box configuration.
- Add Travis configuration.
- Add composer.lock back in to source control.
- Updated installation instructions.
- Updated 3rd party dependencies.
  - Upgraded PHP to a minimum of 7.4.
  - Upgraded Symfony to a minimum of 5.4.

## Fixed
- Changed interpreter for CLI scripts to use `/usr/bin/env php` instead of
  `/usr/bin/php` to allow it to work on Travis.
- Directories that contain `~` in the name no longer get replaced with `$HOME`.

# [0.2.1] - 2020-11-14
## Fixed
- Fixed fatal error encountered when a file could not be stat'd.
- Fixed installation instructions and process.
- Fixed problem loading relative paths from inside of the Phar archive.
- Fixed shebang showing during phar run.

# [0.2.0] - 2020-11-13
## Added
- Basic documentation.
- Script to compile funique into a .phar file.

## Changed
- Reduced code cyclomatic complexity through refactoring.
- Updated to code to modern standards.
- Tuned sleep and checksum sizes.

# [0.1.0] - 2017-04-01
## Added
- Initial Release

[Unreleased]: https://github.com/dharple/funique/compare/v0.4.0...master
[0.4.0]: https://github.com/dharple/funique/compare/v0.3.0...v0.4.0
[0.3.0]: https://github.com/dharple/funique/compare/v0.2.1...v0.3.0
[0.2.1]: https://github.com/dharple/funique/compare/v0.2.0...v0.2.1
[0.2.0]: https://github.com/dharple/funique/compare/v0.1.0...v0.2.0
[0.1.0]: https://github.com/dharple/funique/releases/tag/v0.1.0
