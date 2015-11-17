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

class DomHelper
{

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
        $dom->loadHTML($string, LIBXML_COMPACT);

        libxml_use_internal_errors($useInternalErrors);
        libxml_disable_entity_loader($disableEntityLoader);

        return $dom;
    }

    function getInnerHtml(DOMNode $domElement)
    {
        $innerHtml = '';

        foreach ($domElement->childNodes as $child) {
            $innerHtml .= $child->ownerDocument->saveHtml($child);
        }

        return trim(str_replace('[nbsp]', '&nbsp;', $innerHtml));
    }

    function replaceWith(DOMNode $domElement, $htmlFragment)
    {
        $dom = $this->create($htmlFragment);

        $fragment = $dom->createDocumentFragment();

        $bodyElement = $dom->getElementsByTagName('body')->item(0);

        foreach($bodyElement->childNodes as $childNode) {
            $fragment->appendChild($childNode);
        }

        $newNode = $domElement->ownerDocument->importNode($fragment, true);

        $domElement->parentNode->replaceChild($newNode, $domElement);
    }

    function remove(DOMNode $domElement)
    {
        $domElement->parentNode->removeChild($domElement);
    }
}