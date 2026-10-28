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

## Development

`vendor/bin/phpunit` runs the tests. GitHub Actions runs them on every push and pull request.
