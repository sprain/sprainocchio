# Changelog

All notable changes to sprainocchio. Versions follow [semantic versioning](https://semver.org/); while sprainocchio is below 1.0, a minor version (0.x.0) may break things and says so here.

Every pull request that changes the engine (`src/`, `bin/`, `themes/`, `builder.php`, `composer.json`) adds a new version at the top of this file. When it is merged, GitHub Actions tags it and publishes a release with these notes.

## 0.4.0 – 2026-10-28

### Added
- An Atom feed of the 20 newest posts at `public/feed.xml`, with full content, summary, tags, author and, from the header field `coauthor`, a contributor ([#1](https://github.com/sprain/sprainocchio/issues/1)). New settings in `config.yml`: `title` (the feed's title, default «Blog») and `author` (for posts without their own `author`). Relative links in posts resolve against `baseUrl`.
- sprainNineteen links the feed with `<link rel="alternate">`, so browsers and feed readers find it.

## 0.3.1 – 2026-10-28

### Changed
- sprainNineteen: the post date carries a machine-readable `datetime` attribute, for search engines, feed readers and screen readers.

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
