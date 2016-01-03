<?php

use Gavrya\Componizer\ComponizerConfig;

include('init.php');

$config = [
    ComponizerConfig::CONFIG_LANG => 'en',
    ComponizerConfig::CONFIG_CACHE_DIR => '/Users/gavrya/Projects/componizer/vendor/test_cache',
    ComponizerConfig::CONFIG_PUBLIC_DIR => '/Users/gavrya/Projects/componizer/vendor/test_public',
    ComponizerConfig::CONFIG_PREVIEW_URL => '/preview.php',
];

var_dump($config);

$componnizerConfig = new ComponizerConfig($config);

var_dump($componnizerConfig);