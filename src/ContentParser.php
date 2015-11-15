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
use Helper\DomHelper;

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

        // find first widget
        $widgetElement = $xpath->query('(//*[@data-widget])[1]', $bodyElement)->item(0);

        if ($widgetElement === null || !($widgetElement instanceof DOMElement)) {
            return $editorContent;
        }

        // widget json attr
        $widgetJsonAttr = $widgetElement->getAttribute('data-widget-json');
        $widgetJsonData = json_decode($widgetJsonAttr, true);

        // widget content type attr
        $widgetContentTypeAttr = $widgetElement->getAttribute('data-widget-content-type');

        // widget content dom element
        $widgetContentElement = $xpath->query('(//*[@data-widget-content])[1]', $widgetElement)->item(0);
        $widgetContent = $domHelper->getInnerHtml($widgetContentElement);

        // parse widget to display content
        $displayContent = $this->parseWidget(
            [$this, __FUNCTION__],
            $widgetJsonData,
            $widgetContentTypeAttr,
            $widgetContent
        );

        if(is_string($displayContent) && !empty(trim($displayContent))) {
            $domHelper->replaceWith($widgetElement, $displayContent);
        }

        return $this->parseNative($domHelper->getInnerHtml($bodyElement));
    }

    public function parseWidget(callable $parser, $jsonParams, $contentType, $content = null)
    {
        //var_dump($content);
        //exit;

        return empty($content) ? "<div>empty</div>" : "<div>{$parser($content)}</div>";
    }

}