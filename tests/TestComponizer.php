<?php

use Gavrya\Componizer\Componizer;
use Gavrya\Componizer\ComponizerConfig;

include('init.php');

$timerStart = microtime(true);

$config = [
    ComponizerConfig::CONFIG_LANG => 'en',
    ComponizerConfig::CONFIG_CACHE_DIR => '/Users/gavrya/Projects/componizer/vendor/test_cache',
    ComponizerConfig::CONFIG_PUBLIC_DIR => '/Users/gavrya/Projects/componizer/vendor/test_public',
    ComponizerConfig::CONFIG_PREVIEW_URL => '/preview.php',
];

var_dump($config);

$componnizerConfig = new ComponizerConfig($config);


var_dump($componnizerConfig);

$componizer = new Componizer($componnizerConfig);

var_dump($componizer);

$pluginManager = $componizer->getPluginManager();

$pluginManager->enablePlugin($pluginManager->getDisabledPlugins());

var_dump($pluginManager->getEnabledPlugins());

$editorContent = <<<EOD
        <div
         data-componizer-widget
         data-componizer-widget-id="22222222"
         data-componizer-widget-name="Bootstrap column widget"
         data-componizer-widget-properties='{"key": "value"}'
         data-componizer-widget-content-type="mixed">
            <div data-componizer-widget-content>

                <div
                 data-componizer-widget
                 data-componizer-widget-id="44444444"
                 data-componizer-widget-name="Bootstrap row widget"
                 data-componizer-widget-properties='{"class": "col-md-6"}'
                 data-componizer-widget-content-type="mixed">
                    <div data-componizer-widget-content>
                        First column
                    </div>
                </div>

                <div
                 data-componizer-widget
                 data-componizer-widget-id="44444444"
                 data-componizer-widget-name="Bootstrap row widget"
                 data-componizer-widget-properties='{"class": "col-md-6"}'
                 data-componizer-widget-content-type="mixed">
                    <div data-componizer-widget-content>
                        Second column
                    </div>
                </div>

            </div>
        </div>
EOD;

$contentProcessor = $componizer->getContentProcessor();

echo PHP_EOL;
echo $contentProcessor->initEditorContent($editorContent);
echo PHP_EOL;

echo PHP_EOL;
echo 'Required widgets: ' . count($contentProcessor->getRequiredWidgets());
echo PHP_EOL;

echo PHP_EOL;
echo $contentProcessor->makeDisplayContent($editorContent);
echo PHP_EOL;

$timerStop = microtime(true);

echo PHP_EOL;
echo round($timerStop - $timerStart, 3);
echo PHP_EOL;
