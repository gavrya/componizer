<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 11/9/15
 * Time: 8:31 PM
 */

namespace Gavrya\Componizer;


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
    // Content init/make section
    //-----------------------------------------------------

    public function initEditorContent($editorContent)
    {
        // reset previously required widgets
        $this->requiredWidgets = [];

        if (!is_string($editorContent) || empty($editorContent)) {
            return;
        }

        /** @var ContentParser $contentParser */
        $contentParser = $this->componizer->resolve(ContentParser::class);

        $widgetIds = array_unique($contentParser->parseWidgetIds($editorContent));

        // return passed "editor content" back if no widget ids detected inside
        if (empty($widgetIds)) {
            return $editorContent;
        }

        /** @var WidgetManager $widgetManager */
        $widgetManager = $this->componizer->resolve(WidgetManager::class);

        $allowedWidgets = $widgetManager->allowed();

        foreach ($widgetIds as $widgetId) {
            if (isset($allowedWidgets[$widgetId])) {
                $this->requiredWidgets[$widgetId] = $allowedWidgets[$widgetId];
            }
        }
    }

    public function makeDisplayContent($editorContent)
    {
        // check editor content
        if ($editorContent === null) {
            return '';
        }

        /** @var ContentParser $contentParser */
        $contentParser = $this->componizer->resolve(ContentParser::class);

        $displayContent = $contentParser->parseDisplayContent($editorContent);

        return $displayContent;
    }

    //-----------------------------------------------------
    // Required components section
    //-----------------------------------------------------

    public function requiredComponents()
    {
        return [];
    }

    public function requiredWidgets()
    {
        return $this->requiredWidgets;
    }

    //-----------------------------------------------------
    // Assets section (move to AssetsManager?)
    //-----------------------------------------------------

    public function requiredGeneralAssets()
    {
        // todo: return componizer general assets needed in order to editor gets worked
    }

    public function requiredEditorAssets()
    {
        $assets = [];

        /** @var \Gavrya\Componizer\Skeleton\ComponizerWidget $requiredWidget */
        foreach ($this->requiredWidgets as $requiredWidget) {
            $assets[] = $requiredWidget->editorAssets();
        }

        return $assets;
    }

    public function requiredDisplayAssets()
    {
        $assets = [];

        /** @var \Gavrya\Componizer\Skeleton\ComponizerWidget $requiredWidget */
        foreach ($this->requiredWidgets as $requiredWidget) {
            $assets[] = $requiredWidget->displayAssets();
        }

        return $assets;
    }

}