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
use Gavrya\Componizer\Skeleton\ComponizerParser;


class ContentParser implements ComponizerParser
{

    // Componizer
    private $componizer = null;

    //-----------------------------------------------------
    // Instance creation/init section
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

        // todo: find generic componizer component at first, then determine its type, then find that element

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
        return $docXpath->query('(//*[@' . ComponizerParser::WIDGET_ATTR . '])[1]', $docRoot)->item(0);
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
            !$widgetElement->hasAttribute(ComponizerParser::WIDGET_ATTR_ID) ||
            !$widgetElement->hasAttribute(ComponizerParser::WIDGET_ATTR_NAME) ||
            !$widgetElement->hasAttribute(ComponizerParser::WIDGET_ATTR_PROPERTIES) ||
            !$widgetElement->hasAttribute(ComponizerParser::WIDGET_ATTR_CONTENT_TYPE)
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
        // find first nested element with target attribute
        foreach ($widgetElement->childNodes as $childNode) {
            if ($childNode instanceof DOMElement && $childNode->hasAttribute(ComponizerParser::WIDGET_ATTR_CONTENT)) {
                return $childNode;
            }
        }

        return null;
    }

    /**
     * Parse widget element and replace its "editor content" to "diplay content" representation.
     *
     * @param DOMElement $widgetElement
     * @param DomHelper $domHelper
     */
    private function parseWidgetElement(DOMElement $widgetElement, DomHelper $domHelper)
    {
        if (!$this->isValidWidgetElement($widgetElement)) {
            // remove invalid widget representation from "editor content"
            $domHelper->removeNode($widgetElement);

            return;
        }

        // widget id
        $widgetId = trim($widgetElement->getAttribute(ComponizerParser::WIDGET_ATTR_ID));

        // widget name
        $widgetName = trim($widgetElement->getAttribute(ComponizerParser::WIDGET_ATTR_NAME));

        // widget properties
        $widgetProperties = trim($widgetElement->getAttribute(ComponizerParser::WIDGET_ATTR_PROPERTIES));
        $widgetProperties = json_decode($widgetProperties, true);

        if (!is_array($widgetProperties)) {
            $widgetProperties = [];
        }

        // widget content type
        $widgetContentType = trim($widgetElement->getAttribute(ComponizerParser::WIDGET_ATTR_CONTENT_TYPE));

        $widgetContentTypes = [
            ComponizerParser::WIDGET_CT_NONE,
            ComponizerParser::WIDGET_CT_CODE,
            ComponizerParser::WIDGET_CT_PLAIN_TEXT,
            ComponizerParser::WIDGET_CT_RICH_TEXT,
            ComponizerParser::WIDGET_CT_MIXED,
        ];

        if (!in_array($widgetContentType, $widgetContentTypes)) {
            $widgetContentType = ComponizerParser::WIDGET_CT_NONE;
        }

        $widgetContentElement = $this->findWidgetContentElement($widgetElement);

        // widget content
        $widgetContent = $widgetContentType !== ComponizerParser::WIDGET_CT_NONE ? $domHelper->getInnerHtml($widgetContentElement) : null;

        /** @var WidgetManager $widgetManager */
        $widgetManager = $this->componizer->resolve(WidgetManager::class);

        // find widget by id
        $widget = $widgetManager->findAllowed($widgetId);

        // display content
        $widgetDisplayContent = null;

        // check if widget exists and allowed
        if ($widget !== null) {
            $widgetDisplayContent = $widget->makeDisplayContent(
                [$this, 'parseDisplayContent'],
                $widgetProperties,
                $widgetContentType,
                $widgetContent
            );
        }

        if (is_string($widgetDisplayContent) && !empty($widgetDisplayContent)) {
            // replace widget "editor content" to "display content" representation
            $domHelper->replaceNodeWith($widgetElement, $widgetDisplayContent);
        } else {
            $id = htmlentities($widgetId);
            $name = htmlentities($widgetName);
            $comment = '<!-- Componizer widget not found or disabled: id: "' . $id . '", name: "' . $name . '" -->';

            // replace widget "editor content" representation to componizer html warning comment
            $domHelper->replaceNodeWith($widgetElement, $comment);
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
        $widgetElements = $docXpath->query('(//*[@' . ComponizerParser::WIDGET_ATTR . '])', $docRoot);

        if ($widgetElements !== false) {
            /** @var DOMElement $widgetElement */
            foreach ($widgetElements as $widgetElement) {
                if (!$this->isValidWidgetElement($widgetElement)) {
                    continue;
                }

                $widgetId = trim($widgetElement->getAttribute(ComponizerParser::WIDGET_ATTR_ID));

                if (!empty($widgetId)) {
                    $widgetIds[] = $widgetId;
                }
            }
        }

        return $widgetIds;
    }

}