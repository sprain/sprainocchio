# Changelog

All notable changes to sprainocchio. Versions follow [semantic versioning](https://semver.org/); while sprainocchio is below 1.0, a minor version (0.x.0) may break things and says so here.

Every pull request that changes the engine (`src/`, `bin/`, `themes/`, `builder.php`, `composer.json`) adds a new version at the top of this file. When it is merged, GitHub Actions tags it and publishes a release with these notes.

## 0.6.5 – 2026-11-13

### Fixed
- sprainNineteen: pages have landmarks for screen readers. The content is a `<main id="content">` and the sidebar an `<aside id="sidebar">` labelled «About <your_name>», instead of two `<div>`s, so screen reader users can jump straight to the text and reader modes find it (WCAG 1.3.1, 2.4.1). The IDs stay the same, so the look does not change and custom styles keep working; `main` is added to the reset stylesheet for older browsers.

## 0.6.4 – 2026-11-11

### Fixed
- sprainNineteen: only the window width decides whether the sidebar stands next to the text or below it. The media queries also asked for the device's screen size (`min-device-width`, `max-device-width`, deprecated in Media Queries Level 4), so laptops with a screen narrower than 1366 px and tablets in landscape got the phone layout although the window was wide enough, and windows between 880 and 1200 px on large screens got a half-stacked sidebar. Now the desktop layout starts at a window width of 1200 px, the wider margins at 880 px, and the stacked sidebar below 1200 px, without overlap.

## 0.6.3 – 2026-11-10

### Fixed
- sprainNineteen: text and links in the sidebar are readable. They reached a contrast of only 2.9 : 1 and 3.5 : 1 against the sidebar colour; with a slightly darker sidebar (`rgb(68,101,120)`), near-white text and white links they reach 5.2 : 1 and 6.2 : 1, above the 4.5 : 1 that WCAG 2.2 AA asks for. Links within the text are underlined, so they can be told apart without relying on colour; hover and keyboard focus make the underline solid.

## 0.6.2 – 2026-11-09

### Fixed
- sprainNineteen: the photo in the sidebar, which links to the home page, now has a text alternative («<your_name>, home page»). Screen readers announced it as an unnamed link before, and it was the only way back to the home page.

## 0.6.1 – 2026-11-03

### Fixed
- sprainNineteen: on wide screens the colored sidebar now reaches the bottom of the window on short pages such as an archive with few posts, instead of ending right after its content ([#10](https://github.com/sprain/sprainocchio/issues/10)). `#container` is at least as tall as the window; the ineffective `min-height: 100%` on `#sidebar` is gone. In the stacked layout on narrow screens, the colored area below the sidebar now reaches the bottom too.

## 0.6.0 – 2026-11-02

### Added
- Quotes as a post type ([#11](https://github.com/sprain/sprainocchio/issues/11)). A post with `type: quote` in its header is a quote: its body is the quoted text, `source` (required) names who said or wrote it, `source_url` and a short Markdown `description` are optional. Without `type`, a post is an article as before. A quote gets its own page like an article and appears in archives, tag pages and the feed; without `title` its source serves as title, without `slug` the URL is made of the source and the first words of the quote. The build fails with a clear message when `source` is missing or `type` is unknown.
- `post.type`, `post.source`, `post.sourceUrl` and `post.description` in templates. In the feed, a quote's content contains the quote, its source and the description.
- sprainNineteen renders each post type with its own snippet in `includes/types/<type>.html.twig` (falling back to `article.html.twig`), shows quotes as a large block quote with their source, and marks them in lists.

Links as a post type follow later.

## 0.5.0 – 2026-10-28

### Added
- Twig function `asset()`: `{{ asset('styles/styles.css') }}` returns the file's URL with a hash of its content (`…/styles/styles.css?v=3f2a9c1e`), so browsers load a stylesheet again as soon as it changed instead of showing a cached old version ([#13](https://github.com/sprain/sprainocchio/issues/13)). Works for files from the theme's `public_dirs` and from `static/`. The build fails if the file doesn't exist.

### Changed
- sprainNineteen loads its stylesheets with `asset()`.

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
