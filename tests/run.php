<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 10/23/15
 * Time: 5:50 PM
 */
use Gavrya\Componizer\Componizer;

require('/Users/gavrya/Projects/componizer/vendor/autoload.php');


error_reporting(E_ALL);

$timerStart = microtime(true);

$config = [
    ComponizerConfig::CONFIG_LANG => 'en',
    ComponizerConfig::CONFIG_CACHE_DIR => '/Users/gavrya/Projects/componizer/vendor/test_cache',
    ComponizerConfig::CONFIG_PUBLIC_DIR => '/Users/gavrya/Projects/componizer/vendor/test_public',
    ComponizerConfig::CONFIG_PREVIEW_URL => '/preview.php',
];

Componizer::setup($config);
$componizer = Componizer::instance();

$pluginManager = $componizer->pluginManager();

$pluginManager->enablePlugin($pluginManager->getDisabledPlugins());
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
echo 'Required widgets: ' . count($contentProcessor->getRequiredWidgets());
echo PHP_EOL;

echo PHP_EOL;
echo 'Required editor assets: ' . count($contentProcessor->getRequiredEditorAssets());
echo PHP_EOL;

echo PHP_EOL;
echo 'Required display assets: ' . count($contentProcessor->getRequiredDisplayAssets());
echo PHP_EOL;

echo PHP_EOL;
echo $contentProcessor->makeDisplayContent($editorContent);
echo PHP_EOL;

$timerStop = microtime(true);

echo PHP_EOL;
echo round($timerStop - $timerStart, 3);
echo PHP_EOL;
