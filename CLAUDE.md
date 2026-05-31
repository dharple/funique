# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with
code in this repository.

## Project Overview

`funique` is a PHP CLI tool that compares two sets of directories and reports
which files are unique to each side. It's hardlink-aware (useful for
`backintime`/`rsnapshot` backups) and supports pre-computed checksum files as
input in addition to live directories.

This PHP project is built into an executable, using [box] or a legacy build
method.

## Commands

### Setup

```bash
composer install
```

### Running

```bash
bin/funique --left /path/to/left --right /path/to/right
```

### Build PHAR

```bash
box compile        # preferred method
bin/compile        # backup method
```

### Dev Tools

```bash
# All of these commands can take one or more filenames or directories on the
# command line, to narrow the scope of their execution

composer phpstan    # Static Analysis
composer phpcs      # Check Code Style
composer phpcbf     # Fix Code Style
composer rector     # Show Opportunities for Automatic Refactoring
composer test       # Run unit tests

# Direct access tools

vendor/bin/rector   # Do Automatic Refactoring
vendor/bin/phpunit --filter <pattern> # Run unit tests matching a given pattern
```

## Architecture

The entry point is `bin/funique`, which bootstraps a Symfony Console `Application` with a single command: `FuniqueCommand`.

**Model hierarchy:**
- `Entry` (abstract) — base for anything with a path
  - `Directory` — wraps a filesystem directory, returns `Entry[]` via `getEntries()`
    - `BaseDirectory` — root anchor; strips from relative paths so output shows paths relative to the specified root, not the full absolute path
  - `Summable` (abstract extends Entry) — anything that can be checksummed; holds the `isUnique` flag (defaults `true`, set to `false` when a match is found)
    - `File` — a real filesystem file; computes size/inode/device via `stat()`, uses a fast `adler32` leading-checksum (first 2 KB) before the full hash to short-circuit comparisons on large files
    - `ChecksumEntry` — a pre-computed checksum read from a checksum file (e.g. `sha512sum` output); holds the hash string directly and returns it from `getSum()`

**Services:**
- `DirectoryService` — recursively walks a `Directory`, groups `File` objects by `floor(size / 256)` into size buckets (so only same-size-group files are ever compared), skips empty files, skips symlinks, skips hidden files unless `--hidden` is set
- `FileService` — compares two `Summable` objects; `checkContents()` delegates to `isSameAs()`, `checkHardlink()` compares device+inode
- `AccessService` — static singleton that records the last time the process read from disk (`hrtime()`). `FuniqueCommand` uses this to throttle I/O: it sleeps briefly every 10 comparisons if a disk access has occurred since the last sleep

**Algorithm in `FuniqueCommand::execute()`:**
1. Load all left/right directories into size-grouped `File[][]` maps
2. Parse any `--left-checksum-file` / `--right-checksum-file` inputs into `ChecksumEntry[]`
3. For each size group, first detect hardlinks (same device+inode = not unique, no I/O needed), then run up to three checksum-comparison rounds:
   - Primary: live left files vs live right files
   - Left checksums vs live right files
   - Live left files vs right checksums
4. After all comparisons, print remaining unique files prefixed with `L:` or `R:`

## Testing

Tests live in `tests/` with namespace `Outsanity\Tests\Funique`. `BaseTestCase` provides `buildTestDirectory()` which materialises mock `File` objects as real temporary files under `sys_get_temp_dir()/funique-tests/<uuid>` using `ramsey/uuid` and `symfony/filesystem`.

`tests/Mock/Model/File` is a test double for `File` that lets tests control size and content (via a repeating filler string) without touching the real filesystem during construction.

## Code Style

- PSR-4 autoloading; namespace root `Outsanity\Funique\` → `src/`, `Outsanity\Tests\Funique\` → `tests/`
- Coding standard enforced by `outsanity/phpcs` (see `phpcs.xml.dist`)
- PHPStan level 5
- Target PHP 8.2

[box]: https://github.com/box-project/box/
