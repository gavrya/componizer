<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 11/17/15
 * Time: 8:59 PM
 */

namespace Gavrya\Componizer\Example\Widget;


use Gavrya\Componizer\Asset\AssetsCollection;
use Gavrya\Componizer\Component\AbstractWidgetComponent;
use Gavrya\Componizer\Content\ContentParserInterface;


class BootstrapColumn extends AbstractWidgetComponent
{

    private $id = '22222222';
    private $editorAssetsCollection = null;
    private $diasplayAssetsCollection = null;

    //-----------------------------------------------------
    // Constructor section
    //-----------------------------------------------------

    public function __construct()
    {
        $this->initEditorAssets();
        $this->initDisplayAssets();
    }

    private function initEditorAssets()
    {
        $this->editorAssetsCollection = new AssetsCollection();
    }

    private function initDisplayAssets()
    {
        $this->diasplayAssetsCollection = new AssetsCollection();
    }

    //-----------------------------------------------------
    // ComponentInterface section
    //-----------------------------------------------------

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return 'Bootstrap column widget';
    }

    public function getVersion()
    {
        return '1.0';
    }

    public function getInfo()
    {
        return 'Provides bootstrap column element';
    }

    public function hasAssets()
    {
        return false;
    }

    public function getAssetsDir()
    {
        return dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . $this->id;
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
    // AbstractWidgetComponent section
    //-----------------------------------------------------

    public function getEditorAssets()
    {
        return $this->editorAssetsCollection;
    }

    public function getDisplayAssets()
    {
        return $this->diasplayAssetsCollection;
    }

    public function makeDisplayContent(
        ContentParserInterface $contentParser,
        array $properties,
        $contentType,
        $content = null
    ) {

        $class = 'row';
        $content = isset($content) ? $contentParser->parseDisplayContent($content) : '';

        return sprintf('<div class="%s">%s</div>', $class, $content);
    }

}