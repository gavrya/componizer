<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 11/15/15
 * Time: 7:55 PM
 */

namespace Gavrya\Componizer\Helper;


use DOMDocument;
use DOMNode;

/**
 * Contains helpfull methods for working with DOM document and its elements.
 *
 * Class DomHelper
 * @package Gavrya\Componizer\Helper
 */
class DomHelper
{

    /**
     * Creates a new DOM document from a UTF-8 encoded string.
     *
     * @param string $string String containing html
     * @return \DOMDocument Newly created DOM document
     */
    public function create($string)
    {
        $charset = 'UTF-8';

        $string = '<!DOCTYPE html>
                   <html>
                        <head><meta http-equiv="content-type" content="text/html; charset=' . $charset . '"></head>
                        <body>' . $string . '</body>
                   </html>';

        $string = mb_convert_encoding($string, 'HTML-ENTITIES', $charset);
        $string = str_replace('&nbsp;', '[nbsp]', $string);

        $useInternalErrors = libxml_use_internal_errors(true);
        $disableEntityLoader = libxml_disable_entity_loader(true);

        $dom = new DOMDocument('1.0', $charset);
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->encoding = $charset;
        $dom->loadHTML($string, LIBXML_COMPACT);

        libxml_use_internal_errors($useInternalErrors);
        libxml_disable_entity_loader($disableEntityLoader);

        return $dom;
    }

    /**
     * Returns the HTML content of the DOM node (aka inner HTML).
     *
     * @param \DOMNode $domNode DOM node
     * @return string Inner HTML content of the DOM node
     */
    function getInnerHtml(DOMNode $domNode)
    {
        $innerHtml = '';

        foreach ($domNode->childNodes as $child) {
            $innerHtml .= trim($child->ownerDocument->saveHTML($child));
        }

        return trim(str_replace('[nbsp]', '&nbsp;', $innerHtml));
    }

    /**
     * Replaces DOM node from the owned DOM document with the provided HTML string (aka outer HTML).
     *
     * @param \DOMNode $domNode DOM node to be replaced
     * @param string $htmlFragment String containing HTML
     */
    function replaceNodeWith(DOMNode $domNode, $htmlFragment)
    {
        $dom = $this->create($htmlFragment);

        $fragment = $dom->createDocumentFragment();

        $bodyElement = $dom->getElementsByTagName('body')->item(0);

        foreach ($bodyElement->childNodes as $childNode) {
            $fragment->appendChild($childNode);
        }

        $newNode = $domNode->ownerDocument->importNode($fragment, true);

        $domNode->parentNode->replaceChild($newNode, $domNode);
    }

    /**
     * Removes node from the owned DOM document.
     *
     * @param \DOMNode $domNode DOM node to remove
     */
    function removeNode(DOMNode $domNode)
    {
        $parentNode = $domNode->parentNode;

        if ($parentNode !== null) {
            $parentNode->removeChild($domNode);
        }
    }

    /**
     * Clears DOM document from JavaScript.
     *
     * Removes all 'script' DOM elements.
     * Removes all event based on* attributes on each DOM element.
     * Replaces all bookmarklet links with the dummy anchor hash.
     *
     * @param \DOMDocument $doc DOM document to clear from JavaScript
     */
    function clearFromJavaScript(\DOMDocument $doc)
    {
        /** @var \DOMElement $domElement */
        foreach ($doc->getElementsByTagName('script') as $domElement) {
            $this->removeNode($domElement);
        }

        /** @var \DOMElement $domElement */
        foreach ($doc->getElementsByTagName('*') as $domElement) {
            /** @var \DOMAttr $domAttribute */
            foreach ($domElement->attributes as $domAttribute) {
                $attributeName = trim(strtolower($domAttribute->name));

                if (strpos($attributeName, 'on') === 0) {
                    $domElement->removeAttribute($domAttribute->name);
                }

                if (strtolower($domElement->tagName) === 'a' && $domElement->hasAttribute('href')) {
                    $href = trim(strtolower($domElement->getAttribute('href')));

                    if (strpos($href, 'javascript:') === 0) {
                        $domElement->setAttribute('href', '#');
                    }
                }
            }
        }
    }
}