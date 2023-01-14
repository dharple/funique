# Overview

A command line script for comparing two sets of directories and identifying
which files are unique to one or other.

Its intended purpose was comparing directories after a major restructuring, to
confirm that no files had been lost.  It works well in a live environment and
within a backup environment.  It's aware of hardlinks, making it useful for
comparing backups generated by [backintime] or [rsnapshot].

# Requirements

* composer
* git
* php (7.4.3 or higher)

# Installation

```bash
git clone https://github.com/dharple/funique.git
cd funique
composer check-platform-reqs
```

If everything looks good after the previous check, install the project
dependencies:

```bash
composer install --no-dev -o
```

If you have [box] installed globally, you can use that to build the utility:

```bash
box compile
```

Otherwise, we provide our own:

```bash
bin/compile
```

Finally, install the resulting PHAR file to /usr/local/bin:

```bash
sudo cp dist/funique.phar /usr/local/bin/funique
```

# Comparing Directories

A basic run of the script looks like this:

```bash
funique --left /path/to/one/directory --right /path/to/another/directory
```

You can specify multiple left and right values, and each one will be considered
part of that group.

You can redirect output, either with `>` or with `--output`.  You can get
additional information, including a progress bar, using `-v`.  Enable debugging
with `-vvv` to see each file as its processed.

# Known Limitations

When compiling the utility using the provided script, `bin/compile`, you may
see a message about compression failing.  This is a soft failure, and does not
stop the script from working.  It's due to a limitation in the standard PHAR
library.  You can get around this by increasing your open files limit, or by
using [box].

[backintime]: https://github.com/bit-team/backintime
[box]: https://github.com/box-project/box
[rsnapshot]: https://github.com/rsnapshot/rsnapshot
