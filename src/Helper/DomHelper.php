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
     * Returns the inner HTML content of an element.
     *
     * @param \DOMNode $domElement DOM element
     * @return string Inner HTML content of the DOM element
     */
    function getInnerHtml(DOMNode $domElement)
    {
        $innerHtml = '';

        foreach ($domElement->childNodes as $child) {
            $innerHtml .= trim($child->ownerDocument->saveHTML($child));
        }

        return trim(str_replace('[nbsp]', '&nbsp;', $innerHtml));
    }

    /**
     * Replaces an element from the owned DOM document with the provided HTML string.
     *
     * @param \DOMNode $domElement DOM element to be replaced
     * @param string $htmlFragment String containing HTML
     */
    function replaceWith(DOMNode $domElement, $htmlFragment)
    {
        $dom = $this->create($htmlFragment);

        $fragment = $dom->createDocumentFragment();

        $bodyElement = $dom->getElementsByTagName('body')->item(0);

        foreach ($bodyElement->childNodes as $childNode) {
            $fragment->appendChild($childNode);
        }

        $newNode = $domElement->ownerDocument->importNode($fragment, true);

        $domElement->parentNode->replaceChild($newNode, $domElement);
    }

    /**
     * Removes an element from the owned DOM document.
     *
     * @param \DOMNode $domElement DOM element to remove
     */
    function remove(DOMNode $domElement)
    {
        $parentNode = $domElement->parentNode;

        if ($parentNode !== null) {
            $parentNode->removeChild($domElement);
        }
    }
}