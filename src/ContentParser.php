<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 11/9/15
 * Time: 8:30 PM
 */

namespace Gavrya\Componizer;


use DOMElement;
use DOMXpath;
use Gavrya\Componizer\Helper\DomHelper;
use Gavrya\Componizer\Skeleton\ComponizerPlugin;


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

    public function parseNative($editorContent)
    {
        $editorContent = is_string($editorContent) ? trim($editorContent) : null;

        if ($editorContent === null) {
            return '';
        }

        $domHelper = $this->componizer->resolve(DomHelper::class);

        $dom = $domHelper->create($editorContent);

        $bodyElement = $dom->getElementsByTagName('body')->item(0);

        $xpath = new DOMXpath($dom);

        // find first widget element
        $widgetElement = $xpath->query('(//*[@data-widget])[1]', $bodyElement)->item(0);

        if ($widgetElement === null || !($widgetElement instanceof DOMElement)) {
            return $editorContent;
        }

        // widget id
        $widgetId = trim($widgetElement->getAttribute('data-widget-id'));

        // widget json
        $widgetProperties = trim($widgetElement->getAttribute('data-widget-json'));
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
        $widget = !empty($widgetId) ? $this->findWidget($widgetId) : null;

        // display content
        $displayContent = null;

        if ($widget !== null) {
            $displayContent = $widget->makeDisplayContent(
                [$this, __FUNCTION__],
                $widgetProperties,
                $widgetContentType,
                $widgetContent
            );
        }

        if ($displayContent !== null && is_string($displayContent)) {
            // replace dom element with html display content
            $domHelper->replaceWith($widgetElement, $displayContent);
        } else {
            // remove node with invalid widget id
            $domHelper->remove($widgetElement);
        }

        return $this->parseNative($domHelper->getInnerHtml($bodyElement));
    }

    private function findWidget($widgetId)
    {
        $pluginManager = $this->componizer->resolve(PluginManager::class);

        foreach ($pluginManager->enabled() as $plugin) {
            if ($widget = $plugin->getWidget($widgetId)) {
                return $widget;
            }
        }

        return null;
    }

}