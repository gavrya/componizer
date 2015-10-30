<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 10/23/15
 * Time: 5:50 PM
 */
require('/Users/gavrya/Projects/gravitizer/vendor/autoload.php');

use Gavrya\Gravitizer\Gravitizer;
use Gavrya\Gravitizer\Helper\FsHelper;
use Gavrya\Gravitizer\Helper\StorageHelper;
use Gavrya\Gravitizer\PluginManager;

$ts = microtime(true);

$config = [
    Gravitizer::CONFIG_LANG => 'en',
    Gravitizer::CONFIG_CACHE_DIR => '/Users/gavrya/Projects/gravitizer/vendor/test_cache',
    Gravitizer::CONFIG_PUBLIC_DIR => '/Users/gavrya/Projects/gravitizer/vendor/test_public',
    Gravitizer::CONFIG_ASSETS_HANDLER => Gravitizer::ASSETS_HANDLER_SYMLINK_BY_PHP,
    Gravitizer::CONFIG_PREVIEW_URL => '/preview.php',
];

Gravitizer::setup($config);
$gr = Gravitizer::instance();
$gr->resolve(FsHelper::class);
$gr->resolve(StorageHelper::class);
$pm = $gr->resolve(PluginManager::class);

//$pm->enable('1a79a4d60de6718e8e5b326e338ae533');

var_dump($pm->all());

echo round(microtime(true) - $ts, 3) . PHP_EOL;