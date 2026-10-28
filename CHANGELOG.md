# Changelog

All notable changes to sprainocchio. Versions follow [semantic versioning](https://semver.org/); while sprainocchio is below 1.0, a minor version (0.x.0) may break things and says so here.

Every pull request that changes the engine (`src/`, `bin/`, `themes/`, `builder.php`, `composer.json`) adds a new version at the top of this file. When it is merged, GitHub Actions tags it and publishes a release with these notes.

## 0.3.0 – 2026-10-28

First release since 2018.

### Breaking
- Requires PHP 8.5 or newer, with Twig 3, Symfony 8 and Parsedown 1.8.

### Added
- Blogs can live in their own project and require sprainocchio via Composer; build them with `vendor/bin/sprainocchio`.
- Pages outside the post lists (`pages/`) and a `static/` folder copied into `public/` as is.
- Post header fields `summary` and `image`, and `author`; every header field is available in templates as `post.meta`.
- A PHPUnit test suite, run by GitHub Actions on every push and pull request.

### Changed
- Improved styles of the sprainNineteen theme.
- Replaced the dead image URL in the welcome post.

## 0.2.1 – 2018

Earlier versions have no notes.
