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

        // widget json
        $widgetProperties = trim($widgetElement->getAttribute('data-widget-json'));
        $widgetProperties = json_decode($widgetProperties, true);

        if (!is_array($widgetProperties)) {
            $widgetProperties = [];
        }

        // widget content type
        $widgetContentType = trim($widgetElement->getAttribute('data-widget-content-type'));

        if (!in_array($widgetContentType, ['empty', 'plain_text', 'rich_text', 'mixed'])) {
            $widgetContentType = 'empty';
        }

        // widget content
        $widgetContentElement = $xpath->query('(//*[@data-widget-content])[1]', $widgetElement)->item(0);
        $widgetContent = $domHelper->getInnerHtml($widgetContentElement);

        if ($widgetContentType === 'empty') {
            $widgetContent = '';
        }

        // parse widget display content
        $displayContent = $this->parseWidget(
            [$this, __FUNCTION__],
            $widgetProperties,
            $widgetContentType,
            $widgetContent
        );

        if (is_string($displayContent) && !empty(trim($displayContent))) {
            $domHelper->replaceWith($widgetElement, $displayContent);
        }

        return $this->parseNative($domHelper->getInnerHtml($bodyElement));
    }

    public function parseWidget(callable $parser, $properties, $contentType, $content = null)
    {
        return empty($content) ? "<div>empty</div>" : "<div>{$parser($content)}</div>";
    }

}