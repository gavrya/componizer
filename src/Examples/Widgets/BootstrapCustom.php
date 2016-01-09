<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 11/17/15
 * Time: 8:59 PM
 */

namespace Gavrya\Componizer\Examples\Widgets;


use Gavrya\Componizer\Assets\AssetInterface;
use Gavrya\Componizer\Assets\AssetsCollection;
use Gavrya\Componizer\Assets\ExternalCssAsset;
use Gavrya\Componizer\Assets\ExternalJsAsset;
use Gavrya\Componizer\Assets\InternalCssAsset;
use Gavrya\Componizer\Assets\InternalJsAsset;
use Gavrya\Componizer\Components\AbstractWidgetComponent;
use Gavrya\Componizer\Processing\ContentParserInterface;


class BootstrapCustom extends AbstractWidgetComponent
{

    private $id = '88888888';
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
        $extJsHead = new ExternalJsAsset('/componizer/' . $this->id . '/js/custom_head.js', AssetInterface::POSITION_HEAD);
        $extJsBodyTop = new ExternalJsAsset('/componizer/' . $this->id . '/js/custom_body_top.js', AssetInterface::POSITION_BODY_TOP);
        $extJsBodyBottom = new ExternalJsAsset('/componizer/' . $this->id . '/js/custom_body_bottom.js', AssetInterface::POSITION_BODY_BOTTOM);

        $intJsHead = new InternalJsAsset('<script>console.log("int js head");</script>', AssetInterface::POSITION_HEAD);
        $intJsBodyTop = new InternalJsAsset('<script>console.log("int js body top");</script>', AssetInterface::POSITION_BODY_TOP);
        $intJsBodyBottom = new InternalJsAsset('<script>console.log("int js body bottom");</script>', AssetInterface::POSITION_BODY_BOTTOM);

        $extCssHead = new ExternalCssAsset('/componizer/' . $this->id . '/css/custom_head.css');
        $intCssHead = new InternalCssAsset('<style>.blabla { background-color: darkgrey;}</style>');

        $this->diasplayAssetsCollection = new AssetsCollection();
        $this->diasplayAssetsCollection->add([
            $extJsHead,
            $extJsBodyTop,
            $extJsBodyBottom,

            $intJsHead,
            $intJsBodyTop,
            $intJsBodyBottom,

            $extCssHead,
            $intCssHead,
        ]);
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
        return 'Bootstrap custom widget';
    }

    public function getVersion()
    {
        return '1.0';
    }

    public function getInfo()
    {
        return 'Provides bootstrap custom element';
    }

    public function hasAssets()
    {
        return true;
    }

    public function getAssetsDir()
    {
        return dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'assets'. DIRECTORY_SEPARATOR . $this->id;
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
        $editorContent = null
    ) {

        $editorContent = isset($editorContent) ? $contentParser->parseDisplayContent($editorContent) : '';

        return sprintf('<div class="custom">%s</div><p class="blabla">blabla</p>', $editorContent);
    }

}