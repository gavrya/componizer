<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 11/9/15
 * Time: 8:30 PM
 */

namespace Gavrya\Componizer\Content;


use DOMXPath;
use Gavrya\Componizer\Componizer;
use Gavrya\Componizer\Helper\DomHelper;

/**
 * Class ContentParser is used for parsing "editor content".
 *
 * @package Gavrya\Componizer\Content
 */
class ContentParser implements ContentParserInterface
{

    /**
     * @var Componizer
     */
    private $componizer = null;

    //-----------------------------------------------------
    // Constructor section
    //-----------------------------------------------------

    /**
     * ContentParser constructor.
     *
     * @param Componizer $componizer
     */
    public function __construct(Componizer $componizer)
    {
        $this->componizer = $componizer;

        ini_set('xdebug.max_nesting_level', 500);
    }

    //-----------------------------------------------------
    // Content parsing section
    //-----------------------------------------------------

    /**
     * Parses provided "editor content" to the "display content" representation.
     *
     * Parsing is processed based on allowed/disabled plugins/components and other settings from SettingsManager.
     *
     * @param string $editorContent
     * @return string
     */
    public function parseDisplayContent($editorContent)
    {
        $editorContent = is_string($editorContent) ? trim($editorContent) : null;

        if ($editorContent === null || empty($editorContent)) {
            return '';
        }

        /** @var DomHelper $domHelper */
        $domHelper = $this->componizer->resolve(DomHelper::class);

        $doc = $domHelper->createDoc($editorContent);
        $docRoot = $domHelper->getDocRoot($doc);

        /** @var WidgetParser $widgetParser */
        $widgetParser = $this->componizer->resolve(WidgetParser::class);

        $widgetElement = $widgetParser->findWidgetElement($doc, $docRoot);

        if ($widgetElement === null) {
            return $editorContent;
        }

        $widgetParser->replaceWidgetElementContent($widgetElement, $this);

        return $this->parseDisplayContent($domHelper->getInnerHtml($docRoot));
    }

}