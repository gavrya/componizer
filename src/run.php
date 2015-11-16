<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 10/23/15
 * Time: 5:50 PM
 */
require('/Users/gavrya/Projects/componizer/vendor/autoload.php');

use Gavrya\Componizer\Componizer;
use Gavrya\Componizer\ContentParser;
use Gavrya\Componizer\Helper\DomHelper;
use Gavrya\Componizer\Helper\FsHelper;
use Gavrya\Componizer\Helper\StorageHelper;
use Gavrya\Componizer\PluginManager;


$ts = microtime(true);

$config = [
    Componizer::CONFIG_LANG => 'en',
    Componizer::CONFIG_CACHE_DIR => '/Users/gavrya/Projects/componizer/vendor/test_cache',
    Componizer::CONFIG_PUBLIC_DIR => '/Users/gavrya/Projects/componizer/vendor/test_public',
    Componizer::CONFIG_ASSETS_HANDLER => Componizer::ASSETS_HANDLER_SYMLINK_BY_PHP,
    Componizer::CONFIG_PREVIEW_URL => '/preview.php',
];

Componizer::setup($config);
$gr = Componizer::instance();
$pm = $gr->pluginManager();

$pm->enable($pm->disabled());

//var_dump($pm->all());

$str = <<<EOD
<div data-widget data-widget-id="2222" data-widget-name="lister" data-widget-json='{"key": "value"}' data-widget-content-type="mixed">
            <div data-widget-content>
            <div data-widget data-widget-id="2222" data-widget-name="lister" data-widget-json='{"key": "value"}' data-widget-content-type="mixed">
            <div data-widget-content>
	        </div>
        </div>
        <div data-widget data-widget-id="2222" data-widget-name="lister" data-widget-json='{"key": "value"}' data-widget-content-type="mixed">
            <div data-widget-content>
            <div data-widget data-widget-id="2222" data-widget-name="lister" data-widget-json='{"key": "value"}' data-widget-content-type="mixed">
            <div data-widget-content>
            <div data-widget data-widget-id="2222" data-widget-name="lister" data-widget-json='{"key": "value"}' data-widget-content-type="mixed">
            <div data-widget-content>
            <div data-widget data-widget-id="2222" data-widget-name="lister" data-widget-json='{"key": "value"}' data-widget-content-type="mixed">
            <div data-widget-content>
	        </div>
        </div>
        <div data-widget data-widget-id="2222" data-widget-name="lister" data-widget-json='{"key": "value"}' data-widget-content-type="mixed">
            <div data-widget-content>

	        </div>
        </div>
        <div data-widget data-widget-id="2222" data-widget-name="lister" data-widget-json='{"key": "value"}' data-widget-content-type="mixed">
            <div data-widget-content>
	        </div>
        </div>
	        </div>
        </div>
        <div data-widget data-widget-id="2222" data-widget-name="lister" data-widget-json='{"key": "value"}' data-widget-content-type="mixed">
            <div data-widget-content>

	        </div>
        </div>
        <div data-widget data-widget-id="2222" data-widget-name="lister" data-widget-json='{"key": "value"}' data-widget-content-type="mixed">
            <div data-widget-content>
	        </div>
        </div>
	        </div>
        </div>
        <div data-widget data-widget-id="2222" data-widget-name="lister" data-widget-json='{"key": "value"}' data-widget-content-type="mixed">
            <div data-widget-content>

	        </div>
        </div>
        <div data-widget data-widget-id="2222" data-widget-name="lister" data-widget-json='{"key": "value"}' data-widget-content-type="mixed">
            <div data-widget-content>
	        </div>
        </div>

	        </div>
        </div>
        <div data-widget data-widget-id="2222" data-widget-name="lister" data-widget-json='{"key": "value"}' data-widget-content-type="mixed">
            <div data-widget-content>
	        </div>
        </div>
	        </div>
        </div>
        <div data-widget data-widget-id="2222" data-widget-name="lister" data-widget-json='{"key": "value"}' data-widget-content-type="mixed">
            <div data-widget-content>

	        </div>
        </div>
        <div data-widget data-widget-id="2222" data-widget-name="lister" data-widget-json='{"key": "value"}' data-widget-content-type="mixed">
            <div data-widget-content>
	        </div>
        </div>
EOD;

$cp = $gr->resolve(ContentParser::class);

echo $cp->parseNative($str) . PHP_EOL;

echo round(microtime(true) - $ts, 3) . PHP_EOL;
