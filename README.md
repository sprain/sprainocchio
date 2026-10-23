# sprainocchio
A simple and easy-to-use static-page blog engine written in php

**Note: This is a project in early-stage development.**

## Requirements

PHP 8.5 or newer with the `dom` and `iconv` extensions.

## Getting started

1. Fetch the project with [Composer](https://getcomposer.org/).<br>
`composer create-project sprain/sprainocchio`

2. Run the build process.<br>
`php sprainocchio/builder.php`

3. Open the generated website.<br>
`open sprainocchio/public/index.html`

## Using sprainocchio in your own project

Instead of writing posts inside the sprainocchio folder, you can keep your blog in its own project and require sprainocchio via Composer. Your project then holds `config.yml`, `posts/`, `themes/` and optionally `static/` (files copied into `public/` as they are, e.g. images). Build it from the project folder with:

`vendor/bin/sprainocchio`
