<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 11/17/15
 * Time: 8:59 PM
 */

namespace Gavrya\Componizer\Component\Widget;


use Gavrya\Componizer\Skeleton\ComponizerAssets;
use Gavrya\Componizer\Skeleton\ComponizerComponent;
use Gavrya\Componizer\Skeleton\ComponizerExternalCss;
use Gavrya\Componizer\Skeleton\ComponizerExternalJs;
use Gavrya\Componizer\Skeleton\ComponizerWidget;

class ExampleWidget extends ComponizerWidget implements ComponizerComponent
{

    const ID = 'c770076f';

    // internal vars
    private $editorAssets = null;
    private $displayAssets = null;

    //-----------------------------------------------------
    // Create/init section
    //-----------------------------------------------------

    public function __construct()
    {
        $this->initEditorAssets();
        $this->initDisplayAssets();
    }

    private function initEditorAssets()
    {
        $this->editorAssets = new ComponizerAssets([]);
    }

    private function initDisplayAssets()
    {
        $assets = [
            new ComponizerExternalJs('/componizer/' . self::ID . '/js/example.js'),
            new ComponizerExternalCss('/componizer/' . self::ID . '/css/example.css'),
        ];

        $this->displayAssets = new ComponizerAssets($assets);
    }

    //-----------------------------------------------------
    // ComponizerComponent section
    //-----------------------------------------------------

    public function id()
    {
        return self::ID;
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
        return true;
    }

    public function assetsDir()
    {
        return dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . self::ID;
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
    // ComponizerWidget section
    //-----------------------------------------------------

    public function makeDisplayContent(callable $parser, array $properties, $contentType, $content = null)
    {
        return empty($content) ? "<div>{$this->name()}</div>" : "<div>{$parser($content)}</div>";
    }

    public function editorAssets()
    {
        return $this->editorAssets;
    }

    public function displayAssets()
    {
        return $this->displayAssets;
    }
}