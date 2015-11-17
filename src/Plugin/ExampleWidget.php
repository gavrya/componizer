<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 11/17/15
 * Time: 8:59 PM
 */

namespace Gavrya\Componizer\Plugin;


use Gavrya\Componizer\Skeleton\ComponizerComponent;
use Gavrya\Componizer\Skeleton\ComponizerWidget;

class ExampleWidget extends ComponizerWidget implements ComponizerComponent
{

    public function id()
    {
        return 'c770076f713a31250680e6810dceb6aa';
    }

    public function name()
    {
        return 'Example widget';
    }

    public function version()
    {
        return '1.0';
    }

    public function info()
    {
        return 'Info about example plugin';
    }

    public function hasAssets()
    {
        return false;
    }

    public function assetsDir()
    {
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . $this->id();
    }

    public function init($lang, $cacheDir)
    {
        echo 'example widget init' . PHP_EOL;
    }

    public function up()
    {
        echo 'example widget up' . PHP_EOL;
    }

    public function down()
    {
        echo 'example widget down' . PHP_EOL;
    }

    //-----------------------------------------------------
    // Widget section
    //-----------------------------------------------------

    public function makeDisplayContent(callable $parser, array $properties, $contentType, $content = null)
    {
        return empty($content) ? "<div>{$this->id()}</div>" : "<div>{$parser($content)}</div>";
    }
}