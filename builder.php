<?php

require 'src/bootstrap.php';

$container['themeDataCopier']->copyPublicData();
$container['blogBuilder']->buildBlog();