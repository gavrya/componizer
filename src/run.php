<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 10/23/15
 * Time: 5:50 PM
 */
require('/Users/gavrya/Projects/componizer/vendor/autoload.php');

use Gavrya\Componizer\Componizer;
use Gavrya\Componizer\ComponizerParser;

error_reporting(E_ALL);

$timerStart = microtime(true);

$config = [
    Componizer::CONFIG_LANG => 'en',
    Componizer::CONFIG_CACHE_DIR => '/Users/gavrya/Projects/componizer/vendor/test_cache',
    Componizer::CONFIG_PUBLIC_DIR => '/Users/gavrya/Projects/componizer/vendor/test_public',
    Componizer::CONFIG_PREVIEW_URL => '/preview.php',
];

Componizer::setup($config);
$componizer = Componizer::instance();

$pluginManager = $componizer->pluginManager();

$pluginManager->enable($pluginManager->disabled());
//$pluginManager->disable($pluginManager->enabled());

//var_dump($pluginManager->enabled());

$editorContent = <<<EOD
        <div
         data-componizer-widget
         data-componizer-widget-id="c770076f"
         data-componizer-widget-name="lister"
         data-componizer-widget-properties='{"key": "value"}'
         data-componizer-widget-content-type="mixed">
            <div data-componizer-widget-content>

                <div
                 data-componizer-widget
                 data-componizer-widget-id="c770076f"
                 data-componizer-widget-name="lister"
                 data-componizer-widget-properties='{"key": "value"}'
                 data-componizer-widget-content-type="mixed">
                    <div data-componizer-widget-content>
                    </div>
                </div>

	        </div>
        </div>

        <div
         data-componizer-widget
         data-componizer-widget-id="fake-id"
         data-componizer-widget-name="faker"
         data-componizer-widget-properties='{"key": "value"}'
         data-componizer-widget-content-type="mixed">
            <div data-componizer-widget-content>
            </div>
        </div>
EOD;

$contentProcessor = $componizer->contentProcessor();

echo PHP_EOL;
echo $contentProcessor->initEditorContent($editorContent);
echo PHP_EOL;

echo PHP_EOL;
echo 'Required widgets: ' . count($contentProcessor->requiredWidgets());
echo PHP_EOL;

echo PHP_EOL;
echo 'Required editor assets: ' . count($contentProcessor->requiredEditorAssets());
echo PHP_EOL;

echo PHP_EOL;
echo 'Required display assets: ' . count($contentProcessor->requiredDisplayAssets());
echo PHP_EOL;

echo PHP_EOL;
echo $contentProcessor->makeDisplayContent($editorContent);
echo PHP_EOL;

$timerStop = microtime(true);

echo PHP_EOL;
echo round($timerStop - $timerStart, 3);
echo PHP_EOL;
