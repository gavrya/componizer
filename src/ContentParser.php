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

    // Widget attributes
    const WIDGET_ATTR = 'data-componizer-widget';
    const WIDGET_ATTR_ID = 'data-componizer-widget-id';
    const WIDGET_ATTR_NAME = 'data-componizer-widget-name';
    const WIDGET_ATTR_PROPERTIES = 'data-componizer-widget-properties';
    const WIDGET_ATTR_CONTENT_TYPE = 'data-componizer-widget-content-type';
    const WIDGET_ATTR_CONTENT = 'data-componizer-widget-content';

    // Widget content types
    const WIDGET_CT_NONE = 'none';
    const WIDGET_CT_CODE = 'code';
    const WIDGET_CT_PLAIN_TEXT = 'plain_text';
    const WIDGET_CT_RICH_TEXT = 'rich_text';
    const WIDGET_CT_MIXED = 'mixed';

    private $widgetContentTypes = [
        self::WIDGET_CT_NONE,
        self::WIDGET_CT_CODE,
        self::WIDGET_CT_PLAIN_TEXT,
        self::WIDGET_CT_RICH_TEXT,
        self::WIDGET_CT_MIXED,
    ];

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
     * Parse provided "editor content" to "display content" representation.
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
        return $docXpath->query('(//*[@' . self::WIDGET_ATTR . '])[1]', $docRoot)->item(0);
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
            !$widgetElement->hasAttribute(self::WIDGET_ATTR_ID) ||
            !$widgetElement->hasAttribute(self::WIDGET_ATTR_NAME) ||
            !$widgetElement->hasAttribute(self::WIDGET_ATTR_PROPERTIES) ||
            !$widgetElement->hasAttribute(self::WIDGET_ATTR_CONTENT_TYPE)
        ) {
            return false;
        }

        // todo: add additional check: length, format, value

        if($this->findWidgetContentElement($widgetElement) !== null) {
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
            if ($childNode instanceof DOMElement && $childNode->hasAttribute(self::WIDGET_ATTR_CONTENT)) {
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
            $domHelper->remove($widgetElement);

            return;
        }

        // widget id
        $widgetId = trim($widgetElement->getAttribute(self::WIDGET_ATTR_ID));

        // widget name
        $widgetName = trim($widgetElement->getAttribute(self::WIDGET_ATTR_NAME));

        // widget properties
        $widgetProperties = trim($widgetElement->getAttribute(self::WIDGET_ATTR_PROPERTIES));
        $widgetProperties = json_decode($widgetProperties, true);

        if (!is_array($widgetProperties)) {
            $widgetProperties = [];
        }

        // widget content type
        $widgetContentType = trim($widgetElement->getAttribute(self::WIDGET_ATTR_CONTENT_TYPE));

        if (!in_array($widgetContentType, $this->widgetContentTypes)) {
            $widgetContentType = self::WIDGET_CT_NONE;
        }

        $widgetContentElement = $this->findWidgetContentElement($widgetElement);

        // widget content
        $widgetContent = $widgetContentType !== self::WIDGET_CT_NONE ? $domHelper->getInnerHtml($widgetContentElement) : null;

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
            $domHelper->replaceWith($widgetElement, $widgetDisplayContent);
        } else {
            $id = htmlentities($widgetId);
            $name = htmlentities($widgetName);
            $comment = '<!-- Componizer widget not found or disabled: id: "' . $id . '", name: "' . $name . '" -->';

            // replace widget "editor content" representation to componizer html warning comment
            $domHelper->replaceWith($widgetElement, $comment);
        }
    }

    /**
     * Return all widget ids found in provided "editor content".
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

        $doc = $domHelper->create($editorContent);

        $docRoot = $doc->getElementsByTagName('body')->item(0);

        $docXpath = new DOMXpath($doc);

        /** @var DOMNodeList $widgetElements */
        $widgetElements = $docXpath->query('(//*[@' . self::WIDGET_ATTR . '])', $docRoot);

        if ($widgetElements !== false) {
            /** @var DOMElement $widgetElement */
            foreach ($widgetElements as $widgetElement) {
                if (!$this->isValidWidgetElement($widgetElement)) {
                    continue;
                }

                $widgetId = trim($widgetElement->getAttribute(self::WIDGET_ATTR_ID));

                if (!empty($widgetId)) {
                    $widgetIds[] = $widgetId;
                }
            }
        }

        return $widgetIds;
    }

}