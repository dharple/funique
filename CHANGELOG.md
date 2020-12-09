# [Unreleased]
## Changed
- Add Box configuration.
- Add Travis configuration.
- Add composer.lock back in to source control.
- Updated installation instructions.

## Fixed
- Changed interpreter for CLI scripts to use `/usr/bin/env php` instead of
  `/usr/bin/php` to allow it to work on Travis.

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
