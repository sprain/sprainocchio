<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config.php';

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

const THEME_PATH = __DIR__ . '/../themes/' . THEME;
const PUBLIC_PATH = __DIR__ . '/../public/';

$themeConfigFile = THEME_PATH . DIRECTORY_SEPARATOR . 'config.php';
if (file_exists($themeConfigFile)) {
    include $themeConfigFile;
}

$container = new Container();

$container['twig'] = function($c) {
    $loader = new Twig_Loader_Filesystem(THEME_PATH);
    $twig = new Twig_Environment($loader);
    $twig->addGlobal('baseUrl', BASE_URL);
    if (defined('THEME_CONFIG')) {
        $twig->addGlobal('theme', THEME_CONFIG);
    }

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
        PUBLIC_PATH
    );
};

$container['archiveSaver'] = function($c) {
    return new ArchiveSaver(
        $c['twig'],
        PUBLIC_PATH
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
        THEME_PATH,
        PUBLIC_PATH
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