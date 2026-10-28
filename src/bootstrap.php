<?php

// Standalone checkout has its own vendor/, installed as a dependency it lives in vendor/sprain/sprainocchio
$autoloadFile = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloadFile)) {
    $autoloadFile = __DIR__ . '/../../../autoload.php';
}
require $autoloadFile;

// The folder holding config.yml, posts/, themes/, static/ and public/; set by the caller
$projectRoot ??= dirname(__DIR__);

use Pimple\Container;
use Sprainocchio\Archive\Archive;
use Sprainocchio\Archive\ArchiveSaver;
use Sprainocchio\Blog\BlogBuilder;
use Sprainocchio\Feed\FeedSaver;
use Sprainocchio\Page\PageBuilder;
use Sprainocchio\Redirect\RedirectSaver;
use Sprainocchio\Post\PostFinder;
use Sprainocchio\Post\PostCreator;
use Sprainocchio\Post\PostSaver;
use Sprainocchio\Theme\DataCopier;
use Sprainocchio\Twig\UrlizerExtension;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

$container = new Container();

$container['blogConfig'] = function($c) use ($projectRoot) {
    $blogConfigFile = $projectRoot . '/config.yml';
    if (!file_exists($blogConfigFile)) {
        $blogConfigFile = $projectRoot . '/config.dist.yml';
    }
    $blogConfig = Yaml::parse(file_get_contents($blogConfigFile));

    $blogConfig['themePath'] = $projectRoot . '/themes/' . $blogConfig['theme'];
    $blogConfig['publicPath'] = $projectRoot . '/public/';
    $blogConfig['staticPath'] = $projectRoot . '/static';
    $blogConfig['redirects'] ??= [];
    $blogConfig['title'] ??= 'Blog';
    $blogConfig['author'] ??= null;

    if (!isset($blogConfig['baseUrl']) || null == $blogConfig['baseUrl']) {
        $blogConfig['baseUrl'] = $blogConfig['publicPath'];
    }

    return $blogConfig;
};

$container['themeConfig'] = function($c) {
    $themeConfigFile = $c['blogConfig']['themePath'] . DIRECTORY_SEPARATOR . 'config.yml';
    if (file_exists($themeConfigFile)) {
        return Yaml::parse(file_get_contents($themeConfigFile));
    }

    return [];
};

$container['twig'] = function($c) {
    $loader = new FilesystemLoader($c['blogConfig']['themePath']);
    $twig = new Environment($loader);
    $twig->addExtension(new UrlizerExtension());
    $twig->addGlobal('baseUrl', $c['blogConfig']['baseUrl']);

    $themeTemplateData = [];
    if (isset($c['themeConfig']['template'])) {
        $themeTemplateData = $c['themeConfig']['template'];
    }

    $twig->addGlobal('theme', $themeTemplateData);

    return $twig;
};

$container['postFinder'] = function($c) use ($projectRoot) {
    return new PostFinder(
        new Finder(),
        $projectRoot . '/posts'
    );
};

$container['postCreator'] = function($c) {
    return new PostCreator(
        new Parsedown()
    );
};

$container['postSaver'] = function($c) {
    return new PostSaver(
        $c['twig'],
        $c['blogConfig']['publicPath']
    );
};

$container['archiveSaver'] = function($c) {
    return new ArchiveSaver(
        $c['twig'],
        $c['blogConfig']['publicPath']
    );
};

$container['mainArchive'] = function($c) {
    return (new Archive(20))
        ->setPath('archive')
        ->setTemplateName('archive');
};

$container['tagsArchive'] = function($c) {
    return (new Archive(20))
        ->setPath('tags')
        ->setTemplateName('tag');
};

$container['startpage'] = function($c) {
    return (new Archive(20))
        ->setTemplateName('index');
};

$container['themeDataCopier'] = function($c) {
    return new DataCopier(
        $c['blogConfig']['themePath'],
        $c['blogConfig']['publicPath']
    );
};

$container['pageBuilder'] = function($c) use ($projectRoot) {
    return new PageBuilder(
        new PostFinder(new Finder(), $projectRoot . '/pages'),
        $c['postCreator'],
        $c['twig'],
        $c['blogConfig']['publicPath']
    );
};

$container['redirectSaver'] = function($c) {
    return new RedirectSaver(
        $c['blogConfig']['publicPath'],
        $c['blogConfig']['baseUrl']
    );
};

$container['feedSaver'] = function($c) {
    return new FeedSaver(
        $c['blogConfig']['publicPath'],
        $c['blogConfig']['baseUrl'],
        $c['blogConfig']['title'],
        $c['blogConfig']['author']
    );
};

$container['blogBuilder'] = function($c) {
    return new BlogBuilder(
        $c['postFinder'],
        $c['postCreator'],
        $c['postSaver'],
        $c['archiveSaver'],
        $c['mainArchive'],
        $c['tagsArchive'],
        $c['startpage'],
        $c['feedSaver']
    );
};