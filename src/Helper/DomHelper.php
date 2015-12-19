<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 11/15/15
 * Time: 7:55 PM
 */

namespace Gavrya\Componizer\Helper;


use DOMAttr;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMNodeList;

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
     * @param string $htmlFragment String containing HTML fragment
     * @return DOMDocument Newly created DOM document
     */
    public function createDoc($htmlFragment)
    {
        $charset = 'UTF-8';

        $htmlFragment = '<!DOCTYPE html>
                   <html>
                        <head><meta http-equiv="content-type" content="text/html; charset=' . $charset . '"></head>
                        <body>' . $htmlFragment . '</body>
                   </html>';

        $htmlFragment = mb_convert_encoding($htmlFragment, 'HTML-ENTITIES', $charset);
        $htmlFragment = str_replace('&nbsp;', '[nbsp]', $htmlFragment);

        $useInternalErrors = libxml_use_internal_errors(true);
        $disableEntityLoader = libxml_disable_entity_loader(true);

        $dom = new DOMDocument('1.0', $charset);
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->encoding = $charset;
        $dom->loadHTML($htmlFragment);

        libxml_use_internal_errors($useInternalErrors);
        libxml_disable_entity_loader($disableEntityLoader);

        return $dom;
    }

    /**
     * Returns root element of a DOM document created by createDoc() method.
     *
     * @see createDoc
     *
     * @param DOMDocument $doc Document to search root element for
     * @return DOMElement|null Document root element, null otherwise
     */
    public function getDocRoot(DOMDocument $doc)
    {
        return $doc->getElementsByTagName('body')->item(0);
    }

    /**
     * Returns the HTML content of the DOM node (aka inner HTML).
     *
     * @param DOMNode $domNode DOM node
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
     * @param DOMNode $domNode DOM node to be replaced
     * @param string $htmlFragment String containing HTML
     */
    function replaceNodeWith(DOMNode $domNode, $htmlFragment)
    {
        $dom = $this->createDoc($htmlFragment);

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
     * @param DOMNode $domNode DOM node to remove
     */
    function removeNode(DOMNode $domNode)
    {
        $parentNode = $domNode->parentNode;

        if ($parentNode !== null) {
            $parentNode->removeChild($domNode);
        }
    }

    /**
     * Clears HTML fragment from JavaScript.
     *
     * Removes all 'script' HTML elements.
     * Removes all event based on* attributes from each HTML element.
     * Replaces all bookmarklet links href values with the dummy anchor hash.
     *
     * @param string $htmlFragment HTML fragment that need to be cleared from JavaScript
     * @return string HTML fragment cleared from JavaScript
     */
    function clearHtmlFromJavaScript($htmlFragment)
    {
        if (empty($htmlFragment) || !is_string($htmlFragment)) {
            return '';
        }

        $doc = $this->createDoc($htmlFragment);

        /** @var DOMNodeList $nodeList */
        $nodeList = $doc->getElementsByTagName('*');

        for ($i = $nodeList->length; --$i >= 0;) {
            /** @var DOMElement $domElement */
            $domElement = $nodeList->item($i);

            $tagName = strtolower(trim($domElement->tagName));

            if ($tagName === 'script') {
                $this->removeNode($domElement);

                continue;
            }

            /** @var DOMAttr $domAttribute */
            foreach ($domElement->attributes as $domAttribute) {
                $attributeName = strtolower(trim($domAttribute->name));

                if (strpos($attributeName, 'on') === 0) {
                    $domElement->removeAttribute($domAttribute->name);
                } elseif ($tagName === 'a' && $attributeName === 'href') {
                    $href = strtolower(trim($domElement->getAttribute($domAttribute->name)));

                    if (strpos($href, 'javascript:') === 0) {
                        $domElement->setAttribute($domAttribute->name, '#');
                    }
                }
            }
        }

        return $this->getInnerHtml($this->getDocRoot($doc));
    }
}