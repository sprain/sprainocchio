# sprainocchio
A simple and easy-to-use static-page blog engine written in php

## Maintained by Claude

Since October 2026, sprainocchio is maintained by Claude, an AI by Anthropic, as part of an open experiment on [sprain.ch/blog](https://www.sprain.ch/blog/experiment.html), the blog sprainocchio was written for. Claude decides what to work on, writes the code and ships it on its own. Manuel Reinhard, who created sprainocchio, has the last word.

Planned work is tracked in the [issues](https://github.com/sprain/sprainocchio/issues). Every change comes as a pull request that explains why it was made, and is merged only once the test suite passes. Every change to the engine gets a new version: see the [changelog](CHANGELOG.md) and the [releases](https://github.com/sprain/sprainocchio/releases).

## Requirements

PHP 8.5 or newer with the `dom` extension.

## Getting started

1. Fetch the project with [Composer](https://getcomposer.org/).<br>
`composer create-project sprain/sprainocchio sprainocchio ^0.3`

2. Run the build process.<br>
`php sprainocchio/builder.php`

3. Open the generated website.<br>
`open sprainocchio/public/index.html`

## Using sprainocchio in your own project

Instead of writing posts inside the sprainocchio folder, you can keep your blog in its own project and require sprainocchio via Composer. Your project then holds `config.yml`, `posts/`, `themes/` and optionally `pages/` (pages outside the post lists, like an about page) and `static/` (files copied into `public/` as they are, e.g. images). Build it from the project folder with:

`vendor/bin/sprainocchio`

## Quotes and links

Besides articles, a post can be a quote or a link, set with `type` in its header:

```
type: quote
date: 2026-11-02
source: Grace Hopper
source_url: https://example.com/hopper   # optional
+++
The most dangerous phrase in the language is "We've always done it this way."
```

```
type: link
date: 2026-11-21
url: https://example.com/article
title: The article's title               # optional, falls back to the domain
+++
A short note on why it is worth reading.
```

Themes render each type with `includes/types/<type>.html.twig` and fall back to the article snippet; templates get `post.type`, `post.source`, `post.sourceUrl`, `post.url` and `post.domain`.

## Image captions

Put an italic line right below an image, and it becomes the image's caption (`<figure>` with `<figcaption>`):

```markdown
![A house above a gorge](ronda.jpg)
*Ronda*
```

## Image sizes

Images from `static/` get `width` and `height` from the file, so browsers reserve their space and the text doesn't jump while they load. This works for absolute URLs below `baseUrl` (`https://example.com/blog/media/ronda.jpg`), paths below its path (`/blog/media/ronda.jpg`) and relative paths (`media/ronda.jpg`). External images and images that already have a size stay as written. Themes that scale images need `height: auto` next to `width` or `max-width`, otherwise the images are stretched.

## Escaping

Templates are autoescaped, but only where HTML needs it: `<`, `"` and an `&` that could start a character reference become entities, everything else (`'`, `>`, `«»`, umlauts) stays as written. Link previews then show `It's Love > Money` instead of `It&#039;s Love &gt; Money`. Quote attributes with double quotes (`content="{{ post.title }}"`), and don't add `|e`: autoescaping already covers it.

## Theme assets

Load stylesheets and other files from your theme with `asset()`, so browsers fetch them again after every change instead of showing a cached old version:

```twig
<link rel="stylesheet" href="{{ asset('styles/styles.css') }}">
{# → https://example.com/blog/styles/styles.css?v=3f2a9c1e #}
```

The version is a hash of the file's content and changes only when the file does. The path is relative to `public/`, so it works for the theme's `public_dirs` and for `static/`. The build fails if the file doesn't exist.

## Preview images

Link previews on LinkedIn, Mastodon, messengers and the like show a post's `image`. For posts without one, sprainocchio can draw a 1200 × 630 PNG with the title. Configure it in your theme's `config.yml` (paths are relative to the theme; keep the fonts outside `public_dirs`):

```yaml
share_images:
  font: share/title.ttf                 # required, .ttf or .otf
  meta_font: share/text.ttf             # optional, for meta line and label
  background: '#f6f5f1'                 # a colour or an image (scaled to cover)
  color: '#15171c'
  meta_color: '#555a66'
  label_color: '#4a2fd6'
  title: '{{ post.title }}'             # Twig, with post and the theme's globals
  meta: "{{ post.date|date('j F Y') }}"
  label: '{% if post.author %}By {{ post.author }}{% endif %}'
```

The images land in `public/share/` and templates get their path as `post.shareImage`:

```twig
{% set image = post.image ?: (post.shareImage ? baseUrl ~ post.shareImage : null) %}
{% if image %}<meta property="og:image" content="{{ image }}">{% endif %}
```

This needs PHP's GD extension with FreeType.

## Feed

Every build writes an Atom feed of the 20 newest posts to `public/feed.xml`. Set its title and the default author in `config.yml`:

```yaml
title: My blog
author: Jane Doe
```

Posts with their own `author` keep it in the feed. Link the feed in your theme's `<head>` so feed readers find it:

```twig
<link rel="alternate" type="application/atom+xml" href="{{ baseUrl }}feed.xml">
```

## Development

`vendor/bin/phpunit` runs the tests. GitHub Actions runs them on every push and pull request.
