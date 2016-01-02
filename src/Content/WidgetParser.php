<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 1/2/16
 * Time: 12:21 PM
 */

namespace Gavrya\Componizer\Content;


use DOMElement;
use DOMNodeList;
use DOMXpath;
use Exception;
use Gavrya\Componizer\Component\AbstractWidgetComponent;
use Gavrya\Componizer\Componizer;
use Gavrya\Componizer\Helper\DomHelper;
use Gavrya\Componizer\Manager\WidgetManager;

/**
 * Class WidgetParser is used for widget related content parsing.
 *
 * @package Gavrya\Componizer\Content
 */
class WidgetParser
{

    /**
     * @var Componizer Componizer instance
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
            $domHelper->removeNode($widgetElement);

            return;
        }

        $widgetId = $this->getWidgetId($widgetElement);
        $widgetName = $this->getWidgetName($widgetElement);
        $widgetProperties = $this->getWidgetProperties($widgetElement);
        $widgetContentType = $this->getWidgetContentType($widgetElement);
        $widgetContent = $this->getWidgetContent($widgetElement, $widgetContentType);

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
        } else {
            try {
                $widgetDisplayContent = $widget->makeDisplayContent(
                    $contentParser,
                    $widgetProperties,
                    $widgetContentType,
                    $widgetContent
                );

                if(
                    !is_string($widgetDisplayContent) ||
                    empty(trim($widgetDisplayContent)) ||
                    !empty($this->parseWidgetIds($widgetDisplayContent))
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
        $docXpath = new DOMXpath($doc);

        /** @var DOMNodeList $widgetElements */
        $widgetElements = $this->findWidgetElements($docXpath, $docRoot);

        if ($widgetElements instanceof DOMNodeList) {
            /** @var DOMElement $widgetElement */
            foreach ($widgetElements as $widgetElement) {
                if (!$this->isValidWidgetElement($widgetElement)) {
                    continue;
                }

                $widgetId = $this->getWidgetId($widgetElement);

                if (!empty($widgetId)) {
                    $widgetIds[] = $widgetId;
                }
            }
        }

        return $widgetIds;
    }

    /**
     * Finds first widget element.
     *
     * @param DOMXpath $docXpath
     * @param DOMElement $docRoot
     * @return DOMElement|null
     */
    public function findWidgetElement(DOMXpath $docXpath, DOMElement $docRoot)
    {
        return $docXpath->query('(//*[@' . ContentParserInterface::WIDGET_ATTR . '])[1]', $docRoot)->item(0);
    }

    /**
     * Finds all widget elements.
     *
     * @param DOMXpath $docXpath
     * @param DOMElement $docRoot
     * @return DOMNodeList|bool
     */
    public function findWidgetElements(DOMXpath $docXpath, DOMElement $docRoot)
    {
        return $docXpath->query('(//*[@' . ContentParserInterface::WIDGET_ATTR . '])', $docRoot);
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
            !$widgetElement->hasAttribute(ContentParserInterface::WIDGET_ATTR_ID) ||
            !$widgetElement->hasAttribute(ContentParserInterface::WIDGET_ATTR_NAME) ||
            !$widgetElement->hasAttribute(ContentParserInterface::WIDGET_ATTR_PROPERTIES) ||
            !$widgetElement->hasAttribute(ContentParserInterface::WIDGET_ATTR_CONTENT_TYPE)
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

        return $domHelper->findFirstChildByAttribute($widgetElement, ContentParserInterface::WIDGET_ATTR_CONTENT);
    }

    public function getWidgetId(DOMElement $widgetElement)
    {
        return trim($widgetElement->getAttribute(ContentParserInterface::WIDGET_ATTR_ID));
    }

    public function getWidgetName(DOMElement $widgetElement)
    {
        return trim($widgetElement->getAttribute(ContentParserInterface::WIDGET_ATTR_NAME));
    }

    public function getWidgetProperties(DOMElement $widgetElement)
    {
        $widgetProperties = trim($widgetElement->getAttribute(ContentParserInterface::WIDGET_ATTR_PROPERTIES));
        $widgetProperties = json_decode($widgetProperties, true);

        if (!is_array($widgetProperties)) {
            $widgetProperties = [];
        }

        return $widgetProperties;
    }

    public function getWidgetContentType(DOMElement $widgetElement)
    {
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

        return $widgetContentType;
    }

    public function getWidgetContent(DOMElement $widgetElement, $widgetContentType)
    {
        $widgetContentElement = $this->findWidgetContentElement($widgetElement);

        if ($widgetContentType === ContentParserInterface::WIDGET_CT_NONE) {
            return null;
        }

        /** @var DomHelper $domHelper */
        $domHelper = $this->componizer->resolve(DomHelper::class);

        return $domHelper->getInnerHtml($widgetContentElement);
    }

}