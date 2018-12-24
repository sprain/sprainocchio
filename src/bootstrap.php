<?php

require __DIR__ . '/../vendor/autoload.php';

use Masterminds\HTML5;
use Pimple\Container;
use Sprainocchio\Archive\Archive;
use Sprainocchio\Archive\ArchiveSaver;
use Sprainocchio\Blog\BlogBuilder;
use Sprainocchio\Post\PostFinder;
use Sprainocchio\Post\PostCreator;
use Sprainocchio\Post\PostSaver;
use Sprainocchio\Theme\DataCopier;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

$container = new Container();

$container['blogConfig'] = function($c) {
    $blogConfigFile = __DIR__ . '/../config.yml';
    if (!file_exists($blogConfigFile)) {
        $blogConfigFile = __DIR__ . '/../config.dist.yml';
    }
    $blogConfig = Yaml::parse(file_get_contents($blogConfigFile));

    $blogConfig['themePath'] = __DIR__ . '/../themes/' . $blogConfig['theme'];
    $blogConfig['publicPath'] = __DIR__ . '/../public/';

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
    $loader = new Twig_Loader_Filesystem($c['blogConfig']['themePath']);
    $twig = new Twig_Environment($loader);
    $twig->addGlobal('baseUrl', $c['blogConfig']['baseUrl']);

    $themeTemplateData = [];
    if (isset($c['themeConfig']['template'])) {
        $themeTemplateData = $c['themeConfig']['template'];
    }

    $twig->addGlobal('theme', $themeTemplateData);

    return $twig;
};

$container['postFinder'] = function($c) {
    return new PostFinder(
        new Finder(),
        __DIR__ . '/../posts'
    );
};

$container['postCreator'] = function($c) {
    return new PostCreator(
        new Parsedown(),
        new HTML5()
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

$container['blogBuilder'] = function($c) {
    return new BlogBuilder(
        $c['postFinder'],
        $c['postCreator'],
        $c['postSaver'],
        $c['archiveSaver'],
        $c['mainArchive'],
        $c['tagsArchive'],
        $c['startpage']
    );
};