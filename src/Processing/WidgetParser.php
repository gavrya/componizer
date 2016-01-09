<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 1/2/16
 * Time: 12:21 PM
 */

namespace Gavrya\Componizer\Processing;


use DOMDocument;
use DOMElement;
use DOMNodeList;
use DOMXPath;
use Exception;
use Gavrya\Componizer\Components\AbstractWidgetComponent;
use Gavrya\Componizer\Componizer;
use Gavrya\Componizer\Helpers\DomHelper;
use Gavrya\Componizer\Managers\WidgetManager;

/**
 * Class WidgetParser is used for widget related content parsing.
 *
 * @package Gavrya\Componizer\Processing
 */
class WidgetParser
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

    /**
     * @var Componizer
     */
    private $componizer = null;

    //-----------------------------------------------------
    // Constructor section
    //-----------------------------------------------------

    /**
     * WidgetParser constructor.
     *
     * @param Componizer $componizer
     */
    public function __construct(Componizer $componizer)
    {
        $this->componizer = $componizer;
    }

    //-----------------------------------------------------
    // General methods section
    //-----------------------------------------------------

    /**
     * Replaces widget element "editor content" to the corresponding "diplay content" representation.
     *
     * @param DOMElement $widgetElement
     * @param ContentParserInterface $contentParser
     */
    public function replaceWidgetElementContent(DOMElement $widgetElement, ContentParserInterface $contentParser)
    {
        /** @var DomHelper $domHelper */
        $domHelper = $this->componizer->resolve(DomHelper::class);

        if (!$this->isValidWidgetElement($widgetElement)) {
            $domHelper->replaceNodeWith($widgetElement, '<!-- Widget component removed -->');

            return;
        }

        $widgetId = $this->parseWidgetId($widgetElement);
        $widgetName = $this->parseWidgetName($widgetElement);
        $widgetProperties = $this->parseWidgetProperties($widgetElement);
        $widgetContentType = $this->parseWidgetContentType($widgetElement);
        $widgetContent = $this->parseWidgetContent($widgetElement, $widgetContentType);

        /** @var WidgetManager $widgetManager */
        $widgetManager = $this->componizer->resolve(WidgetManager::class);

        /** @var AbstractWidgetComponent $widget */
        $widget = $widgetManager->findAllowedWidget($widgetId);

        if ($widget === null) {
            $comment = sprintf(
                '<!-- Widget component not found or disabled: id: %s, name: %s -->',
                htmlentities($widgetId),
                htmlentities($widgetName)
            );
            $domHelper->replaceNodeWith($widgetElement, $comment);

            return;
        }

        try {
            $widgetDisplayContent = $widget->makeDisplayContent(
                $contentParser,
                $widgetProperties,
                $widgetContentType,
                $widgetContent
            );

            if(
                !is_string($widgetDisplayContent) ||
                empty(trim($widgetDisplayContent))
                //!empty($this->parseWidgetIds($widgetDisplayContent))
            ) {
                $comment = sprintf(
                    '<!-- Widget component with invalid content: id: %s, name: %s -->',
                    htmlentities($widgetId),
                    htmlentities($widgetName)
                );
                $domHelper->replaceNodeWith($widgetElement, $comment);
            } else {
                $domHelper->replaceNodeWith($widgetElement, $widgetDisplayContent);
            }
        } catch (Exception $ex) {
            $comment = sprintf(
                '<!-- Widget component error: id: %s, name: %s -->',
                htmlentities($widgetId),
                htmlentities($widgetName)
            );
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
     * @param string $editorContent
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

        /** @var DOMNodeList $widgetElements */
        $widgetElements = $this->findWidgetElements($doc, $docRoot);

        if ($widgetElements instanceof DOMNodeList) {
            /** @var DOMElement $widgetElement */
            foreach ($widgetElements as $widgetElement) {
                if (!$this->isValidWidgetElement($widgetElement)) {
                    continue;
                }

                $widgetIds[] = $this->parseWidgetId($widgetElement);
            }
        }

        return $widgetIds;
    }

    /**
     * Finds first widget element.
     *
     * @param DOMDocument $domDoc
     * @param DOMElement $docRoot
     * @return DOMElement|null
     */
    public function findWidgetElement(DOMDocument $domDoc, DOMElement $docRoot)
    {
        $docXpath = new DOMXpath($domDoc);

        return $docXpath->query('(//*[@' . static::WIDGET_ATTR . '])[1]', $docRoot)->item(0);
    }

    /**
     * Finds all widget elements.
     *
     * @param DOMDocument $domDoc
     * @param DOMElement $docRoot
     * @return DOMNodeList|bool
     */
    public function findWidgetElements(DOMDocument $domDoc, DOMElement $docRoot)
    {
        $docXpath = new DOMXpath($domDoc);

        return $docXpath->query('(//*[@' . static::WIDGET_ATTR . '])', $docRoot);
    }

    /**
     * Checks if widget element is valid based on HTML attributes.
     *
     * @param DOMElement $widgetElement
     * @return bool
     */
    public function isValidWidgetElement(DOMElement $widgetElement)
    {
        if (
            !$widgetElement->hasAttribute(static::WIDGET_ATTR_ID) ||
            !$widgetElement->hasAttribute(static::WIDGET_ATTR_NAME) ||
            !$widgetElement->hasAttribute(static::WIDGET_ATTR_PROPERTIES) ||
            !$widgetElement->hasAttribute(static::WIDGET_ATTR_CONTENT_TYPE)
        ) {
            return false;
        }

        if ($this->findWidgetContentElement($widgetElement) !== null) {
            return true;
        }

        return false;
    }

    /**
     * Finds widget content element of the provided widget element.
     *
     * Returns first found nested element with the attribute 'data-componizer-widget-content'.
     *
     * @param DOMElement $widgetElement
     * @return DOMElement|null
     */
    public function findWidgetContentElement(DOMElement $widgetElement)
    {
        /** @var DomHelper $domHelper */
        $domHelper = $this->componizer->resolve(DomHelper::class);

        return $domHelper->findFirstChildByAttribute($widgetElement, static::WIDGET_ATTR_CONTENT);
    }

    /**
     * Returns widget id.
     *
     * @param DOMElement $widgetElement
     * @return string
     */
    public function parseWidgetId(DOMElement $widgetElement)
    {
        return trim($widgetElement->getAttribute(static::WIDGET_ATTR_ID));
    }

    /**
     * Returns widget name.
     *
     * @param DOMElement $widgetElement
     * @return string
     */
    public function parseWidgetName(DOMElement $widgetElement)
    {
        return trim($widgetElement->getAttribute(static::WIDGET_ATTR_NAME));
    }

    /**
     * Returns widget properties.
     *
     * @param DOMElement $widgetElement
     * @return array
     */
    public function parseWidgetProperties(DOMElement $widgetElement)
    {
        $widgetProperties = trim($widgetElement->getAttribute(static::WIDGET_ATTR_PROPERTIES));
        $widgetProperties = html_entity_decode($widgetProperties, null, 'UTF-8');
        $widgetProperties = json_decode($widgetProperties, true);

        if (!is_array($widgetProperties)) {
            $widgetProperties = [];
        }

        return $widgetProperties;
    }

    /**
     * Returns widget content type.
     *
     * @param DOMElement $widgetElement
     * @return string
     */
    public function parseWidgetContentType(DOMElement $widgetElement)
    {
        $widgetContentType = trim($widgetElement->getAttribute(static::WIDGET_ATTR_CONTENT_TYPE));

        $widgetContentTypes = [
            static::WIDGET_CT_NONE,
            static::WIDGET_CT_CODE,
            static::WIDGET_CT_PLAIN_TEXT,
            static::WIDGET_CT_RICH_TEXT,
            static::WIDGET_CT_MIXED,
        ];

        if (!in_array($widgetContentType, $widgetContentTypes)) {
            $widgetContentType = static::WIDGET_CT_NONE;
        }

        return $widgetContentType;
    }

    /**
     * Returns widget content.
     *
     * @param DOMElement $widgetElement
     * @param $widgetContentType
     * @return string|null
     */
    public function parseWidgetContent(DOMElement $widgetElement, $widgetContentType)
    {
        $widgetContentElement = $this->findWidgetContentElement($widgetElement);

        if ($widgetContentType === static::WIDGET_CT_NONE) {
            return null;
        }

        /** @var DomHelper $domHelper */
        $domHelper = $this->componizer->resolve(DomHelper::class);

        return $domHelper->getInnerHtml($widgetContentElement);
    }

}