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

//var_dump($config);

$componnizerConfig = new ComponizerConfig($config);

//var_dump($componnizerConfig);

$componizer = new Componizer($componnizerConfig);

//var_dump($componizer);

$pluginManager = $componizer->getPluginManager();

$pluginManager->enablePlugin($pluginManager->getDisabledPlugins());

foreach($pluginManager->getEnabledPlugins() as $plugin) {
    echo $plugin->getName() . PHP_EOL;
}

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
                        Some crazy content
                    </div>
                </div>

                <div
                 data-componizer-widget
                 data-componizer-widget-id="44444444"
                 data-componizer-widget-name="Bootstrap row widget"
                 data-componizer-widget-properties='{"class": "col-md-6"}'
                 data-componizer-widget-content-type="mixed">
                    <div data-componizer-widget-content>
                        Some crazy content
                    </div>
                </div>

            </div>
        </div>

        <div
         data-componizer-widget
         data-componizer-widget-id="99999999"
         data-componizer-widget-name="Bootstrap alerts widget"
         data-componizer-widget-properties='{"type": "info"}'
         data-componizer-widget-content-type="mixed">
            <div data-componizer-widget-content>
                Some crazy content
            </div>
        </div>

        <div
         data-componizer-widget
         data-componizer-widget-id="77777777"
         data-componizer-widget-name="Bootstrap jumbotron widget"
         data-componizer-widget-properties='{"class": "col-md-6"}'
         data-componizer-widget-content-type="mixed">
            <div data-componizer-widget-content>
                Some crazy content
            </div>
        </div>

        <div
         data-componizer-widget
         data-componizer-widget-id="88888888"
         data-componizer-widget-name="Bootstrap custom widget"
         data-componizer-widget-properties='{}'
         data-componizer-widget-content-type="mixed">
            <div data-componizer-widget-content>
                Some crazy content
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

//echo $contentProcessor->getRequiredDisplayAssets()->getBodyTopAssetsHtml() . PHP_EOL;

//echo $componizer->getWidgetManager()->findWidget('88888888')->getDisplayAssets()->getBodyTopAssetsHtml();

//var_dump($componizer->getWidgetManager()->findWidget('88888888')->getDisplayAssets());

$timerStop = microtime(true);

echo PHP_EOL;
echo round($timerStop - $timerStart, 3);
echo PHP_EOL;
