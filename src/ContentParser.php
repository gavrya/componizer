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

    public function parseDisplayContent($editorContent)
    {
        $editorContent = is_string($editorContent) ? trim($editorContent) : null;

        if ($editorContent === null || empty($editorContent)) {
            return '';
        }

        $domHelper = $this->componizer->resolve(DomHelper::class);

        $dom = $domHelper->create($editorContent);

        $bodyElement = $dom->getElementsByTagName('body')->item(0);

        $xpath = new DOMXpath($dom);

        // find first widget element
        $widgetElement = $xpath->query('(//*[@data-componizer-widget])[1]', $bodyElement)->item(0);

        if ($widgetElement === null || !($widgetElement instanceof DOMElement)) {
            return $editorContent;
        }

        // widget id
        $widgetId = trim($widgetElement->getAttribute('data-widget-id'));

        // widget properties
        $widgetProperties = trim($widgetElement->getAttribute('data-widget-properties'));
        $widgetProperties = json_decode($widgetProperties, true);

        if (!is_array($widgetProperties)) {
            $widgetProperties = [];
        }

        // widget content type
        $widgetContentType = trim($widgetElement->getAttribute('data-widget-content-type'));

        if (!in_array($widgetContentType, ['none', 'plain_text', 'rich_text', 'mixed'])) {
            $widgetContentType = 'none';
        }

        // widget content
        $widgetContentElement = $xpath->query('(//*[@data-widget-content])[1]', $widgetElement)->item(0);
        $widgetContent = $domHelper->getInnerHtml($widgetContentElement);

        if ($widgetContentType === 'none') {
            $widgetContent = null;
        }

        // find widget by id
        $widget = !empty($widgetId) ? $this->findAllowedWidget($widgetId) : null;

        // display content
        $widgetDisplayContent = null;

        if ($widget !== null) {
            $widgetDisplayContent = $widget->makeDisplayContent(
                [$this, __FUNCTION__],
                $widgetProperties,
                $widgetContentType,
                $widgetContent
            );

            // TODO: check for widget element in generated $widgetDisplayContent for avoid infinite loop
        }

        if (is_string($widgetDisplayContent) && !empty($widgetDisplayContent)) {
            // replace dom widget element with widget display content
            $domHelper->replaceWith($widgetElement, $widgetDisplayContent);
        } else {
            // remove dom widget element
            // replace with html comment: "<!-- Componizer component not found or disabled: id:43nbvnjm43, name:Spinner -->" ???
            $domHelper->remove($widgetElement);
        }

        return $this->parseDisplayContent($domHelper->getInnerHtml($bodyElement));
    }

    private function findAllowedWidget($widgetId)
    {
        // TODO: reimplement based on scopes/settings in future

        $pluginManager = $this->componizer->resolve(PluginManager::class);

        foreach ($pluginManager->enabled() as $plugin) {
            if ($widget = $plugin->getWidget($widgetId)) {
                return $widget;
            }
        }

        return null;
    }

}