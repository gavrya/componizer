<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 11/9/15
 * Time: 8:30 PM
 */

namespace Gavrya\Componizer;


use DOMElement;
use DOMXPath;
use Gavrya\Componizer\Helper\DomHelper;


class ContentParser
{
    // Componizer
    private $componizer = null;

    //-----------------------------------------------------
    // Instance creation/init section
    //-----------------------------------------------------

    public function __construct(Componizer $componizer)
    {
        $this->componizer = $componizer;
    }

    //-----------------------------------------------------
    // Content parsing section
    //-----------------------------------------------------

    /**
     * Parse provided "editor content" to the "display content".
     *
     * Parsing is processed based on allowed/disabled plugins/components and other settings from SettingsManager.
     *
     * @param $editorContent
     * @return string Parsed display content or empty string
     */
    public function parseDisplayContent($editorContent)
    {
        $editorContent = is_string($editorContent) ? trim($editorContent) : null;

        if ($editorContent === null || empty($editorContent)) {
            return '';
        }

        /** @var DomHelper $domHelper */
        $domHelper = $this->componizer->resolve(DomHelper::class);

        $doc = $domHelper->create($editorContent);

        $docRoot = $doc->getElementsByTagName('body')->item(0);

        $docXpath = new DOMXpath($doc);

        $widgetElement = $this->findWidgetElement($docXpath, $docRoot);

        if ($widgetElement === null) {
            return $editorContent;
        }

        $this->parseWidgetElement($widgetElement, $domHelper);

        // todo: prevent loop caused by incorrect widget if widget return editor content in display content

        return $this->parseDisplayContent($domHelper->getInnerHtml($docRoot));
    }

    //-----------------------------------------------------
    // Widget parsing section
    //-----------------------------------------------------

    /**
     * Find first widget element.
     *
     * @param DOMXPath $docXpath
     * @param DOMElement $docRoot
     * @return \DOMElement|null widget element
     */
    private function findWidgetElement(DOMXpath $docXpath, DOMElement $docRoot)
    {
        return $docXpath->query('(//*[@data-componizer-widget])[1]', $docRoot)->item(0);
    }

    /**
     * Parse widget element.
     *
     * @param DOMElement $widgetElement
     * @param DomHelper $domHelper
     */
    private function parseWidgetElement(DOMElement $widgetElement, DomHelper $domHelper)
    {
        // widget id
        $widgetId = trim($widgetElement->getAttribute('data-componizer-widget-id'));

        // widget name
        $widgetName = trim($widgetElement->getAttribute('data-componizer-widget-name'));

        // widget properties
        $widgetProperties = trim($widgetElement->getAttribute('data-componizer-widget-properties'));
        $widgetProperties = json_decode($widgetProperties, true);

        if (!is_array($widgetProperties)) {
            $widgetProperties = [];
        }

        // widget content type
        $widgetContentType = trim($widgetElement->getAttribute('data-componizer-widget-content-type'));

        if (!in_array($widgetContentType, ['none', 'plain_text', 'rich_text', 'mixed'])) {
            $widgetContentType = 'none';
        }

        // widget content
        $widgetContent = null;

        $widgetContentElement = $widgetElement->firstChild;

        if ($widgetContentElement !== null && $widgetContentType !== 'none') {
            $widgetContent = $domHelper->getInnerHtml($widgetContentElement);
        }

        // find widget by id
        $widget = !empty($widgetId) ? $this->findAllowedWidget($widgetId) : null;

        // display content
        $widgetDisplayContent = null;

        if ($widget !== null) {
            $widgetDisplayContent = $widget->makeDisplayContent(
                [$this, 'parseDisplayContent'],
                $widgetProperties,
                $widgetContentType,
                $widgetContent
            );
        }

        if (is_string($widgetDisplayContent) && !empty($widgetDisplayContent)) {
            $domHelper->replaceWith($widgetElement, $widgetDisplayContent);
        } else {
            $id = htmlentities($widgetId);
            $name = htmlentities($widgetName);
            $comment = '<!-- Componizer widget not found or disabled: id: "' . $id . '", name: "' . $name . '" -->';

            $domHelper->replaceWith($widgetElement, $comment);
        }
    }

    //-----------------------------------------------------
    // Parse component id's section
    //-----------------------------------------------------

    /**
     * Return all parsed widget ids.
     *
     * Returned array may contain multiple identical ids, id per every found widget.
     * See helpfull links for array filtering.
     *
     * @link http://php.net/manual/en/function.array-unique.php
     * @link http://php.net/manual/en/function.array-count-values.php
     *
     * @param $editorContent
     * @return array
     */
    public function parseWidgetIds($editorContent)
    {
        $widgetIds = [];

        $editorContent = is_string($editorContent) ? trim($editorContent) : null;

        if ($editorContent === null || empty($editorContent)) {
            return [];
        }

        /** @var DomHelper $domHelper */
        $domHelper = $this->componizer->resolve(DomHelper::class);

        $doc = $domHelper->create($editorContent);

        $docRoot = $doc->getElementsByTagName('body')->item(0);

        $docXpath = new DOMXpath($doc);

        /** @var DOMNodeList $widgetElements */
        $widgetElements = $docXpath->query('(//*[@data-componizer-widget])', $docRoot);

        if ($widgetElements !== false) {
            foreach ($widgetElements as $widgetElement) {
                $widgetId = trim($widgetElement->getAttribute('data-componizer-widget-id'));

                if (!empty($widgetId)) {
                    $widgetIds[] = $widgetId;
                }
            }
        }

        return $widgetIds;
    }

    //-----------------------------------------------------
    // Temp section
    //-----------------------------------------------------

    /**
     * Find allowed widget component by widget id.
     *
     * @param $widgetId
     * @return Skeleton\ComponizerWidget|null
     */
    private function findAllowedWidget($widgetId)
    {
        // TODO: reimplement based on scopes/settings in future

        // todo: move method to the WidgetManager

        /** @var PluginManager $pluginManager */
        $pluginManager = $this->componizer->resolve(PluginManager::class);

        foreach ($pluginManager->enabled() as $plugin) {
            if ($widget = $plugin->getWidget($widgetId)) {
                return $widget;
            }
        }

        return null;
    }

}