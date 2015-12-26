<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 11/9/15
 * Time: 8:31 PM
 */

namespace Gavrya\Componizer\Content;


use Gavrya\Componizer\Asset\AssetsCollection;
use Gavrya\Componizer\Component\AbstractWidgetComponent;
use Gavrya\Componizer\Componizer;
use Gavrya\Componizer\Manager\WidgetManager;

class ContentProcessor
{

    // Componizer
    private $componizer = null;

    // Internal variables
    private $requiredWidgets = [];

    //-----------------------------------------------------
    // Instance creation/init section
    //-----------------------------------------------------

    public function __construct(Componizer $componizer)
    {
        $this->componizer = $componizer;
    }

    //-----------------------------------------------------
    // Construct section
    //-----------------------------------------------------

    public function initEditorContent($editorContent)
    {
        $this->requiredWidgets = [];

        if (!is_string($editorContent) || empty($editorContent)) {
            return;
        }

        /** @var ContentParser $contentParser */
        $contentParser = $this->componizer->resolve(ContentParser::class);

        $widgetIds = array_unique($contentParser->parseWidgetIds($editorContent));

        if (empty($widgetIds)) {
            return $editorContent;
        }

        /** @var WidgetManager $widgetManager */
        $widgetManager = $this->componizer->resolve(WidgetManager::class);

        $allowedWidgets = $widgetManager->getAllowedWidgets();

        foreach ($widgetIds as $widgetId) {
            if (isset($allowedWidgets[$widgetId])) {
                $this->requiredWidgets[$widgetId] = $allowedWidgets[$widgetId];
            }
        }
    }

    public function makeDisplayContent($editorContent)
    {
        if ($editorContent === null) {
            return '';
        }

        /** @var ContentParser $contentParser */
        $contentParser = $this->componizer->resolve(ContentParser::class);

        return $contentParser->parseDisplayContent($editorContent);
    }

    //-----------------------------------------------------
    // Required components section
    //-----------------------------------------------------

    public function getRequiredComponents()
    {
        return [];
    }

    public function getRequiredWidgets()
    {
        return $this->requiredWidgets;
    }

    //-----------------------------------------------------
    // Required assets methods
    //-----------------------------------------------------

    public function getRequiredGeneralAssets()
    {
        // todo: return componizer general assets needed in order to editor gets worked
    }

    public function getRequiredEditorAssets()
    {
        $requiredAssets = new AssetsCollection();

        /** @var AbstractWidgetComponent $requiredWidget */
        foreach ($this->requiredWidgets as $requiredWidget) {
            /** @var AssetsCollection $widgetAssets */
            $widgetAssets = $requiredWidget->getEditorAssets();

            if(!$widgetAssets->hasAssets()) {
                continue;
            }

            if($widgetAssets->hasAddedAssets()) {
                $requiredAssets->add($widgetAssets->getAddedAssets());
            }

            if($widgetAssets->hasInjectedAssets()) {
                $requiredAssets->inject($widgetAssets->getInjectedAssets());
            }
        }

        return $requiredAssets;
    }

    public function getRequiredDisplayAssets()
    {
        $requiredAssets = new AssetsCollection();

        /** @var AbstractWidgetComponent $requiredWidget */
        foreach ($this->requiredWidgets as $requiredWidget) {
            /** @var AssetsCollection $widgetAssets */
            $widgetAssets = $requiredWidget->getDisplayAssets();

            if(!$widgetAssets->hasAssets()) {
                continue;
            }

            if($widgetAssets->hasAddedAssets()) {
                $requiredAssets->add($widgetAssets->getAddedAssets());
            }

            if($widgetAssets->hasInjectedAssets()) {
                $requiredAssets->inject($widgetAssets->getInjectedAssets());
            }
        }

        return $requiredAssets;
    }

}