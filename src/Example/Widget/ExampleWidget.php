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
use Gavrya\Componizer\Skeleton\ComponizerParser;

class ExampleWidget extends ComponizerWidget implements ComponizerComponent
{

    private $id = 'c770076f';
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
            new ComponizerExternalJs(sprintf('/componizer/%s/js/example.js', $this->id)),
            new ComponizerExternalCss(sprintf('/componizer/%s/css/example.css', $this->id)),
        ];

        $this->displayAssets = new ComponizerAssets($assets);
    }

    //-----------------------------------------------------
    // ComponizerComponent section
    //-----------------------------------------------------

    public function id()
    {
        return $this->id;
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
        return dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR . $this->id;
    }

    public function init($lang, $cacheDir)
    {
        // callback on widget init
    }

    public function up()
    {
        // callback on widget up
    }

    public function down()
    {
        // callback on widget down
    }

    //-----------------------------------------------------
    // ComponizerWidget section
    //-----------------------------------------------------

    public function makeDisplayContent(ComponizerParser $parser, array $properties, $contentType, $content = null)
    {
        return empty($content) ? "<div>{$this->name()}<p>{$this->name()}</p></div>" : "<div>{$parser->parseDisplayContent($content)}</div>";
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