date: 2018-12-24
+++
# Welcome to sprainocchio
You can find the source for this post in the project folder you just created in `posts/welcome.md`.
You will see that it is a simple textfile written in [Markdown](https://github.com/adam-p/markdown-here/wiki/Markdown-Cheatsheet) format.

## Adding a new post is simple
* Create a new markdown file in `posts` with some contents.
* Start it with a header that holds at least the post's date, followed by `+++`:
  `date: 2018-12-24` on the first line, `+++` on the second.
  The header can also set `tags: [Business]`, `title`, `summary`, `image`, `author` and `slug` (the post's file name in the URL).
* Pages that don't belong in the post lists, like an about page, go into a `pages` folder. They don't need a date and are saved as `<slug>.html`.
* Run `php sprainocchio/builder.php` again.
* Reload this page.

## Configure your blog
* Check out `config.yml` in project root
* Also check out `themes/sprainNineteen/config.yml`

## Deploy your blog
* You need to put the generated content of the `public` folder onto the internet somewhere. How you do that is currently totally up to you.

## What else?

Since this project is still in early-stage development, further improvements are not impossible.
You are welcome to contribute [on Github](https://github.com/sprain/sprainocchio).

Oh, the template supports images, too:
<div><img src="https://images.unsplash.com/photo-1764462752458-3d07cfc92fb0?q=80&w=1600&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=NHwxMjA3fDA%3D"></div>