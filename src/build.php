<?php

// Expects $projectRoot to be set by the caller (see builder.php and bin/sprainocchio)
require __DIR__ . '/bootstrap.php';

$container['themeDataCopier']->copyPublicData();
$container['themeDataCopier']->copyStaticData($container['blogConfig']['staticPath']);
$container['blogBuilder']->buildBlog();
$container['pageBuilder']->buildPages();
$container['redirectSaver']->save($container['blogConfig']['redirects']);
