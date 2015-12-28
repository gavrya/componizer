<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 11/9/15
 * Time: 8:30 PM
 */

namespace Gavrya\Componizer\Content;


use DOMElement;
use DOMXPath;
use Gavrya\Componizer\Component\AbstractWidgetComponent;
use Gavrya\Componizer\Componizer;
use Gavrya\Componizer\Helper\DomHelper;
use Gavrya\Componizer\Manager\WidgetManager;


class ContentParser implements ContentParserInterface
{

    // Componizer
    private $componizer = null;

    //-----------------------------------------------------
    // Construct section
    //-----------------------------------------------------

    public function __construct(Componizer $componizer)
    {
        $this->componizer = $componizer;

        // Avoid "Maximum function nesting level of '100' reached, aborting!".
        ini_set('xdebug.max_nesting_level', 1000);
    }

    //-----------------------------------------------------
    // Content parsing section
    //-----------------------------------------------------

    /**
     * Parse provided "editor content" to "display content" representation.
     *
     * Parsing is processed based on allowed/disabled plugins/components and other settings from SettingsManager.
     *
     * @param string $editorContent Editor content to parse
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

        $doc = $domHelper->createDoc($editorContent);

        $docRoot = $domHelper->getDocRoot($doc);

        $docXpath = new DOMXpath($doc);

        $widgetElement = $this->findWidgetElement($docXpath, $docRoot);

        if ($widgetElement === null) {
            return $editorContent;
        }

        $this->replaceWidgetElementContent($widgetElement, $domHelper);

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
     * @return DOMElement|null widget element
     */
    private function findWidgetElement(DOMXpath $docXpath, DOMElement $docRoot)
    {
        return $docXpath->query('(//*[@' . ContentParserInterface::WIDGET_ATTR . '])[1]', $docRoot)->item(0);
    }

    /**
     * Check if widget is valid based on html parameters.
     *
     * @param DOMElement $widgetElement
     * @return bool
     */
    private function isValidWidgetElement(DOMElement $widgetElement)
    {
        if (
            !$widgetElement->hasAttribute(ContentParserInterface::WIDGET_ATTR_ID) ||
            !$widgetElement->hasAttribute(ContentParserInterface::WIDGET_ATTR_NAME) ||
            !$widgetElement->hasAttribute(ContentParserInterface::WIDGET_ATTR_PROPERTIES) ||
            !$widgetElement->hasAttribute(ContentParserInterface::WIDGET_ATTR_CONTENT_TYPE)
        ) {
            return false;
        }

        // todo: add additional check: length, format, value

        if ($this->findWidgetContentElement($widgetElement) !== null) {
            return true;
        }

        return false;
    }

    /**
     * Find widget content element for provided widget element.
     *
     * Returns first found nested element with attribute 'data-componizer-widget-content'.
     *
     * @param DOMElement $widgetElement
     * @return DOMElement|null
     */
    private function findWidgetContentElement(DOMElement $widgetElement)
    {
        /** @var DomHelper $domHelper */
        $domHelper = $this->componizer->resolve(DomHelper::class);

        return $domHelper->findFirstChildByAttribute($widgetElement, ContentParserInterface::WIDGET_ATTR_CONTENT);
    }

    /**
     * Parse widget element and replace its "editor content" to the "diplay content" representation.
     *
     * @param DOMElement $widgetElement
     * @param DomHelper $domHelper
     */
    private function replaceWidgetElementContent(DOMElement $widgetElement, DomHelper $domHelper)
    {
        if (!$this->isValidWidgetElement($widgetElement)) {
            $domHelper->removeNode($widgetElement);

            return;
        }

        $widgetId = trim($widgetElement->getAttribute(ContentParserInterface::WIDGET_ATTR_ID));

        $widgetName = trim($widgetElement->getAttribute(ContentParserInterface::WIDGET_ATTR_NAME));

        $widgetProperties = trim($widgetElement->getAttribute(ContentParserInterface::WIDGET_ATTR_PROPERTIES));
        $widgetProperties = json_decode($widgetProperties, true);

        if (!is_array($widgetProperties)) {
            $widgetProperties = [];
        }

        $widgetContentType = trim($widgetElement->getAttribute(ContentParserInterface::WIDGET_ATTR_CONTENT_TYPE));

        $widgetContentTypes = [
            ContentParserInterface::WIDGET_CT_NONE,
            ContentParserInterface::WIDGET_CT_CODE,
            ContentParserInterface::WIDGET_CT_PLAIN_TEXT,
            ContentParserInterface::WIDGET_CT_RICH_TEXT,
            ContentParserInterface::WIDGET_CT_MIXED,
        ];

        if (!in_array($widgetContentType, $widgetContentTypes)) {
            $widgetContentType = ContentParserInterface::WIDGET_CT_NONE;
        }

        $widgetContentElement = $this->findWidgetContentElement($widgetElement);

        $widgetContent = $widgetContentType !== ContentParserInterface::WIDGET_CT_NONE ? $domHelper->getInnerHtml($widgetContentElement) : null;

        /** @var WidgetManager $widgetManager */
        $widgetManager = $this->componizer->resolve(WidgetManager::class);

        /** @var AbstractWidgetComponent $widget */
        $widget = $widgetManager->findAllowedWidget($widgetId);

        $widgetDisplayContent = null;

        if ($widget !== null) {
            $widgetDisplayContent = $widget->makeDisplayContent(
                $this,
                $widgetProperties,
                $widgetContentType,
                $widgetContent
            );
        }

        if (is_string($widgetDisplayContent) && !empty($widgetDisplayContent)) {
            $domHelper->replaceNodeWith($widgetElement, $widgetDisplayContent);
        } else {
            $id = htmlentities($widgetId);
            $name = htmlentities($widgetName);
            $comment = '<!-- Widget component not found or disabled: id: %s, name: %s -->';

            $domHelper->replaceNodeWith($widgetElement, sprintf($comment, $id, $name));
        }
    }

    /**
     * Returns all widget ids found in provided "editor content".
     *
     * Returned array may contain multiple identical ids, id per every found widget in the order of parsing.
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

        $doc = $domHelper->createDoc($editorContent);

        $docRoot = $domHelper->getDocRoot($doc);

        $docXpath = new DOMXpath($doc);

        /** @var DOMNodeList $widgetElements */
        $widgetElements = $docXpath->query('(//*[@' . ContentParserInterface::WIDGET_ATTR . '])', $docRoot);

        if ($widgetElements !== false) {
            /** @var DOMElement $widgetElement */
            foreach ($widgetElements as $widgetElement) {
                if (!$this->isValidWidgetElement($widgetElement)) {
                    continue;
                }

                $widgetId = trim($widgetElement->getAttribute(ContentParserInterface::WIDGET_ATTR_ID));

                if (!empty($widgetId)) {
                    $widgetIds[] = $widgetId;
                }
            }
        }

        return $widgetIds;
    }

}