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
 * Class DomHelper contains helpfull methods for working with DOM document and its elements.
 *
 * @package Gavrya\Componizer\Helper
 */
class DomHelper
{

    /**
     * Creates a new DOM document from a UTF-8 encoded string.
     *
     * @param string $htmlFragment
     * @return DOMDocument
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
     * @param DOMDocument $doc
     * @return DOMElement|null
     */
    public function getDocRoot(DOMDocument $doc)
    {
        return $doc->getElementsByTagName('body')->item(0);
    }

    /**
     * Returns the HTML content of the DOM node (aka inner HTML).
     *
     * @param DOMNode $domNode
     * @return string
     */
    function getInnerHtml(DOMNode $domNode)
    {
        $innerHtml = '';

        foreach ($this->getDomNodes($domNode->childNodes) as $child) {
            $innerHtml .= trim($child->ownerDocument->saveHTML($child));
        }

        return trim(str_replace('[nbsp]', '&nbsp;', $innerHtml));
    }

    /**
     * Replaces DOM node from the owned DOM document with the provided HTML string (aka outer HTML).
     *
     * @param DOMNode $domNode
     * @param string $htmlFragment
     */
    function replaceNodeWith(DOMNode $domNode, $htmlFragment)
    {
        $doc = $this->createDoc($htmlFragment);
        $docRoot = $this->getDocRoot($doc);
        $fragment = $doc->createDocumentFragment();

        foreach($this->getDomNodes($docRoot->childNodes) as $childNode) {
            $fragment->appendChild($childNode);
        }

        $newNode = $domNode->ownerDocument->importNode($fragment, true);

        $domNode->parentNode->replaceChild($newNode, $domNode);
    }

    /**
     * Returns array of nodes in node list.
     *
     * @param DOMNodeList $nodeList
     * @return DOMNode[]
     */
    function getDomNodes(DOMNodeList $nodeList)
    {
        $nodes = [];

        foreach ($nodeList as $node) {
            $nodes[] = $node;
        }

        return $nodes;
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
     * Returns first found child element with provided attribute name.
     *
     * @param DOMNode $domNode
     * @param string $attributeName
     * @return DOMElement|null
     */
    function findFirstChildByAttribute(DOMNode $domNode, $attributeName)
    {
        foreach ($this->getDomNodes($domNode->childNodes) as $childNode) {
            if ($childNode instanceof DOMElement && $childNode->hasAttribute($attributeName)) {
                return $childNode;
            }
        }

        return null;
    }

    /**
     * Clears HTML fragment from JavaScript.
     *
     * Removes all 'script' HTML elements.
     * Removes all event based on* attributes from each HTML element.
     * Replaces all bookmarklet links href values with the dummy anchor hash.
     *
     * @param string $htmlFragment
     * @return string
     */
    function clearHtmlFromJavaScript($htmlFragment)
    {
        if (empty($htmlFragment) || !is_string($htmlFragment)) {
            return '';
        }

        $doc = $this->createDoc($htmlFragment);

        /** @var DOMNodeList $nodeList */
        $nodeList = $doc->getElementsByTagName('*');

        foreach($this->getDomNodes($nodeList) as $domElement) {
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