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

/**
 * Class ContentProcessor is responsible for processing "editor content".
 *
 * @package Gavrya\Componizer\Content
 */
class ContentProcessor
{

    /**
     * @var Componizer
     */
    private $componizer = null;

    /**
     * @var AbstractWidgetComponent[]
     */
    private $requiredWidgets = [];

    //-----------------------------------------------------
    // Constructor section
    //-----------------------------------------------------

    /**
     * ContentProcessor constructor.
     *
     * @param Componizer $componizer
     */
    public function __construct(Componizer $componizer)
    {
        $this->componizer = $componizer;
    }

    //-----------------------------------------------------
    // Content processing methods section
    //-----------------------------------------------------

    /**
     * Analyzes provided "editor content" and finds required widgets and assets in order to make/view "display content".
     *
     * @param string $editorContent
     */
    public function initEditorContent($editorContent)
    {
        $this->requiredWidgets = [];

        /** @var WidgetParser $widgetParser */
        $widgetParser = $this->componizer->resolve(WidgetParser::class);

        $widgetIds = array_unique($widgetParser->parseWidgetIds($editorContent));

        if (empty($widgetIds)) {
            return;
        }

        /** @var WidgetManager $widgetManager */
        $widgetManager = $this->componizer->resolve(WidgetManager::class);

        $allowedWidgets = $widgetManager->getAllowedWidgets();

        foreach ($widgetIds as $widgetId) {
            if (isset($allowedWidgets[$widgetId]) && !isset($this->requiredWidgets[$widgetId])) {
                $this->requiredWidgets[$widgetId] = $allowedWidgets[$widgetId];
            }
        }
    }

    /**
     * Makes "display content" from the "editor content".
     *
     * @param string $editorContent
     * @return string
     */
    public function makeDisplayContent($editorContent)
    {
        /** @var ContentParser $contentParser */
        $contentParser = $this->componizer->resolve(ContentParser::class);

        return $contentParser->parseDisplayContent($editorContent);
    }

    //-----------------------------------------------------
    // Required components section
    //-----------------------------------------------------

    /**
     * Returns required components.
     *
     * @return ComponentInterface[]
     */
    public function getRequiredComponents()
    {
        return $this->requiredWidgets;
    }

    /**
     * Returns required widgets.
     *
     * @return AbstractWidgetComponent[]
     */
    public function getRequiredWidgets()
    {
        return $this->requiredWidgets;
    }

    //-----------------------------------------------------
    // Required assets methods
    //-----------------------------------------------------

    /**
     * Returns required general assets needed in order to editor gets worked.
     *
     * @return AssetsCollection
     */
    public function getRequiredGeneralAssets()
    {
        return new AssetsCollection();
    }

    /**
     * Returns required editor assets.
     *
     * @return AssetsCollection
     */
    public function getRequiredEditorAssets()
    {
        return $this->getRequiredWidgetAssets(
            function (AbstractWidgetComponent $requiredWidget) {
                return $requiredWidget->getEditorAssets();
            }
        );
    }

    /**
     * Returns required display assets.
     *
     * @return AssetsCollection
     */
    public function getRequiredDisplayAssets()
    {
        return $this->getRequiredWidgetAssets(
            function (AbstractWidgetComponent $requiredWidget) {
                return $requiredWidget->getDisplayAssets();
            }
        );
    }

    /**
     * Returns required widget display assets.
     *
     * @param callable $assetsResolver
     * @return AssetsCollection
     */
    private function getRequiredWidgetAssets(callable $assetsResolver)
    {
        $requiredAssets = new AssetsCollection();

        /** @var AbstractWidgetComponent $requiredWidget */
        foreach ($this->requiredWidgets as $requiredWidget) {
            /** @var AssetsCollection $widgetAssets */
            $widgetAssets = $assetsResolver($requiredWidget);

            if (!$widgetAssets->hasAssets()) {
                continue;
            }

            if ($widgetAssets->hasAddedAssets()) {
                $requiredAssets->add($widgetAssets->getAddedAssets());
            }

            if ($widgetAssets->hasInjectedAssets()) {
                $requiredAssets->inject($widgetAssets->getInjectedAssets());
            }
        }

        return $requiredAssets;
    }

}