# Build Instructions

For general instructions, please see the build instructions in `README.md`.

---

# Release Instructions

## Pre-Release

1. Review the diff.  Confirm that what's in the CHANGELOG matches what's in the diff.

## Release

1. Update version at head of `CHANGELOG.md`.
2. Update version link at footer of `CHANGELOG.md`.
3. Update version in `bin/funique`.
4. Commit and push.

```bash
git diff
git commit -a -m "Release v1.2.0"
git tag v1.2.0
git tag # confirm your tag was created correctly
git push
git push --tags
```

5. Build phar and sign.

```
cd ~/deploy/funique
git pull
composer install --no-dev -o
box compile
cd dist
box verify funique.phar
gpg -a --detach-sign funique.phar
gpg --verify funique.phar.asc 
```

6. Create a new release on GitHub, using the raw contents of the CHANGELOG, and
   attaching the phar and signature.
7. Play [The Prisoning: Fletcher's Quest].

[The Prisoning: Fletcher's Quest]: https://playacclaim.com/the-prisoning-fletchers-quest/
